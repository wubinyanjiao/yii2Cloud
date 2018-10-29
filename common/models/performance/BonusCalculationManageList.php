<?php

namespace common\models\performance;

use Yii;
use \common\models\performance\base\BonusCalculationManageList as BaseBonusCalculationManageList;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "ohrm_bonus_calculation_manage_list".
 */
class BonusCalculationManageList extends BaseBonusCalculationManageList
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

    public function getBonusCalculationManageListByDate($bonusDate,$emp_number){
        $query = self::find();
        $query->where('bonusDate = :bonusDate',[':bonusDate' => $bonusDate]);
        $query->andWhere('emp_number = :emp_number',[':emp_number' => $emp_number]);
        
        $list  = $query->one();
        return $list;
    }
}
