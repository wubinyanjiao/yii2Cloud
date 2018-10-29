<?php

namespace common\models\system;

use Yii;
use \common\models\system\base\SystemUserRole as BaseSystemUserRole;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "ohrm_user_role".
 */
class SystemUserRole extends BaseSystemUserRole
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
