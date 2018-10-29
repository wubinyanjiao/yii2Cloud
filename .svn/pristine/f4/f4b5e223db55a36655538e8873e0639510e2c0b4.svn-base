<?php

namespace common\models\performance;

use Yii;
use \common\models\performance\base\BonusCalculationConfig as BaseBonusCalculationConfig;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "ohrm_bonus_calculation_config".
 */
class BonusCalculationConfig extends BaseBonusCalculationConfig
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

    public function getBonusCalculationConfigBySub($groupId){
        $list = $this->getBonusCalculationConBySub($groupId);

        if($list){
            $salarySheetField = json_decode($list->salarySheetField);
            $allSheetField = json_decode($list->allSheetField);

            $showList = array();
            $hideArr = $allSheetField;



            if(!empty($salarySheetField)){
                foreach ($salarySheetField as $key => $value) {
                    //$showList[] = array('title'=>$value);
                    $showList[]  = $key;
                }

                //$hideArr = array_diff($hideArr,$salarySheetField);
            }
            foreach ($hideArr as $key => $value) {
                //$hideList[] = array('title'=>$value);
                $hideList[] = array('key'=>$key,'label'=>$value,'disabled'=>false);
            }

            return array('targetKeys'=>$showList,'data'=>$hideList,'titles'=>array('隐藏的列','公开的列'));

        }else{
            return '';
        }

    }

    public function getBonusCalculationConBySub($groupId){
        $query = self::find();
        $query->where('groupId = :groupId',[':groupId' => $groupId]);
        $list  = $query->one();
        return $list;
    }


}
