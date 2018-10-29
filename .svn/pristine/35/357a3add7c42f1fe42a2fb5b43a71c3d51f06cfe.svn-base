<?php

namespace common\models\leave;

use Yii;
use \common\models\leave\base\LeaveRequestComment as BaseLeaveRequestComment;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "ohrm_leave_request_comment".
 */
class LeaveRequestComment extends BaseLeaveRequestComment
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

    public function getLeaveRequestComment($id){
        $query = LeaveRequestComment::find();
        $query->where('leave_request_id = :id',[':id' => $id]);
        $query->orderBy('created desc');
        $list  = $query->all();
        return $list;

    }
}
