<?php

namespace common\models\employee;

use Yii;
use \common\models\employee\base\Contacts as BaseContacts;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "hs_hr_emp_emergency_contacts".
 */
class Contacts extends BaseContacts
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
