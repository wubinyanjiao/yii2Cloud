<?php

namespace common\models\shift;

use Yii;
use \common\models\shift\base\ShiftResultOrange as BaseShiftResultOrange;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "ohrm_work_shift_result_orange".
 */
class ShiftResultOrange extends BaseShiftResultOrange
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

    public function getShiftResult($schedule_id)
    {
        
        $data = self::find()->where('schedule_id = :sid', [':sid' => $schedule_id])->asArray()->all();
        return $data;
    }

    public function getOrangeResult($id)
    {
        
       $data=self::find()->where('id =:oid ',[':oid'=>$id])->one();
       return $data;
    }

    /**
     * @author 吴斌  2018/7/19 修改 
     * 根据模版id搜索班次
     * @param array $model_id 模板id
     * @return array | 班次统计
     */
    public function getOrangeResultByModel($model_id)
    {
        
       $data = self::find()->where('model_id = :model_id', [':model_id' => $model_id])->asArray()->all();
       return $data;
    }

    
    public function addShiftOrange($data){
        if ($this->load($data) && $this->save()){
            return true;
        }else {
             return false;
        }
    }


    public function delResult($schedule_id)
    {
        
        $data = self::deleteAll('schedule_id = :sid', [':sid' => $schedule_id]);
        return $data;
    }
}
