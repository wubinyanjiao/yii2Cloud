<?php

namespace common\models\employee;

use Yii;
use \common\models\employee\base\Record as BaseRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "ohrm_work_rotary_record".
 */
class Record extends BaseRecord
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
