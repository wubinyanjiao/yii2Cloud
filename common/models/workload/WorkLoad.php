<?php

namespace common\models\workload;

use Yii;
use \common\models\workload\base\WorkLoad as BaseWorkLoad;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "ohrm_work_load".
 */
class WorkLoad extends BaseWorkLoad
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
     * 按小组,和名称查询工作名称
     */
    public function getWorkContentByName($name=null,$work_station=null){
        try {
            $query = workContent::find();

                    if(!empty($work_station)){
                        $query->where('work_station =:work_station',[':work_station'=>$work_station]);
                    }
                    if(!empty($name)){
                        $query->where('name =:name',[':name'=>$name]);
                    }
                    
             $list = $query->one();
            
             return $list;
            
        } catch (Exception $ex) {
            throw new DaoException($ex->getMessage());
        }
    }

    public function getWorkLoadByArr($arr) {


        try {
            $employeeId = !empty($arr['employeeId'])?$arr['employeeId']:null;
            $work_date = !empty($arr['work_date'])?$arr['work_date']:null;
            $workcontent_id = !empty($arr['workcontent_id'])?$arr['workcontent_id']:null;

            $q = WorkLoad::find();
                               

            if($employeeId){
                $q->andWhere('employee_id = :employeeId',[':employeeId'=> $employeeId]);
            }

            if($work_date){
                $q->andWhere('work_date = :work_date',[':work_date'=>$work_date]);
            }
            if($workcontent_id){
                $q->andWhere('workcontent_id = :workcontent_id', [':workcontent_id'=>$workcontent_id]);
            }              

            $result = $q->one();
            
            if (!$result) {
                return null;
            }

            return $result;

        // @codeCoverageIgnoreStart
        } catch (Exception $e) {
            throw new DaoException($e->getMessage(), $e->getCode(), $e);
        }
        // @codeCoverageIgnoreEnd

    }

}
