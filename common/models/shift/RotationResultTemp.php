<?php

namespace common\models\shift;

use Yii;
use \common\models\shift\base\RotationResultTemp as BaseRotationResultTemp;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "ohrm_rotationResultTemp".
 */
class RotationResultTemp extends BaseRotationResultTemp
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
    public function ResultTempCount($rotationId)
    {
        $data = self::find()
            ->where(['rotationId'=>$rotationId])
            ->count();
        return $data;
    }
    /**
     * @param $usersPrepareIn
     * @param $firstemp
     * @return mixed
     */
    public function unsetArray($usersPrepareIn,$firstemp)
    {
        if(!empty($usersPrepareIn)){
            foreach ($usersPrepareIn as $key=>$emp)
            {
                if($firstemp == $emp['userId']){
                    if(count($usersPrepareIn) == 1){
                        unset($usersPrepareIn);
                    }else{
                        unset($usersPrepareIn[$key]);
                    }
                }
            }
        }

        return $usersPrepareIn;
    }
    public function getRotationResultTempOne($rotationId,$rotationDate,$groupId)
    {
        $data = self::find()
            ->where(['rotationId'=>$rotationId,'rotationDate'=>$rotationDate,'groupId'=>$groupId])
            ->asArray()
            ->one();
        return $data;
    }
    public function getRotationResultTempOb($rotationId,$rotationDate,$groupId)
    {
        $data = self::find()
            ->where(['rotationId'=>$rotationId,'rotationDate'=>$rotationDate,'groupId'=>$groupId])
            ->one();
        return $data;
    }
    public function getRotationResultTemp($rotationId,$rotationDate)
    {
        $data = self::find()
            ->where(['rotationId'=>$rotationId,'rotationDate'=>$rotationDate])
            ->asArray()
            ->all();
        return $data;
    }
    public function getRotationResultTempList($rotationId,$rotationDate)
    {
        $data = self::find()
            ->where(['rotationId'=>$rotationId,'rotationDate'=>$rotationDate])
            ->all();
        return $data;
    }
    public function getRotationResultTempPublish($rotationId)
    {
        $data = self::find()
            ->where(['rotationId'=>$rotationId])
            ->asArray()
            ->all();
        return $data;
    }
    public function createOriginal($all)
    {
        foreach ($all as $k=>$v)
        {
            //保存至轮转结果原始表（rotationResultOriginal）
            $rotationResultOriginal = new RotationResultOriginal();
            $OriginalOne = $rotationResultOriginal->getOriginalOne($v['rotationId'],$v['rotationDate'],$v['groupId']);
            if(empty($OriginalOne)){
                $rotationResultOriginal->rotationId = $v['rotationId'];
                $rotationResultOriginal->rotationDate = $v['rotationDate'];
                $rotationResultOriginal->groupId = $v['groupId'];
                $rotationResultOriginal->usersRecommend = $v['usersRecommend'];
                $rotationResultOriginal->usersRecommendUnselected = $v['usersRecommendUnselected'];
                $rotationResultOriginal->usersPrepareIn = $v['usersPrepareIn'];
                $rotationResultOriginal->usersPrepareOut = $v['usersPrepareOut'];
                $rotationResultOriginal->rotationUserCount = $v['rotationUserCount'];
                $rotationResultOriginal->save();
            }
            //保存至轮转结果最新表（rotationResultNewest）如果为空就新增,否则就修改
            $rotationResultNewest = new RotationResultNewest();
            $ResultNewest = $rotationResultNewest->getNewestOne($v['rotationId'],$v['rotationDate'],$v['groupId']);
            if(empty($ResultNewest)){
                $rotationResultNewest->rotationId = $v['rotationId'];
                $rotationResultNewest->rotationDate = $v['rotationDate'];
                $rotationResultNewest->groupId = $v['groupId'];
                $rotationResultNewest->usersRecommend = $v['usersRecommend'];
                $rotationResultNewest->usersRecommendUnselected = $v['usersRecommendUnselected'];
                $rotationResultNewest->usersPrepareIn = $v['usersPrepareIn'];
                $rotationResultNewest->usersPrepareOut = $v['usersPrepareOut'];
                $rotationResultNewest->rotationUserCount = $v['rotationUserCount'];
                $rotationResultNewest->save();
            }else{
                $ResultNewest->rotationId = $v['rotationId'];
                $ResultNewest->rotationDate = $v['rotationDate'];
                $ResultNewest->groupId = $v['groupId'];
                $ResultNewest->usersRecommend = $v['usersRecommend'];
                $ResultNewest->usersRecommendUnselected = $v['usersRecommendUnselected'];
                $ResultNewest->usersPrepareIn = $v['usersPrepareIn'];
                $ResultNewest->usersPrepareOut = $v['usersPrepareOut'];
                $ResultNewest->rotationUserCount = $v['rotationUserCount'];
                $ResultNewest->save();
            }
        }
    }

}
