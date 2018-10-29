<?php

namespace common\models\overtime;

use Yii;
use \common\models\overtime\base\OvertimeComment as BaseOvertimeComment;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "ohrm_overtime_comment".
 */
class OvertimeComment extends BaseOvertimeComment
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
