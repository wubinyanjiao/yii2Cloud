<?php

namespace common\models\leave;

use Yii;
use \common\models\leave\base\LeaveEntitlementLog as BaseLeaveEntitlementLog;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "ohrm_leave_entitlement_log".
 */
class LeaveEntitlementLog extends BaseLeaveEntitlementLog
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
     * 修改假期 记录日志
     * 
     * @param string $orderField field to order by
     * @param string $orderBy order (ASC/DESC)
     * @return Collection of LeaveEntitlementType
     * @throws DaoException on an error
     */
    public function saveLeaveEntitlementLog(leaveEntitlement $leaveEntitlement,$days,$status,$note = null,$createByName = null,$createById =null,$schedule_id = null,$requestId = null) {
        $entitlementLog =new LeaveEntitlementLog();
 
        $entitlementLog->emp_number = $leaveEntitlement->emp_number; 
        $entitlementLog->entitlement_id = $leaveEntitlement->id;  
        $entitlementLog->entitlement_type =(string) $leaveEntitlement->leave_type_id; 
        $entitlementLog->create_time = date('Y-m-d H:i:s');
        if($note){
            $entitlementLog->note = $note; 
        }else{
            $entitlementLog->note = $leaveEntitlement->note; 
        }
        //$entitlementLog->note = $leaveEntitlement->note; 
        if($createByName){
            $entitlementLog->create_by_name = $createByName; 
        }else{
            $entitlementLog->create_by_name = 'Admin'; 
        }
        if($createById){
            $entitlementLog->create_by_id = $createById; 
        }else{
            $entitlementLog->create_by_id = 1; 
        }
  
        if($status==1){
            $entitlementLog->status = '1';
            $entitlementLog->days = $days; 
        }else{
            $entitlementLog->status = '2';
            $entitlementLog->days = -abs($days); 
        }
        $entitlementLog->no_of_days = $leaveEntitlement->no_of_days-$leaveEntitlement->days_used;

        if($schedule_id){
            $entitlementLog->schedule_id = $schedule_id;
        }
    
        if($requestId){
            $entitlementLog->leave_id = $requestId;
        }

        $entitlementLog->save();



        return true;  
    }

    public function getEntitlementLogReport($search){
        $statDate = !empty($search['startDate'])?$search['startDate']:'';
        $endDate = !empty($search['endDate'])?$search['endDate']:'';
        $empNumber =!empty($search['empNumber'])?$search['empNumber']:'';
        $leaveTypeId =!empty($search['leaveTypeId'])?$search['leaveTypeId']:'';
        $page   = $search['page'];
        $limit = $search['limit'];   //每页数 20
        $offset   = $search['offset'];
 
 
        $query = LeaveEntitlementLog::find();
        $query->joinWith('employee');
        $query->joinWith('leaveType');
        $query->joinWith('user');
        $query->where(['in','ohrm_leave_entitlement_log.emp_number',$empNumber]);

        // if($leaveTypeId){
        //     $query->andWhere('ohrm_leave_entitlement_log.entitlement_type = :leaveTypeId',[':leaveTypeId' => $leaveTypeId]);
        // }

        if(is_array($leaveTypeId)){
            $query->andWhere(['in','ohrm_leave_entitlement_log.entitlement_type',$leaveTypeId]); 
        }else{
            $query->andWhere('ohrm_leave_entitlement_log.entitlement_type = :leaveTypeId',[':leaveTypeId' => $leaveTypeId]); 
        }

        if($statDate){
            $statDate = $statDate.' 00:00:00';

            $query->andWhere('ohrm_leave_entitlement_log.create_time >=:statDate',[':statDate' => $statDate]);
        }
        if($endDate){
            $endDate = $endDate.' 23:59:59';
            $query->andWhere('ohrm_leave_entitlement_log.create_time <=:endDate',[':endDate' => $endDate]);
        }
        $query->orderBy('ohrm_leave_entitlement_log.id desc');   
        $count = $query->count();
        
  
             

        $query->offset($offset);
        $query->limit(20);
        $list = $query->all();

        return array('list'=>$list,'count'=>$count);
    }

    public function getEntitlementLogById($leaveType,$entitlementId,$status = 0,$pageSize,$offset){
    
 
        $query = LeaveEntitlementLog::find();

        $query->joinWith('leaveType');
        
        $query->where('ohrm_leave_entitlement_log.entitlement_id = :entitlementId',[':entitlementId' => $entitlementId]);
        if($leaveType){
            $query->andWhere('ohrm_leave_entitlement_log.entitlement_type = :leaveType',[':leaveType' => $leaveType]);
        }

        if($status){
            $query->andWhere('ohrm_leave_entitlement_log.status = :status',[':status' => $status]);
        }
        $count = $query->count();

        $query->orderBy('ohrm_leave_entitlement_log.id desc');   
        
         $query->offset($offset);
         $query->limit($pageSize);
        $list = $query->all();

        return array('list'=>$list,'count'=>$count);
    }

    public function getLeaveEntitlementLogByScheduleId($scheduleId){
        $query = self::find();
        $query->where('schedule_id = :scheduleId',[':scheduleId' => $scheduleId]);
        
        $list = $query->all();
        //echo $query->createCommand()->getRawSql();die;

        return $list;
    }



}
