<?php

namespace common\models\performance;

use Yii;
use \common\models\performance\base\BonusCalculationList as BaseBonusCalculationList;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "ohrm_bonus_calculation_list".
 */
class BonusCalculationList extends BaseBonusCalculationList
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

    public function getBonusCalculationListByDate($bonusDate,$emp_number,$groupId = null){
        $query = self::find();
        $query->where('bonusDate = :bonusDate',[':bonusDate' => $bonusDate]);
        $query->andWhere('emp_number = :emp_number',[':emp_number' => $emp_number]);
        if($groupId){
            $query->andWhere('groupId = :groupId',[':groupId' => $groupId]);
        }
        
        
        $list  = $query->one();
        return $list;
    }

    public function getCalculationListByCaId($calculation_id,$groupId){
        $query = self::find();
        $query->where('calculation_id = :calculation_id',[':calculation_id' => $calculation_id]);
        $query->andWhere('groupId = :groupId',[':groupId' => $groupId]);
        
        $list  = $query->all();
        return $list;
    }

    public function getBonusCalculationListDateByEmp($empNumber,$customerId,$status){
        $query = self::find();
        $query->select('bonusDate');
        $query->where('customerId = :customerId',[':customerId' => $customerId]);
        $query->andWhere('emp_number = :empNumber',[':empNumber' => $empNumber]);
        $query->andWhere('status = :status',[':status' => $status]);
        $query->groupBy('bonusDate');
        $list  = $query->asArray()->all();
        return $list;
    }
    
    public function getBonusCalculationListAllByDate($customerId,$bonusDate){
        $query = self::find();
        $query->where('customerId = :customerId',[':customerId' => $customerId]);
        $query->andWhere('bonusDate = :bonusDate',[':bonusDate' => $bonusDate]);
        
        $query->orderBy('groupId');
        $list  = $query->asArray()->all();
        return $list;
    }
}
