<?php

namespace common\models\shift;

use Yii;
use \common\models\shift\base\RotationList as BaseRotationList;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "ohrm_rotationlist".
 */
class RotationList extends BaseRotationList
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

    /**
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getRotationAll()
    {
        $data = self::find()
            ->where(["<","status", 2])
            ->asArray()
            ->all();
        return $data;
    }
    /**
     * 查找轮转表单个数据
     * @param int $rotationId 轮转表id
     * @return Array | 对象
     */
    public function getRotationOne($rotationId)
    {
        $data = self::find()
            ->where('id=:rotationId',[':rotationId'=>$rotationId])
            ->asArray()
            ->one();
        return $data;
    }

    public function updateGroupInfo()
    {
        $list = $this->getRotationAll();
        foreach ($list as $k=>$v)
        {
            $rotationId = $v['id'];
            $groupinfo = json_decode($v['groupInfo'],true);
            if(!empty($groupinfo))
            {
                $info = array();
                foreach ($groupinfo as $key=>$val){
                    $groupid = $val['groupId'];
                    $rullone = RotationRule::find()->where(['rotationId'=>$rotationId])->andWhere(['groupId'=>$groupid])->asArray()->one();

                    if(!empty($rullone)){
                        $info[$key]['groupId'] = $val['groupId'];
                        $info[$key]['groupName'] = $val['groupName'];
                    }

                }
                $rotationone = RotationList::findOne($rotationId);
                if(empty($info)){
                    $info = null;
                }else{
                    $info = json_encode(array_values($info),JSON_UNESCAPED_UNICODE);
                }
                $rotationone->groupInfo = $info;
                $rotationone->save();
            }
        }
    }
}
