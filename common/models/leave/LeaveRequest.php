<?php

namespace common\models\leave;

use Yii;
use \common\models\leave\base\LeaveRequest as BaseLeaveRequest;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "ohrm_leave_request".
 */
class LeaveRequest extends BaseLeaveRequest
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

    public function judgeLeaveIsUsedmore($employeeId, $leaveTypeId, $date,$ishalf=0) {

        $Leave = new Leave();
        //$Leave->_markApprovedLeaveAsTaken();


        $query = Leave::find();
        $query->where('emp_number = :employeeId',[':employeeId' => $employeeId]);

        if($leaveTypeId){
            $query->andWhere('leave_type_id = :leaveTypeId',[':leaveTypeId' => $leaveTypeId]);
        }
        if($date){
            $query->andWhere('date = :date',[':date' => $date]);
        }
        if($ishalf){
            $query->andWhere('duration_type = :ishalf',[':ishalf' => $ishalf]);
        }

        $query->andWhere('status > 0');     
        $record = $query->all();


        if(empty($record)){
            return null;
        }
        return $record;
    }
    /**
     * 休假列表查询
     * @param  [type] $search [description]
     * @return [type]         [description]
     */
    public function getViemLeaveList($search){

        

        $statDate = !empty($search['statDate'])?$search['statDate']:'';
        $endDate = !empty($search['endDate'])?$search['endDate']:'';
        $status = $search['status'];
        $workStation = !empty($search['workStation'])?$search['workStation']:0;
        $empNumber =!empty($search['empNumber'])?$search['empNumber']:null;
        $lastEmp = !empty($search['lastEmp'])?$search['lastEmp']:0;
        $page   = $search['page'];
        $limit = $search['limit'];   //每页数 20
        $offset = $search['offset'];   //每页数 20

        $Leave = new Leave();
        $Leave->_markApprovedLeaveAsTaken();

        $query = LeaveRequest::find();
        $query->joinWith('leave');
        $query->joinWith('leaveType');
        $query->joinWith('employee');
        $query->joinWith('leaveRequestComment');
        
        if (!empty($statDate)) {
            $query->andWhere("ohrm_leave.date >= :statDate",[':statDate'=>$statDate]);
        }

        if (!empty($endDate)) {
            $query->andWhere("ohrm_leave.date <= :endDate",[':endDate'=>$endDate]);
        }
        if($status){
            $query->andWhere(['in' ,"ohrm_leave.status",$status]);
            // if($status>=2){
            //     $query->andWhere("ohrm_leave.status >= :status",[':status'=>$status]);
            // }else if($status <=1){
            //     $query->andWhere("ohrm_leave.status = :status",[':status'=>$status]);
            // }
        }
        
        
        if($workStation){
            $query->andWhere("hs_hr_employee.work_station = :workStation",[':workStation'=>$workStation]);
        }

        if($lastEmp){
            $query->andWhere("hs_hr_employee.termination_id IS NOT NULL");
        }else{
            $query->andWhere("hs_hr_employee.termination_id IS NULL");
        }

        if($empNumber){
            $query->andWhere(['in' ,"ohrm_leave_request.emp_number",$empNumber]);
        }
        //$query->asArray();
        $query->groupBy('ohrm_leave_request.id');
        $count = $query->count();
  
        $query->orderBy('ohrm_leave.date desc');
       
        $query->offset($offset);
        $query->limit($limit);
        $query->asArray();
        $list = $query->all();
        //$sql=$query ->createCommand()->getRawSql(); 
        //var_dump($sql);die;   

        return array('list'=>$list,'count'=>$count);



    }
    /**
     * 根据requestId获取休假列表
     * @param  [type] $requestId [description]
     * @return [type]         [description]
     */
    public function getViemLeaveRequestList($requestId){
        $Leave = new Leave();
        $Leave->_markApprovedLeaveAsTaken();
        $query = Leave::find();
        $query->joinWith('leaveType');
        $query->joinWith('employee');
        $query->joinWith('leaveComment');
        if($requestId){
            $query->andWhere("ohrm_leave.leave_request_id = :requestId",[':requestId'=>$requestId]);
        }

        $query->orderBy('ohrm_leave.date desc');
        $query->asArray();
        $list = $query->all();
        return $list;
    }
    public function getLeaveRequestById($requestId){
        $Leave = new Leave();
        //$Leave->_markApprovedLeaveAsTaken();
        $query = LeaveRequest::find();
        if($requestId){
            $query->andWhere("id = :requestId",[':requestId'=>$requestId]);
        }

        $list = $query->one();
        return $list;
    }

    public function getLeaveByDate($empNumber,$date){
        
        $query = Leave::find();
        $query->where('status>=1');
        if($empNumber){
            $query->andWhere('emp_number = :empNumber',[':empNumber'=>$empNumber]);
        }
        if($date){
            $query->andWhere('date = :date',[':date'=>$date]);
        }

        $list = $query->asArray()->all;
        return $list;
    }


    public function getLeaveRequestByAPI($arr){
        $query = LeaveRequest::find();

        $query->joinWith('leaveType');
        $query->joinWith('leave');
        $query->orderBy('ohrm_leave_request.create_time DESC');

        if( !empty($arr['empNumber'])){
            if(is_array($arr['empNumber'])){
                $query->andWhere(['in','ohrm_leave_request.emp_number',$arr['empNumber']]);
                
            } else {
                $query->andWhere("ohrm_leave_request.emp_number = :empNumber",[':empNumber'=>$arr['empNumber']]);
            }            
        } 



        if(!is_null($arr['queryType'])){
            $query->andWhere("ohrm_leave_request.is_pro = :is_pro",[':is_pro'=>$arr['queryType']]);
        }else{
           // $q->andWhere('lr.is_pro = ?',0);
        }

        if(!empty($arr['id'])){
            if (is_numeric($arr['id']) && $arr['id'] > 0) {
                $query->andWhere("ohrm_leave_request.id = :id",[':id'=>$arr['id']]);
      
                $record = $query->one();
            }else if (is_array($arr['id'])) {
                $query->andWhere(['in','ohrm_leave_request.id',$arr['id']]);
                $record = $query->all();
            }
            
        }else{
            $record = $query->all();
        }

        
        if($record){
            return $record;
        }else{
            return false;
        }
    }











}
