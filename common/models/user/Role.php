<?php

namespace common\models\user;

use Yii;
use \common\models\user\base\Role as BaseRole;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "ohrm_user_role".
 */
class Role extends BaseRole
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
