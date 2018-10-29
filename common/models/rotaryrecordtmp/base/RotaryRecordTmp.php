<?php
// This class was automatically generated by a giiant build task
// You should not change it manually as it will be overwritten on next build

namespace common\models\rotaryrecordtmp\base;

use Yii;

/**
 * This is the base-model class for table "ohrm_work_shift_rotary".
 *
 * @property integer $id
 * @property string $name
 * @property string $date_from
 * @property string $date_to
 * @property integer $first_department_id
 * @property integer $second_department_id
 * @property integer $third_department_id
 * @property string $status
 */
abstract class RotaryRecordTmp extends \yii\db\ActiveRecord
{



    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'ohrm_work_rotary_record_tmp';
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
          //  [['first_department_id','second_department_id','third_department_id','name','date_from','date_to'], 'required'],
           // [['name', 'date_from', 'date_to', 'first_department_id', 'second_department_id', 'third_department_id'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
     /*   return [
            'id' => 'ID',
            'first_department_id' => 'first_department_id',
            'second_department_id' => 'second_department_id',
            'third_department_id' => 'third_department_id',
            'name' => 'name',
            'date_from' => 'date_from',
            'date_to' => 'date_to',
            'status' => 'status',
        ];*/
    }

    /**
     * @inheritdoc
     */
    public function attributeHints()
    {
      /*  return array_merge(parent::attributeHints(), [
            'id' => 'ID',
            'first_department_id' => '第一组ID',
            'second_department_id' => '第二组ID',
            'third_department_id' => '第三组ID',
            'name' => '名称',
            'date_from' => '开始时间',
            'date_to' => '结束时间',
            'status' => '状态 0隐藏/1展示',
        ]);*/
    }




}
