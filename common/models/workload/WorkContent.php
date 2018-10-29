<?php

namespace common\models\workload;

use Yii;
use \common\models\workload\base\WorkContent as BaseWorkContent;
use yii\helpers\ArrayHelper;
use common\models\workload\WorkLoad;

/**
 * This is the model class for table "ohrm_work_content".
 */
class WorkContent extends BaseWorkContent
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
     * 获取工作量名称
     * @param $employeeId, $actionableStatesList
     * @return AttendanceRecord
     */
    public function getWorkContentList($work_station = null) {

            $query = self::find();
                    

            if(!empty($work_station)){
                $query->where('work_station = :work_station',[':work_station'=>$work_station]);
            }

            $list = $query->all();
            $arr = array();
            ///$arr['0'] = '--请选择--';
            foreach ($list as $key => $value) {
                $arr[$value['id']] = $value['name'];
            }
             return $arr;
            
        
    }
    /**
     * 按小组,和名称查询工作名称
     */
    public function getWorkContentByName($name = null,$work_station = null){
        
            $query = self::find();


            if(!empty($work_station)){
                $query->andWhere('work_station = :work_station',[':work_station'=>$work_station]);
            }
            if(!empty($name)){
                $query->andWhere('name = :name',[':name'=>$name]);
                
            }
                    
             $list = $query->one();
            
             return $list;

    }

    public function getWorkLoadByArr($arr) {

            $q = WorkLoad::find();
                              

            if(!empty($arr['employeeId'])){
                $q->andWhere('employee_id = :employeeId',[':employeeId' =>$arr['employeeId']]);
            }

            if(!empty($arr['work_date'])){
                $q->andWhere('work_date = :work_date', [':work_date'=>$arr['work_date']]);
            }
            if(!empty($arr['workcontent_id'])){
                $q->andWhere('workcontent_id = :workcontent_id', [':workcontent_id'=>$arr['workcontent_id']]);
            }              

            $result = $q->one();
            
            if (!$result) {
                return null;
            }

            return $result;


    }
}
