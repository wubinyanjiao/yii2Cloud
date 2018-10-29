<?php

namespace common\models\shift;

use Yii;
use \common\models\shift\base\ShiftResult as BaseShiftResult;
use yii\helpers\ArrayHelper;
use common\models\shift\Schedule;
use common\models\shift\ShiftDate;
use common\models\shift\ShiftResultConfirm;
use common\models\shift\ShiftType;
use common\models\shift\ShiftOrderBy;
use common\models\shift\EmpSkill;
use common\models\shift\ShiftTypeDetail;
use common\models\employee\Employee;
use common\models\leave\LeaveEntitlement;
use common\models\attendance\AttendanceRecord;
use common\models\user\User;

/**
 * This is the model class for table "ohrm_work_shift_result".
 */
class ShiftResult extends BaseShiftResult
{
    const NIGHT_REST = -1;//夜休
    const GENERAN_REST = -3;//公休
    const BUSY_REST = -2;//补休
    const NO_REST_SHIFT = -100;//空白

    const IS_REST_HALF = 2;//半天班
    const IS_REST_DAY = 1;//全天班
    const IS_REST_NO = 0;//全天班

    const IS_LEAVE_HALF = 2;//半天假
    const IS_LEAVE_DAY = 1;//全天假
    const IS_LEAVE_NO = 0;//没有假

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

    public function getShiftResult($schedule_id)
    {
        
        $data = self::find()->where('schedule_id = :sid', [':sid' => $schedule_id])->asArray()->all();
        return $data;
    }

     public function getShiftsByScheduleList($schedulelist){
        $query = self::find()->where(['in','schedule_id',$schedulelist])->orderBy('shift_date ASC')->asArray()->all();
        return $query;
    }

    
    public function addShiftResult($data){
        if ($this->load($data) && $this->save()){
            return true;
        }else {
             return false;
        }
    }


    public function delResult($schedule_id)
    {
        
        $data = self::deleteAll('schedule_id = :sid', [':sid' => $schedule_id]);
        return $data;
    }


    /**
     * @author 吴斌  2018/4/3 修改 
     * 获取员工班次信息
     * @return array $date_format   规范化数组 
     */
    public function getResultByEmpAndDate($emp,$shift_date,$schedule_id) {


        $query = self::find()
        ->where(['emp_number'=>$emp])
        ->andWhere(['shift_date'=>$shift_date])
        ->andWhere(['schedule_id'=>$schedule_id])
        ->one();
        return $query;
    }


    /**
     * @author 吴斌  2018/4/3 修改 
     * 将员工班次变为假期
     * @param int $emp 员工编号
     * @param int $shift_date 日期信息
     * @param int $leave_type_id 假期类型id
     * @param int $if_half 1全天假，2半天假
     * @return array $date_format   规范化数组 
     */
    public function changShiftToLeave($emp,$shift_date,$leave_type_id,$if_half) {

        $query=self::updateAll(['shift_type_id'=>0,'rest_type'=>0,'leave_type'=>$if_half,'leave_type_id'=>$leave_type_id],['emp_number'=>$emp,'shift_date'=>$shift_date]);
        return $query;
    }

    /**
     * @author 吴斌  2018/8/3 修改 
     * 获取某天上某个班次的人员列表
     * @param array $schedule_id 计划id 
     * @param int $shift_type_id 班次类型id
     * @param int $shift_date 日期信息
     * @return array  规范化数组 
     */
    public function getResutByIdAndDateAndType($schedule_id,$shift_date,$shift_type_id) {
        $query = self::find()
        ->where(['schedule_id'=>$schedule_id])
        ->andWhere(['shift_date'=>$shift_date])
        ->andWhere(['shift_type_id'=>$shift_type_id])
        ->asArray()
        ->all();
        return $query;
    }

    /**
     * @author 吴斌  2018/7/19 修改 
     * 判断员工是否公休两天
     * @param array $emp_number 员工id
     * @param array $start_date 开始时间
     * @param array $end_date 结束时间
     * @return array | 班次统计
     */

    public function judgeNight($assignment_list,$schedule,$workStation){

        $typemode=new ShiftType;
        $night_emp=array();
        $restall=array();

        $shiftTypes=$typemode->getNightType($workStation);
        $night_id=isset($shiftTypes)?$shiftTypes->id:'';

        $shift_date=$schedule->shift_date;
        $copy_type=$schedule->copy_type;

         //查询有谁在上夜班
        foreach ($assignment_list as $ks1 => $va1) {
            if($va1['shift_type_id']==$night_id){
                 $night_emp[$va1['emp_number']][$ks1]=$va1;
            }

            if($va1['rest_type']>0){
                 $restall[$va1['emp_number']][$ks1]=$va1;
            }
            
        }


        


        //判断夜班后是否是夜修
        $tmpbe=array();

        //查询上周最后一天上夜班的员工信息
        $datebefore=  date("Y-m-d",strtotime("-1 day",strtotime($schedule->shift_date)));
        $lastNithe=$this->getShiftByDateAndType($datebefore,$night_id);
        foreach ($lastNithe as $key_b => $value_b) {
            $tmpbe[$value_b['emp_number']][$key_b]=$value_b;
        }

        //判断夜班后是否是夜修
        $night_emp_new=array();
        $arr_keys=array();
        $nitght_after=array();
        if(count($night_emp)>0){
            if(count($tmpbe)>0){
                foreach ($tmpbe as $ke1 => $va1) {
                    $arr_keys=array_keys($night_emp);
                    if(in_array($ke1, $arr_keys)){
                        $night_emp_new[$ke1]=array_merge($va1,$night_emp[$ke1]);
                    }else{
                        $night_emp_new[$ke1]= $va1;
                    }
                }
            }else{
                $night_emp_new=$night_emp;
            }

            $new_rest_date=array();
            foreach ($night_emp_new as $k_n4 => $v_n4) {
                foreach ($v_n4 as $k_n5 => $v_n5) {
                   $new_rest_date[$k_n4][]=  date("Y-m-d",strtotime("+1 day",strtotime($v_n5['shift_date'])));
                }
            }

            //获取员工夜班后一天的班次
            foreach ($assignment_list as  $k_n6 => $v_n6) {
                foreach ($new_rest_date as $k_n7 => $v_n7) {
                    foreach ($v_n7 as $k_n8 => $v_n8) {
                        if($v_n6['shift_date']==$v_n8&&$v_n6['emp_number']==$k_n7){

                            if($v_n6['shift_type_id']>0){
                                $nitght_after[$k_n7]='夜班后没有夜休';
                            }
                            
                        }
                    }
                }
                
            }
        }

        return $nitght_after;
    }


    /**
     * @author 吴斌  2018/7/19 修改 
     * 判断员工是否公休两天
     * @param array $emp_number 员工id
     * @param array $start_date 开始时间
     * @param array $end_date 结束时间
     * @return array | 班次统计
     */

    public function judgeRest($assignment_list,$schedule,$workStation){

        $typemode=new ShiftType;
        $shiftTypes=$typemode->getNightType($workStation);
        $night_id=isset($shiftTypes)?$shiftTypes->id:'';
        $shift_date=$schedule->shift_date;
        $copy_type=$schedule->copy_type;
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
        $overtop=array();
        $restall=array();
        $new_data3=array();

        $sholud=2;
        if($copy_type=='two'){
            $sholud=4;
        }



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

            if($va1['rest_type']==1 && $va1['shift_type_id']!=-100 && $va1['shift_type_id']!=-2 && $va1['shift_type_id']!=-1){
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

        $new_data3=array_unique(array_column($assignment_list, 'emp_number'));

        foreach ($new_data3 as $k_n2 => $v_n2) {

           $half_count=0;
           //半天换算为整体
           $half_count=isset($new_data2[$v_n2])?count($new_data2[$v_n2])/2:0;
           $whole_count=isset($new_data1[$v_n2])?count($new_data1[$v_n2]):0;
           $new_da=$whole_count+$half_count;
    

           $if_enough=$sholud-$new_da;

           //如果$should_rest>0,每天公休不足两天
           $should_rest[$v_n2]=$if_enough;

           if($if_enough<0){//获取休息天大于两天的班次
                $overtop[$v_n2]=$if_enough;
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
        $data=$should_rest;
        return $data;


    }



    /**
     * @author 吴斌  2018/7/19 修改 
     * 格式化拼接排班数据
     * @param array $schedule_id 计划id 
     * @param int $workStation 组id
     * @param int $data 日期信息
     * @return array | 日期
     */

    public function formatData($schedule_id,$workStation,$data,$if_confrim=null,$copy_type=null,$schedule){

        $confirmmodel = new ShiftResultConfirm;

        //半天班派假
        $confirmmodel->setLeaves($schedule_id);

        $resultmmodel = new ShiftResult;
        $typemode=new ShiftType;
        $orderbymode=new ShiftOrderBy;
        $empskillmodel=new EmpSkill;

        $detailmodel=new ShiftTypeDetail;

        $employeemodel=new Employee;

        $leaveenmodel=new LeaveEntitlement;

        $leaveTypeList=$leaveenmodel->getLeaveTypeList();
        $leaveTypeList=array_column($leaveTypeList,NULL, 'id');

        $usermodel=new User;

        $emp_new_all=array();

        //拼接数据
        if($if_confrim==1){
            $assignment_list=$this->getShiftResult($schedule_id);
        }else{
            $assignment_list=$confirmmodel->getRosterResultConfirm($schedule_id);
        }

        //获取班次类型信息
        $shiftTypes=$typemode->getNightType($workStation);
        $night_id=isset($shiftTypes)?$shiftTypes->id:'';
        
        //获取参与排班的人
        $emp_new_all=$orderbymode->getShiftOrderByAndEmp($schedule_id);
        // $emp_new_all=$employeemodel->getEmpByWorkStation($workStation);
        $emp_new=array_column($emp_new_all, 'emp_number');
        $emp_new_all=array_column($emp_new_all,NULL, 'emp_number');
        $date_list=$data['dateList'];
        $date_list=array_column($date_list,'date');

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
        $overtop=array();
        $restall=array();
        $employeeList=$emp_new;

        $should_rest=$this->judgeRest($assignment_list,$schedule,$workStation);
        $nitght_after=$this->judgeNight($assignment_list,$schedule,$workStation);


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

            if($va1['rest_type']==1 && $va1['shift_type_id']!=-100 && $va1['shift_type_id']!=-2 && $va1['shift_type_id']!=-1){
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
        
        foreach ($assignment_list as $k => $assignment) {
            $employee_num=$assignment['emp_number'];
            $employee_array[$employee_num][]=$assignment;
        }



        

        $emarray=array();

        $empskilldata=array();
        $skempskill=array();

        //获取员工技能
        $empskilldata=$empskillmodel->getEmpSkillListByStation($workStation);

        foreach ($empskilldata as $key_skill => $value_skill) {
            $sklemp=$value_skill['emp_number'];
            $skempskill[$sklemp][$key_skill]=$value_skill['skill_id'];
        }

        foreach ($employee_array as $key => $employee) {

            $i=0;
            $leavid='';
            $form_a=array();
            $form_b=array();
            $form_a['empnum']=$key;
            //获取个人剩余假期数
            $form_a['leavecount']=$leaveenmodel->getEntitlementSurplusDay($key,null,null,null,false);
            if(!isset($emp_new_all[$key])){
                $emp_new_all[$key]=$employeemodel->getEmpByNum($key); 
            }
            $form_a['name']=$emp_new_all[$key]['emp_firstname'];
            $form_a['restcount']=isset($should_rest[$key])?$should_rest[$key]:0;
            $form_a['nighterror']=isset($nitght_after[$key])?$nitght_after[$key]:'';
            $form_a['index']=isset($emp_new_all[$key]['shift_index'])?isset($emp_new_all[$key]['shift_index']):$i;
            $form_b=$confirmmodel->jointData2($employee,$workStation,$key,$date_list,$skempskill,$if_confrim);
            $emarray[$key]=$form_a+$form_b;
            ksort($emarray);
            $i++;
        }


        $emarray=array_replace(array_flip($emp_new), $emarray);


        $new_result=$confirmmodel->jointData($emarray,$if_confrim);
        return $new_result;
    }

    /**
     * @author 吴斌  2018/7/19 修改 
     * 验证技能
     * @param array $emp_skill 员工所拥有技能 
     * @param array $type_skill 班次所需技能
     * @return array | 日期
     */

    public function skillCheck($emp_skill,$type_skill){

         $if_skill_ok=array();
         $data=array();
         if(count($type_skill)>0){
            if(is_array($emp_skill)&&is_array($type_skill)&&$type_skill[0]!=0){//班次需要技能
                $if_skill_ok=array_intersect($emp_skill,$type_skill);
                if(count($if_skill_ok)>0){
                    $data['status']=true;
                    $data['message']='员工技能与该班次所需技能匹配';
                }else{
                    $data['status']=false;
                    $data['message']='员工技能与该班次所需技能不匹配';
                }
            }
         }else{ //班次不需要技能
            $data['status']=true;
            $data['message']='员工技能与该班次所需技能匹配';
         }


        return $data;

    }

    /**
     * @author 吴斌  2018/7/19 修改 
     * 根据日期和ID查找排班信息
     * @param array $shift_date 员工所拥有技能 
     * @param array $shift_type_id 班次所需技能
     * @return array | 日期
     */

    public function getShiftByDateAndType($shift_date,$shift_type_id){
        $query=self::find()->where(['shift_date'=>$shift_date,'shift_type_id'=>$shift_type_id])->asArray()->all();
        return $query;
    }

    /**
     * @author 吴斌  2018/7/19 修改 
     * 根据日期和ID查找排班信息
     * @param array $shift_date 员工所拥有技能 
     * @param array $shift_type_id 班次所需技能
     * @return array | 日期
     */

    public function getEmpShiftByDateAndType($shift_date,$shift_type_id,$emp_number){
        $query=self::find()->where(['shift_date'=>$shift_date,'shift_type_id'=>$shift_type_id,'emp_number'=>$emp_number])->asArray()->all();
        return $query;
    }

    /**
     * @author 吴斌  2018/7/19 修改 
     * 获取某员工某天的班次
     * @param array $shift_date 员工所拥有技能 
     * @param array $emp_number 员工工资号
     * @return array | 日期
     */
    public function getShiftByDateAndEmp($emp_number,$shift_date){
        $query=self::find()->where(['shift_date'=>$shift_date,'emp_number'=>$emp_number])->asArray()->one();
        
        return $query;
    }

    /**
     * @author 吴斌  2018/7/19 修改 
     * 获取某员工某天的班次
     * @param array $shift_date 员工所拥有技能 
     * @param array $emp_number 员工工资号
     * @return array | 日期
     */
    public function getShiftByDateAndEmp2($emp_number,$shift_date,$workStation){
        $emday=self::find()->where(['shift_date'=>$shift_date,'emp_number'=>$emp_number])->asArray()->one();
        $typemode=new ShiftType;
        $shiftTypes=$typemode->getShifType($workStation);
        $shiftTypes = array_column($shiftTypes, NULL, 'id');
        $type_id=$emday['shift_type_id'];
        $leave_type=$emday['leave_type'];
        $rest_type=$emday['rest_type'];
        $data=array();

        $frist_type_id=$emday['frist_type_id'];
        $second_type_id=$emday['second_type_id'];
        $third_type_id=$emday['third_type_id'];

        if($emday){
            $data['status']=true;
            $data['schedule_id']=$emday['schedule_id'];
            $data['shift_type_id']=$type_id;
            $data['first']['mark']=1;
            $data['first']['type_id']=$frist_type_id;
            $data['first']['name']=isset($shiftTypes[$frist_type_id])?$shiftTypes[$frist_type_id]['name']:'休';
            $data['first']['start_time']=isset($shiftTypes[$frist_type_id])?$shiftTypes[$frist_type_id]['start_time']:'00:00';
            $data['first']['end_time']=isset($shiftTypes[$frist_type_id])?$shiftTypes[$frist_type_id]['end_time_afternoon']:'12:00';

            $data['second']['mark']=2;
            $data['second']['type_id']=$second_type_id;
            $data['second']['name']=isset($shiftTypes[$second_type_id])?$shiftTypes[$second_type_id]['name']:'休';
            $data['second']['start_time']=isset($shiftTypes[$second_type_id])?$shiftTypes[$second_type_id]['start_time_afternoon']:'12:00';
            $data['second']['end_time']=isset($shiftTypes[$second_type_id])?$shiftTypes[$second_type_id]['end_time']:'18:00';

            $data['third']['mark']=3;
            $data['third']['type_id']=$third_type_id;
            $data['third']['name']=isset($shiftTypes[$third_type_id])?$shiftTypes[$third_type_id]['name']:'休';
            $data['third']['start_time']=isset($shiftTypes[$third_type_id])?$shiftTypes[$third_type_id]['time_start_third']:'18:00';
            $data['third']['end_time']=isset($shiftTypes[$third_type_id])?$shiftTypes[$third_type_id]['time_end_third']:'24:00';

        }else{
            $data['status']=false;
        }

        return $data;

    }

    /**
     * @author 吴斌  2018/7/19 修改 
     * 获取某员工某天的班次
     * @param array $shift_date 员工所拥有技能 
     * @param array $emp_number 员工工资号
     * @return array | 日期
     */
    public function getShiftByDateAndEmp3($date,$shift_type_id,$time_mark,$schedule_id,$work_station,$empId){

        $empmodel=new Employee;
        $allEmp=$empmodel->getEmpByWorkStation($work_station);
        $allEmp=array_column($allEmp,NULL,'emp_number');
        unset($allEmp[$empId]);

        if($shift_type_id=='gongxiu'){
            $shift_type_id=NULL;
        }

 
        if($time_mark==1){

            $emday=self::find()->select('emp_number')->where(['schedule_id'=>$schedule_id,'frist_type_id'=>$shift_type_id,'shift_date'=>$date])->asArray()->all();

        }else if($time_mark==2){
            $emday=self::find()->select('emp_number')->where(['schedule_id'=>$schedule_id,'second_type_id'=>$shift_type_id,'shift_date'=>$date])->asArray()->all();

        }else if($time_mark==3){
            $emday=self::find()->select('emp_number')->where(['schedule_id'=>$schedule_id,'third_type_id'=>$shift_type_id,'shift_date'=>$date])->asArray()->all();

        }else if($time_mark==0){//整体换班
            if($shift_type_id==NULL){
                $emday=self::find()
                ->select('emp_number')
                ->where(['schedule_id'=>$schedule_id,'shift_date'=>$date])
                ->andWhere(['<','shift_type_id',1])
                ->asArray()
                ->all();
            }else{
                $emday=self::find()->select('emp_number')->where(['schedule_id'=>$schedule_id,'shift_type_id'=>$shift_type_id,'shift_date'=>$date])->asArray()->all();
            }
        }

        $emday=array_unique(array_column($emday, 'emp_number'));


        if($emday){      
            $data['status']=true;
            foreach ($emday as $key => $value) {
                if(isset($allEmp[$value])){
                    $data[$key]['emp_name']=$allEmp[$value]['emp_firstname'];
                    $data[$key]['emp_number']=$value;
                }
                
            }
 

        }else{
            $data['status']=false;
        }

        return $data;

    }



    /**
     * @author 吴斌  2018/7/19 修改 
     * 获取某员工某天的班次
     * @param array $shift_date 员工所拥有技能 
     * @param array $schedule_id 计划id
     * @param array $时间标记 1，2，3
     * @return array | 日期
     */
    public function getResultByformat($schedule_id,$shift_date,$timemark,$work_station,$orange_type){
        $emday=self::find()->where(['shift_date'=>$shift_date,'schedule_id'=>$schedule_id])->asArray()->all();
        $typemode=new ShiftType;
        $shiftTypes=$typemode->getShifType($work_station);
        $shiftTypes = array_column($shiftTypes, NULL, 'id');
        $type_id_list=array();

        $data=array();

        if($emday){
            $data['status']=true;
            if($timemark==1){//第一个时间段
                $type_id_list=array_unique(array_column($emday, 'frist_type_id'));
                $key2 = array_search($orange_type, $type_id_list);
                if ($key2 !== false){
                    array_splice($type_id_list, $key2, 1);
                }
              
                foreach ($type_id_list as $key => $type_id) {

                    $data[$key]['mark']=1;
                    $data[$key]['is_show']=1;
                    $data[$key]['type_id']=($type_id>0)?$type_id:'gongxiu';
                    $data[$key]['type_name']=isset($shiftTypes[$type_id])?$shiftTypes[$type_id]['name']:'休息';
                    $data[$key]['start_time']=isset($shiftTypes[$type_id])?$shiftTypes[$type_id]['start_time']:'00:00';
                    $data[$key]['end_time']=isset($shiftTypes[$type_id])?$shiftTypes[$type_id]['end_time_afternoon']:'00:00';
                }

                
            }else if($timemark==2){//第二个时间段

                $type_id_list=array_unique(array_column($emday, 'second_type_id'));
                $key2 = array_search($orange_type, $type_id_list);
                if ($key2 !== false){
                    array_splice($type_id_list, $key2, 1);
                }

                foreach ($type_id_list as $key => $type_id) {
                 
                    $data[$key]['mark']=2;
                    $data[$key]['is_show']=1;
                    $data[$key]['type_id']=($type_id>0)?$type_id:'gongxiu';
                    $data[$key]['type_name']=isset($shiftTypes[$type_id])?$shiftTypes[$type_id]['name']:'休息';
                    $data[$key]['start_time']=isset($shiftTypes[$type_id])?$shiftTypes[$type_id]['start_time']:'00:00';
                    $data[$key]['end_time']=isset($shiftTypes[$type_id])?$shiftTypes[$type_id]['end_time_afternoon']:'00:00';
                }

            }else if($timemark==3){//第三个时间段

                $type_id_list=array_unique(array_column($emday, 'third_type_id'));
                $key2 = array_search($orange_type, $type_id_list);
                if ($key2 !== false){
                    array_splice($type_id_list, $key2, 1);
                }
                foreach ($type_id_list as $key => $type_id) {
                    $data[$key]['mark']=3;
                    $data[$key]['is_show']=1;
                    $data[$key]['type_id']=($type_id>0)?$type_id:'gongxiu';
                    $data[$key]['type_name']=isset($shiftTypes[$type_id])?$shiftTypes[$type_id]['name']:'休息';
                    $data[$key]['start_time']=isset($shiftTypes[$type_id])?$shiftTypes[$type_id]['start_time']:'00:00';
                    $data[$key]['end_time']=isset($shiftTypes[$type_id])?$shiftTypes[$type_id]['end_time_afternoon']:'00:00';
                }

            }else{//整天
                $type_id_list=array_unique(array_column($emday, 'shift_type_id'));
                $key2 = array_search($orange_type, $type_id_list);
                if ($key2 !== false){
                    array_splice($type_id_list, $key2, 1);
                }
                foreach ($type_id_list as $key => $type_id) {

                    $data[$key]['mark']=0;
                    $data[$key]['is_show']=1;
                    $data[$key]['type_id']=($type_id>0)?$type_id:'gongxiu';
                    $data[$key]['type_name']=isset($shiftTypes[$type_id])?$shiftTypes[$type_id]['name']:'休息';
                    $data[$key]['start_time']=isset($shiftTypes[$type_id])?$shiftTypes[$type_id]['start_time']:'00:00';
                    $data[$key]['end_time']=isset($shiftTypes[$type_id])?$shiftTypes[$type_id]['end_time_afternoon']:'00:00';
                }
            }
        }else{
            $data['status']=false;
        }
        return $data;

    }


    /**
     * @author 吴斌  2018/7/19 修改 
     * 获取某计划某天所有的班次
     * @param array $shift_date 员工所拥有技能 
     * @param array $emp_number 员工工资号
     * @return array | 日期
     */

    public function getShiftTypeByDateAndScheduleId($scheduleId,$shift_date){
        $query=self::find()->where(['shift_date'=>$shift_date,'schedule_id'=>$scheduleId])->asArray()->all();
        return $query;
    }

     /**
     * @author 吴斌  2018/7/19 修改 
     * 获取班次
     * @param array $work_station 所属小组 
     * @param array $start_date 开始日期
     * @param array $end_date 结束日期 
     * @param array $type_id 技能id
     * @param array $emp_num 员工id
     * @return array | 日期
     */

    public function getResultList($work_station,$start_date,$end_date,$type_id,$emp_num){
        $query = (new \yii\db\Query())
        ->select(['a.emp_number','GROUP_CONCAT(a.shift_type_id) type'])
        ->from('orangehrm_mysql.ohrm_work_shift_result a')
        ->leftJoin('orangehrm_mysql.ohrm_work_schedule b','b.id = a.schedule_id')
        ->where(['b.location_id'=>$work_station])
        ->andWhere(['>','a.shift_type_id',0]);

        if(isset($start_date)&&!empty($start_date)){
            $query->andWhere(['>=','a.shift_date',$start_date]);
        }

        if(isset($end_date)&&!empty($end_date)){
            $query->andWhere(['<=','a.shift_date',$end_date]);
        }
        if(isset($emp_num)&&!empty($emp_num)){
            $query->andWhere(['a.emp_number'=>$emp_num]);
        }
        if(isset($type_id)&&!empty($type_id)){
            $query->andWhere(['a.shift_type_id'=>$type_id]);
        }
        $data=$query->groupBy(['a.emp_number'])->all();
        return $data;

    }


    /**
     * @author 吴斌  2018/7/19 修改 
     * 搜索班次
     * @param array $emp_num 员工id
     * @param array $work_station_list 该部门所有小组的id
     * @return array | 班次统计
     */

    public function searchShifts($shift_date,$work_station_list){

        $query = (new \yii\db\Query())
            ->select(['a.*','b.location_id'])
            ->from('orangehrm_mysql.ohrm_work_shift_result a')
            ->leftJoin('orangehrm_mysql.ohrm_work_schedule b','b.id = a.schedule_id')
            ->where(['b.is_confirm'=>1])
            ->andWhere(['b.is_show'=>1])
            ->andWhere('a.shift_type_id > 0')
            ->andWhere(['a.shift_date'=>$shift_date])
            ->andWhere(['in','b.location_id',$work_station_list])
            ->all();

        return $query;
      
    }


     /**
     * @author 吴斌  2018/7/19 修改 
     * 查询时间范围内员工所上的班次
     * @param array $emp_number 员工id
     * @param array $start_date 开始时间
     * @param array $end_date 结束时间
     * @return array | 班次统计
     */

    public function getRosterResultAllByEmpAndDate($emp_number,$start_date,$end_date){
        $query =self::find()->where(['emp_number'=>$emp_number])->andWhere(['<=','shift_date',$end_date])->andWhere(['>=','shift_date',$start_date])->asArray()->all();

        return $query;
      
    }


    
    /**
     * @author 吴斌  2018/1/11 修改 
     * 删除重复脏数据
     * @param int $dateId 日期ID
     * @return object | 对象
     */
   
    public function delDirtyData($schedule_id){
        $detailmodel=new ShiftTypeDetail;
        //获取该计划中的临时表数据
        $shiftTemp=$detailmodel->getDetailByScheduleId($schedule_id);

        $id=array();

        foreach ($shiftTemp as $key => $value) {
           $type_id=$value['shift_type_id'];
           $date=$value['shift_date'];
           $tmp[$type_id][$date][$key]=$value;
        }


        $id=array();
        foreach ($tmp as $k => $v) {
            
 
            foreach ($v as $k2 => $v2) {

                $data=array_column($v2, 'emp_number','id');
          
                $unique_arr = array_unique ( $data );
                // 获取重复数据的数组
                $repeat_arr = array_diff_assoc ( $data, $unique_arr );

                $array_unique_data=array_unique($repeat_arr);
    
                foreach ($data as $key2 => $value2) {
                    if(in_array($value2, $array_unique_data)){
                        $id[]=$key2;
                    }
                }
          
            }
        }

        $delCount='';
        if(count($id)>0){
            $delCount=$detailmodel->deleteTypeDetail($id);
        }

        return $delCount;

    }

    /**
     * @author 吴斌  2018/1/11 修改 
     * 调班，分时间段调班
     * @param int $schedule_id 计划id
     * @param int $shift_date 日期ID
     * @param int $orange_emp 原始员工
     * @param int $confirm_emp 被调班人
     * @param int $orange_type 原始班次
     * @param int $confirm_type 新班次
     * @param int $time_mark 调班时间段 0全天，1第一个时间段，2，第二个时间段
     * @param int $orange_end_time 原始班次结束时间
     * @param int $confrim_start_time 新始班次开始时间
     * @param int $confrim_end_time 新始班次结束时间
     * @param int $typeOrange 原班次信息
     * @param int $typeConfrim 新班次信息
     * @return object | 对象
     */
    public function confirmShiftNoLeave($schedule_id,$shift_date,$orange_emp,$confirm_emp,$orange_type,$confirm_type,$time_mark=0,$work_station=null,$typeOrange=null,$typeConfrim=null){
        //删除脏数据
        $attendmodel=new AttendanceRecord;
        $detailmodel=new ShiftTypeDetail;
        $typemodel=new ShiftType;
        $if_daka_orange=$attendmodel->getAttendanceRecordByWB($orange_emp,$shift_date);
        $if_daka_confrim=$attendmodel->getAttendanceRecordByWB($confirm_emp,$shift_date);
       
        if($if_daka_orange==-1 || $if_daka_confrim==-1){
            $result['status'] = false;
            $result['message'] = '已有打卡记录，不可调班';
            return $result;
        }

        if($if_daka_orange >0){//加入不是全天打开
            if($time_mark !=$if_daka_orange){
                $result['status'] = false;
                $result['message'] = '已有打卡记录，不可调班';
                return $result;
            }
        }

        if($if_daka_confrim>0){//加入不是全天打开
            if($time_mark != $if_daka_confrim){
                $result['status'] = false;
                $result['message'] = '已有打卡记录，不可调班';
                return $result;
            }
        }
        
        if (!is_numeric($orange_emp)||!is_numeric($confirm_emp)||!is_numeric($schedule_id)) {
            $result['status'] = false;
            $result['message'] = '无效的参数';
            return $result;
            
        }

        if(!strtotime($shift_date)){
            $result['status'] = false;
            $result['message'] = '不是正确的时间格式';
            return $result;
        }


        $shiftEmtity=$this->getResultByEmpAndDate($orange_emp,$shift_date,$schedule_id);
        $shiftEmtityTwo=$this->getResultByEmpAndDate($confirm_emp,$shift_date,$schedule_id);


        if(!$shiftEmtity){
            $result['status'] = false;
            $result['message'] = '原班次信息不存在';
            return $result;
        }

        if(!$shiftEmtityTwo ){
            $result['status'] = false;
            $result['message'] = '新班次信息不存在';
        }


        $orangetype=$typemodel->getShifTypeById($orange_type);
        $confirmtype=$typemodel->getShifTypeById($confirm_type);

        $transaction = Yii::$app->db->beginTransaction();
        try{

            if(!is_numeric($confirm_type)){
                $confirm_type=NULL;
            }

            if(!is_numeric($orange_type)){
                $orange_type=NULL;
            }


            $timemessage='';

            if($time_mark==0){//整天调班，不往临时表插数据
                if(empty($shiftEmtity)){
                    throw new \Exception();
                }else{
                    if(isset($shiftEmtity)&&isset($shiftEmtityTwo)&&!empty($confirm_emp)&&!empty($orange_emp)){
                        $shiftEmtity->emp_number=$confirm_emp;
                        $shiftEmtityTwo->emp_number=$orange_emp;

                        if(!$shiftEmtity->save()|| !$shiftEmtityTwo->save()){
                            throw new \Exception();
                        }

                        $transaction->commit();
                        $result['status'] = true;
                        $result['message'] = '调班成功';
                        return $result;

                    } 
                 } 


            }else if($time_mark==1){//第一个时间段调班

                //第一个员工，员工班次变为：$confirm_type(1)/$orange_type(2)
                if(isset($confirmtype)&& isset($orangetype)){
                    if($confirmtype['end_time_afternoon']!='00:00:00' && $orangetype['start_time_afternoon']!='00:00:00' &&$orangetype['end_time_afternoon']!='00:00:00' && $confirmtype['start_time_afternoon']!='00:00:00' ){
                        if(($confirmtype['end_time_afternoon']>$orangetype['start_time_afternoon'])||  ($orangetype['end_time_afternoon']>$confirmtype['start_time_afternoon'])){
                            $timemessage='调完后班次冲突，不能调班';
                            throw new \Exception();
                        }
                    }
                }
                
                $shiftEmtity->shift_type_id=($confirm_type==NULL)?0:$confirm_type;
                $shiftEmtity->frist_type_id=$confirm_type;

                if(isset($confirmtype)&&$confirmtype['is_work_half']==1){
                    if($shiftEmtity->leave_type==1){
                        $shiftEmtity->leave_type=2;
                    }

                    if($shiftEmtity->rest_type==1){
                        $shiftEmtity->rest_type=2;
                    }
                }

                if(!$shiftEmtity->save()){
                    throw new \Exception();
                }

                //第二个员工，员工班次变为：$orange_type(1)/$confirm_type(2)
                $shiftEmtityTwo->shift_type_id=($orange_type==NULL)?0:$orange_type;
                $shiftEmtityTwo->frist_type_id=$orange_type;

                if(isset($orangetype)&&$orangetype['is_work_half']==1){
                    if($shiftEmtityTwo->leave_type==1){
                        $shiftEmtityTwo->leave_type=2;
                    }

                    if($shiftEmtityTwo->rest_type==1){
                        $shiftEmtityTwo->rest_type=2;
                    }
                }

                
                if(!$shiftEmtityTwo->save()){
                    throw new \Exception();
                }

                $transaction->commit();
                $result['status'] = true;
                $result['message'] = '调班成功';
                return $result;

            }else if($time_mark==2){//第二个时间段调班

                //第一个员工，员工班次变为：$orange_type(1)/$confirm_type(2)

                //第一个员工，员工班次变为：$confirm_type(1)/$orange_type(2)
                if(isset($confirmtype)&& isset($orangetype)){
                    if($confirmtype['end_time_afternoon']!='00:00:00' && $orangetype['start_time_afternoon']!='00:00:00' &&$orangetype['end_time_afternoon']!='00:00:00' && $confirmtype['start_time_afternoon']!='00:00:00' ){
                        if(($confirmtype['end_time_afternoon']>$orangetype['start_time_afternoon']) ||  ($orangetype['end_time_afternoon']>$confirmtype['start_time_afternoon'])){
                            $timemessage='调完后班次冲突，不能调班';
                            throw new \Exception();
                        }
                    }
                }
                

                $shiftEmtity->second_type_id=$confirm_type;
                if(!$shiftEmtity->save()){
                    throw new \Exception();
                }

                //第二个员工，员工班次变为：$confirm_type(1)/$orange_type(2)
                $shiftEmtityTwo->second_type_id=$orange_type;
                if(!$shiftEmtityTwo->save()){
                    throw new \Exception();
                }

                $transaction->commit();
                $result['status'] = true;
                $result['message'] = '调班成功';
                return $result;
                
            }else if($time_mark==3){//第三个时间段调班

                $shiftEmtity->third_type_id=$confirm_type;
                if(!$shiftEmtity->save()){
                    throw new \Exception();
                }

                $shiftEmtityTwo->third_type_id=$orange_type;
                if(!$shiftEmtityTwo->save()){
                    throw new \Exception();
                }
                $transaction->commit();
                $result['status'] = true;
                $result['message'] = '调班成功';
                return $result;
            }

        }catch(\Exception $e) {
            $transaction->rollback();
            $result['status'] = false;

            if(isset($timemessage)&&!empty($timemessage)){
                $result['message'] = $timemessage;
            }else{
                $result['message'] = '调班失败';
            }
            
            return $result;
        }

    }

}
