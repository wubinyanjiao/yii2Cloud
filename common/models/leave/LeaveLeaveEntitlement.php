<?php

namespace common\models\leave;

use Yii;
use \common\models\leave\base\LeaveLeaveEntitlement as BaseLeaveLeaveEntitlement;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "ohrm_leave_leave_entitlement".
 */
class LeaveLeaveEntitlement extends BaseLeaveLeaveEntitlement
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


    public function deleteLeaveLeaveEntitlementByLeaveId($leaveId){
        $query = new LeaveLeaveEntitlement();
        $recod = $query->deleteAll('leave_id =:leaveId ',array(':leaveId'=>$leaveId));
        return $recod;
    }

}
