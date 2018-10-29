<?php

namespace common\models\RotaryRecord;

use Yii;
use \common\models\rotaryrecord\base\RotaryRecord as BaseAttendanceRecord;
use yii\helpers\ArrayHelper;

use common\models\shift\ShiftResult;
use common\models\shift\Schedule;
use common\models\shift\ShiftType;
use common\models\shift\ShiftTypeDetail;

//ohrm_work_rotary_record
/**
 * This is the model class for table "ohrm_attendance_record".
 */
class RotaryRecord extends BaseAttendanceRecord
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
     * 获取所有的轮转列表
     */
    public function RotaryRecord(){
        $query = RotaryRecord::find()->all();
        return $query;
 /*       $query = ShiftResult::find();
        $query->joinWith('schedule');
        $query->joinWith('shiftType');

        if(!empty($search['empNumber'])){
            $query->andWhere('ohrm_work_shift_result.emp_number = :empNumber',[':empNumber'=>$search['empNumber']]);
        }
        if(!empty($search['date'])){
            $query->andWhere("ohrm_work_shift_result.shift_date = :date",[':date'=>$search['date']]);
        }

         $query->andWhere('ohrm_work_schedule.is_show = 1');
         $query->andWhere('ohrm_work_schedule.is_confirm = 1');
        

        $list = $query->asArray()->one();
        return $list;*/
    }
}
