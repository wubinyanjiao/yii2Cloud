<?php

namespace app\models;

use Yii;
use \app\models\base\Schedule as BaseSchedule;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "ohrm_work_schedule".
 */
class Schedule extends BaseSchedule
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
