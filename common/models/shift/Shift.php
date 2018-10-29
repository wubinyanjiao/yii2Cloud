<?php

namespace common\models\shift;

use Yii;
use \common\models\shift\base\Shift as BaseShift;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "ohrm_work_shift".
 */
class Shift extends BaseShift
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

    public function addShifts($data){
        if ($this->load($data) && $this->save()){
            return true;
        }else {
             return false;
        }
    }

    public function getShiftsBySchedule($schedule_id){
        $query = self::find()->with('shiftType')->where('schedule_id = :sid', [':sid' => $schedule_id])->orderBy('id desc')->asArray()->all();
        return $query;
    }

   

    public function getShiftByTypeAndDate($typeid,$dateid){
        $query = self::find()->with('shiftType')->where('shift_type_id = :tid', [':tid' => $typeid])->andWhere('shiftdate_id = :dateid', [':dateid' => $dateid])->orderBy('id desc')->one();
        return $query;
    }

    public function getShiftByTypeAndSchedule($typeid,$schedule_id){
        $query = self::find()->with('shiftType')->where('shift_type_id = :tid', [':tid' => $typeid])->andWhere('schedule_id = :sid', [':sid' => $schedule_id])->orderBy('id desc')->asArray()->all();
        return $query;
    }


    public function getShiftByDateAgo($dateid) {

        $query = self::find()->where('shiftdate_id = :dateid', [':dateid' => $dateid])->orderBy('id desc')->asArray()->all();
        return $query;
    }

    /**
     * @author 吴斌  2018/1/11 修改 
     * 获取根据计划ID和日期获取班次
     * @param int $shiftDateId 日期id
     * @param int $scheduleID  计划id
     * @return object | 获取结果数组
     */
    public function getShiftByDate($shiftDateId,$scheduleID) {
        
        $query = self::find()
        ->where('schedule_id = :sid', [':sid' => $scheduleID])
        ->andWhere('shiftdate_id = :dateid', [':dateid' => $shiftDateId])
        ->orderBy('id desc')
        ->asArray()
        ->all();
        return $query;
    }
}
