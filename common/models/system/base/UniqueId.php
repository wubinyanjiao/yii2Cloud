<?php
// This class was automatically generated by a giiant build task
// You should not change it manually as it will be overwritten on next build

namespace common\models\system\base;

use Yii;

/**
 * This is the base-model class for table "hs_hr_unique_id".
 *
 * @property integer $id
 * @property integer $last_id
 * @property string $table_name
 * @property string $field_name
 * @property string $aliasModel
 */
abstract class UniqueId extends \yii\db\ActiveRecord
{



    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'hs_hr_unique_id';
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
            [['last_id', 'table_name', 'field_name'], 'required'],
            [['last_id'], 'integer'],
            [['table_name', 'field_name'], 'string', 'max' => 50],
            [['table_name', 'field_name'], 'unique', 'targetAttribute' => ['table_name', 'field_name']]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'last_id' => 'Last ID',
            'table_name' => 'Table Name',
            'field_name' => 'Field Name',
        ];
    }




}
