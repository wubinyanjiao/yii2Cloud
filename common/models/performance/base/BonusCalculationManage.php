<?php
// This class was automatically generated by a giiant build task
// You should not change it manually as it will be overwritten on next build

namespace common\models\performance\base;

use Yii;

/**
 * This is the base-model class for table "ohrm_bonus_calculation_manage".
 *
 * @property integer $id
 * @property string $customerId
 * @property string $bonusDate
 * @property integer $isBase
 * @property integer $groupId
 * @property integer $status
 * @property string $sheetName
 * @property string $sendTime
 * @property string $submitTime
 * @property string $confirmTime
 *
 * @property \common\models\performance\OhrmSubunit $group
 * @property string $aliasModel
 */
abstract class BonusCalculationManage extends \yii\db\ActiveRecord
{



    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'ohrm_bonus_calculation_manage';
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
            [['bonusDate', 'sendTime', 'submitTime', 'confirmTime'], 'safe'],
            [['groupId', 'status'], 'integer'],
            [['customerId'], 'string', 'max' => 20],
            [['isBase'], 'integer', 'max' => 4],
            [['sheetName'], 'string', 'max' => 50],
            [['groupId'], 'exist', 'skipOnError' => true, 'targetClass' => \common\models\subunit\Subunit::className(), 'targetAttribute' => ['groupId' => 'id']]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'customerId' => 'Customer ID',
            'bonusDate' => 'Bonus Date',
            'isBase' => 'Is Base',
            'groupId' => 'Group ID',
            'status' => 'Status',
            'sheetName' => 'Sheet Name',
            'sendTime' => 'Send Time',
            'submitTime' => 'Submit Time',
            'confirmTime' => 'Confirm Time',
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeHints()
    {
        return array_merge(parent::attributeHints(), [
            'isBase' => '是否是刚新建未下发 0默认1是',
            'groupId' => '组id',
            'status' => '状态 0默认10已下发未上报11已下发已上报1确认归档   isBase=1时，此状态为综合状态',
            'sheetName' => 'excel名(isBase为1时使用)',
            'sendTime' => '下发时间',
            'submitTime' => '组上报时间',
            'confirmTime' => '确认归档时间',
        ]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSubunit()
    {
        return $this->hasOne(\common\models\subunit\Subunit::className(), ['id' => 'groupId']);
    }




}
