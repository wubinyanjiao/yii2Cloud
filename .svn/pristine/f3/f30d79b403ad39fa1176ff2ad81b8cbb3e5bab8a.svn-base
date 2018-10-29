<?php

namespace common\models\shift;

use Yii;
use \common\models\shift\base\RotationVersion as BaseRotationVersion;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "ohrm_rotationVersion".
 */
class RotationVersion extends BaseRotationVersion
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
    public function getVersionAll($rotationId,$status)
    {
        if($status == 1){
            $status = "status = '1'";
        }else{
            $status = "status = '0'";
        }
        $where = "rotationId='$rotationId' and ".$status."";
        $data = self::find()->where($where)->asArray()
            ->all();
        return $data;
    }
}
