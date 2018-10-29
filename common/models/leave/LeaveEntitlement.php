<?php

namespace common\models\leave;

use Yii;
use \common\models\leave\base\LeaveEntitlement as BaseLeaveEntitlement;
use yii\helpers\ArrayHelper;

use \common\models\leave\LeaveEntitlementLog;
use \common\models\attendance\ApproverTab;
use \common\models\user\User;
use \common\models\employee\Employee;

/**
 * This is the model class for table "ohrm_leave_entitlement".
 */
class LeaveEntitlement extends BaseLeaveEntitlement
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
     * 根据小组ID 查询假期;
     * @param  [type] $workStation [description]
     * @return [type]              [description]
     */
    public function getSubunitEntitlementbyDate($workStation){
        $query = LeaveEntitlement::find();
        //$query->select('emp_number');
        $query->joinWith('employee');
        if($workStation){
            $query->andWhere('hs_hr_employee.work_station= :workStation',[':workStation'=>$workStation]);
        }
        $query->andWhere(['hs_hr_employee.termination_id'=>null]);
        $list = $query->asArray()->all();
        return $list;
    } 

    /**
     * 查询员工假期
     * @return [type] [description]
     */
    public function getLeaveBalance($empNumber, $leaveTypeId, $statDate = null, $endDate = null){
        $query = LeaveEntitlement::find();
        $query->where('leave_type_id = :leaveTypeId',[':leaveTypeId' => $leaveTypeId])
                ->andWhere('emp_number = :empNumber',[':empNumber' => $empNumber]);

                if($statDate){
                    $query->andWhere('from_date <= :statDate',[':statDate' => $statDate]);
                } 
                if($endDate){
                    $query->andWhere('to_date >= :endDate',[':endDate' => $endDate]);
                }

                
             $list = $query->one();
        return $list;
    }

    /**
     * 获取员工的请假情况
     * @param  [type] $empNumber     [description]
     * @param  [type] $leave_type_id [description]
     * @param  [type] $fromDate      [description]
     * @param  [type] $toDate        [description]
     * @return [type]                [description]
     */
    public function getEmtitlementHoliday($empNumber,$leave_type_id,$fromDate=null,$toDate=null)
    {

        $query = LeaveEntitlement::find();
        $query->where('emp_number = :empNumber',[':empNumber' => $empNumber]);

        if($leave_type_id){
           $query->andWhere('leave_type_id =:leave_type_id',[':leave_type_id'=>$leave_type_id]); 
        }

        if($fromDate){
           $query->andWhere('from_date <= :fromDate',[':fromDate'=>$fromDate]); 
        }
        if($toDate){
           $query->andWhere('to_date >=:toDate',[':toDate'=>$toDate]); 
        }
        $query->orderBy('leave_type_id asc'); 

        $results = $query->all();
        return $results;      
    }

    /**
     *  请假申请         
     * 指定定员工休假   //假期状态默认为已同意
     * $ishalf         默认null AM 上午 PM下午
     * $isAd  1 申请中  2已同意
     * $allDays  数组格式 请假天数不连续
     * $schedule_id  计划排班表id 
    */
     public function appointEmployeeLeave($empNumber,$leaveTypeId,$fromDate,$toDate,$ishalf = null,$isAd = 2,$note = null,$createByName = null,$createById = null,$allDays= null,$schedule_id = null){

        $ischa = false ;  //是否假期不够
        $agaen = false;
        $agaenTypeId = 0 ; 
        $isAddL = false; 

        $Employee = new Employee();
        $emp = $Employee->getEmpByNumNber($empNumber);

        if(empty($leaveTypeId)){
            $hodd = $this->getEntitlementRemaining($empNumber,null,$fromDate);
        }else{
            $getLeaveTypeService = new LeaveType();
            $leaveType = $getLeaveTypeService->getLeaveTypeById($leaveTypeId);

            if($leaveType->islimit==1){
                $hodd = $this->getEntitlementRemaining($empNumber,$leaveTypeId,$fromDate);
                if(empty($hodd[$empNumber])){
                    $hodd = $this->getEntitlementRemaining($empNumber,'',$fromDate);
                }
            }else{

                $isAddL = true;
                $hodd = array();
            }
            
        }

        
        if($hodd){
            foreach ($hodd as $key => $value) {
                if($ishalf){
                    if($value['balance']<0.5){
                        return array('status'=>false,'message'=>$emp->emp_firstname.'假期不够扣');
                        return false; // 假期不够扣
                    }
                }else{

                    if($value['balance']<1){
                        return array('status'=>false,'message'=>$emp->emp_firstname.'假期不够扣');
                        return false; // 假期不够扣
                    }
                }

             
                foreach($value['entitlement'] as $k=>$v){
                    if(empty($ishalf)){
                        if($v['balance']>=1){
                            if($agaen){
                                 $agaenTypeId = $v['leaveTypeId'];
                            }else{
                                $leaveTypeId = $v['leaveTypeId'];
                            }
                            
                            break;
                        }else if($v['balance']>0&&$v['balance']<1){
                            $ischa = true;
                            if($agaen){
                                $agaenTypeId = $v['leaveTypeId'];
                                break;
                            }

                            $leaveTypeId = $v['leaveTypeId'];
                            $agaen =true;

                            
                        }

                    }else if($ishalf==1){
                        if($v['balance']<0.5){
                            continue;
                        }else{
                            $leaveTypeId = $v['leaveTypeId'];
                            break;
                        }


                    }else if($ishalf==2){
                        if($v['balance']<0.5){
                            continue;
                        }else{
                            $leaveTypeId = $v['leaveTypeId'];
                            break;
                        }
                    }else{
                        return array('status'=>false,'message'=>$emp->emp_firstname.'参数错误');
                        return false;
                    }


                }
            }
        }

     
        if($agaen&&empty($agaenTypeId)){
            return array('status'=>false,'message'=>$emp->emp_firstname.'你还没有可休假期');
            return false;
        }
        if(empty($leaveTypeId)){
            return array('status'=>false,'message'=>$emp->emp_firstname.'你还没有可休假期');
            return  false;
        }

        $Leave = new Leave();
        //$Leave->_markApprovedLeaveAsTaken();

        if($agaen){
            $isha1 = $this->qingjiashenqingzhong($empNumber,$leaveTypeId,$fromDate,$toDate,1,$isAddL,$isAd,$note,$createByName,$createById,$allDays,$schedule_id);
            $isha2 = $this->qingjiashenqingzhong($empNumber,$agaenTypeId,$fromDate,$toDate,2,$isAddL,$isAd,$note,$createByName,$createById,$allDays,$schedule_id);
            
            if($isha1['status'] || $isha2['status']){
                return array('status'=>true,'message'=>'','result'=>$isha2['result']);
                return $isha2;
            }else{
                return array('status'=>false,'message'=>$emp->emp_firstname.'请假失败');
                return false;
            }

        }else{

            $isha = $this->qingjiashenqingzhong($empNumber,$leaveTypeId,$fromDate,$toDate,$ishalf,$isAddL,$isAd,$note,$createByName,$createById,$allDays,$schedule_id);

            if($isha['status']){
                return array('status'=>true,'message'=>'','result'=>$isha['result']);
                return $isha;
            }else{
                return array('status'=>false,'message'=>$emp->emp_firstname.'请假失败');
                return false;
            }

        }

    }

     /**
     * 返回某类型休假的使用天数和剩余天数
     * @param  [type] $empNumber   [description]
     * @param  [type] $leaveTypeId 假期类型 可不传
     * @param  [type] $date        [description]
     * @return [type]              [description]
     */
    public function getEntitlementRemaining($empNumber,$leaveTypeId,$date=null){ 
        if(empty($date)){
            $date = date('Y-m-d');
        }
        if($leaveTypeId){
            $islimit = 0;
        }else{
            $islimit = 0;
        }
        $getLeaveTypeService = new LeaveType();
        $leaveTypeList = $getLeaveTypeService->getLeaveTypeList($islimit);
        //echo '<pre>';var_dump($leaveTypeList);die;
        $i = 0 ;
        $all_balance = 0;
        if(!is_array($empNumber)){
            $empArr = array($empNumber);
        }else{
            $empArr = $empNumber;
        }
        foreach($empArr as $k=>$emp){
            $balance = array();
            $balance['all'] = 0; 
            $balance['balance'] = 0;
            foreach ($leaveTypeList as $type) {
                
                if(empty($leaveTypeId)){

                    $detail = $this->getLeaveBalance($emp,$type->id,$date,$date);

                    if($detail &&(floatval($detail->no_of_days) - floatval($detail->days_used))>0){
                       
                            $balance['all'] += floatval($detail->no_of_days);

                            $balance['balance'] +=floatval($detail->no_of_days) - floatval($detail->days_used);
                            $balance['entitlement'][$i]['leaveTypeId'] = $type->id;
                            $balance['entitlement'][$i]['leaveTypeName'] = $type->name;
                            $balance['entitlement'][$i]['all'] = floatval($detail->no_of_days);
                            $balance['entitlement'][$i]['balance'] =floatval($detail->no_of_days) - floatval($detail->days_used);;
                            $balance['entitlement'][$i]['used'] = floatval($detail->days_used);
                            $all_balance +=floatval($detail->no_of_days) - floatval($detail->days_used);;
                            $i++;
                        }
                }else{
                    if($type->id==$leaveTypeId){

                        $detail = $this->getLeaveBalance($emp, $leaveTypeId,$date,$date);

                        if($detail &&(floatval($detail->no_of_days) - floatval($detail->days_used))>0){
                            $balance['all'] += floatval($detail->no_of_days);

                            $balance['balance'] +=floatval($detail->no_of_days) - floatval($detail->days_used);
                            $balance['entitlement'][$i]['leaveTypeId'] = $type->id;
                            $balance['entitlement'][$i]['leaveTypeName'] = $type->name;
                            $balance['entitlement'][$i]['all'] = floatval($detail->no_of_days);
                            $balance['entitlement'][$i]['balance'] =floatval($detail->no_of_days) - floatval($detail->days_used);;
                            $balance['entitlement'][$i]['used'] = floatval($detail->days_used);
                            $all_balance +=floatval($detail->no_of_days) - floatval($detail->days_used);;
                            $i++;
                        }
                    }
                    
                }
            }
            $allArr[$emp] = $balance;
        }
        return $allArr;

    }


     public function qingjiashenqingzhong($empNumber,$leaveTypeId,$fromDate,$toDate,$ishalf,$isAddL = false,$isAd = 1,$note = null,$createByName = null,$createById = null,$allDays = null,$schedule_id = null){


        $LeaveRequest = new LeaveRequest();
        $leave = $LeaveRequest->judgeLeaveIsUsedmore($empNumber,null,$fromDate);

        if(!empty($leave)&&count($leave)>1){
            return array('status'=>true,'message'=>'','result'=>0);
            return false;
        }else{

            if($isAddL){
                if($ishalf){
                    $num=0.5;
                }else{
                    $num=1;
                    if($allDays){
                        $num = count($allDays);
                    }
                }

                $this->changeEntitlementDays($empNumber,$num,$leaveTypeId,$note,$createByName,$createById ,$schedule_id);
            }

            if(empty($leave[0]->id)){
                $isha = $this->qingjiashenqing($empNumber,$leaveTypeId,$fromDate,$toDate,$ishalf,$isAd,$note,$createByName,$createById,$allDays,$schedule_id);
            }else{
                if($leave[0]->duration_type==1){
                    $ishalf = 2;
                    $isha = $this->qingjiashenqing($empNumber,$leaveTypeId,$fromDate,$toDate,$ishalf,$isAd,$note,$createByName,$createById,$allDays,$schedule_id);
                }else if($leave[0]->duration_type==2){
                    $ishalf = 1;
                    $isha = $this->qingjiashenqing($empNumber,$leaveTypeId,$fromDate,$toDate,$ishalf,$isAd,$note,$createByName,$createById,$allDays,$schedule_id);
                }else{
                    $isha =false;
                    return array('status'=>true,'message'=>'','result'=>0);
                }

                
            }
            if($isha){
                return array('status'=>true,'message'=>'','result'=>$isha);
                return $isha;
            }else{
                return array('status'=>false,'message'=>'请假失败');
                return false;
            }
        }

    }


    /**
     * 修改休假类型的总天数
     * @param  [type]  $empNumber   [description]
     * @param  [type]  $leaveTypeId [description]
     * @param  integer $num         [description] 朢 
     * @return [type]               [description]  
     */
    public function changeEntitlementDays($empNumber,$num=0,$leaveTypeId=null,$note = null,$createByName = null,$createById =null,$schedule_id= null){
        if(empty($num)){
            return false;
        }
        if(empty($leaveTypeId)){
            $leaveTypeId = 1;
        }

        $date = date('Y-m-d');
        $entitlement = $this->getEmtitlementHoliday($empNumber,$leaveTypeId,null,null);

        $LeaveEntitlementLog = new LeaveEntitlementLog();
        if(!empty($entitlement)&&$entitlement[0]->id){
            foreach($entitlement as $hodd){
                $hodd->no_of_days = $hodd->no_of_days + $num;
                $hodd->deleted = (string) $hodd->deleted;
                //$hodd->to_date = date('Y-m-d',strtotime("+ $num days",strtotime($hodd->to_date)));
                $hodd->to_date = date('Y-m-d',(strtotime($hodd->to_date)+3600*24*$num));
                $hodd->note = $note;
                $hodd->save();

                if($num>0){
                    $status = 1;
                }else{
                    $status = 2;
                }
                $LeaveEntitlementLog->saveLeaveEntitlementLog($hodd,$num,$status,$note,$createByName,$createById,$schedule_id);
                break;
            }

        }else{

            $this->saveLeaveEntitlementByAdmin($empNumber,$leaveTypeId,$num,$note,$createById,$createByName,$schedule_id) ;
        }

        return true;

    }

    /**
     * 新增假期
     * @param  [type] $empNumber   [description]
     * @param  [type] $leaveTypeId [description]
     * @param  [type] $days        [description]
     * @return [type]              [description]
     */
    public function saveLeaveEntitlementByAdmin($empNumber,$leaveTypeId,$days,$note=null,$created_by_id = null,$created_by_name = null,$schedule_id = null){
        if(empty($leaveTypeId)){
            $leaveTypeId = 1;
        }

        $LeaveEntitlement = $this->getEmpLeaveEntitlementByType($empNumber,$leaveTypeId);
        if($LeaveEntitlement){
            $LeaveEntitlement->no_of_days =$LeaveEntitlement->no_of_days + floatval($days);

            if($days<0){
                if(($LeaveEntitlement->no_of_days - $LeaveEntitlement->days_used) < abs($days)){
                    return false;
                }
            }

        }else{
            $LeaveEntitlement = new LeaveEntitlement();

            $LeaveEntitlement->created_by_id = $created_by_id;
 
            $LeaveEntitlement->created_by_name = $created_by_name ;
            $LeaveEntitlement->no_of_days = floatval($days);
            
        }

        $LeaveEntitlement->emp_number = $empNumber;
        
        $LeaveEntitlement->leave_type_id = $leaveTypeId;
        $LeaveEntitlement->from_date = '1900-01-01';
        $LeaveEntitlement->to_date = date('Y-m-d',strtotime('+1000 year',strtotime(date('Y-m-d'))));
        $LeaveEntitlement->note = $note;
         $LeaveEntitlement->credited_date = date('Y-m-d H:i:s');
        $LeaveEntitlement->entitlement_type = 1;
        $LeaveEntitlement->save();
        if($days>0){
            $status = 1;
        }else{
            $status = 2;
        }

        $LeaveEntitlementLog = new LeaveEntitlementLog();
        $LeaveEntitlementLog->saveLeaveEntitlementLog($LeaveEntitlement,$days,$status,$note,$created_by_name,$created_by_id,$schedule_id);
        return true;
    }

    public function qingjiashenqing($empNumber,$leaveTypeId,$fromDate,$toDate,$ishalf = 0,$isAd=1,$note = null,$createByName = null,$createById = null,$allDays = null,$schedule_id = null){
        //查询员工假期
        $tr = Yii::$app->db->beginTransaction();
        try { 

            $etitlementv = $this->getEmpLeaveEntitlementByType($empNumber,$leaveTypeId);
            if($etitlementv){
                $surplus = $etitlementv->no_of_days - $etitlementv->days_used;
            }else{
                $surplus = 0;
            }

            $LeaveRequest = new LeaveRequest();

            $LeaveRequest->leave_type_id = $leaveTypeId;
            $LeaveRequest->emp_number = $empNumber;
            $LeaveRequest->date_applied = $fromDate;
            $LeaveRequest->comments = $note;
            $LeaveRequest->is_pro =(string) $isAd;
            $LeaveRequest->create_time = date('Y-m-d H:i:s');
            

            $LeaveRequest->save();

            $reId = $LeaveRequest->id;

            if($reId){
                $LeaveRequestComment = new LeaveRequestComment();
                $LeaveRequestComment->leave_request_id = $reId;
                $LeaveRequestComment->created = date('Y-m-d H:i:s');
                $LeaveRequestComment->created_by_name = $createByName;

                $LeaveRequestComment->created_by_id = $createById;
                $LeaveRequestComment->comments = $note;
                $LeaveRequestComment->save();

                if($ishalf=='1'){
                    $duration = '1';
                    $start_time = '09:00:00';
                    $end_time = '13:00:00';
                    $length_hours = 4;
                    $length_days = 0.5;
                }else if($ishalf=='2'){
                    $duration = '2';
                    $start_time = '13:00:00';
                    $end_time = '17:00:00';
                    $length_hours = 4;
                    $length_days = 0.5;
                }else{
                    $duration = '0';
                    $start_time = '09:00:00';
                    $end_time = '17:00:00';
                    $length_hours = 8;
                    $length_days = 1;
                }
                if($allDays){
                    $LeaveRequest->no_of_days = $surplus-count($allDays);
                }else{
                    $LeaveRequest->no_of_days = $surplus-$length_days;

                    $allDays = prDates($fromDate,$toDate);
                }
                
                $LeaveRequest->save();

                do{
                    

                    if(!in_array($fromDate, $allDays)){
                        $empda  =date('Y-m-d',strtotime ("+1 day", strtotime($fromDate)));
                        $fromDate = $empda;

                        continue;
                    }

                    $Leave = new Leave();

                    $Leave->date = $fromDate;
                    $Leave->status = $isAd;
                    $Leave->leave_request_id = $reId;
                    $Leave->leave_type_id = $leaveTypeId;
                    $Leave->emp_number = $empNumber;
                    $Leave->start_time = $start_time;
                    $Leave->end_time = $end_time;
                    $Leave->length_hours = $length_hours;
                    $Leave->length_days = $length_days;
                    $Leave->duration_type = $duration;
                    $Leave->save();
                    
                    $lId = $Leave->id;

                    $Entitlement = $this->getLeaveBalance($empNumber,$leaveTypeId);

                    $Entitlement->days_used = $Entitlement->days_used + $length_days;
                    $Entitlement->deleted =(string) $Entitlement->deleted ;

                    $Entitlement->save();
                    
                    $LeaveLeaveEntitlement = new LeaveLeaveEntitlement();

                    $LeaveLeaveEntitlement->leave_id = $lId;
                    $LeaveLeaveEntitlement->entitlement_id = $Entitlement->id;
                    $LeaveLeaveEntitlement->length_days = $length_days;
                    $LeaveLeaveEntitlement->save();

                    //记录日志
                    //$entitlement = $this->getEmtitlementHoliday($empNumber,$leaveTypeId,null,null);

                    $LeaveEntitlementLog = new LeaveEntitlementLog();
                    $notes = $Entitlement->leaveType->name.'请假扣'.$length_days;
                    $LeaveEntitlementLog->saveLeaveEntitlementLog($Entitlement,$length_days,2,$notes,$createByName,$createById,$schedule_id,$lId);
                            

                    $empda  =date('Y-m-d',strtotime ("+1 day", strtotime($fromDate)));
                    $fromDate = $empda;
                }while($fromDate <= $toDate);

            
                $tr->commit(); 
                return $reId;
            }else{
               $tr->rollBack(); 
                return false; 
            }

        } catch (Exception $e) { 

            $tr->rollBack(); 
            return false;
        }
        
    }

    /**
     * 修改请假状态
     * @param  [type]  $empNumber 员工ID
     * @param  [type]  $id        休假ID
     * @param  integer $queryType 状态类型  0取消 2同意 -1拒绝
     * @param           ishalf  0整天  1上午  2下午
     * @param           power  操作人
     * @return [type]             [description]
     */
    public function updateLeaveStatus($empNumber,$id=null,$queryType=0,$date=null,$ishalf = 0,$note = null,$power = null,$schedule_id = null){

        $LeaveRequest = new LeaveRequest();
        $Leave = new Leave();
        $LeaveLeaveEntitlement = new LeaveLeaveEntitlement();
        if (empty($id)) {
            if(!empty($date)){
                $leave =  $LeaveRequest->judgeLeaveIsUsedmore($empNumber,null,$date,$ishalf);
                if($leave){
                    foreach($leave as $k=>$v){
                        $ids[] = $v->id;
                    }
                }else{
                    $ids = null;
                }
            }
           
        }else{
            if(is_array($id)){
                $ids = $id;
            }else{
                $ids = array($id);
            }
            
        } 
        if(empty($ids)){
            return false;
        }
        $tr = Yii::$app->db->beginTransaction();
        try { 
            foreach($ids as $v){
                $leave = $Leave->getLeaveById($v);

                if($queryType>1){  
                    if($leave){
                        if($leave->status==1){
                            $leave->status = $queryType;
                            $leave->save();
                        }
                          
                    }

                }else if($queryType<1){
                    $sat = $leave->status ;

                    $leave->status = $queryType;
                    $leave->save();
                    
                    $Entitlement = $this->getLeaveBalance($empNumber,$leave->leave_type_id);

                    $Entitlement->days_used = $Entitlement->days_used - abs($leave->length_days);
                    if($leave->leave_type_id>=8){
                        $Entitlement->no_of_days = $Entitlement->no_of_days - abs($leave->length_days);
                    }
                    $Entitlement->save();

                    if($sat>0){
                        if($power){
                           $User = new User();
                           $user = $User->getSystemUsersByEmpNumber($power);
                           $createByName = $user->employee->emp_firstname;
                           $createById = $user->id;
                        }else{
                            $createByName = 'Admin';
                            $createById = 1;
                        }

                        $LeaveEntitlementLog = new LeaveEntitlementLog();
                        if(!$note){
                            $notes = $Entitlement->leaveType->name.'销假加'.abs($leave->length_days);
                        }else{
                            $notes = $note;
                        }
                        //$notes = $Entitlement->leaveType->name.'销假加'.abs($leave->length_days);
                        $LeaveEntitlementLog->saveLeaveEntitlementLog($Entitlement,abs($leave->length_days),1,$notes,$createByName,$createById,$schedule_id);


                    }

                    $LeaveLeaveEntitlement->deleteLeaveLeaveEntitlementByLeaveId($v);
                }

                //验证是否有申请的状态修改 
                $LeaveRequest = new LeaveRequest();
                $request =$LeaveRequest->getLeaveRequestById($leave->leave_request_id);
                $request->is_pro = $queryType;
                if($note){
                    $request->comments = $note;
                }
                $request->save();

                $ApproverTab = new ApproverTab();
                $TabList = $ApproverTab->getApplicantById($empNumber,$leave->leave_request_id,1);
                if($TabList){
                    $agree = null;
                    foreach ($TabList as $key => $value) {
                        if(empty($agree)){
                            if($power){
                                $agree = $power;
                            }else{
                                $agree = $value['sup_employee'];
                            }
                            
                        }
                        $value->agree_employee = $agree;
                        $value->status = $queryType;
                        $value->save();
                    }
                }
            
            }

            $tr->commit(); 


            return true;
        } catch (Exception $e) { 

            $tr->rollBack(); 
            return false;
        }
        
        //$LeaveRequestService->changeLeaveStatus($changes,'change_leave',$note,null,$empNumber);
        
        //var_dump($changes);die;
        


        return true;
    }

    /**
     * 返回休假的剩余天数
     * @param  [type] $empNumber   [description]
     * @param  [type] $leaveTypeId 假期类型 可不传
     * @param  [type] $statdate     开始时间
     * @param  [type] $enddate      结束时间
     * @return [type] $isFromPool   是否从余假池来        
     */
    public function getEntitlementSurplusDay($empNumber,$leaveTypeId=null,$statdate=null,$enddate=null,$isFromPool = false){ 
        if(empty($statdate)){
            $statdate =date('Y-m-d');
        }
        if(empty($enddate)){
            $enddate = $statdate;
        }
        // $getLeaveTypeService = new LeaveTypeService();
        // $leaveTypeList = $getLeaveTypeService->getLeaveTypeList();

        $getLeaveTypeService = new LeaveType();
        $leaveTypeList = $getLeaveTypeService->getLeaveTypeList(0);
        $i = 0 ;
        $all_balance = 0;
        foreach ($leaveTypeList as $type) {
            if(empty($leaveTypeId)){
                if($isFromPool){
                    if($type->orderid<=0){
                        continue;
                    }
                }
                if(empty($type->islimit)){
                    continue;
                }
                $detail = $this->getLeaveBalance($empNumber,$type->id,$statdate,$enddate);

                if($detail &&floatval($detail->no_of_days)){
                    $balance[$i]['leaveTypeId'] = $type->id;
                    $balance[$i]['leaveTypeName'] = $type->name;
                    $balance[$i]['all'] =floatval($detail->no_of_days);
                    $balance[$i]['balance'] = floatval($detail->no_of_days) - floatval($detail->days_used);
                    $balance[$i]['used'] =floatval($detail->days_used);
                    $all_balance +=floatval($detail->no_of_days) - floatval($detail->days_used);
                    $i++;
                }
            }else{
                if($type->id==$leaveTypeId){

                    $detail = $this->getLeaveBalance($empNumber, $leaveTypeId,$statdate,$enddate);

                    if($detail){
                        $balance[$i]['leaveTypeId'] = $type->id;
                        $balance[$i]['leaveTypeName'] = $type->name;
                        $balance[$i]['all'] = floatval($detail->no_of_days);
                        $balance[$i]['balance'] = floatval($detail->no_of_days) - floatval($detail->days_used);
                        $balance[$i]['used'] = floatval($detail->days_used);
                        $all_balance +=floatval($detail->no_of_days) - floatval($detail->days_used);
                        $i++;
                    }
                }
                
            }
        }
        return $all_balance;

    }

    /**
     * 查询已使用的假期天数
     * $empNumberArr array('802'=>'2018-05-30,2018-05-31','803'=>'2018-05-12,2018-05-21')
     */
    public function getAlreadyUsedLeave($empNumberArr=null){
        $Leave  = new Leave();
        if(!is_array($empNumberArr)){
            $empArr = array($empNumberArr);
        }else{
            $empArr = $empNumberArr;
        }

        $allArr = array();
        foreach($empArr as $k=>$emp){
            $date = explode(',', trim($emp,','));


            $list = $Leave->getEmpUserLeave($k,$date);
            $num = 0;
            foreach($list as $key=>$val){
                if($val['duration_type']){
                    $num +=0.5;
                }else{
                    $num +=1;
                }
            }
            $allArr[$k] = $num;
        }

        return $allArr;



    }

    /**
     * 查询员工某天的假期是半天假还是整天假
     * return   -1 没有使用假期  0一天  1上午 2下午
     */

    public function judgeUsedLeave($empNumber,$date){
    
        $Leave  = new Leave();

        $list = $Leave->getEmpUserLeave($empNumber,$date,true);
        $num = 0;
        if(empty($list)){
            return -1;
        }else{
            foreach($list as $key=>$val){
                if($val['duration_type']==0){
                    return 0;
                }
                if($val['duration_type']==1){
                    $num += 0.5;
                }
                if($val['duration_type']==2){
                    $num += 2;
                }
            }

            if($num<=0.5){
                return 1;
            }else if($num>=2&&$num<2.5){
                return 2;
            }else{
                return 0;
            }
        }

    }

    /**
     * *
     * @param  integer $islimit [description] 是否是有限假期
     * @return [type]           [description]
     */
    public function getLeaveTypeList($islimit = 0){

        $getLeaveTypeService = new LeaveType();
        $leaveTypeList = $getLeaveTypeService->getLeaveTypeList($islimit);
        $list = array();
        foreach ($leaveTypeList as $key => $value) {
            $arr['id'] = $value->id;
            $arr['name'] = $value->name;
            $arr['deleted'] = $value->deleted;
            $arr['islimit'] = $value->islimit;
            $arr['orderid'] = $value->orderid;
            $arr['leave_is_disable'] = $value->leave_is_disable;
            $arr['operational_country_id'] = $value->operational_country_id;
            $arr['exclude_in_reports_if_no_entitlement'] = $value->exclude_in_reports_if_no_entitlement;

            $list[] = $arr;
        }

        return $list;
    }

    /**
     * 根据日期获取当天的假期是第几天
     * @param  [type] $workStation [description]
     * @param  [type] $date        [description]
     * @return [type]              [description]
     */
    public function getEmpLeaveByDays($empNumber=null,$leaveTypeId=null,$date=array()){

        if(!is_array($date)){
            $date = array($date);
        }
         $Leave = new Leave();
         $arr = array();
        foreach ($date as $key => $value) {
            $search['empNumber'] = $empNumber;
            $search['leaveTypeId']=$leaveTypeId;
            $search['date'] = $value;
            $list = $Leave->getLeaveBySearch($search);

            if($list){
                $requestId = $list[0]['leave_request_id'];
                $leavelist = $Leave->getLeaveBySearch(array('requestId'=>$requestId));

                $i =0 ;
                foreach ($leavelist as $k => $v) {
                    $i++;
                    if($v['date']==$value){
                        break;
                    }

                }
                $arr[$value] = $i;

            }else{
                $arr[$value] = 0;
            }
        }

        $back[$empNumber] = $arr;

        return $back;
    
    }


    /**
     * 查询员工的假期
     * @param  [type] $workStation [description]
     * @param  [type] $date        [description]
     * @return [type]              [description]
     */
    public function getEmpLeaveEntitlement($empNumber=null,$leaveTypeId=null,$islimit=false,$orderid = false){

        $query = LeaveEntitlement::find();
        $query->joinWith('leaveType');
        $query->where('ohrm_leave_entitlement.emp_number = :empNumber',[':empNumber' => $empNumber]);

        if($leaveTypeId){
           $query->andWhere('ohrm_leave_entitlement.leave_type_id = :leaveTypeId',[':leaveTypeId'=>$leaveTypeId]); 
        }

        if($islimit){
            $query->andWhere('ohrm_leave_type.islimit > 0');
        }
        if($orderid){
            $query->andWhere('ohrm_leave_type.orderid > 0');
        }

        // if($fromDate){
        //    $query->andWhere('from_date <= :fromDate',[':fromDate'=>$fromDate]); 
        // }
        // if($toDate){
        //    $query->andWhere('to_date >=:toDate',[':toDate'=>$toDate]); 
        // }
        $query->orderBy('ohrm_leave_entitlement.leave_type_id asc'); 

        $results = $query->all(); 
        //echo $query->createCommand()->getRawSql();die;
        return $results;      
        

        return $back;
    
    }

    public function getEmpLeaveEntitlementByType($empNumber,$leaveTypeId){
        $query = LeaveEntitlement::find();
        $query->where('ohrm_leave_entitlement.emp_number = :empNumber',[':empNumber' => $empNumber]);
        $query->andWhere('ohrm_leave_entitlement.leave_type_id =:leaveTypeId',[':leaveTypeId'=>$leaveTypeId]); 
        $results = $query->one();
        return $results;
    }


    public function returnSchedulingById($schedule_id){
        $LeaveEntitlementLog = new LeaveEntitlementLog();
        $Leave = new Leave();
        $list = $LeaveEntitlementLog->getLeaveEntitlementLogByScheduleId($schedule_id);
        $note = '删除排班计划表恢复休假操作' ;

        foreach ($list as $key => $value) {

            if($value->leave_id){
                $lea = $Leave->getLeaveById($value->leave_id);

                if($lea){

                    
                    if($value->status==1){   //新增的减操作
                        //$empNumber,$id=null,$queryType=0,$date=null,$ishalf = 0,$note = null,$power = null,$schedule_id = null
                        if($lea->status<1){
                            $this->appointEmployeeLeave($lea->emp_number,$lea->leave_type_id,$lea->date,$lea->date,$lea->duration_type,2,$note);
                        }
                        
                    }else{

                        if($lea->status>=1){
                            $this->updateLeaveStatus($lea->emp_number,$lea->id,0,null,null,$note);
                        }

                        
                        
                    }
                }
                
            }else{
                $entitlement_id = $value->entitlement_id;
                $leaveTypeId = $value->entitlement_type;
                $days = $value->days;
                $empNumber = $value->emp_number;
                if($value->status==1){ //1新增的减操作
                    $num = -abs($days);
                    $this->changeEntitlementDays($empNumber,$num,$leaveTypeId,$note);
                }else{      //2 减少的新增
                    $num = abs($days);
                    $this->changeEntitlementDays($empNumber,$num,$leaveTypeId,$note);
                }
            }
        }
        return true;
    }



}
