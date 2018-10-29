<?php

namespace common\models;

use Yii;
use \common\models\base\ConfigCustomer as BaseConfigCustomer;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "config_customer".
 */
class ConfigCustomer extends BaseConfigCustomer
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
