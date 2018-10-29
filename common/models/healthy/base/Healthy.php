<?php
// This class was automatically generated by a giiant build task
// You should not change it manually as it will be overwritten on next build

namespace common\models\healthy\base;

use Yii;

/**
 * This is the base-model class for table "hs_hr_emp_healthy".
 *
 * @property integer $id
 * @property integer $emp_number
 * @property string $healthy_name
 * @property string $healthy_years
 * @property integer $is_qualified
 * @property string $create_time
 * @property string $aliasModel
 */
abstract class Healthy extends \yii\db\ActiveRecord
{



    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'hs_hr_emp_healthy';
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
            [['emp_number'], 'integer'],
            [['healthy_years', 'create_time'], 'safe'],
            [['healthy_name'], 'string', 'max' => 100],
            [['is_qualified'], 'string', 'max' => 11]
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
            'healthy_name' => 'Healthy Name',
            'healthy_years' => 'Healthy Years',
            'is_qualified' => 'Is Qualified',
            'create_time' => 'Create Time',
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeHints()
    {
        return array_merge(parent::attributeHints(), [
            'emp_number' => '员工id',
            'healthy_name' => '体检项目名称',
            'healthy_years' => '体检年份',
            'is_qualified' => '是否合格',
            'create_time' => '创建时间',
        ]);
    }




}
