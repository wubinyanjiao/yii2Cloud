<?php

namespace common\models\employee;

use Yii;
use \common\models\employee\base\EmpTermination as BaseEmpTermination;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "ohrm_emp_termination".
 */
class EmpTermination extends BaseEmpTermination
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
