<?php

namespace common\models\system;

use Yii;
use \common\models\system\base\UniqueId as BaseUniqueId;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "hs_hr_unique_id".
 */
class UniqueId extends BaseUniqueId
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

    public function getTableIdByName($name){
        $query = self::find()->where("table_name = :name",[':name'=>$name])->one();
        return $query;
    }
}
