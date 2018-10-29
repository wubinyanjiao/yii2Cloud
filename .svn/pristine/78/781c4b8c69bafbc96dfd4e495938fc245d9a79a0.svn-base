<?php

namespace common\models\pim;

use Yii;
use \common\models\pim\base\Employee as BaseEmployee;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "hs_hr_employee".
 */
class Employee extends BaseEmployee
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
