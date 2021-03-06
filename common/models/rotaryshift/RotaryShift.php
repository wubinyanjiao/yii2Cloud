<?php

namespace common\models\RotaryShift;

use common\models\base\Subunit;
use common\models\RotaryConfrim\RotaryConfrim;
use Yii;
use \common\models\rotaryshift\base\RotaryShift as BaseAttendanceRecord;
use yii\helpers\ArrayHelper;

use common\models\shift\ShiftResult;
use common\models\shift\Schedule;
use common\models\shift\ShiftType;
use common\models\rotationrule\RotationRule;
use common\models\employee\Employee;

//ohrm_work_shift_rotary
/**
 * This is the model class for table "ohrm_attendance_record".
 */
class RotaryShift extends BaseAttendanceRecord
{
    public function behaviors()
    {
        return ArrayHelper::merge(
            parent::behaviors(),
            [
                # custom behaviors
            ]
        );
    }

    public function rules()
    {
        return ArrayHelper::merge(
            parent::rules(),
            [
                # custom validation rules
            ]
        );
    }

    //删除轮转列表
    public function deleteWorkShiftList($rotary_id){
        $query =  self::find()->where("id = :rotary_id",[':rotary_id'=>$rotary_id])->one();
        $query->status = 0;
        $recod = $query->save();   //保存
        return $recod;
    }

    /*
     * 查询轮转部门
     * */
    public function getWorkSHiftList($rotary_id)
    {
        $query = self::find();
        $query->where("id = :rotary_id",[':rotary_id'=>$rotary_id]);
        $query->andWhere(['status' => 1]);
        $list = $query->one();
        return $list;
    }


    /*
     *  查询列表是否有规则
     * */
    public function getDepartmentStatus($id)
    {
        $query = RotationRule::find();
        $query->where("rotaryid = :rotaryid",[':rotaryid'=>$id]);
        $list = $query->one();
        if($list){
            return true;
        }else{
            return false;
        }
    }

    /*
     * 获取所有的轮转列表
     */
    public function getWorkShift(){

        $query = RotaryShift::find()->where(['status' => 1])->all();
        return $query;
    }

    public function getTest($search)
    {

    }

    public function getRoaryShiftUser($search)
    {
        $query = Employee::find();
        $query->select('emp_number,emp_firstname,work_station,emp_retire,emp_gender,
        emp_marital_status,job_title_code,subunit_time,education_id,working_years,is_leader
        ');
        if($search['leader_no_rotary_status']){
            $query->andWhere(['is_leader'=>0]);
        }
        if(!empty($search['work_station'])){
            $query->andWhere("work_station = :work_station",[':work_station'=>$search['work_station']]);
        }

        // 查询已经排过班的人
        $RotaryConfrim = RotaryConfrim::find();
        $RotaryConfrim->select('emp_number,orange_department_id,rotary_department_id');
        $RotaryConfrim->where("rotary_id = :rotary_id",[':rotary_id'=>$search['rotaryid']]);
        $RotaryConfrim->andWhere("orange_department_id = :orange_department_id",[':orange_department_id'=>$search['work_station']]);

        $RotaryConfrim_list = $RotaryConfrim->asArray()->all();

        if($RotaryConfrim_list){
            foreach($RotaryConfrim_list as $val){
                $query->andWhere(['not in','emp_number',$val['emp_number']]);
            }
        }


        //按照时间来选出最合适的人
        $query->orderBy('subunit_time ASC');        //进组时间越小， 则时间越长

        if(!empty($search['averge_mid_level_status'])){
          //  $query->andWhere(['averge_mid_level_status'=>$search['averge_mid_level_status']]);
        }
        if(!empty($search['midlevel_year_count'])){
           // $query->andWhere(['midlevel_year_count'=>$search['midlevel_year_count']]);
        }
        if(!empty($search['min_age_rotary'])){
            //  $query->andWhere(['min_age_rotary'=>$search['min_age_rotary']]);  <60;
        }

        if(!empty($search['rotary_limit_year'])){
            if($search['work_station'] == 11){      //门诊部  需要判断满足 年
                $i = $search['rotary_limit_year'];
                $date_from  = date("Y-m-d", strtotime("-$i years", strtotime($search['date_from'])));
                $query->andWhere(['<', 'subunit_time', $date_from]);
            }
        }
        if(!empty($search['emp_gender'])){
            //筛选只为男士
            $query->andWhere("emp_gender = :emp_gender",[':emp_gender'=>$search['emp_gender']]);
        }

        if(!empty($search['education_id'])){
            //筛选只为研究生
            if($search['education_id'] == 7){
                $query->andWhere("education_id = :education_id",[':education_id'=>$search['education_id']]);
            }else{
                $query->andWhere(['in','education_id',[6,7]]);
            }
        }

        $list = $query->asArray()->one();
        // 通过list来判断是否有查询
        if($list){
            return $list;
        }else{

            $info = RotaryConfrim::find()->select([
                'hs_hr_employee.emp_number',
                'hs_hr_employee.emp_firstname',
                'hs_hr_employee.work_station as w_station',
                'hs_hr_employee.emp_retire',
                'hs_hr_employee.emp_gender',
                'hs_hr_employee.emp_marital_status',
                'hs_hr_employee.job_title_code',
                'hs_hr_employee.subunit_time',
                'hs_hr_employee.education_id',
                'hs_hr_employee.working_years',
                'hs_hr_employee.is_leader',
                'ohrm_work_rotary_confrim.rotary_id',
                'ohrm_work_rotary_confrim.rotary_department_id as work_station'
            ]);
            $info->innerjoin('hs_hr_employee', 'hs_hr_employee.emp_number=ohrm_work_rotary_confrim.emp_number');
            $info->where(['ohrm_work_rotary_confrim.rotary_id'=>$search['rotaryid']]);
          //  $info->andWhere("work_station = :work_station",[':work_station'=>$search['work_station']]);
            $info->andWhere("ohrm_work_rotary_confrim.rotary_department_id = :rotary_department_id",[':rotary_department_id'=>$search['work_station']]);

            //按照时间来选出最合适的人
            $query->orderBy('ohrm_work_rotary_confrim.data_to ASC');        //进组时间越小， 则时间越长

            if(!empty($search['averge_mid_level_status'])){
                //  $query->andWhere(['averge_mid_level_status'=>$search['averge_mid_level_status']]);
            }
            if(!empty($search['midlevel_year_count'])){
                // $query->andWhere(['midlevel_year_count'=>$search['midlevel_year_count']]);
            }
            if(!empty($search['rotary_limit_year'])){
                //$query->andWhere(['rotary_limit_year'=>$search['rotary_limit_year']]);
            }
            if(!empty($search['min_age_rotary'])){
                //  $query->andWhere(['min_age_rotary'=>$search['min_age_rotary']]);
            }
            $user_list = $info->asArray()->one();
            return $user_list;
        }

    }

    /**
     * @author
     * 获取部门中所有小组下的员工
     * @param array $work_station_list 该部门所有小组的id
     * @return array | 班次统计
     */

    public function getEmployeeByList($v){

        $query = Employee::find();

        $query->select('emp_number,emp_firstname,work_station,emp_retire,emp_gender,
        emp_marital_status,job_title_code,subunit_time,education_id,working_years,is_leader
        ');
        $query->where("work_station = :work_station",[':work_station'=>$v]);
        $query->andWhere(['is_leader'=>0]);

        $list = $query->all();
        return $list;

    }
}
