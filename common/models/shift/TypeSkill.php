<?php

namespace common\models\shift;

use Yii;
use \common\models\shift\base\TypeSkill as BaseTypeSkill;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "ohrm_work_type_skill".
 */
class TypeSkill extends BaseTypeSkill
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

    public function add($data)
    {
        if ($this->load($data) && $this->save()) {
            return true;
        }
        return false;
    }

    /**
     * @author 吴斌  2018/7/19 修改 
     * 获取班次绑定技能列表
     * @param int $workStation 组id
     * @return array | 日期
     */
    public function getShiftTypeToSkillList($workStation){
        $data=self::find()->where('work_station=:stationid',[':stationid'=>$workStation])->asArray()->all();
        return $data;
    }
}
