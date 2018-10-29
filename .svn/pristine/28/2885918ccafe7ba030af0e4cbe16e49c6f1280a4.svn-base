<?php

namespace common\models\RotaryConfrim;

use Yii;
use \common\models\rotaryconfrim\base\RotaryConfrim as BaseAttendanceRecord;
use yii\helpers\ArrayHelper;
use common\models\employee\Employee;
use common\models\shift\ShiftResult;
use common\models\shift\Schedule;
use common\models\shift\ShiftType;
use common\models\shift\ShiftTypeDetail;

//ohrm_work_rotary_confrim
/**
 * This is the model class for table "ohrm_attendance_record".
 */
class RotaryConfrim extends BaseAttendanceRecord
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
    /*
     *查询分组下的用户信息
     */
    public function getWorkConfrim($id){

        $info = self::find()->select([
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
            'ohrm_work_rotary_confrim.*',
            'ohrm_subunit.name'
        ])
            ->innerjoin('hs_hr_employee', 'hs_hr_employee.emp_number=ohrm_work_rotary_confrim.emp_number')
            ->innerjoin('ohrm_subunit', 'ohrm_subunit.id=hs_hr_employee.work_station')
           ->where(['ohrm_work_rotary_confrim.rotary_id'=>$id])->asArray()->all();
        return $info;
    }

        /*
         * 查询该分组下的所有用户
         * education_id   学历ID
         * emp_firstname  姓名
         * emp_retire   退休时间
         * emp_gender   性别   男1  女2
         * emp_marital_status   婚姻状态
         * job_title_code  职称ID
         * working_years 工作年限
         * */
    public function getConfrimList( $work_station, $search = array())
    {
        $query = Employee::find();
        $query->select('
        emp_number,emp_firstname,work_station,emp_retire,emp_gender,
        emp_marital_status,job_title_code,subunit_time,education_id,working_years,is_leader,emp_birthday,
        ohrm_job_title.job_title,
        ohrm_job_title.job_description,
        ohrm_job_title.job_grade,
        ohrm_job_title.note,
        ohrm_education.name,
        ohrm_subunit.name
        ');
        if(!empty($search['leader_no_rotary_status'])){
            $query->andWhere(['is_leader'=>0]);
        }
        $query->innerjoin('ohrm_job_title', 'ohrm_job_title.id=hs_hr_employee.job_title_code');
        $query->innerjoin('ohrm_education', 'ohrm_education.id=hs_hr_employee.education_id');
        $query->innerjoin('ohrm_subunit', 'ohrm_subunit.id=hs_hr_employee.work_station');

        if(!empty($work_station)){
            //$query->andWhere("work_station = :work_station",[':work_station'=>$work_station]);
            $query->andWhere(['in','work_station',$work_station]);
        }else{
            return false;
        }
        if(!empty($search['rotary_limit_year'])){
            if($search['work_station'] == 11){      //门诊部  需要判断满足 年
                $i = $search['rotary_limit_year'];
                $date_from  = date("Y-m-d", strtotime("-$i years", strtotime($search['date_from'])));
                $query->andWhere(['<', 'subunit_time', $date_from]);
            }
        }
        if(!empty($search['emp_gender'])){
            //男士平均分配
            $query->andWhere("emp_gender = :emp_gender",[':emp_gender'=>$search['emp_gender']]);
        }
        if(!empty($search['leader_no_rotary_status'])){
            //组长不参与轮转
                $query->andWhere(['is_leader'=>0]);
        }
        if(!empty($search['education_id'])){
            //筛选只为研究生
            if($search['education_id'] == 7){
                $query->andWhere("education_id = :education_id",[':education_id'=>$search['education_id']]);
            }else{
                $query->andWhere(['in','education_id',[6,7]]);
            }
        }

        $query->andWhere(['ohrm_job_title.is_deleted'=>0]);
        //按照时间来选出最合适的人
        $query->orderBy('subunit_time ASC');        //进组时间越小， 则时间越长

        $list = $query->asArray()->all();
        foreach($list as $list_k=>$list_v){

            if(!empty($search['midlevel_year_count'])) {
                //  中级职称满多长时间轮转 ---------------------
                if($list_v['working_years'] < $search['midlevel_year_count']){
                        unset($list[$list_k]);
                    continue;
                }
            }
            if(!empty($search['min_age_rotary'])) {
                //年龄满足至少多少不轮转到门诊
                $age = $search['date_from'] -  $list_k['emp_birthday'];;   //当前时间 - 生日 = 年龄
                if($search['min_age_rotary'] <= $age){
                    unset($list[$list_k]);
                    continue;
                }

            }
            if(!empty($search['averge_mid_level_status'])) {
                //中级职称平均分配            return $search;
                if($list_v['job_grade'] === '中级' || $list_v['job_grade'] === '高级'){
                    continue;
                }else{
                    unset($list[$list_k]);
                    continue;
                }
            }
        }
            $Confrim = self::find();
        $Confrim->where("rotary_id = :rotary_id",[':rotary_id'=>$search['rotaryid']]);
        $Confrim->andWhere("orange_department_id = :orange_department_id",[':orange_department_id'=>$search['work_station']]);
        $confrim_list = $Confrim->asArray()->all();
        if($confrim_list){
            foreach($confrim_list as $k=>$v){
                $arr[] = $v['emp_number'];
            }
            foreach($list as $key=>$val)
            {
                if(in_array($val['emp_number'],$arr)){
                    unset($list[$key]);
                }
            }
            return current($list);
        }else{
            return current($list);
        }

        //查询出所有的人 以后  筛选合适的人


    }




    public function getWorkConfrimList($id)
    {
        $info = self::find();
        $info->andWhere("rotary_id = :rotary_id",[':rotary_id'=>$id]);
        $query = $info->asArray()->all();
        return $query;
    }

    /*
     * 删除临时数据
     * */
    public function deleteWorkConfrim($id)
    {
        self::deleteAll("rotary_id = :rotary_id",[':rotary_id'=>$id]);
    }
}
