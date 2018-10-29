<?php

namespace common\models\shift;

use Yii;
use \common\models\shift\base\ShiftRotary as BaseShiftRotary;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "ohrm_work_shift_rotary".
 */
class ShiftRotary extends BaseShiftRotary
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
