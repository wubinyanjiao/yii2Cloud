<?php

namespace common\models\leave;

use Yii;
use \common\models\leave\base\LeaveComment as BaseLeaveComment;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "ohrm_leave_comment".
 */
class LeaveComment extends BaseLeaveComment
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

     public function getLeaveComment($id){
        $query = LeaveComment::find();
        $query->where('leave_id = :id',[':id' => $id]);
        $query->orderBy('created desc');
        $list  = $query->all();
        return $list;

    }
}
