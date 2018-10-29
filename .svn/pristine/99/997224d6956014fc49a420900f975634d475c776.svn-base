<?php

namespace common\models\shift;

use Yii;
use \common\models\shift\base\ShiftOrderBy as BaseShiftOrderBy;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "ohrm_work_shift_orderby".
 */
class ShiftOrderBy extends BaseShiftOrderBy
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

    public function addShiftOrder($data){
        if ($this->load($data) && $this->save()){
            return true;
        }else {
             return false;
        }
    }

     public function getShiftOrderByAndEmp($scheduleID){

        $query = (new \yii\db\Query())
        ->select(['a.id','a.emp_number','a.shift_index','b.emp_firstname'])
        ->from('orangehrm_mysql.ohrm_work_shift_orderby a')
        ->leftJoin('orangehrm_mysql.hs_hr_employee b','a.emp_number = b.emp_number')
        ->where(['a.schedule_id'=>$scheduleID])
        ->orderBy('a.shift_index ASC')
        ->all();
        return $query;
    }

    public function getShiftOrderBy($schedule_id){
        $query=self::find()->where(['schedule_id'=>$schedule_id])->asArray()->all();
        return $query;
    }

    /**
     * @author 吴斌  2018/4/3 修改 
     * 获取员工顺序
     * @param int $schedule_id 计划ID
     * @param int $emps 员工组
     * @return array $date_format   规范化数组 
     */
    public function getEmpShiftOrderBy($schedule_id,$emp){
        $query=self::find()->where(['schedule_id'=>$schedule_id,'emp_number'=>$emp])->one();
        return $query;
    }

    /**
     * @author 吴斌  2018/4/3 修改 
     * 获取排序区间的员工
     * @param int $schedule_id 计划ID
     * @param int $emps 员工组
     * @return array $date_format   规范化数组 
     */
    public function getEmpShiftOrderDur($schedule_id,$first_order,$last_order){
        $query=self::find()
        ->where(['schedule_id'=>$schedule_id])
        ->andWhere(['>=','shift_index',$first_order])
        ->andWhere(['<=','shift_index',$last_order])
        ->all();
        return $query;
    }

     /**
     * @author 吴斌  2018/4/3 修改 
     * 删除某些员工时间段内所有班次
     * @param int $schedule_id 计划ID
     * @param int $emps 员工组
     * @return array $date_format   规范化数组 
     */
    public function del_shift_index($schedule_id,$emps){

        $query=self::deleteAll([ 'and', 'schedule_id = :schedule_id', ['in', 'emp_number', $emps]],[ ':schedule_id' => $schedule_id ]);

        return $query;
    }

    /**
     * @author 吴斌  2018/4/3 修改 
     * 删除计划内的员工排序
     * @param int $schedule_id 计划ID
     * @param int $emps 员工组
     * @return array $date_format   规范化数组 
     */
    public function delshiftindex($schedule_id){

        $query=self::deleteAll([ 'schedule_id' => $schedule_id ]);

        return $query;
    }

    /**
     * @author 吴斌  2018/4/3 修改 
     * 更新员工编号
     * @param int $id 计划ID
     * @param int $emp 员工编号员工组
     * @return array $date_format   规范化数组 
     */
    public function updateOrderby($id,$emp){
        $query=self::updateAll(['emp_number'=>$emp],['id'=>$id]);
        if($query){
            return true;
        }else{
            return false;
        }
    }

    
}
