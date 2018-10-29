<?php

namespace common\models\user;

use Yii;
use \common\models\user\base\UserRole as BaseUserRole;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "ohrm_user_role".
 */
class UserRole extends BaseUserRole
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

    public function getUserRoleById($id){

        $user = UserRole::find()->where(['id'=>$id])->one();

        return $user;

    }
}
