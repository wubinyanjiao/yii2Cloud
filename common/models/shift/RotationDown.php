<?php

namespace common\models\shift;

use Yii;
use \common\models\shift\base\RotationDown as BaseRotationDown;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "ohrm_rotationdown".
 */
class RotationDown extends BaseRotationDown
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
