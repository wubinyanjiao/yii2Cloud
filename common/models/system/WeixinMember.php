<?php

namespace common\models\system;

use Yii;
use \common\models\system\base\WeixinMember as BaseWeixinMember;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "weixin_member".
 */
class WeixinMember extends BaseWeixinMember
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

    public function searchWeixinMemberBy($customer_id = null,$openid = null,$userid = null){
        $query = WeixinMember::find();

        if($customer_id){
            $query->andWhere('customer_id = :customer_id',[':customer_id' => $customer_id]);

            if($openid){
                $query->andWhere('openid = :openid',[':openid' => $openid]);
            }
        }        
        if($userid){
            $query->andWhere('userid = :userid',[':userid' => $userid]);
        }
        $token  = $query->asArray()->one();
        return $token;
    }

    public function deleteWeiXinTokenById($id){

        $query = new WeixinMember();
        $recod = $query->deleteAll('userid =:id ',array(':id'=>$id));
        return $recod;
    }
    public function updateWeinXinTokenById($id,$openId,$customerId){
        $query = WeixinMember::find();
        $list = $query->where('userid = :id',[':id' => $id])->one();

        if($list){
            $list->openid = $openId;
            $list->customer_id = $customerId;
            $list->save(); 
        }else{
            $query = new WeixinMember();
            $list->openid = $openId;
            $list->customer_id = $customerId;
            $query->userid= $id;
            $query->save();
        }

        //$recod = $query->updateAll(array('token'=>$token),'userid =:id',array(':id'=>$id));
        return 1;
    }


    
}
