<?php

namespace common\models\system;

use Yii;
use \common\models\system\base\UserLoginLog as BaseUserLoginLog;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "ohrm_user_login_log".
 */
class UserLoginLog extends BaseUserLoginLog
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


    public function CreateUserLoginLog($username,$content,$empNumber=null){

        $loginLog = new UserLoginLog();
        if($empNumber){
            $loginLog->emp_number = $empNumber;
        }
        $loginLog->user_name = $username;
        $loginLog->create_date = date('Y-m-d H:i:s');
        $loginLog->content = '(YII)'.$content;
        $loginLog->save();
    }
    public function getLogOrderBy($limit = 100,$offset =100){
        $query = UserLoginLog::find();
        $query->orderBy('create_date desc');        

        $query->offset($offset);
        $query->limit($limit);
        $list = $query->asArray()->all();
        return $list;
    }

}
