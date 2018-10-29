<?php
// This class was automatically generated by a giiant build task
// You should not change it manually as it will be overwritten on next build

namespace common\models\base;

use Yii;

/**
 * This is the base-model class for table "ohrm_leave_entitlement_log".
 *
 * @property integer $id
 * @property integer $emp_number
 * @property integer $entitlement_type
 * @property integer $entitlement_id
 * @property string $create_time
 * @property integer $status
 * @property string $days
 * @property string $no_of_days
 * @property string $note
 * @property string $create_by_name
 * @property integer $create_by_id
 * @property string $aliasModel
 */
abstract class LeaveEntitlementLog extends \yii\db\ActiveRecord
{



    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'ohrm_leave_entitlement_log';
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
            [['emp_number', 'entitlement_id', 'create_by_id'], 'integer'],
            [['create_time'], 'safe'],
            [['days', 'no_of_days'], 'number'],
            [['entitlement_type', 'status'], 'integer', 'max' => 4],
            [['note'], 'string', 'max' => 250],
            [['create_by_name'], 'string', 'max' => 50]
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
            'entitlement_type' => 'Entitlement Type',
            'entitlement_id' => 'Entitlement ID',
            'create_time' => 'Create Time',
            'status' => 'Status',
            'days' => 'Days',
            'no_of_days' => 'No Of Days',
            'note' => 'Note',
            'create_by_name' => 'Create By Name',
            'create_by_id' => 'Create By ID',
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeHints()
    {
        return array_merge(parent::attributeHints(), [
            'status' => '状态 1 新增 2减',
            'days' => '天数',
            'no_of_days' => '变更后的天数',
        ]);
    }




}
