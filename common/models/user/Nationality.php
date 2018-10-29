<?php

namespace common\models\user;

use Yii;
use \common\models\user\base\Nationality as BaseNationality;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "ohrm_nationality".
 */
class Nationality extends BaseNationality
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
