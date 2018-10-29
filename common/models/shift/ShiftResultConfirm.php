<?php

namespace common\models\shift;

use Yii;
use \common\models\shift\base\ShiftResultConfirm as BaseShiftResultConfirm;
use yii\helpers\ArrayHelper;
use common\models\leave\LeaveEntitlement;
use common\models\leave\LeaveType;
use common\models\shift\ShiftType;
use common\models\shift\ShiftOrderBy;
use common\models\employee\Employee;
use common\models\shift\ShiftDate;
use common\models\shift\ShiftResult;
use common\models\leave\Leave;
use common\models\user\User;


/**
 * This is the model class for table "ohrm_work_shift_result_confirm".
 */
class ShiftResultConfirm extends BaseShiftResultConfirm
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

    public function addConfrim($data){
        if ($this->load($data) && $this->save()){
            return true;
        }else {
             return false;
        }
    }

    public function getRosterResultConfirm($schedule_id){
        $query = (new \yii\db\Query())
        ->from('orangehrm_mysql.ohrm_work_shift_result_confirm')
        ->where(['schedule_id'=>$schedule_id])
        ->all();
        return $query;

    }


    public function getShiftResultConfrim($schedule_id)
    {
        
        $data = self::find()->where('schedule_id = :sid', [':sid' => $schedule_id])->asArray()->all();
        return $data;
    }



    

    /**
     * @author 吴斌  2018/1/16 创建
     * 获休息班次，不包含叶修
     * @param int $scheduleID 计划id
     * @return object | 对象
     */
    public function getRosterRestNoNightConfirm($schedule_id) {

        $idarray=array(ShiftResult::GENERAN_REST,0);
   
        $query=self::find()
        ->where(['schedule_id'=>$schedule_id])
        ->andWhere(['<>','rest_type',0])
        ->andWhere(['in','shift_type_id',$idarray])
        ->asArray()
        ->all();

        return $query;
    }

    /**
     * @author 吴斌  2018/1/16 创建
     * 获取半天班/休
     * @param int $scheduleID 计划id
     * @return object | 对象
     */
    public function getHalftShiftConfirm($schedule_id) {

        $query=self::find()
        ->where(['schedule_id'=>$schedule_id])
        ->andWhere(['<>','rest_type',0])
        ->andWhere(['>','shift_type_id',0])
        ->asArray()
        ->all();

        return $query;
    }

    /**
     * @author 吴斌  2018/1/16 创建
     * 获取班次，不包含不上班的数据
     * @param int $scheduleID 计划id
     * @return object | 对象
     */
    public function getConfrimNoRest($schedule_id) {

        $query=self::find()
        ->where(['schedule_id'=>$schedule_id])
        ->andWhere(['>','shift_type_id',0])
        ->asArray()
        ->all();

        return $query;
    }

    /**
     * @author 吴斌  2018/1/16 创建
     * 获取不上班的人
     * @param int $scheduleID 计划id
     * @return object | 对象
     */
    public function getConfrimNoShift($schedule_id) {

        $query=self::find()
        ->where(['schedule_id'=>$schedule_id])
        ->andWhere(['<','shift_type_id',1])
        ->asArray()
        ->all();

        return $query;
    }

    /**
     * @author 吴斌  2018/1/16 创建
     * 获取补休，夜休，公休的人
     * @param int $scheduleID 计划id
     * @return object | 对象
     */
    public function getConfrimIsRest($schedule_id) {
        $restArr=['-1','-2','-3'];
        $query=self::find()
        ->where(['schedule_id'=>$schedule_id])
        ->andWhere(['in','shift_type_id',$restArr])
        ->asArray()
        ->all();

        return $query;
    }


    /**
     * @author 吴斌  2018/1/16 创建
     * 获取某天休假的员工
     * @param int $scheduleID 计划id
     * @return object | 对象
     */
    public function getShiftofEmpNo($schedule_id) {

        $query=self::find()
        ->where(['schedule_id'=>$schedule_id])
        ->andWhere(['<>','leave_type',0])
        ->andWhere(['shift_type_id'=>0])
        ->asArray()
        ->all();
        return $query;

    }

    /**
     * @author 吴斌  2018/1/16 创建
     * 获取某天休假的员工
     * @param int $scheduleID 计划id
     * @return object | 对象
     */
    public function getShiftofEmpNo2($schedule_id) {

        $query=self::find()
        ->where(['schedule_id'=>$schedule_id])
        ->andWhere(['<>','leave_type',0])
        ->asArray()
        ->all();
        return $query;

    }


    /**
     * @author 吴斌  2018/1/16 创建
     * 获取计划中某员工一周的班次
     * @param int $scheduleID 计划id
     * @return object | 对象
     */
    public function getShiftOneEmp($schedule_id,$emp) {

        $query=self::find()
        ->where(['schedule_id'=>$schedule_id])
        ->andWhere(['emp_number'=>$emp])
        ->asArray()
        ->all();
        return $query;

    }

    /**
     * @author 吴斌  2018/1/16 创建
     * 获取计划中某员工一周的班次
     * @param int $scheduleID 计划id
     * @return object | 对象
     */
    public function getShiftSomeEmp($schedule_id,$emp) {

        $query=self::find()
        ->where(['schedule_id'=>$schedule_id])
        ->andWhere(['in','emp_number',$emp])
        ->asArray()
        ->all();
        return $query;

    }


    /**
     * @author 吴斌  2018/1/16 创建
     * 获取计划中某员工一周的班次
     * @param int $scheduleID 计划id
     * @return object | 对象
     */
    public function formatShifDate($schedule_id,$if_result=false) {

        $datemmodel=new ShiftDate;
        $schedule=Schedule::find()->where('id =:schedule_id ',[':schedule_id'=>$schedule_id])->one();
        $copy_type=$schedule->copy_type;

        //根据schedule获取日期列别
        $date_list=$datemmodel->getShiftDateListBySchedule($schedule_id);

        $weekArr=array('1'=> '周一', '2'=> '周二', '3'=>'周三','4'=>'周四', '5'=> '周五', '6'=>'周六','0'=>'周日');

        foreach ($date_list as $key => $value) {
           $index=get_week($value['shift_date']);
            if($index==1){
                $tmp=$key;
            }

            if($if_result==true){
                $shift_types=$this->typeCountJudge($schedule_id,$value['shift_date'],$shiftTypeList);
                $counttype=array_column($shift_types, 'diff');
                $tmp1=0;
                $tmp2=0;
                foreach ($counttype as $k => $v) {
                   if($v<0){
                     $tmp1+=$v;
                   }
                   if($v>0){
                    $tmp2+=$v;
                   }
                }
                if($is_confirm==1){
                    $message='';
                    $isError=false;
                }else{
                    if($tmp1==0 && $tmp2==0){
                     $message='';
                     $isError=false;
                    }else if($tmp1==0 && $tmp2!=0){
                     $message='+'.$tmp2;
                     $isError=true;
                    }else if($tmp1!=0 && $tmp2==0){
                     $message=$tmp1;
                     $isError=true;
                    }else{
                      $message=$tmp1.',+'.$tmp2;
                      $isError=true;
                    }
                }
                

               
               $week['isError']=$isError;
               $week['errorMessage']=$message;
            }

            $week['id']=$value['id'];
            $week['title']=$weekArr[$index];
            $week['type']=0;

            $date['id']=$value['id'];
            $date['date']=$value['shift_date'];
            $date['type']=0;

            

            $data['dateList'][$key]=$date;
            $data['titleList'][$key]=$week;

        }


        $tmp2=array();

        $tmp2['id']='';
        $tmp2['title']='';
        $tmp2['type']='2';
        $tmp2['date']='';

        $tmp_arr1['titleList']=$tmp2;
        $tmp_arr2['dateList']=$tmp2;

        array_splice($data['titleList'], $tmp, 0, $tmp_arr1);
        array_splice($data['dateList'], $tmp, 0, $tmp_arr2);


        if($copy_type=="two"){
            array_unshift($data['titleList'],$tmp2);
            array_unshift($data['dateList'],$tmp2);
        }


        return $data;



    }

    public function jointData2($employee,$workStation,$emp_num,$date_list,$skempskill,$if_confrim=0){

        $employeemodel=new Employee;
        $resultmodel=new ShiftResult;
        $leaveenmodel=new LeaveEntitlement;
        $empskillmodel=new EmpSkill;
        $typemode=new ShiftType;

        $shiftTypes=$typemode->getShifType($workStation);
        $shiftTypes = array_column($shiftTypes, NULL, 'id');
      
        //获取员工技能
        foreach ($date_list as $ked => $date) {
            if(empty($date)){
                $emarray[$ked]='';
            }else{
                $column_name=$ked;
                if(is_array($employee)){
                    foreach ($employee as $ks => $emday) {
                        $if_skill_ok=array();
                        $skilllist=array();
                        $type_name='';
                        $errSkillMes='';
                        $if_have_leave='';
                        $errLeaveMes='';
                        if(isset($emday['shift_date'])&&$date==$emday['shift_date']){
                            $type_id=(int)$emday['shift_type_id'];
                            $type_id=$emday['shift_type_id'];
                            $leave_type=$emday['leave_type'];
                            $rest_type=$emday['rest_type'];
                            $leave_type_id=$emday['leave_type_id'];
                            $orange_type=$emday['shift_type_id_backup'];

                            //判断员工技能是否与该班次所需技能有交集
                            if(isset($shiftTypes[$type_id]['skill_id'])){
                                $skilllist=(array)json_decode($shiftTypes[$type_id]['skill_id']);
                            }

                            $errSkillMes='';
                            if(isset($skempskill[$emp_num])&& $type_id>0){
                                $skillCheck=$resultmodel->skillCheck($skempskill[$emp_num],$skilllist);
                            
                                if(isset($skillCheck['status'])&&$skillCheck['status']==false){
                                    $errSkillMes=$skillCheck['message'];
                                }
                            }else{//员工没有技能
                                //如果班次也不需要技能：true
                                if(count($skilllist)==0){
                                    $errSkillMes='';
                                }else if(count($skilllist)==1 && $skilllist[0]==0){

                                }else{
                                    $errSkillMes='员工技能与该班次所需技能不匹配';
                                }
                            }

                            $type_num=0;

                            $enetity=$this->getConfrimById($emday,$workStation);


                            $errLeaveMes=$enetity['leave_error'];
                            if((isset($errLeaveMes)&&!empty($errLeaveMes))||(isset($errSkillMes)&&!empty($errSkillMes))){
                                if($if_confrim!=1){
                                    $emarray[$column_name]['isError']=true;
                                    $emarray[$column_name]['errorInfo']=$errSkillMes.$errLeaveMes;
                                }
                            }


                            $emarray[$column_name]['label']=$enetity['name'];
                            $emarray[$column_name]['result_id']=$emday['id'];
                            $emarray[$column_name]['type']=$enetity['show_type'];
                            $emarray[$column_name]['type_id']=$enetity['type_id'];
                            $emarray[$column_name]['leave_id']=$enetity['leave_id'];


                        }

                    }

                }else{
                    $emarray[$column_name]['label']='';
                    $emarray[$column_name]['result_id']=0;
                    $emarray[$column_name]['type']=1;
                    $emarray[$column_name]['type_id']='-100';
                    $emarray[$column_name]['leave_id']='';
                }
                
            }

        }

        var_dump($emarray);exit;

        return $emarray;
        
        
    }

    public function jointData($emarray,$if_confrim=0){

        $usermodel=new User;
        $new_result=array();
        foreach ($emarray as $key1 => $value1) {
           $new_result[$key1]['name']=$value1['name'];
           $new_result[$key1]['index']=$value1['index'];
           $new_result[$key1]['num']=$value1['empnum'];
           $useremtity=$usermodel->getSystemUsersByEmpNumber($value1['empnum']);
           $new_result[$key1]['salaryt_num']=isset($useremtity)?$useremtity->user_name:'0';
           $new_result[$key1]['leavecount']=$value1['leavecount'];

           $new_result[$key1]['isError']=false;

           $rest_two='';
           $rest_error='';


           if($if_confrim!=1){
                if($value1['restcount']>0){
                    $rest_error='公休不足两天';
                    $new_result[$key1]['isError']=true;
               }

               if($value1['restcount']<0){
                    $rest_error='公休大于两天';
                    $new_result[$key1]['isError']=true;
               }



               if($value1['nighterror']!=''){
                    $rest_two=$value1['nighterror'];
                    $new_result[$key1]['isError']=true;
                }

                if($new_result[$key1]['isError']==true){
                    $new_result[$key1]['errorInfo']= $rest_error."\n".$rest_two;
                }
            }


           $em=$value1['empnum'];
           unset($value1['name']);
           unset($value1['index']);
           unset($value1['empnum']);
           unset($value1['leavecount']);
           unset($value1['restcount']);
           unset($value1['nighterror']);


           $tm['type']='check';
           $tm['id']=$em;
           $tm['checked']=false;
           foreach ($value1 as $keya => $valuea) {
              if($valuea==''){
                $offset[]=$keya;
              }
           }
    
           //查找空值的索引
           $tm['type']='check';
           $tm['id']=$em;
           $tm['checked']=false;
           foreach ($offset as $keyd => $valued) {
              $value1[$valued]=$tm;
           }

           $new_result[$key1]['cells']=$value1;
        }
        return $new_result;

    }






    /**
     * @author 吴斌  2018/1/16 创建
     * 获取计划中某员工一周的班次,并且格式化
     * @param int $scheduleID 计划id
     * @param int $emplist 员工列表
     * @param int $empsec 第二个员工
     * @return object | 对象
     */
    public function getShiftOneEmpFormat($schedule_id,$workStation,$emplist,$i=NULL) {

        $date_list=$this->formatShifDate($schedule_id,false);
        $date_list=$date_list['dateList'];
        $date_list=array_column($date_list,'date');
        $skempskill=array();

        $employeemodel=new Employee;
        $resultmodel=new ShiftResult;

        $leaveenmodel=new LeaveEntitlement;
        $empskillmodel=new EmpSkill;

        $orderbymode=new ShiftOrderBy;


        if(is_array($emplist)){
            $assignment_list=$this->getShiftSomeEmp($schedule_id,$emplist);
        }else{
            $assignment_list=$this->getShiftOneEmp($schedule_id,$emplist);
        }

       //获取参与排班的人
        $emp_new_all=$orderbymode->getShiftOrderByAndEmp($schedule_id);
        $emp_new=array_column($emp_new_all, 'emp_number');
        $emp_new_all=array_column($emp_new_all,NULL, 'emp_number');

        
        foreach ($assignment_list as $k => $assignment) {
            $employee_num=$assignment['emp_number'];
            $employee_array[$employee_num][]=$assignment;
        }

        //获取员工技能
        $empskilldata=$empskillmodel->getEmpSkillListByStation($workStation);

        foreach ($empskilldata as $key_skill => $value_skill) {
            $sklemp=$value_skill['emp_number'];
            $skempskill[$sklemp][$key_skill]=$value_skill['skill_id'];
        }

        


        foreach ($employee_array as $key => $employee) {

            $form_a=array();
            $form_b=array();

            $i=0;
            $leavid='';
            $form_a['empnum']=$key;
            //获取个人剩余假期数
            $form_a['leavecount']='';
            if(!isset($emp_new_all[$key])){
                $emp_new_all[$key]=$employeemodel->getEmpByNum($key);
            }
            $form_a['name']=$emp_new_all[$key]['emp_firstname'];
            $form_a['restcount']=0;

            $form_a['nighterror']='';
            $form_a['index']=isset($emp_new_all[$key]['shift_index'])?isset($emp_new_all[$key]['shift_index']):$i;


            $form_b=$this->jointData2($employee,$workStation,$key,$date_list,$skempskill);
  
            $emarray[$key]=$form_a+$form_b;
            ksort($emarray[$key]);
            $i++;
        }


        $result_one=$this->jointData($emarray);
        return $result_one;
    }
    /**
     * @author 吴斌  2018/1/16 创建
     * 获取计划中某员工一周的班次
     * @param int $scheduleID 计划id
     * @return object | 对象
     */
    public function ifRestOver($schedule_id,$emp) {

        $shiftresultmodel=new ShiftResult;
        $typemode=new ShiftType;
        $night_id='';
        $shift_on_result=array();
        $new_rest['one']=array();
        $new_rest['half']=array();
        $new_leave['holday']=array();
        $new_leave['halfleave']=array();
        $night_emp=array();
        $night_data=array();
        $new_data1=array();
        $new_data2=array();
        $should_rest=array();
        $lastNithe=array();
        $tmpbe=array();
        $overtop=0;

        $empShifts=$this->getShiftOneEmp($schedule_id,$emp);
        
        $schedule=Schedule::find()->where('id =:schedule_id ',[':schedule_id'=>$schedule_id])->one();
        $copy_type=$schedule->copy_type;

        $shiftTypes=$typemode->getNightType($schedule->location_id);

        $night_id=isset($shiftTypes)?$shiftTypes->id:'';
 
        foreach ($empShifts as $ks1 => $va1) {

            if($va1['shift_type_id']>0){
                $shift_on_result[$ks1]=$va1;
            }
            if($va1['leave_type']==1){
                $new_leave['holday'][$ks1]=$va1;
            }

            if($va1['leave_type']==2){
                $new_leave['halfleave'][$ks1]=$va1;
            }

            if($va1['rest_type']==1 && $va1['rest_type']==-2){
                $new_rest['one'][$ks1]=$va1;
            }

            if($va1['rest_type']==2){
                $new_rest['half'][$ks1]=$va1;
            }
            
            if($va1['shift_type_id']==$night_id){
                $night_emp[$ks1]=$va1;
            }
            
        }

        //查询上周最后一天上夜班的员工信息
        $datebefore=  date("Y-m-d",strtotime("-1 day",strtotime($schedule->shift_date)));
        
        $lastNithe=$shiftresultmodel->getEmpShiftByDateAndType($datebefore,$night_id,$emp);
        $night_data=count($night_emp);
        //休息一天的员工
        foreach ($new_rest['one'] as $k_n1 => $v_n1) {
           $new_data1[$k_n1]=$v_n1;
        }

        //休息半天的个数
        foreach ($new_rest['half'] as $k_n7 => $v_n7) {
           $new_data2[$k_n7]=$v_n7;
        }

        $sholud=2;
        if($copy_type=='two'){
            $sholud=4;
        }
     

        $half_count=isset($new_data2)?count($new_data2)/2:0;
        $new_da=count($new_data1)+$half_count;

        if(isset($night_data)){

            $if_enough=($night_data+$sholud)-$new_da;

        }else{
            
            $if_enough=$sholud-$new_da;
        }
         
        if($if_enough<0){//获取休息天大于两天的班次
            $overtop=abs($if_enough);
        }

        return $overtop;

    }


    /**
     * @author 吴斌  2018/4/3 修改 
     * 获取员工休假日期
     * @return array $date_format   规范化数组 
     */
    public function getLeaveOfEmpByEmpAndDate($emp,$shift_date) {

        $query = (new \yii\db\Query())
        // ->select(['a.id','a.emp_number','a.shift_index','b.emp_firstname'])
        ->from('orangehrm_mysql.ohrm_leave a')
        ->leftJoin('orangehrm_mysql.ohrm_leave_type b','a.leave_type_id = b.id')
        ->where(['a.emp_number'=>$emp])
        ->andWhere(['in','a.date',$shift_date])
        ->andWhere(['>','a.status',1])
        ->one();
        return $query;
    }


    /**
     * @author 吴斌  2018/1/16 创建
     * 获取员工休假信息
     * @param int $scheduleID 计划id
     * @return object | 对象
     */
    public function getRosterLeaveDay($schedule_id){

        $LeaveList=array('1','2','3');
        $query=self::find()
        ->select(['emp_number','GROUP_CONCAT(shift_date) shiftdate'])
        ->where(['schedule_id'=>$schedule_id])
        ->andWhere(['in','leave_type',$LeaveList])
        ->groupBy(['emp_number'])
        ->asArray()
        ->all();

        return $query;
    }

    /**
     * @author 吴斌  2018/1/16 创建
     * 获取员工休假信息
     * @param int $scheduleID 计划id
     * @return object | 对象
     */
    public function getShiftResultByDate($schedule_id,$shift_date){

        $query=self::find()
        ->select(['shift_date','shift_type_id','COUNT(shift_type_id) totaltype'])
        ->where(['schedule_id'=>$schedule_id])
        ->andWhere(['shift_date'=>$shift_date])
        ->groupBy(['shift_type_id'])
        ->asArray()
        ->all();

        return $query;
    }




     /**
     * @author 吴斌  2018/4/3 修改 
     * 获取员工班次信息
     * @return array $date_format   规范化数组 
     */
    public function getConfrimByEmpAndDate($emp,$shift_date,$schedule_id) {
        $query = self::find()
        ->where(['emp_number'=>$emp])
        ->andWhere(['shift_date'=>$shift_date])
        ->andWhere(['schedule_id'=>$schedule_id])
        ->one();
        return $query;
    }

    /**
     * @author 吴斌  2018/4/3 修改 
     * 删除排班或者休假
     * @param int $schedule_id 计划ID
     * @param int $result_id 员工每个排班ID
     * @param int $add_type 添加类型，1是休息类型，2休假类型
     * @return array $date_format   规范化数组 
     */

    public function delShiftOrLeave($schedule_id,$result_id,$add_type){
        //判断原始结果
        //判断当天这个班是不是半天班
        $curmodel=$this->getConfrimResultById($result_id,$schedule_id);
        $emp_number=$curmodel->emp_number;
        $shift_date=$curmodel->shift_date;
      
       /* $LeaveEntitlementService = new LeaveEntitlement;
        //删除这一天假期
        $LeaveEntitlementService->updateLeaveStatus($emp_number,null,0,$shift_date,0,null);*/

        if($add_type==3){//如果是班次变空白
            $query=self::updateAll(['shift_type_id'=>-100,'rest_type'=>1,'leave_type'=>0,'leave_type_id'=>0,'frist_type_id'=>'','second_type_id'=>'','third_type_id'=>''],['id'=>$result_id]);
        }else if($add_type==2){//如果是休假变空白
            $query=self::updateAll(['shift_type_id'=>-100,'rest_type'=>1,'leave_type'=>0,'leave_type_id'=>0,'frist_type_id'=>'','second_type_id'=>'','third_type_id'=>''],['id'=>$result_id]);
        }else{
            $query=self::updateAll(['shift_type_id'=>-100,'rest_type'=>1,'leave_type'=>0,'leave_type_id'=>0,'frist_type_id'=>'','second_type_id'=>'','third_type_id'=>''],['id'=>$result_id]);
        }
        if($query){
            return true;
        }else{
            return false;
        }
    }

    /**
     * @author 吴斌  2018/4/3 修改 
     * 添加排班或者休假:班->休息1，班->班3，班->假2： 
     * @param int $schedule_id 计划ID
     * @param int $result_id 员工每个排班ID
     * @param int $add_type 添加类型，1是休息类型，2休假类型
     * @param int $result_id 传入对应的类型或者休假id
     * @param int $type_id   类型或者休假id
     * @param if_rest_over  大于0，表示公休大于两天
     * @return array $date_format   规范化数组 
     */

    public function updateShiftRest($schedule_id,$result_id,$add_type,$type_id,$if_rest_over){

        $typemodel=new ShiftType;
        $curshiftype=ShiftType::find()->where(['id'=>$type_id])->one();
        $typeTimeSpan=$typemodel->getShifTypeTimeAreaById($type_id);
        $is_work_half=$curshiftype->is_work_half;
        if($add_type==1 || $add_type==3 ){//班换休息
            if($is_work_half==1){//如果半天班->休息
                $query=self::updateAll(['shift_type_id'=>$type_id,'rest_type'=>2,'leave_type'=>0,'frist_type_id'=>$typeTimeSpan['frist_type_id'],'second_type_id'=>$typeTimeSpan['second_type_id'],'third_type_id'=>$typeTimeSpan['third_type_id']],['id'=>$result_id]);
            }else if($is_work_half==0){//全天班->休息
                $query=self::updateAll(['shift_type_id'=>$type_id,'rest_type'=>0,'leave_type'=>0,'frist_type_id'=>$typeTimeSpan['frist_type_id'],'second_type_id'=>$typeTimeSpan['second_type_id'],'third_type_id'=>$typeTimeSpan['third_type_id']],['id'=>$result_id]);
            }

        }else if($add_type==2){//班->假
            if($is_work_half==1){//如果半天班->全天假
                $query=self::updateAll(['shift_type_id'=>$type_id,'rest_type'=>0,'leave_type'=>2,'frist_type_id'=>$typeTimeSpan['frist_type_id'],'second_type_id'=>$typeTimeSpan['second_type_id'],'third_type_id'=>$typeTimeSpan['third_type_id']],['id'=>$result_id]);
            }else if($is_work_half==0){//全天班->全天假
                $query=self::updateAll(['shift_type_id'=>$type_id,'rest_type'=>0,'leave_type'=>0,'frist_type_id'=>$typeTimeSpan['frist_type_id'],'second_type_id'=>$typeTimeSpan['second_type_id'],'third_type_id'=>$typeTimeSpan['third_type_id']],['id'=>$result_id]);
            }
        }

        if($query){
            return true;
        }else{
            return false;
        }

    }

    /**
     * @author 吴斌  2018/4/3 修改 
     * 添加休假:3假->班，2假->假，1假->休
     * @param int $schedule_id 计划ID
     * @param int $result_id 员工每个排班ID
     * @param int $add_type 添加类型，1是休息类型，2休假类型
     * @param int $type_id  休假id
     * @return array $date_format   规范化数组 
     */

    public function updateShiftLeave($schedule_id,$result_id,$add_type,$type_id){

        //判断原始结果
        //判断当天这个班是不是半天班
        $curmodel=$this->getConfrimResultById($result_id,$schedule_id);
        $emp_number=$curmodel->emp_number;
        $shift_date=$curmodel->shift_date;
        $rest_type=$curmodel->rest_type;
        $shift_type=$curmodel->shift_type_id;
        $leave_type=$curmodel->leave_type;
        $leave_type_id=$curmodel->leave_type_id;
        //安排假
        $LeaveEntitlementService = new LeaveEntitlement;

        if($type_id<0){//公休/夜休/补休->空白/假/班
            $query=self::updateAll(['shift_type_id'=>$type_id,'rest_type'=>1,'leave_type'=>0,'leave_type_id'=>0,'frist_type_id'=>'','second_type_id'=>'','third_type_id'=>''],['id'=>$result_id]);
        }else if($type_id>0 && $shift_type<0 ){//假->公休/夜休/补休
             $query=self::updateAll(['shift_type_id'=>0,'rest_type'=>0,'leave_type'=>1,'leave_type_id'=>$type_id,'frist_type_id'=>'','second_type_id'=>'','third_type_id'=>''],['id'=>$result_id]);
        }else{
            if($add_type==1){//假->休

                $query=self::updateAll(['shift_type_id'=>0,'rest_type'=>0,'leave_type'=>1,'leave_type_id'=>$type_id,'frist_type_id'=>'','second_type_id'=>'','third_type_id'=>''],['id'=>$result_id]);

            }else if($add_type==3){//假->班

                $curshiftype=ShiftType::find()->where(['id'=>$curmodel->shift_type_id])->one();

                $typemodel=new ShiftType;
                $typeTimeSpan=$typemodel->getShifTypeTimeAreaById($curmodel->shift_type_id);

                $is_work_half=$curshiftype->is_work_half;
                if($is_work_half==1 && $rest_type==2){//假->班/休：班/假
                    $query=self::updateAll(['rest_type'=>0,'leave_type'=>2,'leave_type_id'=>$type_id,'frist_type_id'=>$typeTimeSpan['frist_type_id'],'second_type_id'=>$typeTimeSpan['second_type_id'],'third_type_id'=>$typeTimeSpan['third_type_id']],['id'=>$result_id]);
                  
                }else if($is_work_half==1 && $leave_type==2){//假->班/假：班/假
                    $query=self::updateAll(['rest_type'=>0,'leave_type'=>2,'leave_type_id'=>$type_id,'frist_type_id'=>$typeTimeSpan['frist_type_id'],'second_type_id'=>$typeTimeSpan['second_type_id'],'third_type_id'=>$typeTimeSpan['third_type_id']],['id'=>$result_id]);
                    
                }else if($is_work_half==0 ){//假->全班: 假
                    $query=self::updateAll(['shift_type_id'=>0,'rest_type'=>0,'leave_type'=>1,'leave_type_id'=>$type_id,'frist_type_id'=>'','second_type_id'=>'','third_type_id'=>''],['id'=>$result_id]);
                } 
            }else{
                 $query=self::updateAll(['shift_type_id'=>0,'rest_type'=>0,'leave_type'=>1,'leave_type_id'=>$type_id,'frist_type_id'=>'','second_type_id'=>'','third_type_id'=>''],['id'=>$result_id]);
            }
        }
    
        

    
        if($query){
            return true;
        }else{
            return false;
        }
    }

    /**
     * @author 吴斌  2018/4/3 修改 
     * 获取员工班次
     * @param int $schedule_id 计划ID
     * @param int $id 员工每个排班ID
     * @return array $date_format   规范化数组 
     */

    public function getConfrimResultById($id,$schedule_id){
        
        $query=self::find()
            ->where(['id'=>$id])
            ->andWhere(['schedule_id'=>$schedule_id])
            ->one();
        return $query;
    }


    /**
     * @author 吴斌  2018/4/3 修改 
     * 获取员工班次
     * @param int $schedule_id 计划ID
     * @param int $id 员工每个排班ID
     * @return array $date_format   规范化数组 
     */

    public function getConfrimResultById2($id,$schedule_id){
        
        $query=self::find()
            ->where(['id'=>$id])
            ->andWhere(['schedule_id'=>$schedule_id])
            ->asArray()
            ->one();
        return $query;
    }

    /**
     * @author 吴斌  2018/4/3 修改 
     * 更改班次所属员工
     * @param int $emp 员工工资号
     * @param int $id 员工每个排班ID
     * @param int $is_over_rest 大于0
     * @return array $date_format   规范化数组 
     */

    public function updateResultEmp($id,$emp){
        $query=self::updateAll(['emp_number'=>$emp],['id'=>$id]);
        if($query){
            return true;
        }else{
            return false;
        }
    }


    /**
     * @author 吴斌  2018/4/3 修改 
     * 更改某人某天的班次
     * @param int $emp 员工工资号
     * @param int $id 员工每个排班ID
     * @param int $is_over_rest 大于0
     * @return array $date_format   规范化数组 
     */

    public function updateEmpShiftType($id,$schedule_id=null,$is_over_rest=null,$orange_type,$confirm_type){
        $curmodel=$this->getConfrimResultById($id,$schedule_id);
        $emp_number=$curmodel->emp_number;
        $shift_date=$curmodel->shift_date;
        $rest_type=$curmodel->rest_type;
        $shift_type=$curmodel->shift_type_id;
        $leave_type=$curmodel->leave_type;
        $orashiftype=ShiftType::find()->where(['id'=>$orange_type])->one();
        $or_work_half=isset($orashiftype)?$orashiftype->is_work_half:'';
        $conshiftype=ShiftType::find()->where(['id'=>$confirm_type])->one();
        $con_work_half=isset($conshiftype)?$conshiftype->is_work_half:'';

        if($con_work_half==1){// ????->?半天班
            if($is_over_rest==0  ){//如果公休刚好合适
                //半天班->班,加半天假
                if($or_work_half==0){
                    $query=self::updateAll(['shift_type_id'=>$confirm_type,'rest_type'=>0,'leave_type'=>2,'leave_type_id'=>1],['id'=>$id]);
                }else if($or_work_half==1){//半天班->半天班
                    $query=self::updateAll(['shift_type_id'=>$confirm_type],['id'=>$id]);
                }else if($or_work_half==''&&$orange_type=='-100'){//半天班->空白
                    $query=self::updateAll(['shift_type_id'=>$confirm_type,'rest_type'=>0,'leave_type'=>2,'leave_type_id'=>1],['id'=>$id]);

                }else if($or_work_half==''&&$orange_type=='-1'){//半天班->夜休
                    $query=self::updateAll(['shift_type_id'=>$confirm_type,'rest_type'=>0,'leave_type'=>2,'leave_type_id'=>1],['id'=>$id]);

                }else if($or_work_half==''&&$orange_type=='-2'){//半天班->补休
                    $query=self::updateAll(['shift_type_id'=>$confirm_type,'rest_type'=>0,'leave_type'=>2,'leave_type_id'=>1],['id'=>$id]);

                }else if($or_work_half==''&&$leave_type=='1'){//半天班->假
                    $query=self::updateAll(['shift_type_id'=>$confirm_type,'rest_type'=>0,'leave_type'=>2,'leave_type_id'=>1],['id'=>$id]);
                }else if($or_work_half==''&&$orange_type=='-3'){//半天班->公休
                    $query=self::updateAll(['shift_type_id'=>$confirm_type,'rest_type'=>2,'leave_type'=>0,'leave_type_id'=>0],['id'=>$id]);
                }
            }else if( $or_work_half>0 ){//如果公休刚好合适
                if($or_work_half==0){
                    if($is_over_rest==0.5){//如果刚好多休息0.5天,
                       $query=self::updateAll(['shift_type_id'=>$confirm_type,'rest_type'=>2,'leave_type'=>0,'leave_type_id'=>0],['id'=>$id]);
                    }else{
                        $query=self::updateAll(['shift_type_id'=>$confirm_type,'rest_type'=>0,'leave_type'=>2,'leave_type_id'=>1],['id'=>$id]);
                    }
                    
                }else if($or_work_half==1){//半天班->半天班
                    $query=self::updateAll(['shift_type_id'=>$confirm_type],['id'=>$id]);
                }else if($or_work_half==''&&$orange_type=='-100'){//半天班->空白
                    $query=self::updateAll(['shift_type_id'=>$confirm_type,'rest_type'=>0,'leave_type'=>2,'leave_type_id'=>1],['id'=>$id]);

                }else if($or_work_half==''&&$orange_type=='-1'){//半天班->夜休
                    $query=self::updateAll(['shift_type_id'=>$confirm_type,'rest_type'=>0,'leave_type'=>2,'leave_type_id'=>1],['id'=>$id]);

                }else if($or_work_half==''&&$orange_type=='-2'){//半天班->补休
                    $query=self::updateAll(['shift_type_id'=>$confirm_type,'rest_type'=>0,'leave_type'=>2,'leave_type_id'=>1],['id'=>$id]);

                }else if($or_work_half==''&&$leave_type=='1'){//半天班->假
                    $query=self::updateAll(['shift_type_id'=>$confirm_type,'rest_type'=>0,'leave_type'=>2,'leave_type_id'=>1],['id'=>$id]);
                }else if($or_work_half==''&&$orange_type=='-3'){//半天班->公休
                    $query=self::updateAll(['shift_type_id'=>$confirm_type,'rest_type'=>2,'leave_type'=>0,'leave_type_id'=>0],['id'=>$id]);
                }
            }else{//如果当天休息不够
                $query=self::updateAll(['shift_type_id'=>$confirm_type,'rest_type'=>2,'leave_type'=>0,'leave_type_id'=>0],['id'=>$id]);
            }

        }else{//全天班->全天班

             $query=self::updateAll(['shift_type_id'=>$confirm_type,'rest_type'=>0,'leave_type'=>0,'leave_type_id'=>0],['id'=>$id]);
        }

        if($query){
            return true;
        }else{
            return false;
        }
    }




    /**
     * @author 吴斌  2018/4/3 修改 
     * 半天班分配假
     * @param int $id 员工每个排班ID
     * @return array $date_format   规范化数组 
     */

    public function addLeaveToHalfShift($id){
        $query=self::updateAll(['leave_type'=>2,'rest_type'=>0,'leave_type_id'=>1],['id'=>$id]);
        if($query){
            return true;
        }else{
            return false;
        }
    }


    /**
     * @author 吴斌  2018/4/3 修改 
     * 半天假变为半天班
     * @param int $id 员工每个排班ID
     * @return array $date_format   规范化数组 
     */

    public function delLeaveToHalfShift($id){
        $query=self::updateAll(['leave_type'=>0,'rest_type'=>2,'leave_type_id'=>0],['id'=>$id]);
        if($query){
            return true;
        }else{
            return false;
        }
    }


    /**
     * @author 吴斌  2018/4/3 修改 
     * 获取某些员工时间段内所有班次
     * @param int $schedule_id 计划ID
     * @param int $emps 员工组
     * @param int $shift_dates 日期组
     * @return array $date_format   规范化数组 
     */
    public function getShiftResultByContions($schedule_id,$emps,$shift_dates){

        $query=self::find()->where(['schedule_id'=>$schedule_id])
            ->andWhere(['in' , 'emp_number' , $emps])
            ->andWhere(['in' , 'shift_date' , $shift_dates])
            ->asArray()
            ->all();
        return $query;
    }



     /**
     * @author 吴斌  2018/4/3 修改 
     * 删除某些员工时间段内所有班次
     * @param int $schedule_id 计划ID
     * @param int $emps 员工组
     * @param int $shift_dates 日期组
     * @return array $date_format   规范化数组 
     */
    public function delShiftResultByContions($schedule_id,$emps,$shift_dates){

        $query=self::deleteAll([ 'and', 'schedule_id = :schedule_id', ['in', 'emp_number', $emps],['in', 'shift_date', $shift_dates]],[ ':schedule_id' => $schedule_id ]);
        return $query;
    }

    /**
     * @author 吴斌  2018/4/3 修改 
     * 发布排班表前的一些操作
     * @param int $schedule_id 计划ID
     * @param int $emps 员工组
     * @param int $shift_dates 日期组
     * @param int $if_leave 是否补假。1是加班补假 0，加班不补偿假
     * @return array $date_format   规范化数组 
     */
    public function publicShiftVerify($scheduleID,$workStation,$confrim_result,$if_leave){
        $week_day = Yii::$app->params['publicRest']['xajdyfyyxb'];
        $typemodel=new ShiftType;
        $orderbymode=new ShiftOrderBy;
        $empmodel=new Employee;
        $shiftdatemodel=new ShiftDate;
        $shiftTypes=$typemodel->getShifType($workStation);
        $shiftTypes = array_column($shiftTypes, NULL, 'id');
        $leave_note='发布排班表时自动操作';

        //1，获取班次和假期冲突的员工和日期
        $LeaveEntitlementService = new LeaveEntitlement;
        $datetmp['messate']='';
        $transaction = Yii::$app->db->beginTransaction();
        try{

            foreach ($confrim_result as $key_confrim => $value_confrim) {
              $emp_number=$value_confrim['emp_number'];
              $shift_date=$value_confrim['shift_date'];
              $shift_type=$value_confrim['shift_type_id'];
              $rest_type=$value_confrim['rest_type'];
              $if_have_leave=$this->getLeaveOfEmpByEmpAndDate($emp_number,$shift_date);

              $is_half_type=isset($shiftTypes[$shift_type]) ? $shiftTypes[$shift_type]['is_work_half']:'';
              $leave_type = $if_have_leave['duration_type'];//'0：1天；1上午；2下午'
        
              //根据日期和员工编号查询是否班次和假期冲突
              if($value_confrim['shift_type_id']>0){
                  //全天班，有假期，销假
                  if($is_half_type==ShiftType::IS_SHIFT_HALF_NO && $leave_type==Leave::LEAVE_TYPE_DAY ){
                    $sa= $LeaveEntitlementService->updateLeaveStatus($emp_number,$id=null,$queryType=0,$shift_date,Leave::LEAVE_TYPE_DAY,$leave_note,NULL,$scheduleID);
                  }else if($is_half_type==ShiftType::IS_SHIFT_HALF_NO && Leave::LEAVE_TYPE_MORNING ){
                     $sa=$LeaveEntitlementService->updateLeaveStatus($emp_number,$id=null,$queryType=0,$shift_date,Leave::LEAVE_TYPE_MORNING,$leave_note,NULL,$scheduleID);
                  }else if($is_half_type==ShiftType::IS_SHIFT_HALF_NO && Leave::LEAVE_TYPE_AFTERNOOTN){
                     $sa=$LeaveEntitlementService->updateLeaveStatus($emp_number,$id=null,$queryType=0,$shift_date,Leave::LEAVE_TYPE_AFTERNOOTN,$leave_note,NULL,$scheduleID);
                  }else if($is_half_type==ShiftType::IS_SHIFT_HALF && Leave::LEAVE_TYPE_DAY){
                    $sa= $LeaveEntitlementService->updateLeaveStatus($emp_number,$id=null,$queryType=0,$shift_date,Leave::LEAVE_TYPE_DAY,$leave_not,NULL,$scheduleID);

                  }  


              }

              //休和假冲突时，销假
              if($value_confrim['shift_type_id']==0 && $value_confrim['leave_type']==0 ){
                if($is_half_type==ShiftType::IS_SHIFT_HALF_NO && $leave_type==Leave::LEAVE_TYPE_DAY ){
                    $LeaveEntitlementService->updateLeaveStatus($emp_number,$id=null,$queryType=0,$shift_date,Leave::LEAVE_TYPE_DAY,$leave_note,NULL,$scheduleID);
                  }else if($is_half_type==ShiftType::IS_SHIFT_HALF_NO && Leave::LEAVE_TYPE_MORNING ){
                     $sa=$LeaveEntitlementService->updateLeaveStatus($emp_number,$id=null,$queryType=0,$shift_date,Leave::LEAVE_TYPE_MORNING,$leave_note,NULL,$scheduleID);
                  }else if($is_half_type==ShiftType::IS_SHIFT_HALF_NO && Leave::LEAVE_TYPE_AFTERNOOTN){
                     $sa=$LeaveEntitlementService->updateLeaveStatus($emp_number,$id=null,$queryType=0,$shift_date,Leave::LEAVE_TYPE_AFTERNOOTN,$leave_note,NULL,$scheduleID);
                  }else if($is_half_type==ShiftType::IS_SHIFT_HALF && Leave::LEAVE_TYPE_DAY){
                    $sa=$LeaveEntitlementService->updateLeaveStatus($emp_number,$id=null,$queryType=0,$shift_date,Leave::LEAVE_TYPE_DAY,$leave_note,NULL,$scheduleID);

                  }  
              }
            }
      
            //获取所有班次的empmumber
            $all_shift_emp=$orderbymode->getShiftOrderBy($scheduleID);
            $all_shift_emp=array_column($all_shift_emp, 'emp_number');
            
            //获取休息的人信息
        
            $rest=array();
            $leave=array();
            $rest['hole']=array();
            $rest['half']=array();
            $leave['hole']=array();
            $leave['half']=array();
            $night=array();
            $restnew=array();

            foreach ($confrim_result as $key_c => $value_c) {
                $type_id_one=$value_c['shift_type_id'];
                if($value_c['rest_type']==1){//获取全天休
                    $rest['hole'][$key_c]=$value_c;
                }
                if($value_c['rest_type']==2){//获取半天休
                    $rest['half'][$key_c]=$value_c;
                }

                if($value_c['leave_type']==1){//全天假期
                    $leave['hole'][$key_c]=$value_c;
                }

                if($value_c['leave_type']==2){//半天假
                    $leave['half'][$key_c]=$value_c;
                }

                if(isset($shiftTypes[$type_id_one])&&$shiftTypes[$type_id_one]['is_night_shift']==ShiftType::IS_SHIFT_NIGHT){
                    $night[$key_c]=$value_c;
                }
            }

            $restnew=array_merge($rest['hole'],$rest['half']);

            //全天都在休息的人员编号
            //1，全是上班的人，安排两天补休
            //一周七天都在上班的人=所有上班人 -  周内有休息的人
            $rest_number=array_unique(array_column($rest['hole'], 'emp_number'));
            $no_rest_emp=array_diff($all_shift_emp, $rest_number);

            //获取要休假的员工
            $leave_week=array();
            $tmp4=array();
            $leave_combine=array();
            $total_leave_day=array();
            $rest_combine=array();
            $total_rest_day=array();

            //获取七天全是休假的人员
            if(isset($leave['hole'])&&count($leave['hole'])>0){
                foreach ($leave['hole'] as $k4 => $v4) {
                    $tmp_emp=$v4['emp_number'];
                    $tmp4[$tmp_emp][$k4]=$v4;
                }
            }
            if(isset($tmp4)){
                foreach ($tmp4 as $k5 => $v5) {
                     if(count($v5)==7){
                        $leave_week[]=$k5;
                     }
                 }
            }

            //全是休假的人，如果休息天数不够，是不安排补休；所以将全是休假的人剔除；
            foreach ($no_rest_emp as $k6 => $v6) {
                if(in_array($v6, $leave_week)){
                 unset($no_rest_emp[$k6]);
                }
            }

            //计算每个员工总共休息天
            if(isset($restnew)){
                foreach ($restnew as $k1 => $v1) {
                    $emp=$v1['emp_number'];
                    if($v1['rest_type']==ShiftResult::IS_REST_HALF){
                      $h[$emp][$k1]=0.5;
                    
                    }else if($v1['rest_type']==ShiftResult::IS_REST_DAY){
                      $h[$emp][$k1]=1;
                   }
                }
            }
            foreach ($h as $key_h => $value_h) {
                $total_rest_day[$key_h]=array_sum($value_h);
            }

            $leave_new=array();
            $leave_new=array_merge($leave['hole'],$leave['half']);
            if(isset($leave_new)){
                foreach ($leave_new as $k7 => $v7) {
                  $emp_number=$v7['emp_number'];
                  $leave_combine[$emp_number][$k7]=$v7;
                }

            }

            //计算每个员工总共休假天
            if(isset($leave_combine)){
                foreach ($leave_combine as $k8 => $cl) {
                    $cl=array_column($cl, 'leave_type');        
                    foreach ($cl as $k1 => $v1) {
                       if($v1==ShiftResult::IS_LEAVE_HALF){
                         $h2[$k8][$k1]=0.5;
                       }else if($v1==ShiftResult::IS_LEAVE_DAY){

                         $h2[$k8][$k1]=1;
                       }
                    }
                   
                    $total_leave_day[$k8]=array_sum($h2[$k8]);
                }
            }
      
            //员工起晚派假的日期
            $leaveDays=$this->getRosterLeaveDay($scheduleID);
            $leaveDays=array_column($leaveDays, 'shiftdate','emp_number');
    
            //每个员工已经用的假期
            $usedLeave=array();
            $usedLeave=$LeaveEntitlementService->getAlreadyUsedLeave($leaveDays);
            $employee=$empmodel->getEmpByWorkStation($workStation);
            $employee=array_column($employee,null, 'emp_number'); 
            //获取排班日期
            $shiftDate=$shiftdatemodel->getDatesBySchedule($scheduleID);
            $shiftDate=array_column($shiftDate, 'shift_date');
            $firstDate=current($shiftDate);
            $lastDate=end($shiftDate);
            $tmpCanTo=array();

            if(isset($total_leave_day)){
                foreach ($total_leave_day as $key_last => $value_last) {
                    //获取每个员工实际派假=期望派假-已用派
                    $canuseLeave=$value_last-$usedLeave[$key_last];
                    //余假池还剩多少假期
                    $orange_total_leavel= $LeaveEntitlementService->getEntitlementSurplusDay($key_last,'',$firstDate,$lastDate,1);

                    $shoulePai=$canuseLeave-$orange_total_leavel;
                    //如果实际需要的派假数大于可用的天数
                    if($canuseLeave>$orange_total_leavel){
                        $name=$employee[$key_last]['emp_firstname'];
                        $tmpCanTo[$key_last]=$name.'总共派假'.$value_last.'天;实际已派'.$usedLeave[$key_last].'天;实际还需派假'.$canuseLeave.'天;  余假池还剩'. $orange_total_leavel;
                    }
                }
            }

            if(isset($tmpCanTo)&&count($tmpCanTo)>0){
                $result['status']=false;
                $result['message']=implode("; \n",$tmpCanTo);
                return $result;
            }
            
            //给一周没有安排休的人安排两天加班调休
            if($if_leave==1){
                if(isset($no_rest_emp)&&count($no_rest_emp)>0){
                    foreach ($no_rest_emp as $k3 => $v3) {
                        $a=$LeaveEntitlementService->changeEntitlementDays($v3,+2,'',$leave_note,NULL,NULL,$scheduleID);
                        if($a==false){
                            $datetmp['messate']='安排假期不成功';
                            throw new \Exception();
                        }
                    }
                }
            }

            //如果休息天小于两天，自动增加加班天休假
            $week_day = Yii::$app->params['publicRest']['xajdyfyyxb'];
            if($if_leave==1){
                if(isset($total_rest_day)&&count($total_rest_day)>0){
                    foreach ($total_rest_day as $k2 => $v2) {
                        $short_day=$v2-$week_day;
                        if($short_day<0){//如果休息天小于两天，自动增加加班天休假
                            //所需要增加的天数
                            $add='+'.abs($short_day);
                            $LeaveEntitlementService->changeEntitlementDays($k2,$add,'',$leave_note,NULL,NULL,$scheduleID);
                        }else if($short_day > 0){

                        }
                    }
                }  
            }
            if(isset($leave_new)&&count($leave_new)>0){
                foreach ($leave_new as $k => $v) {
                    if($v['leave_type']==ShiftResult::IS_LEAVE_DAY){
                      $ishalf=Leave::LEAVE_TYPE_DAY;
                      
                    }else{
                      $ishalf=Leave::LEAVE_TYPE_AFTERNOOTN;
                    }
                    $statusleave=$LeaveEntitlementService->appointEmployeeLeave($v['emp_number'],$v['leave_type_id'],$v['shift_date'],$v['shift_date'],$ishalf,'2',NULL,NULL,NULL,NULL,$scheduleID);
                    // var_dump($statusleave);exit;
                    // var_dump($v['emp_number'],$v['leave_type_id'],$v['shift_date'],$v['shift_date'],$ishalf);exit;
                    if($statusleave['status']==false){
                        $datetmp['messate']=$statusleave['message'];
                        throw new \Exception();
                    }
                }
            }
            
            
            $transaction->commit();
            $result['status']=true;
            $result['message']='';
            return $result;

        }catch(\Exception $e) {
            $transaction->rollback();
            $result['status']=false;
            $result['message']=$datetmp['messate'];
            return $result;
            
        }

    }


    /**
     * @author 吴斌  2018/1/11 修改 
     * 班次个数判断
     * @param int $schedule_id 计划id
     * @param int $shift_date 日期列表
     * @param int $shift_type_list 班次类型列表
     * @return object | 对象
     */
   
    public function typeCountJudge($schedule_id,$shift_date,$shift_type_list){

        $shift_types=array();
        $week=get_week($shift_date);
        $confirmmodel=new ShiftResultConfirm;
        $shift_date=strtotime($shift_date);
        $shift_date=date('Y-m-d',$shift_date);
        $dateforshift=$confirmmodel->getShiftResultByDate($schedule_id,$shift_date);
        if(isset($dateforshift)&&!empty($dateforshift)){
            foreach ($shift_type_list as $key_1 => $value_1) {
                $weeks=json_decode($value_1['week_select']);
                $typeid=$value_1['id'];
                foreach ($weeks as $key_2 => $value_2) {
                   $format[$typeid][$value_2]['week']=$value_2;
                   $format[$typeid][$value_2]['id']=$typeid;
                   $format[$typeid][$value_2]['require_employee']=$value_1['require_employee'];
                   $format[$typeid][$value_2]['name']=$value_1['name'];
                }
            }
            //获取某一天的所有班次
            foreach ($dateforshift as $key_3 => $value_3) {
                $week_3=get_week($value_3['shift_date']);
                $typeid_3=$value_3['shift_type_id'];
                $format3[$typeid_3][$week_3]['week']=$week_3;
                $format3[$typeid_3][$week_3]['id']=$typeid_3;
                $format3[$typeid_3][$week_3]['totaltype']=$value_3['totaltype'];
            }

            foreach ($format as $key_4 => $value_4) {
                //原则上班次特定天所需人数
                if(isset($value_4[$week])){
                    $required=$value_4[$week]['require_employee'];
                    if(isset($format3[$key_4][$week])){
                        //实际该天该班次个数
                        $count_now=$format3[$key_4][$week]['totaltype'];
                    }else{
                        $count_now=0;
                    }

                    $data['id']=$value_4[$week]['id'];
                    $data['name']=$value_4[$week]['name'];
                    $data['require_employee']=$required;
                    $data['now_have']=$count_now;
                    $data['diff']=$count_now-$required;
                    $shift_types[$key_4]=$data;

                }

            }
        }else{
            foreach ($shift_type_list as $key => $value) {
                $data['id']=$value['id'];
                $data['name']=$value['name'];
                $data['require_employee']=$value['require_employee'];
                $data['diff']=0;
                $shift_types[$key]=$data;
                
            }
        }

        return  $shift_types;

    }


     /**
     * @author 吴斌  2018/7/19 修改 
     * 半天班派假
     * @param array $emp_number 员工id
     * @param array $start_date 开始时间
     * @param array $end_date 结束时间
     * @return array | 班次统计
     */

    public function setLeaves($schedule_id,$emp_number=null){

        $schedule=Schedule::find()->where('id =:schedule_id ',[':schedule_id'=>$schedule_id])->one();
        $copy_type=$schedule->copy_type;
        $workStation=$schedule->location_id;

        if($emp_number==null){
            $assignment_list=$this->getRosterResultConfirm($schedule_id);
        }else{
            $assignment_list=$this->getShiftOneEmp($schedule_id,$emp_number);
        }
        $typemodel=new ShiftType;
        $night_id='';
        $orderbymode=new ShiftOrderBy;
        $night=$typemodel->getNightType($workStation);
        if(isset($night)){
            $night_id=$night->id;
        }

        $shift_on_result=array();
        $new_leave['holday']=array();
        $new_leave['halfleave']=array();
        $new_rest['one']=array();
        $new_rest['half']=array();
        $night_emp=array();
        $restall=array();


        //查询有谁在上夜班
        foreach ($assignment_list as $ks1 => $va1) {

            if($va1['shift_type_id']>0){
                $shift_on_result[$ks1]=$va1;
            }
            if($va1['leave_type']==1){
                $new_leave['holday'][$ks1]=$va1;
            }

            if($va1['leave_type']==2){
                $new_leave['halfleave'][$ks1]=$va1;
            }

            if($va1['rest_type']==1 && $va1['shift_type_id']!=-100){
                $new_rest['one'][$ks1]=$va1;
            }

            if($va1['rest_type']==2){
                $new_rest['half'][$ks1]=$va1;
            }
            
            if($va1['shift_type_id']==$night_id){
                 $night_emp[$va1['emp_number']][$ks1]=$va1;
            }

            if($va1['rest_type']>0){
                 $restall[$va1['emp_number']][$ks1]=$va1;
            }
            
        }


        $night_data=array();
        $new_data1=array();
        $new_data2=array();

       //每个员工夜班个数
        foreach ($night_emp as $k_n3 => $v_n3) {
           $night_data[$k_n3]=count($v_n3);
        }

        foreach ($new_rest['one'] as $k_n1 => $v_n1) {
           $new_data1[$v_n1['emp_number']][$k_n1]=$v_n1;
        }

        foreach ($new_rest['half'] as $k_n7 => $v_n7) {
           $new_data2[$v_n7['emp_number']][$k_n7]=$v_n7;
        }

        $sholud=2;
        if($copy_type=='two'){
            $sholud=4;
        }

        $res=array();
        $overtop=array();

        $overlow=array();

        $res=array_keys($restall);

        $new_da=array();
        $if_enough=array();
        $should_rest=array();
        //new_data1：休息全天的人；new_data2：休息半天的人

        //现有休息天数
        foreach ($new_data1 as $k_n2 => $v_n2) {
           $half_count='';
           //半天换算为整体
           $half_count=isset($new_data2[$k_n2])?count($new_data2[$k_n2])/2:0;
           $new_da[$k_n2]=count($v_n2)+$half_count;

           if(isset($night_data[$k_n2])){
             $if_enough[$k_n2]=($night_data[$k_n2]+$sholud)-$new_da[$k_n2];
           }else{
             $if_enough[$k_n2]=$sholud-$new_da[$k_n2];
           }
           //如果$should_rest>0,每天公休不足两天
           $should_rest[$k_n2]=$if_enough[$k_n2];

           if($if_enough[$k_n2]<0){//获取休息天大于两天的班次
                $overtop[$k_n2]=$if_enough[$k_n2];
           }


           if($if_enough[$k_n2]>0){//获取休息天小于两天的班次

             $overlow[$k_n2]=$if_enough[$k_n2];
                    
           }

        }


        //判断每个员工半天班个数
        $half_shift=array();
        foreach ($new_rest['half'] as $key_half => $value_half) {
           $half_emp=$value_half['emp_number'];
           if($value_half['leave_type']==0){
                $half_shift[$half_emp][$key_half]=$value_half;
           }
        }

        //判断每个员工半天假的个数

        $half_leave=array();
        foreach ($new_leave['halfleave'] as $key_half_2 => $value_half_2) {
           $half_leave_emp=$value_half_2['emp_number'];
           
            $half_leave[$half_leave_emp][$key_half_2]=$value_half_2;
           
        }




        foreach ($overlow as $key_low => $value_low) {
            $sholud_low=abs($value_low);
            $combine_low=isset($half_leave[$key_low])?count($half_leave[$key_low])*0.5:0;
            //如果半天班加总共天数小于或等于多休的天数，则把所有半天班都变成半天假；例如多休三天，其中两天半都是半天班，则把所有半天班变为半天班/假
            if(isset($half_leave[$key_low])){
                if( $combine_low < $sholud_low || $combine_low == $sholud_low ){
                    foreach ($half_leave[$key_low] as $key_half3 => $value_half3) {
 
                        //半天假变半天班
                        $this->delLeaveToHalfShift($value_half3['id']);

                    }
                }else{//例如多休半天天，其中两天个半天班，则把其中一个半天班变为半天班/假
                    //判断
                    $chucount1=$sholud_low/0.5;
                    $output1=array();

                    $chucount1 = array_slice($half_leave[$key_low], 0,$chucount1);
                    foreach ($chucount1 as $key_half4 => $value_half4) {
                        //半天班派假
                        $this->delLeaveToHalfShift($value_half4['id']);
                    } 
                }
            }
        }

        foreach ($overtop as $key_over => $value_ove) {
            $sholud=abs($value_ove);

            $combine=isset($half_shift[$key_over])?count($half_shift[$key_over])*0.5:0;

            //如果半天班加总共天数小于或等于多休的天数，则把所有半天班都变成半天假；例如多休三天，其中两天半都是半天班，则把所有半天班变为半天班/假
            if(isset($half_shift[$key_over])){
                if( $combine< $sholud || $combine == $sholud ){
                    foreach ($half_shift[$key_over] as $key_half1 => $value_half1) {

                        //半天班派假
                        $this->addLeaveToHalfShift($value_half1['id']);

                    }
                }else{//例如多休半天天，其中两天个半天班，则把其中一个半天班变为半天班/假
                    //判断
                    $chucount=$sholud/0.5;
                    $output=array();

                    $output = array_slice($half_shift[$key_over], 0,$chucount);
                    foreach ($output as $key_half2 => $value_half2) {
                        //半天班派假
                        $this->addLeaveToHalfShift($value_half2['id']);
                    } 
                }
            }
        }


      
    }


        /**
     * @author 吴斌  2018/7/19 修改 
     * 获取某员工某天的班次
     * @param array $result_id 班次id 
     * @param array $workStation 员工所属组id
     * @return array | 日期
     */
    public function getConfrimById($emday,$workStation){
 
        $typemode=new ShiftType;
        $shiftTypes=$typemode->getShifType($workStation);
        $shiftTypes = array_column($shiftTypes, NULL, 'id');

        $type_id=$emday['shift_type_id'];
        $leave_type=$emday['leave_type'];
        $rest_type=$emday['rest_type'];
        $shift_date=$emday['shift_date'];
        $emp_number=$emday['emp_number'];
        $data=array();
        $frist_type_id=$emday['frist_type_id'];
        $second_type_id=$emday['second_type_id'];
        $third_type_id=$emday['third_type_id'];
        $leave_type_id=$emday['leave_type_id'];

        $leaveenmodel=new LeaveEntitlement;
        $leaveTypeList=$leaveenmodel->getLeaveTypeList();
        $leaveTypeList=array_column($leaveTypeList,NULL, 'id');
        $errLeaveMes='';
        $type_name_1='';
        $type_name_2='';
        $type_name='';
        $leaveid='';
        $typeforshift='';
        $type_name_3='';
        $type_num='';

        if($emday){
            if($frist_type_id!=NULL&&$second_type_id!=NULL&&$third_type_id!=NULL){

                if($frist_type_id==$second_type_id&&$second_type_id==$third_type_id){
                    $type_name=isset($shiftTypes[$frist_type_id]['name'])?$shiftTypes[$frist_type_id]['name']:'';
                }else{
                    $type_name_1=isset($shiftTypes[$frist_type_id]['name'])?$shiftTypes[$frist_type_id]['name']:'';
                    $type_name_2=isset($shiftTypes[$second_type_id]['name'])?$shiftTypes[$second_type_id]['name']:'';
                    $type_name_3=isset($shiftTypes[$third_type_id]['name'])?$shiftTypes[$third_type_id]['name']:'';
                    $type_name=$type_name_1.'/'.$type_name_2.'/'.$type_name_3;
                }
                $type_num=$type_id;
                $typeforshift=3;
                $leaveid='';



            }else if($frist_type_id!=NULL&&$second_type_id!=NULL&&$third_type_id==NULL){

                if($frist_type_id==$second_type_id){
                    $type_name=isset($shiftTypes[$frist_type_id]['name'])?$shiftTypes[$frist_type_id]['name']:'';
                }else{
                    $type_name_1=isset($shiftTypes[$frist_type_id]['name'])?$shiftTypes[$frist_type_id]['name']:'';
                    $type_name_2=isset($shiftTypes[$second_type_id]['name'])?$shiftTypes[$second_type_id]['name']:'';
                    $type_name=$type_name_1.'/'.$type_name_2;
                }

                $if_have_leave=$this->getLeaveOfEmpByEmpAndDate($emp_number,$shift_date);

                if($if_have_leave){
                    $errLeaveMes="\n".'假期与班次冲突';
                }




                $type_num=$type_id;
                $typeforshift=3;
                $leaveid='';

            }else if($frist_type_id!=NULL&&$second_type_id==NULL&&$third_type_id==NULL){   

                $if_have_leave=$this->getLeaveOfEmpByEmpAndDate($emp_number,$shift_date);
                $type_name_1=isset($shiftTypes[$frist_type_id]['name'])?$shiftTypes[$frist_type_id]['name']:'';
                if(isset($shiftTypes[$type_id])&&$shiftTypes[$type_id]['is_work_half']==1){//半天班                    
                    if($leave_type==2 && $rest_type==0){//半天假
                        if($if_have_leave){
                            if($if_have_leave['duration_type']==1){
                                $type_name_2=$if_have_leave['name'].'(上午)';
                            }else if($if_have_leave['duration_type']==2){
                                 $type_name_2=$if_have_leave['name'].'(下午)';
                            }else{
                                 $type_name_2=$if_have_leave['name'];
                            }
                            $leaveid=isset($if_have_leave['id'])?$if_have_leave['id']:$emday['leave_type_id'];
                        }else{

                            $leave_name=isset($leaveTypeList[$leave_type_id]['name'])?$leaveTypeList[$leave_type_id]['name']:'假';
                            $type_name_2=$leave_name.'(未派假)';
                            $leaveid='';
                        }
                        
                    }
                    if($leave_type==0){//判断是否是半天休
                        if($if_have_leave){
                            $type_name_2=$if_have_leave['name'];
                        }else{
                            if($type_id==0){
                                $type_name_2='休息';
                            }else if($type_id==ShiftResult::NIGHT_REST){
                                $type_name_2='夜休';
                            }else if($type_id==ShiftResult::BUSY_REST){
                                $type_name_2='补休';
                            }else if($type_id==ShiftResult::GENERAN_REST){
                                $type_name_2='公休';
                            }else{
                                $type_name_2='公休';
                            }
                        }

                        $leaveid='';
                    }

                    $type_num=$type_id;
                    $typeforshift=3;
                    $type_name=$type_name_1.'/'.$type_name_2;

                }else{//如果是全天班

                    if($if_have_leave){
                        $errLeaveMes="\n".'假期与班次冲突';
                        $leaveid=isset($if_have_leave['id'])?$if_have_leave['id']:$emday['leave_type_id'];
                        $type_name_2=$if_have_leave['name'];
                        
                    }else{
                        $errLeaveMes='';
                        $type_name_2='公休';
                    }
                    $type_num=$type_id;
                    $typeforshift=3;
                    $type_name=$type_name_1.'/'.$type_name_2;
                    $leaveid='';
                }

            }else if($frist_type_id==NULL&&$second_type_id!=NULL&&$third_type_id!=NULL){


                if($second_type_id==$third_type_id){
                    $type_name=isset($shiftTypes[$second_type_id]['name'])?$shiftTypes[$second_type_id]['name']:'';

                }else{
                    $type_name_1=isset($shiftTypes[$second_type_id]['name'])?$shiftTypes[$second_type_id]['name']:'';
                    $type_name_2=isset($shiftTypes[$third_type_id]['name'])?$shiftTypes[$third_type_id]['name']:'';
                    $type_name=$type_name_1.'/'.$type_name_2;
                }
                $leaveid='';
                $typeforshift=3;
                $type_num=$second_type_id;
                $type_name=$type_name;

            }else if($frist_type_id==NULL&&$second_type_id==NULL&&$third_type_id!=NULL){

                $type_name_1=isset($shiftTypes[$third_type_id]['name'])?$shiftTypes[$third_type_id]['name']:'';
                //判断该班次是不是半天班
                $type_id=$third_type_id;
                $if_have_leave=$this->getLeaveOfEmpByEmpAndDate($emp_number,$shift_date);
                if(isset($shiftTypes[$type_id])&&$shiftTypes[$type_id]['is_work_half']==1){//半天班
                    
                    if($leave_type==2 && $rest_type==0){//半天假
                        if($if_have_leave){
                            if($if_have_leave['duration_type']==1){
                                $type_name_2=$if_have_leave['name'].'(上午)';
                            }else if($if_have_leave['duration_type']==2){
                                 $type_name_2=$if_have_leave['name'].'(下午)';
                            }else{
                                 $type_name_2=$if_have_leave['name'];
                            }
                            $type_num='-200';
                        }else{

                            $leave_name=isset($leaveTypeList[$leave_type_id]['name'])?$leaveTypeList[$leave_type_id]['name']:'假';
                            $type_name_2=$leave_name.'(未派假)';
                            $type_num=$type_id;
                        }
                        
                    }
                    if($leave_type==0){//判断是否是半天休
                        if($if_have_leave){
                            $type_name_2=$if_have_leave['name'];
                        }else{
                            if($type_id==0){
                                $type_name_2='休息';
                            }else if($type_id==ShiftResult::NIGHT_REST){
                                $type_name_2='夜休';
                            }else if($type_id==ShiftResult::BUSY_REST){
                                $type_name_2='补休';
                            }else if($type_id==ShiftResult::GENERAN_REST){
                                $type_name_2='公休';
                            }else{
                                $type_name_2='公休';
                            }
                        }
                    }

                    $type_name=$type_name_1.'/'.$type_name_2;
                }else{//如果是全天班
                    if($if_have_leave){
                        $errLeaveMes="\n".'假期与班次冲突';
                    }else{
                        $errLeaveMes='';
                    }
                    $type_name=$type_name_1;
                }

                $leaveid='';
                $type_num=$type_id;
                $typeforshift=3;

            }else if($frist_type_id==NULL&&$second_type_id!=NULL&&$third_type_id==NULL){

                $type_name_1=isset($shiftTypes[$second_type_id]['name'])?$shiftTypes[$second_type_id]['name']:'';
                //判断该班次是不是半天班
                $type_id=$second_type_id;
                $if_have_leave=$this->getLeaveOfEmpByEmpAndDate($emp_number,$shift_date);
                if($shiftTypes[$type_id]['is_work_half']==1){//半天班
                    
                    if($leave_type==2 && $rest_type==0){//半天假
                        if($if_have_leave){
                            if($if_have_leave['duration_type']==1){
                                $type_name_2=$if_have_leave['name'].'(上午)';
                            }else if($if_have_leave['duration_type']==2){
                                 $type_name_2=$if_have_leave['name'].'(下午)';
                            }else{
                                 $type_name_2=$if_have_leave['name'];
                            }
                            $type_num='-200';
                        }else{


                            $leave_name=isset($leaveTypeList[$leave_type_id]['name'])?$leaveTypeList[$leave_type_id]['name']:'假';
                            $type_name_2=$leave_name.'(未派假)';
                            $type_num=$type_id;
                        }
                        
                    }
                    if($leave_type==0){//判断是否是半天休
                        if($if_have_leave){
                            $type_name_2=$if_have_leave['name'];
                        }else{
                            if($type_id==0){
                                $type_name_2='休息';
                            }else if($type_id==ShiftResult::NIGHT_REST){
                                $type_name_2='夜休';
                            }else if($type_id==ShiftResult::BUSY_REST){
                                $type_name_2='补休';
                            }else if($type_id==ShiftResult::GENERAN_REST){
                                $type_name_2='公休';
                            }else{
                                $type_name_2='公休';
                            }
                        }
                    }

                    $type_name=$type_name_1.'/'.$type_name_2;
                }else{//如果是全天班
                    // var_dump($if_have_leave);exit;
                    if($if_have_leave){
                        if($if_have_leave['duration_type']==0){//如果是全天班
                            $errLeaveMes="\n".'假期与班次冲突';
                            $type_name_2=$if_have_leave['name'];
                        }else{
                             $errLeaveMes='';
                             $type_name_2=$if_have_leave['name'];
                        }
                    }else{
                        $type_name_2='休';
                    }
                    $type_name=$type_name_2.'/'.$type_name_1;
                }

                $type_num=$type_id;
                $typeforshift=3;

            }else if($frist_type_id==NULL&&$second_type_id==NULL&&$third_type_id==NULL){


                var_dump('sss');exit;

                if($emday['shift_type_id']>0){
                    $type_name=isset($shiftTypes[$emday['shift_type_id']]['name'])?$shiftTypes[$emday['shift_type_id']]['name']:'';
                    $type_num=$type_id;
                    $typeforshift=3;
                    $leaveid='';
                }else{
        
                    //判断是不是假期
                    $if_have_leave=$this->getLeaveOfEmpByEmpAndDate($emp_number,$shift_date);
                    if($leave_type>0){
                        $type_num=0;
                        if($leave_type==1&&$type_id==0){//全天假
                            if($if_have_leave){
                                $type_name=$if_have_leave['name'];
                            }else{

                                $leave_name=isset($leaveTypeList[$leave_type_id]['name'])?$leaveTypeList[$leave_type_id]['name']:'假';
                                $type_name=$leave_name.'(未派假)';
                            }
                            $typeforshift=2; 
                            $type_num='-200';
                            $leaveid=isset($if_have_leave['id'])?$if_have_leave['id']:$emday['leave_type_id'];
                                                                     
                        }else if($leave_type==2&&$type_id==0){//半天假
                            if($if_have_leave){

                                if($if_have_leave['duration_type']==1){
                                    $type_name=$if_have_leave['name'].'(上午)';
                                }else if($if_have_leave['duration_type']==2){
                                     $type_name=$if_have_leave['name'].'(下午)';
                                }else{
                                     $type_name=$if_have_leave['name'];
                                }
                            }else{

                                $leave_name=isset($leaveTypeList[$leave_type_id]['name'])?$leaveTypeList[$leave_type_id]['name']:'假';
                                $type_name=$leave_name.'(未派假)';
                            }
                        }

                    }else{

                        if($if_have_leave){
                            $type_name=$if_have_leave['name'];
                            $type_num='-200';
                            $leaveid=$if_have_leave['id'];
                            $typeforshift=2; 
                        }else{
                            if($type_id==0){
                                $type_name='公休';
                                $type_num='0';
                            }else if($type_id==ShiftResult::NIGHT_REST){
                                $type_name='夜休';
                                $type_num='0';
                            }else if($type_id==ShiftResult::BUSY_REST){
                                $type_name='补休';
                                $type_num='0';
                            }else if($type_id==ShiftResult::GENERAN_REST){
                                $type_name='公休';
                                $type_num='0';
                            }else{
                                 $type_name='';
                                 $type_num='-100';
                            }

                            $leaveid='';
                            $typeforshift=1;
                        }
                        
                        
                        
                    }
                }
            }


            $data_time['first']['mark']=1;
            $data_time['first']['type_id']=$frist_type_id;
            $data_time['first']['name']=isset($shiftTypes[$frist_type_id])?$shiftTypes[$frist_type_id]['name']:'休';
            $data_time['first']['start_time']=isset($shiftTypes[$frist_type_id])?$shiftTypes[$frist_type_id]['start_time']:'08:00';
            $data_time['first']['end_time']=isset($shiftTypes[$frist_type_id])?$shiftTypes[$frist_type_id]['end_time_afternoon']:'12:00';


            $data_time['second']['mark']=2;
            $data_time['second']['type_id']=$second_type_id;
            $data_time['second']['name']=isset($shiftTypes[$second_type_id])?$shiftTypes[$second_type_id]['name']:'休';
            $data_time['second']['start_time']=isset($shiftTypes[$second_type_id])?$shiftTypes[$second_type_id]['start_time_afternoon']:'12:00';
            $data_time['second']['end_time']=isset($shiftTypes[$second_type_id])?$shiftTypes[$second_type_id]['end_time']:'18:00';

            

            $data_time['third']['mark']=3;
            $data_time['third']['type_id']=$third_type_id;
            $data_time['third']['name']=isset($shiftTypes[$third_type_id])?$shiftTypes[$third_type_id]['name']:'休';
            $data_time['third']['start_time']=isset($shiftTypes[$third_type_id])?$shiftTypes[$third_type_id]['time_start_third']:'18:00';
            $data_time['third']['end_time']=isset($shiftTypes[$third_type_id])?$shiftTypes[$third_type_id]['time_end_third']:'24:00';

            $data['status']=true;
            $data['name']=$type_name;
            $data['type_id']=$type_num;
            $data['leave_id']=$leaveid;
            $data['show_type']=$typeforshift;
            $data['emp_number']=$emp_number;
            $data['leave_error']=$errLeaveMes;
            $data['result_id']=$emday['id'];
            $data['timeformat']=$data_time;



        }else{
            $data['status']=false;
        }



        return $data;

    }

}
