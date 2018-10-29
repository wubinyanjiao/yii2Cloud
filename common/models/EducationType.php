<?php

namespace common\models;

use Yii;
use \common\models\base\EducationType as BaseEducationType;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "hs_hr_education_type".
 */
class EducationType extends BaseEducationType
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
