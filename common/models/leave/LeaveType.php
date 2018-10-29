<?php

namespace common\models\leave;

use Yii;
use \common\models\leave\base\LeaveType as BaseLeaveType;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "ohrm_leave_type".
 */
class LeaveType extends BaseLeaveType
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
     * 获取休假类型列表
     * @param  integer $islimit [description]
     * @return [type]           [description]
     */
    public function getLeaveTypeList($islimit = 1){

        $query = LeaveType::find();
         if($islimit){
             $query->where('islimit = 1');
         }
         $list  = $query->all();

       return $list;
    }
    /**
     * 根据ID 获取休假类型信息
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function getLeaveTypeById($id){

         $query = LeaveType::find();
         $query->where('id = :id',[':id' => $id]);
         $list  = $query->one();
       return $list;
    }


}
