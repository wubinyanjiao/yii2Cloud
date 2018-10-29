<?php

namespace common\models\employee;

use Yii;
use \common\models\employee\base\EmploymentStatus as BaseEmploymentStatus;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "ohrm_employment_status".
 */
class EmploymentStatus extends BaseEmploymentStatus
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
