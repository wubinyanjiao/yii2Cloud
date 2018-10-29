<?php

namespace common\models\shift;

use Yii;
use \common\models\shift\base\RotationRuleWarehouse as BaseRotationRuleWarehouse;
use yii\helpers\ArrayHelper;

/**                                   ohrm_rotationRuleWarehouse
 * This is the model class for table "ohrm_rotationRuleWarehouse".
 */
class RotationRuleWarehouse extends BaseRotationRuleWarehouse
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
    public function getRuleAll($ruleTyp)
    {
        $data = self::find()
            ->where('ruleType=:ruleTyp',[':ruleTyp'=>$ruleTyp])
            ->asArray()
            ->all();
        return $data;
    }
}
