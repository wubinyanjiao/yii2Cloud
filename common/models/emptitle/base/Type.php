<?php
// This class was automatically generated by a giiant build task
// You should not change it manually as it will be overwritten on next build

namespace common\models\emptitle\base;

use Yii;

/**
 * This is the base-model class for table "hs_hr_title_type".
 *
 * @property integer $id
 * @property integer $fu_id
 * @property string $name
 * @property integer $level
 * @property string $aliasModel
 */
abstract class Type extends \yii\db\ActiveRecord
{



    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'hs_hr_title_type';
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
            [['fu_id', 'level'], 'integer'],
            [['name'], 'string', 'max' => 100]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'fu_id' => 'Fu ID',
            'name' => 'Name',
            'level' => 'Level',
        ];
    }




}
