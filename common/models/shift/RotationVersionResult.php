<?php

namespace common\models\shift;

use Yii;
use \common\models\shift\base\RotationVersionResult as BaseRotationVersionResult;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "ohrm_rotationVersionResult".
 */
class RotationVersionResult extends BaseRotationVersionResult
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
    public function VersionResultAll($rotationId,$versionid)
    {
        $data = self::find()
            ->where(['rotationId'=>$rotationId])
            ->andWhere(['rotationVersionId'=>$versionid])
            ->asArray()
            ->all();
        return $data;
    }

}
