<?php
// This class was automatically generated by a giiant build task
// You should not change it manually as it will be overwritten on next build

namespace common\models\overtime\base;

use Yii;

/**
 * This is the base-model class for table "ohrm_overtime_list".
 *
 * @property string $id
 * @property integer $emp_number
 * @property string $creat_time
 * @property string $stat_time
 * @property string $end_time
 * @property double $time_differ
 * @property double $hour_differ
 * @property string $current_day
 * @property string $end_day
 * @property string $content
 * @property integer $is_holiday
 * @property integer $status
 * @property string $operation_name
 * @property integer $is_pro
 *
 * @property \common\models\overtime\OhrmOvertimeComment[] $ohrmOvertimeComments
 * @property \common\models\overtime\HsHrEmployee $empNumber
 * @property string $aliasModel
 */
abstract class Overtime extends \yii\db\ActiveRecord
{



    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'ohrm_overtime_list';
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
            [['emp_number'], 'required'],
            [['emp_number'], 'integer'],
            [['creat_time', 'stat_time', 'end_time', 'current_day', 'end_day'], 'safe'],
            [['time_differ', 'hour_differ'], 'number'],
            [['content'], 'string', 'max' => 255],
            //[['is_holiday', 'status', 'is_pro'], 'integer'],
            //[['operation_name'], 'string', 'max' => 50],
            [['emp_number'], 'exist', 'skipOnError' => true, 'targetClass' => \common\models\employee\Employee::className(), 'targetAttribute' => ['emp_number' => 'emp_number']]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'emp_number' => 'Emp Number',
            'creat_time' => 'Creat Time',
            'stat_time' => 'Stat Time',
            'end_time' => 'End Time',
            'time_differ' => 'Time Differ',
            'hour_differ' => 'Hour Differ',
            'current_day' => 'Current Day',
            'end_day' => 'End Day',
            'content' => 'Content',
            'is_holiday' => 'Is Holiday',
            'status' => 'Status',
            'operation_name' => 'Operation Name',
            'is_pro' => 'Is Pro',
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeHints()
    {
        return array_merge(parent::attributeHints(), [
            'emp_number' => '员工id',
            'creat_time' => '创建时间',
            'stat_time' => '加班开始时间 小时分',
            'end_time' => '加班结束时间 小时分',
            'time_differ' => '加班时间差(工作日)',
            'hour_differ' => '加班时间差(小时)',
            'current_day' => '当前加班日期',
            'end_day' => '加班结束日期',
            'content' => '注释说明',
            'is_holiday' => '是否转休假 1是',
            'status' => '状态 1常 2取消 3删除',
            'operation_name' => '操作人名称',
            'is_pro' => '是否已审核 1是',
        ]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOhrmOvertimeComments()
    {
        return $this->hasMany(\common\models\overtime\OvertimeComment::className(), ['overtime_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEmployee()
    {
        return $this->hasOne(\common\models\employee\Employee::className(), ['emp_number' => 'emp_number']);
    }




}
