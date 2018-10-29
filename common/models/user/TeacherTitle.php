<?php

namespace common\models\user;

use Yii;
use \common\models\user\base\TeacherTitle as BaseTeacherTitle;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "hs_hr_emp_teacher_title".
 */
class TeacherTitle extends BaseTeacherTitle
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
