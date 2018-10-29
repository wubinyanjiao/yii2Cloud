<?php

namespace common\models\emptitle;

use Yii;
use \common\models\emptitle\base\Type as BaseType;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "hs_hr_title_type".
 */
class Type extends BaseType
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
