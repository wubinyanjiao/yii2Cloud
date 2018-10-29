<?php

namespace common\models\system;

use Yii;
use \common\models\system\base\LatitudeLongitude as BaseLatitudeLongitude;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "ohrm_latitude_longitude".
 */
class LatitudeLongitude extends BaseLatitudeLongitude
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

    public function getLatitudeLongitudeByWorkStation($workStation){
        $query = LatitudeLongitude::find();
        $query->where('work_station = :workStation',[':workStation' => $workStation]);
        $data  = $query->one();
        return $data;
    }
}
