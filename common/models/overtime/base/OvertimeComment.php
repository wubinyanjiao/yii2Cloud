<?php
// This class was automatically generated by a giiant build task
// You should not change it manually as it will be overwritten on next build

namespace common\models\overtime\base;

use Yii;

/**
 * This is the base-model class for table "ohrm_overtime_comment".
 *
 * @property string $id
 * @property string $overtime_id
 * @property string $created
 * @property string $created_by_name
 * @property integer $created_by_id
 * @property integer $created_by_emp_number
 * @property string $comments
 *
 * @property \common\models\overtime\OhrmOvertimeList $overtime
 * @property string $aliasModel
 */
abstract class OvertimeComment extends \yii\db\ActiveRecord
{



    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'ohrm_overtime_comment';
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
            [['overtime_id'], 'required'],
            [['overtime_id', 'created_by_id', 'created_by_emp_number'], 'integer'],
            [['created'], 'safe'],
            [['created_by_name', 'comments'], 'string', 'max' => 255],
            [['overtime_id'], 'exist', 'skipOnError' => true, 'targetClass' => \common\models\overtime\Overtime::className(), 'targetAttribute' => ['overtime_id' => 'id']]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'overtime_id' => 'Overtime ID',
            'created' => 'Created',
            'created_by_name' => 'Created By Name',
            'created_by_id' => 'Created By ID',
            'created_by_emp_number' => 'Created By Emp Number',
            'comments' => 'Comments',
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeHints()
    {
        return array_merge(parent::attributeHints(), [
            'overtime_id' => '关联 ohrm_overtime_list id',
            'created' => '创建时间',
            'created_by_name' => '创建人',
            'created_by_id' => '创建人id 关联 ohrm_user id',
            'created_by_emp_number' => '员工id 关联 hs_hr_employee emp_number',
            'comments' => ' 评论内容',
        ]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOvertime()
    {
        return $this->hasOne(\common\models\overtime\Overtime::className(), ['id' => 'overtime_id']);
    }




}
