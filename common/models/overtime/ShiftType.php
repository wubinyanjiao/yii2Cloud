<?php

namespace common\models\overtime;

use Yii;
use \common\models\overtime\base\ShiftType as BaseShiftType;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "ohrm_work_shift_type".
 */
class ShiftType extends BaseShiftType
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
