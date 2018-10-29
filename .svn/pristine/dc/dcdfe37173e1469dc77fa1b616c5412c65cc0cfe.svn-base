<?php

namespace common\models\curriculum;

use Yii;
use \common\models\curriculum\base\CurriculumEmployee as BaseCurriculumEmployee;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "ohrm_curriculum_employee".
 */
class CurriculumEmployee extends BaseCurriculumEmployee
{

    public function behaviors()
    {
        return ArrayHelper::merge(
            parent::behaviors(),
            [
                # custom behaviors
            ]
        );
    }

    public function rules()
    {
        return ArrayHelper::merge(
            parent::rules(),
            [
                # custom validation rules
            ]
        );
    }
}
