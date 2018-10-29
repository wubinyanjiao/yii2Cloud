<?php

namespace common\models\group;

use Yii;
use \common\models\group\base\GroupMenus as BaseGroupMenus;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "ohrm_group_menus".
 */
class GroupMenus extends BaseGroupMenus
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
     * 获取权限列表
     * @param  integer ]
     * @return [type]           [description]
     */
    public function getGroupMenusList(){

        $query = GroupMenus::find();

        $list  = $query->asArray()->all();

       return $list;
    }

    /**
     * 根据ID获取权限信息
     * @return [type] [description]
     */
    public function getGroupMenusById($id) {
        $query = GroupMenus::find();
        $query->where('id = :id',[':id' => $id]);
        $list  = $query->one();
        return $list;
    }

    public function deleteGroupMenusById($id){
        $query = new GroupMenus();
        $recod = $query->deleteAll('id =:id ',array(':id'=>$id));

        if($recod){
            $recod = $query->deleteAll('parentid =:id ',array(':id'=>$id));
        }

        return $recod;
    }

}
