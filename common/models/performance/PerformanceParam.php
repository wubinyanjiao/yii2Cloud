<?php

namespace common\models\performance;

use Yii;
use \common\models\performance\base\PerformanceParam as BasePerformanceParam;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "ohrm_performance_param".
 */
class PerformanceParam extends BasePerformanceParam
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

    public function getPerformanceByUserid($userId,$state = 1){

         $query = PerformanceParam::find();
         $query->where('user_id = :userId',[':userId' => $userId]);
         $query->andWhere('state = :state',[':state' => $state]);
         $list  = $query->one();
       return $list;
    }

    
}
