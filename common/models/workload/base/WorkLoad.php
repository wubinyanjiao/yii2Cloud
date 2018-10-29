<?php
// This class was automatically generated by a giiant build task
// You should not change it manually as it will be overwritten on next build

namespace common\models\workload\base;

use Yii;

/**
 * This is the base-model class for table "ohrm_work_load".
 *
 * @property integer $id
 * @property integer $employee_id
 * @property string $workload
 * @property integer $workweight_id
 * @property string $work_weight
 * @property double $duty_factor
 * @property integer $workcontent_id
 * @property integer $workshift_id
 * @property string $work_date
 * @property integer $check_id
 * @property string $check_name
 * @property string $work_check
 * @property string $check_time
 * @property string $create_time
 * @property string $aliasModel
 */
abstract class WorkLoad extends \yii\db\ActiveRecord
{



    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'ohrm_work_load';
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
            [['employee_id'], 'required'],
            [['employee_id', 'workweight_id', 'workcontent_id', 'workshift_id', 'check_id'], 'integer'],
            [['workload', 'duty_factor', 'work_check'], 'number'],
            [['work_date', 'check_time', 'create_time'], 'safe'],
            [['work_weight'], 'string', 'max' => 100],
            [['check_name'], 'string', 'max' => 50]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'employee_id' => 'Employee ID',
            'workload' => 'Workload',
            'workweight_id' => 'Workweight ID',
            'work_weight' => 'Work Weight',
            'duty_factor' => 'Duty Factor',
            'workcontent_id' => 'Workcontent ID',
            'workshift_id' => 'Workshift ID',
            'work_date' => 'Work Date',
            'check_id' => 'Check ID',
            'check_name' => 'Check Name',
            'work_check' => 'Work Check',
            'check_time' => 'Check Time',
            'create_time' => 'Create Time',
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeHints()
    {
        return array_merge(parent::attributeHints(), [
            'workload' => '工作量',
            'work_weight' => '核对数量和权重系数计算后的工作量',
            'duty_factor' => '工作系数',
            'workshift_id' => '工作班次id',
            'work_date' => '当前时间',
            'check_id' => '核对人id',
            'check_name' => '核对人姓名',
            'work_check' => '核对数量',
            'check_time' => '核对时间',
            'create_time' => '创建时间',
        ]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEmployee()
    {
        return $this->hasOne(\common\models\employee\Employee::className(), ['emp_number' => 'employee_id']);
    }



}
