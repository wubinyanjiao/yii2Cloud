<?php
// This class was automatically generated by a giiant build task
// You should not change it manually as it will be overwritten on next build

namespace common\models\shift\base;

use Yii;

/**
 * This is the base-model class for table "ohrm_rotationRule".
 *
 * @property integer $id
 * @property integer $rotationId
 * @property integer $groupId
 * @property string $ruleType
 * @property integer $rotationRuleWarehouseId
 * @property string $select1
 * @property string $select2
 * @property string $input1
 * @property string $input2
 * @property string $aliasModel
 */
abstract class RotationRule extends \yii\db\ActiveRecord
{



    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'ohrm_rotationrule';
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
            [['rotationId', 'groupId', 'rotationRuleWarehouseId'], 'integer'],
            [['ruleType'], 'string', 'max' => 50],
            [['select1', 'input1', 'input2'], 'string', 'max' => 1000],
            [['select2'], 'string', 'max' => 2000]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'rotationId' => 'Rotation ID',
            'groupId' => 'Group ID',
            'ruleType' => 'Rule Type',
            'rotationRuleWarehouseId' => 'Rotation Rule Warehouse ID',
            'select1' => 'Select1',
            'select2' => 'Select2',
            'input1' => 'Input1',
            'input2' => 'Input2',
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeHints()
    {
        return array_merge(parent::attributeHints(), [
            'id' => '主键',
            'rotationId' => '轮转表id(rotationList）',
            'groupId' => '组id',
            'ruleType' => '规则类型：空默认out调出规则in调入规则',
            'rotationRuleWarehouseId' => '轮转规则库种子',
            'select1' => '存放选中的数据 格式json 例{\"value\": [\"1\", \"2\"]}',
            'select2' => '存放选中的数据 格式json',
            'input1' => '存放选中的数据 格式json',
            'input2' => '存放选中的数据 格式json',
        ]);
    }




}