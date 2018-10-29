<?php

namespace common\models\shift;

use Yii;
use \common\models\shift\base\ShiftModel as BaseShiftModel;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "ohrm_work_shift_model".
 */
class ShiftModel extends BaseShiftModel
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

    public function addModel($data){
        if ($this->load($data) && $this->save()){
            return true;
        }else {
             return false;
        }
    }


    public function getShiftModel($work_station,$type){
        $data=self::find()
        ->select(['id','name'])
        ->where(['type'=>$type])
        ->andWhere(['work_station'=>$work_station])
        ->asArray()
        ->all();
        return $data;
    }

    public function getShiftModelOne($id){
        $data=self::find()
        ->where(['id'=>$id])
        ->one();
        return $data;
    }
}
