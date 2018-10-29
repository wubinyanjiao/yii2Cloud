<?php
// This class was automatically generated by a giiant build task
// You should not change it manually as it will be overwritten on next build

namespace common\models\curriculum\base;

use Yii;

/**
 * This is the base-model class for table "ohrm_curriculum_problems".
 *
 * @property string $id
 * @property string $curriculum_id
 * @property string $question_id
 *
 * @property \common\models\curriculum\OhrmCurriculum $curriculum
 * @property \common\models\curriculum\OhrmCurriculumQuestions $question
 * @property string $aliasModel
 */
abstract class CurriculumProblems extends \yii\db\ActiveRecord
{



    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'ohrm_curriculum_problems';
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
            [['curriculum_id', 'question_id'], 'required'],
            [['curriculum_id', 'question_id'], 'integer'],
            [['curriculum_id'], 'exist', 'skipOnError' => true, 'targetClass' => \common\models\curriculum\Curriculum::className(), 'targetAttribute' => ['curriculum_id' => 'id']],
            [['question_id'], 'exist', 'skipOnError' => true, 'targetClass' => \common\models\curriculum\CurriculumQuestions::className(), 'targetAttribute' => ['question_id' => 'id']]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'curriculum_id' => 'Curriculum ID',
            'question_id' => 'Question ID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCurriculum()
    {
        return $this->hasOne(\common\models\curriculum\OhrmCurriculum::className(), ['id' => 'curriculum_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getQuestion()
    {
        return $this->hasOne(\common\models\curriculum\OhrmCurriculumQuestions::className(), ['id' => 'question_id']);
    }




}