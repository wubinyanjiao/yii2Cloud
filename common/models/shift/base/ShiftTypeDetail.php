<?php
// This class was automatically generated by a giiant build task
// You should not change it manually as it will be overwritten on next build

namespace common\models\shift\base;

use Yii;

/**
 * This is the base-model class for table "ohrm_work_shift_type_detail".
 *
 * @property integer $id
 * @property string $shift_date
 * @property string $start_time
 * @property string $end_time
 * @property integer $emp_number
 * @property integer $schedule_id
 * @property integer $shift_result_id
 * @property integer $status
 * @property integer $time_mark
 * @property integer $shift_type_id
 * @property string $aliasModel
 */
abstract class ShiftTypeDetail extends \yii\db\ActiveRecord
{



    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'ohrm_work_shift_type_detail';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('orangehrm');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['shift_date', 'start_time', 'end_time'], 'safe'],
            [['emp_number', 'schedule_id', 'shift_result_id', 'status', 'time_mark', 'shift_type_id'], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'shift_date' => 'Shift Date',
            'start_time' => 'Start Time',
            'end_time' => 'End Time',
            'emp_number' => 'Emp Number',
            'schedule_id' => 'Schedule ID',
            'shift_result_id' => 'Shift Result ID',
            'status' => 'Status',
            'time_mark' => 'Time Mark',
            'shift_type_id' => 'Shift Type ID',
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeHints()
    {
        return array_merge(parent::attributeHints(), [
            'shift_date' => '调班的日期',
            'start_time' => '该班次某时间段开始时间',
            'end_time' => '该班次某时间段结束时间',
            'emp_number' => '班次所属员工',
            'schedule_id' => '班次所属计划',
            'shift_result_id' => '班次确认表中该数据的id',
            'status' => '状态',
            'time_mark' => '时间段标示；0全天，1第一个时间段，2第二个时间段',
            'shift_type_id' => '班次id',
        ]);
    }


    public function getShiftResult()
    {
        return $this->hasOne(\common\models\shift\ShiftResult::className(), ['id' => 'shift_type_id']);
    }

    public function getSchedule()
    {
        return $this->hasOne(\common\models\shift\Schedule::className(), ['id' => 'schedule_id']);
    }
    public function getShiftType()
    {
        return $this->hasOne(\common\models\shift\ShiftType::className(), ['id' => 'shift_type_id']);
    }


}