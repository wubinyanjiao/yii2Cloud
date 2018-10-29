<?php

namespace common\models\group;

use Yii;
use \common\models\group\base\GroupAcl as BaseGroupAcl;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "ohrm_group_acl".
 */
class GroupAcl extends BaseGroupAcl
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
     * 根据条件查询员工权限
     * @return [type] [description]
     */
    public function getGroupAclList($search) {
        $query = GroupAcl::find()->asArray();

        $empNumber  = !empty($search['empNumber'])?$search['empNumber']:'' ; 
        $groupId  = !empty($search['groupId'])?$search['groupId']:'' ; 

        
        if($groupId){
            $query->andWhere(['and','group_id = :groupId'],[':groupId'=>$groupId]);
        }
        if($empNumber){
            $query->orWhere(['or','emp_number = :empNumber'],[':empNumber'=>$empNumber]);
        }else{
            $query->andWhere(['and','emp_number = 0']);
        }

        //  echo $query->createCommand()->getRawSql();die;  //打印sql语句
        $list  = $query->all();
        return $list;
    }

    public function deleteAclById($id){
        $query = new GroupAcl();
        $recod = $query->deleteAll('id =:id ',array(':id'=>$id));
        return $recod;
    }

}
