<?php

namespace common\models\shift;

use Yii;
use \common\models\shift\base\ShiftAssignment as BaseShiftAssignment;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "ohrm_work_shift_assignment".
 */
class ShiftAssignment extends BaseShiftAssignment
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

    
    public function addShiftAssigiment($data){
        if ($this->load($data) && $this->save()){
            return true;
        }else {
             return false;
        }
       
    }

    public function getShiftAssignmentList($schedule_id){
/*
        $query = (new \yii\db\Query())
        ->select(['a.id','a.shift_id','a.shift_date','c.name','c.id skill_id'])
        ->from('orangehrm_mysql.ohrm_work_shift_assignment a')
        ->leftJoin('orangehrm_mysql.hs_hr_emp_skill b','a.emp_number = b.emp_number')
        ->leftJoin('orangehrm_mysql.ohrm_skill c','b.skill_id = c.id')
        ->where(['a.work_station'=>$work_station])
        ->all();
        return $query;*/

        $data = self::find()->select('id,shift_id,shift_date,shift_index')->where('schedule_id = :sid', [':sid' => $schedule_id])->orderBy('shift_date ASC')->asArray()->all();
        return $data;
    }
}
