<?php

namespace common\models\performance;

use Yii;
use \common\models\performance\base\BonusCalculationManageConfig as BaseBonusCalculationManageConfig;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "ohrm_bonus_calculation_manage_config".
 */
class BonusCalculationManageConfig extends BaseBonusCalculationManageConfig
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
    public function getBonusByCustId($groupId,$customerId){
        $query = self::find();
        $query->where('customerId = :customerId',[':customerId' => $customerId]);
        $query->andWhere('groupId = :groupId',[':groupId' => $groupId]);
        
        $list  = $query->one();
        return $list;
    }


}
