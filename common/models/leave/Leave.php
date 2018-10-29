<?php

namespace common\models\leave;

use common\models\subunit\Subunit;
use Yii;
use \common\models\leave\base\Leave as BaseLeave;
use yii\helpers\ArrayHelper;
use \common\models\employee\Employee;
use \common\models\leave\LeaveEntitlement;
use \common\models\leave\LeaveType;

/**
 * This is the model class for table "ohrm_leave".
 */
class Leave extends BaseLeave
{
    const LEAVE_TYPE_DAY = 0;//休息一天
    const LEAVE_TYPE_MORNING = 1;//上午假
    const LEAVE_TYPE_AFTERNOOTN = 2;//下午假

    private static $doneMarkingApprovedLeaveAsTaken = false;

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
     * 修改已同意的假为已使用
     * @return [type] [description]
     */
    public function _markApprovedLeaveAsTaken() {
        if (self::$doneMarkingApprovedLeaveAsTaken) {
            return;
        } else {
            $date = date('Y-m-d');
            $update= array('status'=>3);
            $where = array();

            $query = new Leave();
            $recod = $query->updateAll(array('status'=>'3'),'date <:date AND status=:status',array(':date'=>$date,':status'=>2));

            if($recod){
                self::$doneMarkingApprovedLeaveAsTaken = true;
            }     
        }
        return ;
    }


    /**
     * 修改已同意的假为已使用
     * @return [type] [description]
     */
    public function getLeaveById($id) {
        $query = Leave::find();
        $query->where('id = :id',[':id' => $id]);
        $list  = $query->one();
        return $list;
    }

    /**
     * 查询已使用的假期
     * @param  [type] $employeeId [description]
     * @param  [type] $date       [description]
     * @param  [type] $isApply    [description]
     * @return [type]             [description]
     */
    public function getEmpUserLeave($employeeId,$date,$isApply=false) {

        $this->_markApprovedLeaveAsTaken();

        $query = Leave::find()->asArray();
        $query->select('l.id,l.date,l.length_days,l.status,l.leave_request_id,l.leave_type_id,l.emp_number,l.duration_type,t.name,t.islimit,t.orderid');
        $query->from('ohrm_leave l');
        $query->leftJoin('ohrm_leave_type t', 't.id = l.leave_type_id');
        $query->where('l.emp_number = :employeeId',[':employeeId' => $employeeId]);

        if($date){
            $query->andWhere(['in',"l.date", $date]);
        }
        if($isApply){
            $query->andWhere("l.status > 1");
        }else{
            $query->andWhere("l.status > 0");
        }

        $list  = $query->all();

        return $list;
    }


    /**
     * 按条件查询假期
     * @return [type] [description]
     */
    public function getLeaveBySearch($search) {
        $id = !empty($search['id'])?$search['id']:0;
        $empNumber = !empty($search['empNumber'])?$search['empNumber']:0;
        $leaveTypeId = !empty($search['leaveTypeId'])?$search['leaveTypeId']:0;
        $requestId = !empty($search['requestId'])?$search['requestId']:0;
        $date = !empty($search['date'])?$search['date']:0;

        $query = Leave::find();

        if($requestId){
            $query->andWhere('leave_request_id = :requestId',[':requestId' => $requestId]);
        }
        if($id){
            $query->andWhere('id = :id',[':id' => $id]);
        }
        if($empNumber){
            $query->andWhere('emp_number = :empNumber',[':empNumber' => $empNumber]);
        }
        if($leaveTypeId){
            $query->andWhere('leave_type_id = :leaveTypeId',[':leaveTypeId' => $leaveTypeId]);
        }
        if($date){
            $query->andWhere('date = :date',[':date' => $date]);
        }
        $query->orderBy('date');
        //echo $query->createCommand()->getRawSql();die;
        $list  = $query->asArray()->all();
        return $list;
    }

    public function verificationLeave($empNumber,$leaveTypeId,$days){
        //$days = '2018-09-22,2018-09-23';
        $query = Leave::find();
        $query->where('emp_number = :empNumber',[':empNumber' => $empNumber]);
        //$query->andWhere('leave_type_id = :leaveTypeId',[':leaveTypeId' => $leaveTypeId]);
        $query->andWhere(['in','date', $days]);
        $query->andWhere('status > 0 ');
        $list  = $query->asArray()->all();
        return $list;

    }


    public function getLeaveBalanceReport($search){

        //$limitType = $this->getLeaveTypeByLimit(1);

        $statDate = !empty($search['startDate'])?$search['startDate']:'';
        $endDate = !empty($search['endDate'])?$search['endDate']:'';
        $status = !empty($search['status'])?$search['status']:'';
        $workStation = !empty($search['workStation'])?$search['workStation']:'';
        $empNumber =!empty($search['empNumber'])?$search['empNumber']:'';
        $leaveTypeId =!empty($search['leaveTypeId'])?$search['leaveTypeId']:'';

        $page   = $search['page'];
        $offset   = $search['offset'];
        $limit = $search['limit'];   //每页数 20

        $query = Employee::find();
        if (!empty($workStation)&&$workStation>0) {    
            $query->andWhere("work_station = :workStation",[':workStation'=>$workStation]);
        }
        if (!empty($empNumber)) {
            $query->andWhere(['in',"emp_number",$empNumber]);
        }
        $query->andWhere('termination_id IS NULL ');
        $count = $query->count();
        
  
        $query->orderBy('emp_number');        

        $query->offset($offset);
        $query->limit($limit);
        $list = $query->all();
        $limitType = $this->getLeaveTypeByLimit(1);
        $recod = array();
        foreach ($list as $key => $value) {
            $user =$value->user;
            $userName = $user->user_name;
            $emp = $value->emp_number;
            $firstName = $value->emp_firstname;
            $q = LeaveEntitlement::find();
            $q->joinWith('leave');
            $q->where('ohrm_leave_entitlement.emp_number = :empNumber',[':empNumber' => $emp]);

            if(is_array($leaveTypeId)){
                $q->andWhere(['in','ohrm_leave_entitlement.leave_type_id',$leaveTypeId]); 
            }else if(is_numeric($leaveTypeId)){
                $q->andWhere('ohrm_leave_entitlement.leave_type_id =:leaveTypeId',[':leaveTypeId'=>$leaveTypeId]); 
            }else{
                $q->andWhere(['in','ohrm_leave_entitlement.leave_type_id',$limitType]); 
            }

            // if($leaveTypeId){
            //    $q->andWhere('ohrm_leave_entitlement.leave_type_id =:leaveTypeId',[':leaveTypeId'=>$leaveTypeId]); 
            // }else{
            //     $q->andWhere(['in','ohrm_leave_entitlement.leave_type_id',$limitType]); 
            // }

            

            $leavelist = $q->asArray()->all();

            
            $arr = array();
            $arr['empNumber'] = $emp;
            $arr['firstName'] = $firstName;
            $arr['userName'] = $userName;

            $leaveDai = 0;    //等待批准
            $leaveUsed =0;    //已同意
            $balance = 0 ;   //假期总数
            $used = 0 ;      //剩余天数
            foreach ($leavelist as $k => $v) {
                
                $qu = Leave::find();
                $qu->where('leave_type_id = :leave_type_id',[':leave_type_id'=>$v['leave_type_id']]);
                $qu->andWhere('emp_number =  :emp_number',['emp_number'=>$v['emp_number']]);

                if (!empty($statDate)) {
                    $qu->andWhere('date >= :statDate',[':statDate'=>$statDate]); 
                }
                if (!empty($endDate)) {

                    $qu->andWhere('date <=:endDate',[':endDate'=>$endDate]); 
                }
                $leave = $qu->all();
                foreach ($leave as $kl => $vl) {
                    
                    if($vl['status']==1){
                        if($vl['duration_type']==0){
                            $leaveDai += 1;
                        }else{
                            $leaveDai += 0.5;
                        }

                    }else if($vl['status']>=2){
                        if($vl['duration_type']==0){
                            $leaveUsed += 1;
                        }else{
                            $leaveUsed += 0.5;
                        }
                    }
                }

                $balance+=$v['no_of_days'];
                $used+=$v['days_used']; 


            }
            $arr['leaveDai'] = $leaveDai;
            $arr['leaveUsed']= $leaveUsed;
            $arr['balance'] = $balance;
            $arr['used']= $balance-$used;



            $recod[] = $arr;
        }

        return array('list'=>$recod,'count'=>$count);
    }


    /**
     * @author 吴斌  2018/9/11 修改 
     * 获取日期范围内的所有员工
     * @param int $shiftDateId 日期id
     * @param int $scheduleID  计划id
     * @return object | 获取结果数组
     */
    public function getLeaveByDate($datalist) {
        
        $query = self::find()
        ->where(['in','date', $datalist])
        ->andWhere('status > 0 ')
        ->asArray()
        ->all();
        return $query;
    }

    public function getLeaveTypeByLimit($islimit = 0) {
        
        $query = LeaveType::find();
        $query->select('id');
        if($islimit){
            $query->where('islimit = :islimit',[':islimit'=>$islimit]);
        }
        $list = $query->asArray()->all();
        $back = array();
        foreach ($list as $key => $value) {
            $back[] = $value['id'];
        }
        return $back;
    }


    public function leaveExcel($workStation,$customerId,$startDate,$endDate,$isLeader,$emp_number){
        if($startDate != ''){
            $startDate=strtotime($startDate);
            $startDate=date('Y-m-d',$startDate);
        }else{
            $startDate ='';
        }
        if($endDate != ''){
            $endDate=strtotime($endDate);
            $endDate=date('Y-m-d',$endDate);
        }else{
            $endDate = '';
        }

        $where = '1=1';
        if($workStation != 0){
            if($isLeader != false){
                $where .= " and a.work_station = '$workStation'";
            }else{
                $where .=" and a.emp_number = '$emp_number'";
            }
        }

        $user = (new \yii\db\Query())
            ->select(['a.emp_number','a.emp_firstname','b.user_name'])
            ->from('orangehrm_mysql.hs_hr_employee a')
            ->leftJoin('orangehrm_mysql.ohrm_user b','a.emp_number = b.emp_number')
            ->where($where)
            ->all();
        $levaeType = LeaveType::find()->select(['id'])->where(['islimit'=>1])->all();
        $Type = array_column($levaeType,'id');
        $jia = array();
        $jian = array();
        foreach ($user as $k => $v){
            if($startDate == ''){
                $levaeEntitlement = LeaveEntitlement::find()->select(['sum(no_of_days) as no_of_days','sum(days_used) as days_used'])->where(['emp_number'=>$v['emp_number']])->andWhere(['in','leave_type_id',$Type])->all();
                $sum = round($levaeEntitlement[0]['no_of_days'],2);
                $hou = round($levaeEntitlement[0]['no_of_days'],2) - round($levaeEntitlement[0]['days_used'],2);
                $user[$k]['levae_sum'] = $sum;
            }else{
                $sum = 0;
                foreach ($Type as $kk => $vv){
                    $where1 = "emp_number = '$v[emp_number]' AND entitlement_type = $vv AND create_time < '$startDate' ORDER BY create_time DESC LIMIT 1";
                    $levaeEntitlement = LeaveEntitlementLog::find()->where($where1)->one();
                    $sum += $levaeEntitlement['no_of_days'];
                }
                $user[$k]['levae_sum'] = $sum;
            }
            $jia = array();
            foreach ($Type as $kk => $vv){
                if($startDate != '' && $endDate !=''){
                    $where2 = "create_time >= '$startDate' and create_time >= '$endDate'";
                }else{
                    $where2 = '';
                }
                $log_jia = LeaveEntitlementLog::find()->select(['sum(days) as days'])->where(['emp_number'=>$v['emp_number'],'status'=>1,'entitlement_type'=>$vv])->andWhere($where2)->all();
                $jia[] = $log_jia[0]['days'];
            }
            $jian = array();
            foreach ($Type as $kk => $vv){
                if($startDate != '' && $endDate !=''){
                    $where3 = "create_time >= '$startDate' and create_time >= '$endDate'";
                }else{
                    $where3 = '';
                }
                $log_jian = LeaveEntitlementLog::find()->select(['sum(days) as days'])->where(['emp_number'=>$v['emp_number'],'status'=>2,'entitlement_type'=>$vv])->andWhere($where3)->all();
                $jian[] = $log_jian[0]['days'];
            }
            if($startDate != ''){
                $jiajia = 0;
                foreach ($jia as $kk =>$vv){
                    $jiajia += $vv;
                }

                $jianjia = 0;
                foreach ($jian as $kk =>$vv){
                    $jianjia += $vv;
                }

                $hou = $sum+$jiajia+($jianjia);
            }


            $user[$k]['jia1'] = $jia[0];
            $user[$k]['jia2'] = $jia[1];
            $user[$k]['jia3'] = $jia[2];
            $user[$k]['jia4'] = $jia[3];
            $user[$k]['jia5'] = $jia[4];
            $user[$k]['jia6'] = $jia[5];
            $user[$k]['jia7'] = $jia[6];


            $user[$k]['jian1'] = $jian[0];
            $user[$k]['jian2'] = $jian[1];
            $user[$k]['jian3'] = $jian[2];
            $user[$k]['jian4'] = $jian[3];
            $user[$k]['jian5'] = $jian[4];
            $user[$k]['jian6'] = $jian[5];
            $user[$k]['jian7'] = $jian[6];

            $user[$k]['huo'] = $hou;
            $user[$k]['sumkey'] = $k+1;



        }
        return $user;
    }


}
