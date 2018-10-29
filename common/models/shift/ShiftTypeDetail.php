<?php

namespace common\models\shift;

use Yii;
use \common\models\shift\base\ShiftTypeDetail as BaseShiftTypeDetail;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "ohrm_work_shift_type_detail".
 */
class ShiftTypeDetail extends BaseShiftTypeDetail
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

    public function addDetail($data){
        if ($this->load($data) && $this->save()){
            return true;
        }else {
             return false;
        }
    }


    public function addDetail2($shift_date,$start_time,$end_time,$confirm_emp,$schedule_id,$shift_result_id,$status,$time_mark,$shift_type_id){

        $data['ShiftTypeDetail']['shift_date']=$shift_date ;
        $data['ShiftTypeDetail']['start_time']=$start_time ;
        $data['ShiftTypeDetail']['end_time']=$end_time ;
        $data['ShiftTypeDetail']['emp_number']=$confirm_emp ;
        $data['ShiftTypeDetail']['schedule_id']=$schedule_id ;
        $data['ShiftTypeDetail']['shift_result_id']=$shift_result_id ;
        $data['ShiftTypeDetail']['status']=$status ;
        $data['ShiftTypeDetail']['time_mark']=$time_mark ;
        $data['ShiftTypeDetail']['shift_type_id']=$shift_type_id;


        if ($this->load($data) && $this->save()){
            return true;
        }else {
             return false;
        }
    }

    /**
     * @author 吴斌  2018/4/3 修改 
     * 获取临时表中信息
     * @return array $date_format   规范化数组 
     */
    public function getShitReslutFromTemp($empId,$shiftDate){

        $data = self::find()->where(['emp_number' => $empId])->andWhere(['shift_date'=>$shiftDate])->orderBy('id ASC')->asArray()->all();
        return $data;
    }

    /**
     * @author 吴斌  2018/4/3 修改 
     * 获取临时表中信息
     * @param string $shift_date   日期
     * @param string $shift_type_id  班次ID
     * @return array $date_format   规范化数组 
     */
     public function getReslutFromTempByType($shift_date,$shift_type_id) {

        $data = self::find()->where(['shift_type_id' => $shift_type_id])->andWhere(['shift_date'=>$shift_date])->orderBy('id ASC')->asArray()->all();
        return $data;
    }

    /**
     * @author 吴斌  2018/4/3 修改 
     * 获取临时表中信息
     * @param string $shift_date   日期
     * @param string $shift_type_id  班次ID
     * @param string $time_mark   被调班时间段日期
     * @param string $schedule_id  计划idID
     * @param string $emp   员工id
     * @return array $date_format   规范化数组 
     */
    public function getReslutFromTempByTypeAndMark($shift_date,$shift_type_id,$time_mark,$schedule_id,$emp='') {


        $data = self::find()->where(['shift_type_id' => $shift_type_id,'shift_date'=>$shift_date,'time_mark'=>$time_mark,'schedule_id'=>$schedule_id]);

        if(!empty($emp)){
            $data->andWhere('emp_number = ?',$emp);
        }
        $data=$data->asArray()->all();
        return $data;
    }

    /**
     * @author 吴斌  2018/4/3 修改 
     * 获取临时表中信息
     * @param string $schedule_id  计划id
     * @param string $shift_date   日期
     * @param string $time_mark   被调班时间段日期
     * @param string $emp   员工id
     * @return array $date_format   规范化数组 
     */
    public function getReslutFromTempEntity($schedule_id,$shift_date,$time_mark,$emp) {

        $data = self::find()->where(['shift_date'=>$shift_date,'time_mark'=>$time_mark,'emp_number'=>$emp,'schedule_id'=>$schedule_id])->one();
        return $data;
    }

    public function getDetailByScheduleId($schedule_id) {

        $data = self::find()->where(['schedule_id'=>$schedule_id])->asArray()->all();
        return $data;
   
    }

    public function deleteTypeDetail($idList) {

        if(is_array($idList)){
            $query=self::deleteAll(['in', 'id', $idList]);
        }else{
            $query=self::deleteAll(['id'=>$idList]);
        }

   
    }
}
