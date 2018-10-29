<?php

namespace common\models\shift;

use Yii;
use \common\models\shift\base\ShiftRotaryEmployee as BaseShiftRotaryEmployee;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "ohrm_work_rotary_employee".
 */
class ShiftRotaryEmployee extends BaseShiftRotaryEmployee
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

    /**
     * @author 吴斌  2018/7/19 修改 
     * 获取时间范围内轮转进组人员列表
     * @param array $date_id_list 日期数组列表 
     * @param int $workStation 组id
     * @return array | 日期
     */

    public function getRotaryEmpIn($date_id_list,$workStation){
        $data=self::find()
                ->where('rotary_department_id=:inid',[':inid'=>$workStation])
                ->andWhere(['in','date_from',$date_id_list])
                ->asArray()
                ->one();
        return $data;
    }

    /**
     * @author 吴斌  2018/7/19 修改 
     * 获取时间范围内轮转出进组人员列表
     * @param array $date_id_list 日期数组列表 
     * @param int $workStation 组id
     * @return array | 日期
     */
    public function getRotaryEmpOut($date_id_list,$workStation){
        $data=self::find()
                ->where('orange_department_id=:inid',[':inid'=>$workStation])
                ->andWhere(['in','date_from',$date_id_list])
                ->asArray()
                ->one();
        return $data;
    }
}
