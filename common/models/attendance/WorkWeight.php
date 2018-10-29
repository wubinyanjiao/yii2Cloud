<?php

namespace common\models\attendance;

use Yii;
use \common\models\attendance\base\WorkWeight as BaseWorkWeight;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "ohrm_work_weight".
 */
class WorkWeight extends BaseWorkWeight
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
