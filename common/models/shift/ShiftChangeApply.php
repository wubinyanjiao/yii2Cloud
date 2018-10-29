<?php

namespace common\models\shift;

use Yii;
use \common\models\shift\base\ShiftChangeApply as BaseShiftChangeApply;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "ohrm_work_shift_change_apply".
 */
class ShiftChangeApply extends BaseShiftChangeApply
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
     * @author 吴斌  2018/1/11 修改 
     * 获取正在申请中的调班
     * @param int $emp_number 员工工资号
     * @return array | 数组
     */
    public function getShiftChangeApply($emp_number) {

        $query=self::find()->where(['orange_emp'=> $emp_number,'status'=>'1'])->asArray()->all();
        return $query;
    }

    /**
     * @author 吴斌  2018/1/11 修改 
     * 获取正在申请中的调班
     * @param int $emp_number 员工工资号
     * @return array | 数组
     */
    public function getShiftChangeApply2($emp_number) {

        $query=self::find()->where(['confirm_emp'=> $emp_number,'status'=>'1'])->asArray()->all();
        return $query;
    }

    public function saveApply($data){
        if ($this->load($data) && $this->save()){
            return true;
        }else {
             return false;
        }
    }

    public function getShiftChangeApplyById($id) {

        $query=self::find()->where(['id'=> $id])->one();
        return $query;
    }
}
