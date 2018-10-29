<?php
// This class was automatically generated by a giiant build task
// You should not change it manually as it will be overwritten on next build

namespace common\models\teach\base;

use Yii;

/**
 * This is the base-model class for table "ohrm_degree".
 *
 * @property integer $id
 * @property string $name
 * @property string $aliasModel
 */
abstract class Degree extends \yii\db\ActiveRecord
{



    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'ohrm_degree';
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
            [['id', 'name'], 'required'],
            [['id'], 'string', 'max' => 4],
            [['name'], 'string', 'max' => 100],
            [['id'], 'unique']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeHints()
    {
        return array_merge(parent::attributeHints(), [
            'name' => '学位名称',
        ]);
    }




}