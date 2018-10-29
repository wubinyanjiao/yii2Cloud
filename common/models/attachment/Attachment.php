<?php

namespace common\models\attachment;

use Yii;
use \common\models\attachment\base\Attachment as BaseAttachment;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "hs_hr_emp_attachment".
 */
class Attachment extends BaseAttachment
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
