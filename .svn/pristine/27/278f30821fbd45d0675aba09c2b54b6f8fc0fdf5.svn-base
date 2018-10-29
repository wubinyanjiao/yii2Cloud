<?php

namespace common\models\shift;

use Yii;
use \common\models\shift\base\ShiftDate as BaseShiftDate;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "ohrm_work_shift_date".
 */
class ShiftDate extends BaseShiftDate
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

    public function add($data){
       if ($this->load($data) && $this->save()) {
            return true;
        }
        return false;
    }

    public function getDatesBySchedule($schedule_id){
        $data = self::find()->select('id,shift_date')->where('schedule_id = :sid', [':sid' => $schedule_id])->orderBy('id ASC')->asArray()->all();
        return $data;
    }

     public function getShiftDateListBySchedule($schedule_id){
        $data = self::find()
        ->select('id,shift_date')
        ->where(['in','schedule_id',$schedule_id])
        ->orderBy('id ASC')
        ->asArray()
        ->all();
        return $data;
    }

    


    public function getDateShifts($schedule_id){
        $query = self::find()->with('shift')->where('schedule_id = :sid', [':sid' => $schedule_id])->asArray()->all();
        return $query;
    }

    /**
     * @author 吴斌  2018/1/11 修改 
     * 根据日期ID查询该日期下的班次信息
     * @param int $workstation 小组id
     * @param int $shift_date 日期
     * @return object | 对象
     */
    public function getShiftDateListByStation($workstation,$shift_date){
        $query = self::find()->where('work_station = :sid', [':sid' => $workstation])->andWhere(['>=','shift_date',$shift_date])->asArray()->all();
        return $query;
    }
    /**
     * @author 吴斌  2018/1/11 修改 
     * 查找计划日期
     * @param int $schedulelist 班次计划组
     * @return object | 对象
     */
    public function getShiftDateLists($schedulelist){
        $query = self::find()->where( ['in','schedule_id',$schedulelist])->orderBy('shift_date ASC')->asArray()->all();
        return $query;
    }

    /**
     * @author 吴斌  2018/1/11 修改 
     * 根据日期ID查询该日期下的班次信息
     * @param int $dateId 日期ID
     * @return object | 对象
     */
    public function getShiftDateById($dateId) {

        $query = self::find()->with('shift')->where('id = :dateId', [':dateId' => $dateId])->one();
        return $query;
    }

    /**
     * @author 吴斌  2018/1/11 修改 
     * 根据日期ID查询日期信息
     * @param int $dateId 日期ID
     * @return object | 对象
     */
    public function getShiftDateAgoById($dateId) {

        $query = self::find()->where('id = :dateId', [':dateId' => $dateId])->one();
        return $query;
    }


}
