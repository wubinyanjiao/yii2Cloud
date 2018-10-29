<?php

namespace common\models\teach;

use Yii;
use \common\models\teach\base\Education as BaseEducation;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "ohrm_education".
 */
class Education extends BaseEducation
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
