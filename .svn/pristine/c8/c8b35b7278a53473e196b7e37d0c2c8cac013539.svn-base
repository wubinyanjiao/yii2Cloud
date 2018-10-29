<?php

namespace common\models\system;

use Yii;
use \common\models\system\base\MemberToken as BaseMemberToken;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "member_token".
 */
class MemberToken extends BaseMemberToken
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

    public function getTokenById($id){
        $query = MemberToken::find()->asArray();
        $query->select('token')->where('userid = :id',[':id' => $id]);
        $token  = $query->one();
        return $token;
    }

    public function getTokenByToken($token){
        $query = MemberToken::find()->asArray();
        $query->select('userid')->where('token = :token',[':token' => $token]);
        $token  = $query->one();
        return $token;
    }

    public function deleteTokenById($id){
        $query = new MemberToken();
        $recod = $query->deleteAll('userid =:id ',array(':id'=>$id));
        return $recod;
    }
    
    public function deleteTokenByToken($Token){
        $query = new MemberToken();
        $recod = $query->deleteAll('token =:Token ',array(':Token'=>$Token));
        return $recod;
    }

    public function updateTokenById($id,$token){
        $query = MemberToken::find();
        $list = $query->where('userid = :id',[':id' => $id])->one();

        if($list){
            $list->token = $token;
            $list->save(); 
        }else{
            $query = new MemberToken();
            $query->token = $token;
            $query->userid= $id;
            $query->save();
        }

        //$recod = $query->updateAll(array('token'=>$token),'userid =:id',array(':id'=>$id));
        return 1;
    }
}
