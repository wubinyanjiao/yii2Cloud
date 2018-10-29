<?php

namespace common\models\thesis;

use Yii;
use \common\models\thesis\base\Publication as BasePublication;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "hs_hr_thesis_publication".
 */
class Publication extends BasePublication
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
