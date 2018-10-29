<?php

namespace common\models\employee;

use Yii;
use \common\models\employee\base\EmpEducation as BaseEmpEducation;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "ohrm_emp_education".
 */
class EmpEducation extends BaseEmpEducation
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
