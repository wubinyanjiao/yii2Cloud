<?php

namespace common\models\shift;

use Yii;
use \common\models\shift\base\Constraint as BaseConstraint;
use yii\helpers\ArrayHelper;
use common\models\employee\Employee;
use common\models\user\User;
use common\models\leave\Leave;
/**
 * This is the model class for table "ohrm_work_schedule_constraint".
 */
class Constraint extends BaseConstraint
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

    public function add($data){
        if ($this->load($data) && $this->save()) {
            return true;
        }
        return false;
    }


    public function getConstraint($work_station){
        $query = self::find()->select('id,name,work_station')->where('work_station = :work_station', [':work_station' => $work_station])->orderBy('id desc')->all();
        return $query;
    }

    public function getConstraintOne($id){
        $query = self::find()->where('id = :id', [':id' => $id])->orderBy('id desc')->one();
        return $query;
    }

    /**
     * 吴斌  2018/1/11 修改 
     * 生成规范化的XML文件，传入java包运行该文件
     *
     * @param string $scheduleID   排班任务ID
     * @param string $workStation  部门ID
      
     * 生成最后XML文件存储到orangehrmShiftPlugin/lib/service/files/xml中
     *
     */
    public function createXml($scheduleID,$workStation,$constraint){


        $shiftmodel=new Shift;
        $shiftdatemodel=new ShiftDate;
        $shifttypemodel=new ShiftType;
        $shifassignmentmodel=new ShiftAssignment;
        $skillmodel=new Skill;
        $empmodel=new Employee;
        $empskillmodel=new EmpSkill;
        $rotayrempmodel=new ShiftRotaryEmployee;
        $typeskillmodel=new TypeSkill;
        $usermodel=new User;
        $leavemodel=new Leave;

        $tcm_pharmacy=array();
        $shifts=$shiftmodel->getShiftsBySchedule($scheduleID);

        $schecheduleEntity=Schedule::find()->where('id =:schedule_id ',[':schedule_id'=>$scheduleID])->one();
        $first_date=$schecheduleEntity->shift_date;

        //获取日期列表
        $datelist=$shiftdatemodel->getDatesBySchedule($scheduleID);
        //获取计划中每个日期下排的班次
        $shiftDates=$shiftdatemodel->getDateShifts($scheduleID);
        $date_id_list=array_column($datelist, 'shift_date');
        $date_id_list2=array_column($datelist, 'id','shift_date');

        //获取个星期对应的日期ID
        $date1=array();
        foreach ($shiftDates as $ksa => $vsa) {
            $date1['shiftdate']=$vsa['shift_date'];
            $date1['id']=$vsa['id'];
            $changeWeek=get_week($date1['shiftdate']);
            $date_to_week[$changeWeek][]=$date1;
        }

        $employeeList=$usermodel->FutureEmployee($workStation,$first_date);
        $emplist=array_column($employeeList, 'emp_number');
        //获取休假人员列表
        $emp_leave=array();
        $leaveEmp=$leavemodel->getLeaveByDate($date_id_list);
        $emp_leave=array();
        foreach ($leaveEmp as $key => $value) {
            if(in_array($value['emp_number'], $emplist)){
                $leave_date['date']= $date_id_list2[$value['date']];
                $leave_date['emp_number']=$value['emp_number'];
                $emp_leave[]=$leave_date;
            }
        }

        $shiftTypes=$shifttypemodel->getShifType($workStation);
        $shiftAssignments=$shifassignmentmodel->getShiftAssignmentList($scheduleID); 

        //读取约束文件
        $patternList=$constraint;
        $skillList=$skillmodel->getSkillList($workStation);
        //查询员工技能
        $employeeSkillList=$empskillmodel->getEmpSkillList($workStation);
        $shiftTypeToSkillList=$typeskillmodel->getShiftTypeToSkillList($workStation);
        $shiftTypes = array_column($shiftTypes, NULL, 'id');
        $shiftDatesByIndex = array_column($shiftDates, NULL, 'id');
        
        $xml_name="roster_".$scheduleID;
        $k=1;
        $tcm_pharmacy['@name']="NurseRoster";
        $tcm_pharmacy['@attributes']['id']=$k;
        $tcm_pharmacy['id']=1;
        $tcm_pharmacy['code']=$xml_name;
        $tcm_pharmacy['nurseRosterParametrization']['@attributes']['id']=$k+1;
        $tcm_pharmacy['nurseRosterParametrization']['id']='0';//全局变量

        $index=$k+2;
        $last_index=count($shiftDates)-1;

        $shiftTypeIndex=array();
        $shiftDateIndex=array();
        $dayIndex=0;

        //只是得到第一天和最后一天信息，同时将创建的shiftTypeId 和shiftDateID存储起来
        foreach ($shiftDates as $key => $shiftDate) {
            $date_format=getFormatDate($shiftDate['shift_date']);
            //如果是第一天
            if($key==0){
                $tcm_pharmacy['nurseRosterParametrization']['firstShiftDate']['@attributes']['id']=$index;
                $firstDateIndex=$index;
                $shiftDateIndex[$shiftDate['id']]=$index;
                
                
                $tcm_pharmacy['nurseRosterParametrization']['firstShiftDate']['id']=$shiftDate['id'];
                $tcm_pharmacy['nurseRosterParametrization']['firstShiftDate']['dayIndex']=$shiftDate['id'];
                $index++;
                $tcm_pharmacy['nurseRosterParametrization']['firstShiftDate']['date']['@attributes']['id']=$index;
                $tcm_pharmacy['nurseRosterParametrization']['firstShiftDate']['date']['@attributes']['resolves-to']='java.time.Ser';

                $tcm_pharmacy['nurseRosterParametrization']['firstShiftDate']['date'][]['byte']='3';
                $tcm_pharmacy['nurseRosterParametrization']['firstShiftDate']['date']['int']=$date_format['y'];
                $tcm_pharmacy['nurseRosterParametrization']['firstShiftDate']['date'][]['byte']=$date_format['m'];
                $tcm_pharmacy['nurseRosterParametrization']['firstShiftDate']['date'][]['byte']=$date_format['d'];
                $index++;
                $tcm_pharmacy['nurseRosterParametrization']['firstShiftDate']['shiftList']['@attributes']['id']=$index;

                if(null!=$shiftDate['shift']){
                    foreach($shiftDate['shift'] as $k=>$shift){
                        $type_id=$shift['shift_type_id'];
                        if(isset($shiftTypes[$type_id])){
                            $shiftType=$shiftTypes[$type_id];
                            $index++;
                            $shiftListIndex[$shift['id']]=$index;
                            $tcm_pharmacy['nurseRosterParametrization']['firstShiftDate']['shiftList'][$k]['Shift']['@attributes']['id']=$index;

                            $tcm_pharmacy['nurseRosterParametrization']['firstShiftDate']['shiftList'][$k]['Shift']['id']=$index;
                            $tcm_pharmacy['nurseRosterParametrization']['firstShiftDate']['shiftList'][$k]['Shift']['shiftDate']['@attributes']['reference']=$firstDateIndex;
                            $tcm_pharmacy['nurseRosterParametrization']['firstShiftDate']['shiftList'][$k]['Shift']['shiftDate']['@data']='';

                            $index++;

                            //记录shiftTypeID
                            $tcm_pharmacy['nurseRosterParametrization']['firstShiftDate']['shiftList'][$k]['Shift']['shiftType']['@attributes']['id']=$index;
                            $shiftTypeIndex[$type_id]=$index;
                            $tcm_pharmacy['nurseRosterParametrization']['firstShiftDate']['shiftList'][$k]['Shift']['shiftType']['id']=$index;
                            $tcm_pharmacy['nurseRosterParametrization']['firstShiftDate']['shiftList'][$k]['Shift']['shiftType']['code']=$type_id;
                            $tcm_pharmacy['nurseRosterParametrization']['firstShiftDate']['shiftList'][$k]['Shift']['shiftType']['index']=$type_id;
                            $tcm_pharmacy['nurseRosterParametrization']['firstShiftDate']['shiftList'][$k]['Shift']['shiftType']['startTimeString']=$shiftType['start_time'];
                            $tcm_pharmacy['nurseRosterParametrization']['firstShiftDate']['shiftList'][$k]['Shift']['shiftType']['endTimeString']=$shiftType['end_time'];
                            $tcm_pharmacy['nurseRosterParametrization']['firstShiftDate']['shiftList'][$k]['Shift']['shiftType']['night']='false';
                            $tcm_pharmacy['nurseRosterParametrization']['firstShiftDate']['shiftList'][$k]['Shift']['shiftType']['description']=$index;

                            $tcm_pharmacy['nurseRosterParametrization']['firstShiftDate']['shiftList'][$k]['Shift']['index']=$shift['id'];
                            $tcm_pharmacy['nurseRosterParametrization']['firstShiftDate']['shiftList'][$k]['Shift']['requiredEmployeeSize']=$shift['required_employee'];
                        }
                    }
                }else{
                    $tcm_pharmacy['nurseRosterParametrization']['firstShiftDate']['shiftList']['@data']='';
                }

                
            }

            //最后一天
            if($key==$last_index){
                $index++;
                $lastDateIndex=$index;
                $tcm_pharmacy['nurseRosterParametrization']['lastShiftDate']['@attributes']['id']=$index;
                $shiftDateIndex[$shiftDate['id']]=$index;
                $tcm_pharmacy['nurseRosterParametrization']['lastShiftDate']['id']=$shiftDate['id'];
                $tcm_pharmacy['nurseRosterParametrization']['lastShiftDate']['dayIndex']=$shiftDate['id'];
                $index++;
                $tcm_pharmacy['nurseRosterParametrization']['lastShiftDate']['date']['@attributes']['id']=$index;
                $tcm_pharmacy['nurseRosterParametrization']['lastShiftDate']['date']['@attributes']['resolves-to']='java.time.Ser';
                $tcm_pharmacy['nurseRosterParametrization']['lastShiftDate']['date'][]['byte']='3';
                $tcm_pharmacy['nurseRosterParametrization']['lastShiftDate']['date']['int']=$date_format['y'];
                $tcm_pharmacy['nurseRosterParametrization']['lastShiftDate']['date'][]['byte']=$date_format['m'];
                $tcm_pharmacy['nurseRosterParametrization']['lastShiftDate']['date'][]['byte']=$date_format['d'];
                $index++;
                $tcm_pharmacy['nurseRosterParametrization']['lastShiftDate']['shiftList']['@attributes']['id']=$index;
                if(null!=$shiftDates[$last_index]['shift']){
                    foreach($shiftDates[$last_index]['shift'] as $k=>$shift){
                        $type_id=$shift['shift_type_id'];
                        if(isset($shiftTypes[$type_id])){

                            $shiftType=$shiftTypes[$type_id];
                            $index++;
                            $shiftListIndex[$shift['id']]=$index;
                            $tcm_pharmacy['nurseRosterParametrization']['lastShiftDate']['shiftList'][$k]['Shift']['@attributes']['id']=$index;
                            $tcm_pharmacy['nurseRosterParametrization']['lastShiftDate']['shiftList'][$k]['Shift']['id']=$index;
                            $tcm_pharmacy['nurseRosterParametrization']['lastShiftDate']['shiftList'][$k]['Shift']['shiftDate']['@attributes']['reference']=$lastDateIndex;
                            $tcm_pharmacy['nurseRosterParametrization']['lastShiftDate']['shiftList'][$k]['Shift']['shiftDate']['@data']='';
                            if(!isset($shiftTypeIndex[$type_id])){
                                $index++;
                                $tcm_pharmacy['nurseRosterParametrization']['lastShiftDate']['shiftList'][$k]['Shift']['shiftType']['@attributes']['id']=$index;
                                $shiftTypeIndex[$type_id]=$index;
                                $tcm_pharmacy['nurseRosterParametrization']['lastShiftDate']['shiftList'][$k]['Shift']['shiftType']['id']=$index;
                                $tcm_pharmacy['nurseRosterParametrization']['lastShiftDate']['shiftList'][$k]['Shift']['shiftType']['code']=$type_id;
                                $tcm_pharmacy['nurseRosterParametrization']['lastShiftDate']['shiftList'][$k]['Shift']['shiftType']['index']=$type_id;
                                $tcm_pharmacy['nurseRosterParametrization']['lastShiftDate']['shiftList'][$k]['Shift']['shiftType']['startTimeString']=$shiftType['start_time'];
                                $tcm_pharmacy['nurseRosterParametrization']['lastShiftDate']['shiftList'][$k]['Shift']['shiftType']['endTimeString']=$shiftType['end_time'];
                                $tcm_pharmacy['nurseRosterParametrization']['lastShiftDate']['shiftList'][$k]['Shift']['shiftType']['night']='false';
                                $tcm_pharmacy['nurseRosterParametrization']['lastShiftDate']['shiftList'][$k]['Shift']['shiftType']['description']=$index;

                            }else{
                                $tcm_pharmacy['nurseRosterParametrization']['lastShiftDate']['shiftList'][$k]['Shift']['shiftType']['@attributes']['reference']=$shiftTypeIndex[$type_id];
                                $tcm_pharmacy['nurseRosterParametrization']['lastShiftDate']['shiftList'][$k]['Shift']['shiftType']['@data']='';
                                
                            }

                            $tcm_pharmacy['nurseRosterParametrization']['lastShiftDate']['shiftList'][$k]['Shift']['index']=$shift['id'];;
                            $tcm_pharmacy['nurseRosterParametrization']['lastShiftDate']['shiftList'][$k]['Shift']['requiredEmployeeSize']=$shift['required_employee'];
                        }
                        

                    }
                }else{
                    $tcm_pharmacy['nurseRosterParametrization']['lastShiftDate']['shiftList']['@data']='';
                }
                

            }
        }

        $tcm_pharmacy['nurseRosterParametrization']['planningWindowStart']['@attributes']['reference']=$firstDateIndex;
        $tcm_pharmacy['nurseRosterParametrization']['planningWindowStart']['@data']='';

        $index++;
 
        //技能列表
        $tcm_pharmacy['skillList']['@attributes']['id']=$index;
        if(isset($skillList)&&count($skillList)>0){
            foreach ($skillList as $key => $skill) {
                $index++;
                $tcm_pharmacy['skillList'][$key]['Skill']['@attributes']['id']=$index;
                $skillListIndex[$skill['id']]=$index;
                $tcm_pharmacy['skillList'][$key]['Skill']['id']=$skill['id'];
                $tcm_pharmacy['skillList'][$key]['Skill']['code']=$skill['id'];
            }
        }else{
            $tcm_pharmacy['skillList']['@data']="";
        }

        
        //持续上某一个班
        $index++;
        $tcm_pharmacy['patternList']['@attributes']['id']=$index;


        if(isset($patternList['assignmentAfterShiftSelect'])){
            foreach ($patternList['assignmentAfterShift'] as $aftkey => $assignmentAfterShift) {
                $shiftType=$assignmentAfterShift['assignmentAfterShiftSelect'];
                if($assignmentAfterShift['assignmentAfterShiftStatus']==1){
                    foreach ($shiftType as $kc1 => $vc1) {
                        $id=(int)$vc1;


                        if(isset($shiftTypeIndex[$id])){
                            $index++;
                            $tcm_pharmacy['patternList'][$aftkey][$kc1]['IdenticalShiftLastSomeDaysPattern']['@attributes']['id']=$index;
                            $patternIndex['LastSomeDaysPattern'][]=$index;
                            $tcm_pharmacy['patternList'][$aftkey][$kc1]['IdenticalShiftLastSomeDaysPattern']['id']=$index;
                            $tcm_pharmacy['patternList'][$aftkey][$kc1]['IdenticalShiftLastSomeDaysPattern']['code']='0';
                            $tcm_pharmacy['patternList'][$aftkey][$kc1]['IdenticalShiftLastSomeDaysPattern']['weight']=$assignmentAfterShift['assignmentAfterShiftWeight'];
                            $tcm_pharmacy['patternList'][$aftkey][$kc1]['IdenticalShiftLastSomeDaysPattern']['dayShiftType']['@attributes']['reference']=$shiftTypeIndex[$id];
                            $tcm_pharmacy['patternList'][$aftkey][$kc1]['IdenticalShiftLastSomeDaysPattern']['dayShiftType']['@data']='';
                            $tcm_pharmacy['patternList'][$aftkey][$kc1]['IdenticalShiftLastSomeDaysPattern']['ShiftLastLength']=$assignmentAfterShift['assignmentAfterShiftDays'];
                           
                        }
                    }
                }
                
            }
        }
        
        $nightShiftTypeID=0;
        //查询夜班ID,首先查询该计划下所有班次
        foreach ($shifts as $sks => $svs) {
            
            if($svs['shiftType']['is_night_shift']==1){
                $nightShiftTypeID=$svs['shiftType']['id'];
            }
        }

        //不希望这两个班连上
        foreach ($patternList['restAfterOneShift'] as $key => $restAfterOneShift) {

            $shiftType0=$restAfterOneShift['startShiftSelect'];
            $shiftType1=$restAfterOneShift['nextShiftSelect'];
            if($restAfterOneShift['restAfterOneShiftStatus']==1){
                if(isset($shiftTypeIndex[$shiftType1]) || isset($shiftTypeIndex[$shiftType0]) ){
                $index++;
                $tcm_pharmacy['patternList'][$key]['ShiftType2DaysPattern']['@attributes']['id']=$index;
                $patternIndex['ShiftType2DaysPattern']=$index;
                $tcm_pharmacy['patternList'][$key]['ShiftType2DaysPattern']['id']=$index;
                $tcm_pharmacy['patternList'][$key]['ShiftType2DaysPattern']['code']='0';
                $tcm_pharmacy['patternList'][$key]['ShiftType2DaysPattern']['weight']=$restAfterOneShift['restAfterOneShiftWeight'];
                $tcm_pharmacy['patternList'][$key]['ShiftType2DaysPattern']['dayIndex0ShiftType']['@attributes']['reference']=$shiftTypeIndex[$shiftType0];
                $tcm_pharmacy['patternList'][$key]['ShiftType2DaysPattern']['dayIndex0ShiftType']['@data']='';

                $tcm_pharmacy['patternList'][$key]['ShiftType2DaysPattern']['dayIndex1ShiftType']['@attributes']['reference']=$shiftTypeIndex[$shiftType1];
                $tcm_pharmacy['patternList'][$key]['ShiftType2DaysPattern']['dayIndex1ShiftType']['@data']='';
                }
                // continue;
            }
        }


        //不希望这三个班连上
        if(isset($patternList['shiftThree'])){
            foreach ($patternList['shiftThree'] as $key => $shiftThree) {

                $shiftType0=$shiftThree['threeStartShiftSelect'];
                $shiftType1=$shiftThree['threeNextShiftSelect'];
                $shiftType2=$shiftThree['threeThirdShiftSelect'];
                if($shiftThree['threeShiftStatus']==1){
                    if(isset($shiftTypeIndex[$shiftType0]) && isset($shiftTypeIndex[$shiftType1]) && isset($shiftTypeIndex[$shiftType2]) ){
                        $index++;
                        $tcm_pharmacy['patternList'][$key]['ShiftType3DaysPattern']['@attributes']['id']=$index;
                        $patternIndex['shiftThree']=$index;
                        $tcm_pharmacy['patternList'][$key]['ShiftType3DaysPattern']['id']=$index;
                        $tcm_pharmacy['patternList'][$key]['ShiftType3DaysPattern']['code']='0';
                        $tcm_pharmacy['patternList'][$key]['ShiftType3DaysPattern']['weight']=$shiftThree['threeShiftWeight'];
                        $tcm_pharmacy['patternList'][$key]['ShiftType3DaysPattern']['dayIndex0ShiftType']['@attributes']['reference']=$shiftTypeIndex[$shiftType0];
                        $tcm_pharmacy['patternList'][$key]['ShiftType3DaysPattern']['dayIndex0ShiftType']['@data']='';

                        $tcm_pharmacy['patternList'][$key]['ShiftType3DaysPattern']['dayIndex1ShiftType']['@attributes']['reference']=$shiftTypeIndex[$shiftType1];
                        $tcm_pharmacy['patternList'][$key]['ShiftType3DaysPattern']['dayIndex1ShiftType']['@data']='';

                        $tcm_pharmacy['patternList'][$key]['ShiftType3DaysPattern']['dayIndex2ShiftType']['@attributes']['reference']=$shiftTypeIndex[$shiftType2];
                        $tcm_pharmacy['patternList'][$key]['ShiftType3DaysPattern']['dayIndex2ShiftType']['@data']='';
                    }
                }
                
            }
        }
        



        //班次只分配给男性
        if(isset($patternList['shiftdOnlyforMan'])){
            foreach ($patternList['shiftdOnlyforMan'] as $onmankey => $shiftdOnlyforMan) {
                if(isset($shiftdOnlyforMan['shiftdOnlyforManShiftSelect'])){
                    $onlyforManShiftType=$shiftdOnlyforMan['shiftdOnlyforManShiftSelect'];

                    if($shiftdOnlyforMan['shiftdOnlyforManStatus']==1){
                        foreach ($onlyforManShiftType as $kc2 => $vc2) {
                            if(isset($shiftTypeIndex[$vc2])){
                                $index++;
                                $tcm_pharmacy['patternList'][$onmankey][$kc2]['ShiftAssignedOnlyforManPattern']['@attributes']['id']= $index;
                                $patternIndex['AssignedOnlyforMan']=$index;
                                $tcm_pharmacy['patternList'][$onmankey][$kc2]['ShiftAssignedOnlyforManPattern']['id']=$index;
                                $tcm_pharmacy['patternList'][$onmankey][$kc2]['ShiftAssignedOnlyforManPattern']['code']='1';
                                $tcm_pharmacy['patternList'][$onmankey][$kc2]['ShiftAssignedOnlyforManPattern']['weight']=$shiftdOnlyforMan['shiftdOnlyforManWeight'];
                                $tcm_pharmacy['patternList'][$onmankey][$kc2]['ShiftAssignedOnlyforManPattern']['dayShiftType']['@attributes']['reference']=$shiftTypeIndex[$vc2];
                                $tcm_pharmacy['patternList'][$onmankey][$kc2]['ShiftAssignedOnlyforManPattern']['dayShiftType']['@data']='';
                            }
                        }
                    }
                }
            }
        }
        


        //周六工作在周二或周四安排调休
        if($patternList['restOnTuOrTues']["restOnTuOrTuesStatus"]==1){
            $index++;
            $tcm_pharmacy['patternList']['FreeAfterWeekendWorkDayPattern']['@attributes']['id']= $index;
            $patternIndex['FreeAfterWeekendWorkDayPattern']=$index;
            $tcm_pharmacy['patternList']['FreeAfterWeekendWorkDayPattern']['id']=$index;
            $tcm_pharmacy['patternList']['FreeAfterWeekendWorkDayPattern']['code']='2';
            $tcm_pharmacy['patternList']['FreeAfterWeekendWorkDayPattern']['weight']=$patternList['restOnTuOrTues']["restOnTuOrTuesWeight"];
            $tcm_pharmacy['patternList']['FreeAfterWeekendWorkDayPattern']['workDayOfWeek']='SATURDAY';
        }

        
        //夜班后夜休息nightAfterNightLeisureStatus
        if($patternList['nightAfterNightLeisureShift']['nightAfterNightLeisureStatus']==1){
            $nightAfterNightShiftType=array();
            $nightAfterNightLeisureShift=$patternList['nightAfterNightLeisureShift'];
            $nightAfterNightShiftType=isset($nightAfterNightLeisureShift['nightAfterNightLeisureShiftSelect'])?$nightAfterNightLeisureShift['nightAfterNightLeisureShiftSelect']:$nightAfterNightShiftType;
            foreach ($nightAfterNightShiftType as $kc3 => $vc3) {
                if(isset($shiftTypeIndex[$nightShiftTypeID])){
                    $index++;
                    $tcm_pharmacy['patternList'][$kc3]['FreeAfterANightShiftPattern']['@attributes']['id']= $index;
                    $patternIndex['FreeAfterANightShiftPattern']=$index;

                    $tcm_pharmacy['patternList'][$kc3]['FreeAfterANightShiftPattern']['id']=$index;
                    $tcm_pharmacy['patternList'][$kc3]['FreeAfterANightShiftPattern']['code']='3';
                    $tcm_pharmacy['patternList'][$kc3]['FreeAfterANightShiftPattern']['weight']=$nightAfterNightLeisureShift['nightAfterNightLeisureWeight'];

                    $tcm_pharmacy['patternList'][$kc3]['FreeAfterANightShiftPattern']['dayShiftType']['@attributes']['reference']=$shiftTypeIndex[$nightShiftTypeID];
                    $tcm_pharmacy['patternList'][$kc3]['FreeAfterANightShiftPattern']['dayShiftType']['@data']='';
                    if(isset($shiftTypeIndex[$vc3])){
                        $tcm_pharmacy['patternList'][$kc3]['FreeAfterANightShiftPattern']['workShiftType']['@attributes']['reference']=$shiftTypeIndex[$vc3];
                        $tcm_pharmacy['patternList'][$kc3]['FreeAfterANightShiftPattern']['workShiftType']['@data']='';
                    }
                }
            }
        }else{

            if(isset($shiftTypeIndex[$nightShiftTypeID])){
                $index++;
                $tcm_pharmacy['patternList']['FreeAfterANightShiftPattern']['@attributes']['id']= $index;
                $patternIndex['FreeAfterANightShiftPattern']=$index;

                $tcm_pharmacy['patternList']['FreeAfterANightShiftPattern']['id']=$index;
                $tcm_pharmacy['patternList']['FreeAfterANightShiftPattern']['code']='3';
                $tcm_pharmacy['patternList']['FreeAfterANightShiftPattern']['weight']= '10';

                $tcm_pharmacy['patternList']['FreeAfterANightShiftPattern']['dayShiftType']['@attributes']['reference']=$shiftTypeIndex[$nightShiftTypeID];
                $tcm_pharmacy['patternList']['FreeAfterANightShiftPattern']['dayShiftType']['@data']='';
            }
           
        }

         //在两个夜班直接不上班或者指定班次

        
        if($patternList['twoNight']['twoNightStatus']==1){
            $shiftTypeTwoNight=$patternList['twoNight']['twoNightShiftSelect'];
            foreach ($shiftTypeTwoNight as $kc4 => $vc4) {
                if(isset($shiftTypeIndex[$vc4])&& isset($shiftTypeIndex[$nightShiftTypeID])){
                    $index++;
                    $tcm_pharmacy['patternList'][$kc4]['FreeSecondDayAfterANightShiftPattern']['@attributes']['id']= $index;
                    $patternIndex['FreeSecondDayAfterANightShiftPattern']=$index;
                    $tcm_pharmacy['patternList'][$kc4]['FreeSecondDayAfterANightShiftPattern']['id']=$index;
                    $tcm_pharmacy['patternList'][$kc4]['FreeSecondDayAfterANightShiftPattern']['code']='2';
                    $tcm_pharmacy['patternList'][$kc4]['FreeSecondDayAfterANightShiftPattern']['weight']=$patternList['twoNight']["twoNightWeight"];
                    $tcm_pharmacy['patternList'][$kc4]['FreeSecondDayAfterANightShiftPattern']['dayShiftType0']['@attributes']['reference']=$shiftTypeIndex[$nightShiftTypeID];
                    $tcm_pharmacy['patternList'][$kc4]['FreeSecondDayAfterANightShiftPattern']['dayShiftType0']['@data']='';
                    $tcm_pharmacy['patternList'][$kc4]['FreeSecondDayAfterANightShiftPattern']['dayShiftType1']['@attributes']['reference']=$shiftTypeIndex[$vc4];
                    $tcm_pharmacy['patternList'][$kc4]['FreeSecondDayAfterANightShiftPattern']['dayShiftType1']['@data']='';
                }
            }
         
        }


        
        //班次平均分配 
        foreach ($patternList['averageAssignment'] as $averkey => $averageAssignment) {
            if(isset($averageAssignment['averageAssignmentShiftSelect'])){
                $averageAssignmentShiftType=$averageAssignment['averageAssignmentShiftSelect'];
                if($averageAssignment['averageAssignmentStatus']==1){
                    foreach ($averageAssignmentShiftType as $kc5 => $vc5) {
                        if(isset($shiftTypeIndex[$vc5])){
                            $index++;
                            $tcm_pharmacy['patternList'][$averkey][$kc5]['ShiftAssignedAveragedAtAllEmployeesPattern']['@attributes']['id']= $index;
                            $patternIndex['ShiftAssignedAveragedAtAllEmployeesPattern']=$index;
                            $tcm_pharmacy['patternList'][$averkey][$kc5]['ShiftAssignedAveragedAtAllEmployeesPattern']['id']=$index;
                            $tcm_pharmacy['patternList'][$averkey][$kc5]['ShiftAssignedAveragedAtAllEmployeesPattern']['code']='4';
                            $tcm_pharmacy['patternList'][$averkey][$kc5]['ShiftAssignedAveragedAtAllEmployeesPattern']['weight']=$averageAssignment['averageAssignmentWeight'];
                            $tcm_pharmacy['patternList'][$averkey][$kc5]['ShiftAssignedAveragedAtAllEmployeesPattern']['dayShiftType']['@attributes']['reference']=$shiftTypeIndex[$vc5];
                            $tcm_pharmacy['patternList'][$averkey][$kc5]['ShiftAssignedAveragedAtAllEmployeesPattern']['dayShiftType']['@data']='';
                            $tcm_pharmacy['patternList'][$averkey][$kc5]['ShiftAssignedAveragedAtAllEmployeesPattern']['dayShiftLength']=$averageAssignment['averageAssignment'];
                        }
                    }
                }
            }

        }

        // 每周公休分配
        if($patternList['freeTwoDays']["freeTwoDaysStatus"]==1){
            $index++;
            $tcm_pharmacy['patternList']['FreeTwoDaysEveryWeekPattern']['@attributes']['id']= $index;
            $patternIndex['FreeTwoDaysEveryWeekPattern']=$index;
            $tcm_pharmacy['patternList']['FreeTwoDaysEveryWeekPattern']['id']=$index;
            $tcm_pharmacy['patternList']['FreeTwoDaysEveryWeekPattern']['code']='5';
            $tcm_pharmacy['patternList']['FreeTwoDaysEveryWeekPattern']['weight']=$patternList['freeTwoDays']["freeTwoDaysWeight"];
            $tcm_pharmacy['patternList']['FreeTwoDaysEveryWeekPattern']['workDayLength']='7';
            $tcm_pharmacy['patternList']['FreeTwoDaysEveryWeekPattern']['freeDayLength']=$patternList['freeTwoDays']["freeTwoDaysSelect"];
        }

        // 该班次分配后间隔后再分配
        foreach ($patternList['assignmentAfterInterval'] as $inkey => $assignmentAfterInterval) {
            if(isset($assignmentAfterInterval['assignmentAfterIntervalShiftSelect'])){
                $AfteInteShiftType=$assignmentAfterInterval['assignmentAfterIntervalShiftSelect'];
                if($assignmentAfterInterval['assignmentAfterIntervalStatus']==1){

                    foreach ($AfteInteShiftType as $kc6 => $vc6) {
                       if(isset($shiftTypeIndex[$vc6])){
                        $index++;
                        $tcm_pharmacy['patternList'][$inkey][$kc6]['ShiftAssignedSomeWeeksPattern']['@attributes']['id']= $index;
                        $patternIndex['ShiftAssignedSomeWeeksPattern'][]=$index;
                        $tcm_pharmacy['patternList'][$inkey][$kc6]['ShiftAssignedSomeWeeksPattern']['id']=$index;
                        $tcm_pharmacy['patternList'][$inkey][$kc6]['ShiftAssignedSomeWeeksPattern']['code']='6';
                        $tcm_pharmacy['patternList'][$inkey][$kc6]['ShiftAssignedSomeWeeksPattern']['weight']=$assignmentAfterInterval['assignmentAfterIntervalWeight'];
                        $tcm_pharmacy['patternList'][$inkey][$kc6]['ShiftAssignedSomeWeeksPattern']['dayShiftType']['@attributes']['reference']=$shiftTypeIndex[$vc6];
                        $tcm_pharmacy['patternList'][$inkey][$kc6]['ShiftAssignedSomeWeeksPattern']['dayShiftType']['@data']='';
                        $tcm_pharmacy['patternList'][$inkey][$kc6]['ShiftAssignedSomeWeeksPattern']['weekGapLength']=$assignmentAfterInterval['assignmentAfterIntervalEmployee'];
                       }
                    }
                }
            }
            
        }


            
         // 孕妇不分配低于设置人次的班次
        if(isset($patternList['gravida'])&&$patternList['gravida']["gravidaStatus"]==1){
            $index++;
            $tcm_pharmacy['patternList']['ShiftWorkNumberAssignedforPergnantPattern']['@attributes']['id']= $index;
            $patternIndex['ShiftWorkNumberAssignedforPergnantPattern']=$index;
            $tcm_pharmacy['patternList']['ShiftWorkNumberAssignedforPergnantPattern']['id']=$index;
            $tcm_pharmacy['patternList']['ShiftWorkNumberAssignedforPergnantPattern']['code']='7';
            $tcm_pharmacy['patternList']['ShiftWorkNumberAssignedforPergnantPattern']['weight']=$patternList['gravida']["gravidaWeight"];
            $tcm_pharmacy['patternList']['ShiftWorkNumberAssignedforPergnantPattern']['NumofWorkers']=$patternList['gravida']["gravidaCount"];
        }
        
         // 轮转先休假
        if(isset($patternList['holidays'])&& $patternList['holidays']["holidaysStatus"]==1){
            $index++;
            $tcm_pharmacy['patternList']['FreeFirstbyTakeTurnsPattern']['@attributes']['id']= $index;
            $patternIndex['FreeFirstbyTakeTurnsPattern']=$index;
            $tcm_pharmacy['patternList']['FreeFirstbyTakeTurnsPattern']['id']=$index;
            $tcm_pharmacy['patternList']['FreeFirstbyTakeTurnsPattern']['code']='8';
            $tcm_pharmacy['patternList']['FreeFirstbyTakeTurnsPattern']['weight']=$patternList['holidays']["holidaysWeight"];
            $tcm_pharmacy['patternList']['FreeFirstbyTakeTurnsPattern']['freeDaysLength']=$patternList['holidays']["holidaysMax"];
            $tcm_pharmacy['patternList']['FreeFirstbyTakeTurnsPattern']['workLength']=$patternList['holidays']["shiftsMax"];
        }
 
        // 周内分配不同班次

        if(isset($patternList['diffShift'])&&$patternList['diffShift']["diffShiftStatus"]==1){
            $index++;
            $tcm_pharmacy['patternList']['ShiftDifferentInAWeekPattern']['@attributes']['id']= $index;
            $patternIndex['ShiftDifferentInAWeekPattern']=$index;
            $tcm_pharmacy['patternList']['ShiftDifferentInAWeekPattern']['id']=$index;
            $tcm_pharmacy['patternList']['ShiftDifferentInAWeekPattern']['code']='8';
            $tcm_pharmacy['patternList']['ShiftDifferentInAWeekPattern']['weight']=$patternList['diffShift']["diffShiftWeight"];
        }

        //contractList
        //第一个contranct
        $index++;
        $tcm_pharmacy['contractList']['@attributes']['id']=$index;
        $index++;
        $tcm_pharmacy['contractList'][1]['Contract']['@attributes']['id']=$index;
        $contractIndex[1]=$index;
        $tcm_pharmacy['contractList'][1]['Contract']['id']=$index;;
        $tcm_pharmacy['contractList'][1]['Contract']['code']='0';
        $tcm_pharmacy['contractList'][1]['Contract']['description']='fulltime';
        $tcm_pharmacy['contractList'][1]['Contract']['weekendDefinition']='SATURDAY_SUNDAY';

        $index++;
        $tcm_pharmacy['contractList'][1]['Contract']['contractLineList']['@attributes']['id']=$index;

        $index++;
        $tcm_pharmacy['contractList'][1]['Contract']['contractLineList'][0]['BooleanContractLine']['@attributes']['id']=$index;
        $tcm_pharmacy['contractList'][1]['Contract']['contractLineList'][0]['BooleanContractLine']['id']=$index;;
        $tcm_pharmacy['contractList'][1]['Contract']['contractLineList'][0]['BooleanContractLine']['contract']['@attributes']['reference']=$contractIndex[1];
        $tcm_pharmacy['contractList'][1]['Contract']['contractLineList'][0]['BooleanContractLine']['contract']['@data']='';
        $tcm_pharmacy['contractList'][1]['Contract']['contractLineList'][0]['BooleanContractLine']['contractLineType']='SINGLE_ASSIGNMENT_PER_DAY';
        $tcm_pharmacy['contractList'][1]['Contract']['contractLineList'][0]['BooleanContractLine']['enabled']='true';
        $tcm_pharmacy['contractList'][1]['Contract']['contractLineList'][0]['BooleanContractLine']['weight']='1';
        $booleanContractIndex[$index]=$index;

        // 最少和最多分配班次数目
        if(isset($patternList['minWorkDay'])&&isset($patternList['maxWorkDay']['maxWorkDayCount'])){
            $index++;
            $tcm_pharmacy['contractList'][1]['Contract']['contractLineList'][1]['MinMaxContractLine']['@attributes']['id']=$index;
            $tcm_pharmacy['contractList'][1]['Contract']['contractLineList'][1]['MinMaxContractLine']['id']=$index;;
            $tcm_pharmacy['contractList'][1]['Contract']['contractLineList'][1]['MinMaxContractLine']['contract']['@attributes']['reference']=$contractIndex[1];
            $tcm_pharmacy['contractList'][1]['Contract']['contractLineList'][1]['MinMaxContractLine']['contract']['@data']='';
            $tcm_pharmacy['contractList'][1]['Contract']['contractLineList'][1]['MinMaxContractLine']['contractLineType']='TOTAL_ASSIGNMENTS';
            $tcm_pharmacy['contractList'][1]['Contract']['contractLineList'][1]['MinMaxContractLine']['minimumEnabled']='true';
            $tcm_pharmacy['contractList'][1]['Contract']['contractLineList'][1]['MinMaxContractLine']['minimumValue']=$patternList['minWorkDay']['minWorkDayCount'];  
            $tcm_pharmacy['contractList'][1]['Contract']['contractLineList'][1]['MinMaxContractLine']['minimumWeight']=$patternList['minWorkDay']['minWorkDayWeight'];
            $tcm_pharmacy['contractList'][1]['Contract']['contractLineList'][1]['MinMaxContractLine']['maximumEnabled']='true';
            $tcm_pharmacy['contractList'][1]['Contract']['contractLineList'][1]['MinMaxContractLine']['maximumValue']=$patternList['maxWorkDay']['maxWorkDayCount'];
            $tcm_pharmacy['contractList'][1]['Contract']['contractLineList'][1]['MinMaxContractLine']['maximumWeight']=$patternList['maxWorkDay']['maxWorkDayWeight'];
            $MinMaxContractIndex[$index]=$index;
        }
        



        //最少和最多连续工作天数
        $index++;
        $tcm_pharmacy['contractList'][1]['Contract']['contractLineList'][2]['MinMaxContractLine']['@attributes']['id']=$index;
        $tcm_pharmacy['contractList'][1]['Contract']['contractLineList'][2]['MinMaxContractLine']['id']=$index;;
        $tcm_pharmacy['contractList'][1]['Contract']['contractLineList'][2]['MinMaxContractLine']['contract']['@attributes']['reference']=$contractIndex[1];
        $tcm_pharmacy['contractList'][1]['Contract']['contractLineList'][2]['MinMaxContractLine']['contract']['@data']='';
        $tcm_pharmacy['contractList'][1]['Contract']['contractLineList'][2]['MinMaxContractLine']['contractLineType']='CONSECUTIVE_WORKING_DAYS';
        $tcm_pharmacy['contractList'][1]['Contract']['contractLineList'][2]['MinMaxContractLine']['minimumEnabled']='true';
        $tcm_pharmacy['contractList'][1]['Contract']['contractLineList'][2]['MinMaxContractLine']['minimumValue']=$patternList['minLastDay']['minWorkCount']; 
        $tcm_pharmacy['contractList'][1]['Contract']['contractLineList'][2]['MinMaxContractLine']['minimumWeight']=$patternList['minLastDay']['minWorkWeight']; 
        $tcm_pharmacy['contractList'][1]['Contract']['contractLineList'][2]['MinMaxContractLine']['maximumEnabled']='true';
        $tcm_pharmacy['contractList'][1]['Contract']['contractLineList'][2]['MinMaxContractLine']['maximumValue']=$patternList['maxLastDay']['maxWorkCount']; 
        $tcm_pharmacy['contractList'][1]['Contract']['contractLineList'][2]['MinMaxContractLine']['maximumWeight']=$patternList['maxLastDay']['maxWorkWeight']; 
        $MinMaxContractIndex[$index]=$index;


        //最少和最多连续休假天数
        $index++;
        $tcm_pharmacy['contractList'][1]['Contract']['contractLineList'][3]['MinMaxContractLine']['@attributes']['id']=$index;
        $tcm_pharmacy['contractList'][1]['Contract']['contractLineList'][3]['MinMaxContractLine']['id']=$index;;
        $tcm_pharmacy['contractList'][1]['Contract']['contractLineList'][3]['MinMaxContractLine']['contract']['@attributes']['reference']=$contractIndex[1];
        $tcm_pharmacy['contractList'][1]['Contract']['contractLineList'][3]['MinMaxContractLine']['contract']['@data']='';
        $tcm_pharmacy['contractList'][1]['Contract']['contractLineList'][3]['MinMaxContractLine']['contractLineType']='CONSECUTIVE_FREE_DAYS';
        $tcm_pharmacy['contractList'][1]['Contract']['contractLineList'][3]['MinMaxContractLine']['minimumEnabled']='true';
        $tcm_pharmacy['contractList'][1]['Contract']['contractLineList'][3]['MinMaxContractLine']['minimumValue']=$patternList['minHoliday']['minHolidayCount'];  
        $tcm_pharmacy['contractList'][1]['Contract']['contractLineList'][3]['MinMaxContractLine']['minimumWeight']=$patternList['minHoliday']['minHolidayWeight'];
        $tcm_pharmacy['contractList'][1]['Contract']['contractLineList'][3]['MinMaxContractLine']['maximumEnabled']='true';
        $tcm_pharmacy['contractList'][1]['Contract']['contractLineList'][3]['MinMaxContractLine']['maximumValue']=$patternList['maxHoliday']['maxHolidayCount'];
        $tcm_pharmacy['contractList'][1]['Contract']['contractLineList'][3]['MinMaxContractLine']['maximumWeight']=$patternList['maxHoliday']['maxHolidayWeight'];
        $MinMaxContractIndex[$index]=$index;


         //周末两天尽量连休
        $index++;

        $tcm_pharmacy['contractList'][1]['Contract']['contractLineList'][5]['BooleanContractLine']['@attributes']['id']=$index;
        $tcm_pharmacy['contractList'][1]['Contract']['contractLineList'][5]['BooleanContractLine']['id']=$index;;
        $tcm_pharmacy['contractList'][1]['Contract']['contractLineList'][5]['BooleanContractLine']['contract']['@attributes']['reference']=$contractIndex[1];
        $tcm_pharmacy['contractList'][1]['Contract']['contractLineList'][5]['BooleanContractLine']['contract']['@data']='';
        $tcm_pharmacy['contractList'][1]['Contract']['contractLineList'][5]['BooleanContractLine']['contractLineType']='COMPLETE_WEEKENDS';
        $tcm_pharmacy['contractList'][1]['Contract']['contractLineList'][5]['BooleanContractLine']['enabled']='true';
        $tcm_pharmacy['contractList'][1]['Contract']['contractLineList'][5]['BooleanContractLine']['weight']=$patternList['restOnStaAndSun']['restOnStaAndSunWeight'];
        $booleanContractIndex[$index]=$index;


        //周末尽量上同一班次

        $index++;

        $tcm_pharmacy['contractList'][1]['Contract']['contractLineList'][6]['BooleanContractLine']['@attributes']['id']=$index;
        $tcm_pharmacy['contractList'][1]['Contract']['contractLineList'][6]['BooleanContractLine']['id']=$index;;
        $tcm_pharmacy['contractList'][1]['Contract']['contractLineList'][6]['BooleanContractLine']['contract']['@attributes']['reference']=$contractIndex[1];
        $tcm_pharmacy['contractList'][1]['Contract']['contractLineList'][6]['BooleanContractLine']['contract']['@data']='';
        $tcm_pharmacy['contractList'][1]['Contract']['contractLineList'][6]['BooleanContractLine']['contractLineType']='IDENTICAL_SHIFT_TYPES_DURING_WEEKEND';
        $tcm_pharmacy['contractList'][1]['Contract']['contractLineList'][6]['BooleanContractLine']['enabled']='true';
        $tcm_pharmacy['contractList'][1]['Contract']['contractLineList'][6]['BooleanContractLine']['weight']=$patternList['continuWeekOneShift']['continuWeekOneShiftWeight'];
        $booleanContractIndex[$index]=$index;


        //连续工作几个周末
         
        $index++;
        $tcm_pharmacy['contractList'][1]['Contract']['contractLineList'][4]['MinMaxContractLine']['@attributes']['id']=$index;
        $tcm_pharmacy['contractList'][1]['Contract']['contractLineList'][4]['MinMaxContractLine']['id']=$index;;
        $tcm_pharmacy['contractList'][1]['Contract']['contractLineList'][4]['MinMaxContractLine']['contract']['@attributes']['reference']=$contractIndex[1];
        $tcm_pharmacy['contractList'][1]['Contract']['contractLineList'][4]['MinMaxContractLine']['contract']['@data']='';
        $tcm_pharmacy['contractList'][1]['Contract']['contractLineList'][4]['MinMaxContractLine']['contractLineType']='CONSECUTIVE_WORKING_WEEKENDS';
        $tcm_pharmacy['contractList'][1]['Contract']['contractLineList'][4]['MinMaxContractLine']['minimumEnabled']='true';
        $tcm_pharmacy['contractList'][1]['Contract']['contractLineList'][4]['MinMaxContractLine']['minimumValue']=$patternList['minWorkWeekendNum']['minWorkWeekendCount'];  
        $tcm_pharmacy['contractList'][1]['Contract']['contractLineList'][4]['MinMaxContractLine']['minimumWeight']='1';
        $tcm_pharmacy['contractList'][1]['Contract']['contractLineList'][4]['MinMaxContractLine']['maximumEnabled']='true';
        $tcm_pharmacy['contractList'][1]['Contract']['contractLineList'][4]['MinMaxContractLine']['maximumValue']=$patternList['maxWeekendShift']["allowWeekendShift"];
        $tcm_pharmacy['contractList'][1]['Contract']['contractLineList'][4]['MinMaxContractLine']['maximumWeight']=$patternList['maxWeekendShift']["maxWeekendShiftWeight"];
        $MinMaxContractIndex[$index]=$index;
        


        $index++;

        $tcm_pharmacy['contractList'][1]['Contract']['contractLineList'][8]['BooleanContractLine']['@attributes']['id']=$index;
        $tcm_pharmacy['contractList'][1]['Contract']['contractLineList'][8]['BooleanContractLine']['id']=$index;;
        $tcm_pharmacy['contractList'][1]['Contract']['contractLineList'][8]['BooleanContractLine']['contract']['@attributes']['reference']=$contractIndex[1];
        $tcm_pharmacy['contractList'][1]['Contract']['contractLineList'][8]['BooleanContractLine']['contract']['@data']='';
        $tcm_pharmacy['contractList'][1]['Contract']['contractLineList'][8]['BooleanContractLine']['contractLineType']='NO_NIGHT_SHIFT_BEFORE_FREE_WEEKEND';
        $tcm_pharmacy['contractList'][1]['Contract']['contractLineList'][8]['BooleanContractLine']['enabled']='true';
        $tcm_pharmacy['contractList'][1]['Contract']['contractLineList'][8]['BooleanContractLine']['weight']='1';
        $booleanContractIndex[$index]=$index;

        $index++;

        $tcm_pharmacy['contractList'][1]['Contract']['contractLineList'][9]['BooleanContractLine']['@attributes']['id']=$index;
        $tcm_pharmacy['contractList'][1]['Contract']['contractLineList'][9]['BooleanContractLine']['id']=$index;;
        $tcm_pharmacy['contractList'][1]['Contract']['contractLineList'][9]['BooleanContractLine']['contract']['@attributes']['reference']=$contractIndex[1];
        $tcm_pharmacy['contractList'][1]['Contract']['contractLineList'][9]['BooleanContractLine']['contract']['@data']='';
        $tcm_pharmacy['contractList'][1]['Contract']['contractLineList'][9]['BooleanContractLine']['contractLineType']='ALTERNATIVE_SKILL_CATEGORY';
        $tcm_pharmacy['contractList'][1]['Contract']['contractLineList'][9]['BooleanContractLine']['enabled']='true';
        $tcm_pharmacy['contractList'][1]['Contract']['contractLineList'][9]['BooleanContractLine']['weight']='1';

        $booleanContractIndex[$index]=$index;


        //contractLineList列表
        $index++;
        $tcm_pharmacy['contractLineList']['@attributes']['id']=$index;

        foreach ($booleanContractIndex as $key => $booleanContract) {
            $tcm_pharmacy['contractLineList'][$key]['BooleanContractLine']['@attributes']['reference']=$booleanContract;
            $tcm_pharmacy['contractLineList'][$key]['BooleanContractLine']['@data']='';
        }

        foreach ($MinMaxContractIndex as $key => $minMaxContract) {
            $tcm_pharmacy['contractLineList'][$key]['MinMaxContractLine']['@attributes']['reference']=$minMaxContract;
            $tcm_pharmacy['contractLineList'][$key]['MinMaxContractLine']['@data']='';
        }

        $index++;
        $tcm_pharmacy['patternContractLineList']['@attributes']['id']=$index;

        if(isset($patternIndex['LastSomeDaysPattern'])&&count($patternIndex['LastSomeDaysPattern'])>0){
            foreach ($patternIndex['LastSomeDaysPattern'] as $key_pat => $value_pat) {

                $index++;
                $tcm_pharmacy['patternContractLineList'][$index]['PatternContractLine']['@attributes']['id']=$index;
                $tcm_pharmacy['patternContractLineList'][$index]['PatternContractLine']['id']=$index;;
                $tcm_pharmacy['patternContractLineList'][$index]['PatternContractLine']['contract']['@attributes']['reference']=$contractIndex[1];
                $tcm_pharmacy['patternContractLineList'][$index]['PatternContractLine']['contract']['@data']='';
                
                $tcm_pharmacy['patternContractLineList'][$index]['PatternContractLine']['pattern']['@attributes']['class']='IdenticalShiftLastSomeDaysPattern';
                $tcm_pharmacy['patternContractLineList'][$index]['PatternContractLine']['pattern']['@attributes']['reference']=$value_pat;
                $tcm_pharmacy['patternContractLineList'][$index]['PatternContractLine']['pattern']['@data']='';
                
            }
            
        }
     
        if(isset($patternIndex['AssignedOnlyforMan'])){
            $index++;
            $tcm_pharmacy['patternContractLineList'][$index]['PatternContractLine']['@attributes']['id']=$index;
            $tcm_pharmacy['patternContractLineList'][$index]['PatternContractLine']['id']=$index;;
            $tcm_pharmacy['patternContractLineList'][$index]['PatternContractLine']['contract']['@attributes']['reference']=$contractIndex[1];
            $tcm_pharmacy['patternContractLineList'][$index]['PatternContractLine']['contract']['@data']='';
            
            $tcm_pharmacy['patternContractLineList'][$index]['PatternContractLine']['pattern']['@attributes']['class']='ShiftAssignedOnlyforManPattern';
            $tcm_pharmacy['patternContractLineList'][$index]['PatternContractLine']['pattern']['@attributes']['reference']=$patternIndex['AssignedOnlyforMan'];
            $tcm_pharmacy['patternContractLineList'][$index]['PatternContractLine']['pattern']['@data']='';
        }    

        if(isset($patternIndex['FreeAfterWeekendWorkDayPattern'])){
            $index++;
            $tcm_pharmacy['patternContractLineList'][$index]['PatternContractLine']['@attributes']['id']=$index;
            $tcm_pharmacy['patternContractLineList'][$index]['PatternContractLine']['id']=$index;;
            $tcm_pharmacy['patternContractLineList'][$index]['PatternContractLine']['contract']['@attributes']['reference']=$contractIndex[1];;
            $tcm_pharmacy['patternContractLineList'][$index]['PatternContractLine']['contract']['@data']='';
            
             $tcm_pharmacy['patternContractLineList'][$index]['PatternContractLine']['pattern']['@attributes']['class']='FreeAfterWeekendWorkDayPattern';
             $tcm_pharmacy['patternContractLineList'][$index]['PatternContractLine']['pattern']['@attributes']['reference']=$patternIndex['FreeAfterWeekendWorkDayPattern'];
            $tcm_pharmacy['patternContractLineList'][$index]['PatternContractLine']['pattern']['@data']='';
        }
       
        if(isset($patternIndex['FreeAfterANightShiftPattern'])){
            $index++;
            $tcm_pharmacy['patternContractLineList'][$index]['PatternContractLine']['@attributes']['id']=$index;
            $tcm_pharmacy['patternContractLineList'][$index]['PatternContractLine']['id']=$index;;
            $tcm_pharmacy['patternContractLineList'][$index]['PatternContractLine']['contract']['@attributes']['reference']=$contractIndex[1];;
            $tcm_pharmacy['patternContractLineList'][$index]['PatternContractLine']['contract']['@data']='';
            
            $tcm_pharmacy['patternContractLineList'][$index]['PatternContractLine']['pattern']['@attributes']['class']='FreeAfterANightShiftPattern';
            $tcm_pharmacy['patternContractLineList'][$index]['PatternContractLine']['pattern']['@attributes']['reference']=$patternIndex['FreeAfterANightShiftPattern'];
            $tcm_pharmacy['patternContractLineList'][$index]['PatternContractLine']['pattern']['@data']='';
        }
        
        if(isset($patternIndex['ShiftAssignedAveragedAtAllEmployeesPattern'])){
            $index++;
            $tcm_pharmacy['patternContractLineList'][$index]['PatternContractLine']['@attributes']['id']=$index;
            $tcm_pharmacy['patternContractLineList'][$index]['PatternContractLine']['id']=$index;;
            $tcm_pharmacy['patternContractLineList'][$index]['PatternContractLine']['contract']['@attributes']['reference']=$contractIndex[1];;
            $tcm_pharmacy['patternContractLineList'][$index]['PatternContractLine']['contract']['@data']='';
            
             $tcm_pharmacy['patternContractLineList'][$index]['PatternContractLine']['pattern']['@attributes']['class']='ShiftAssignedAveragedAtAllEmployeesPattern';
            $tcm_pharmacy['patternContractLineList'][$index]['PatternContractLine']['pattern']['@attributes']['reference']=$patternIndex['ShiftAssignedAveragedAtAllEmployeesPattern'];
            $tcm_pharmacy['patternContractLineList'][$index]['PatternContractLine']['pattern']['@data']='';
        }
        
        if(isset($patternIndex['FreeTwoDaysEveryWeekPattern'])){
            $index++;
            $tcm_pharmacy['patternContractLineList'][$index]['PatternContractLine']['@attributes']['id']=$index;
            $tcm_pharmacy['patternContractLineList'][$index]['PatternContractLine']['id']=$index;;
            $tcm_pharmacy['patternContractLineList'][$index]['PatternContractLine']['contract']['@attributes']['reference']=$contractIndex[1];;
            $tcm_pharmacy['patternContractLineList'][$index]['PatternContractLine']['contract']['@data']='';
            
             $tcm_pharmacy['patternContractLineList'][$index]['PatternContractLine']['pattern']['@attributes']['class']='FreeTwoDaysEveryWeekPattern';
            $tcm_pharmacy['patternContractLineList'][$index]['PatternContractLine']['pattern']['@attributes']['reference']=$patternIndex['FreeTwoDaysEveryWeekPattern'];
            $tcm_pharmacy['patternContractLineList'][$index]['PatternContractLine']['pattern']['@data']='';
        }

        if(isset($patternIndex['ShiftAssignedSomeWeeksPattern'])){
            foreach ($patternIndex['ShiftAssignedSomeWeeksPattern'] as $key_some => $value_some) {
                $index++;
                $tcm_pharmacy['patternContractLineList'][$index]['PatternContractLine']['@attributes']['id']=$index;
                $tcm_pharmacy['patternContractLineList'][$index]['PatternContractLine']['id']=$index;;
                $tcm_pharmacy['patternContractLineList'][$index]['PatternContractLine']['contract']['@attributes']['reference']=$contractIndex[1];
                $tcm_pharmacy['patternContractLineList'][$index]['PatternContractLine']['contract']['@data']='';
                
                $tcm_pharmacy['patternContractLineList'][$index]['PatternContractLine']['pattern']['@attributes']['class']='ShiftAssignedSomeWeeksPattern';
                $tcm_pharmacy['patternContractLineList'][$index]['PatternContractLine']['pattern']['@attributes']['reference']=$value_some;
                $tcm_pharmacy['patternContractLineList'][$index]['PatternContractLine']['pattern']['@data']='';
            }
           
        }
       
        if(isset($patternIndex['ShiftType2DaysPattern'])){
            $index++;
            $tcm_pharmacy['patternContractLineList'][$index]['PatternContractLine']['@attributes']['id']=$index;
            $tcm_pharmacy['patternContractLineList'][$index]['PatternContractLine']['id']=$index;;
            $tcm_pharmacy['patternContractLineList'][$index]['PatternContractLine']['contract']['@attributes']['reference']=$contractIndex[1];
            $tcm_pharmacy['patternContractLineList'][$index]['PatternContractLine']['contract']['@data']='';
            
            $tcm_pharmacy['patternContractLineList'][$index]['PatternContractLine']['pattern']['@attributes']['class']='ShiftType2DaysPattern';
            $tcm_pharmacy['patternContractLineList'][$index]['PatternContractLine']['pattern']['@attributes']['reference']=$patternIndex['ShiftType2DaysPattern'];
            $tcm_pharmacy['patternContractLineList'][$index]['PatternContractLine']['pattern']['@data']='';
        }
        
        if(isset($patternIndex['FreeFirstbyTakeTurnsPattern'])){
            $index++;
            $tcm_pharmacy['patternContractLineList'][$index]['PatternContractLine']['@attributes']['id']=$index;
            $tcm_pharmacy['patternContractLineList'][$index]['PatternContractLine']['id']=$index;;
            $tcm_pharmacy['patternContractLineList'][$index]['PatternContractLine']['contract']['@attributes']['reference']=$contractIndex[1];
            $tcm_pharmacy['patternContractLineList'][$index]['PatternContractLine']['contract']['@data']='';
            
            $tcm_pharmacy['patternContractLineList'][$index]['PatternContractLine']['pattern']['@attributes']['class']='FreeFirstbyTakeTurnsPattern';
            $tcm_pharmacy['patternContractLineList'][$index]['PatternContractLine']['pattern']['@attributes']['reference']=$patternIndex['FreeFirstbyTakeTurnsPattern'];
            $tcm_pharmacy['patternContractLineList'][$index]['PatternContractLine']['pattern']['@data']='';
        }

        if(isset($patternIndex['shiftThree'])){
            $index++;
            $tcm_pharmacy['patternContractLineList'][$index]['PatternContractLine']['@attributes']['id']=$index;
            $tcm_pharmacy['patternContractLineList'][$index]['PatternContractLine']['id']=$index;;
            $tcm_pharmacy['patternContractLineList'][$index]['PatternContractLine']['contract']['@attributes']['reference']=$contractIndex[1];
            $tcm_pharmacy['patternContractLineList'][$index]['PatternContractLine']['contract']['@data']='';
            
            $tcm_pharmacy['patternContractLineList'][$index]['PatternContractLine']['pattern']['@attributes']['class']='ShiftType3DaysPattern';
            $tcm_pharmacy['patternContractLineList'][$index]['PatternContractLine']['pattern']['@attributes']['reference']=$patternIndex['shiftThree'];
            $tcm_pharmacy['patternContractLineList'][$index]['PatternContractLine']['pattern']['@data']='';
        }

        if(isset($patternIndex['FreeSecondDayAfterANightShiftPattern'])){
            $index++;
            $tcm_pharmacy['patternContractLineList'][$index]['PatternContractLine']['@attributes']['id']=$index;
            $tcm_pharmacy['patternContractLineList'][$index]['PatternContractLine']['id']=$index;;
            $tcm_pharmacy['patternContractLineList'][$index]['PatternContractLine']['contract']['@attributes']['reference']=$contractIndex[1];
            $tcm_pharmacy['patternContractLineList'][$index]['PatternContractLine']['contract']['@data']='';
            
            $tcm_pharmacy['patternContractLineList'][$index]['PatternContractLine']['pattern']['@attributes']['class']='FreeSecondDayAfterANightShiftPattern';
            $tcm_pharmacy['patternContractLineList'][$index]['PatternContractLine']['pattern']['@attributes']['reference']=$patternIndex['FreeSecondDayAfterANightShiftPattern'];
            $tcm_pharmacy['patternContractLineList'][$index]['PatternContractLine']['pattern']['@data']='';
        }

        if(isset($patternIndex['ShiftWorkNumberAssignedforPergnantPattern'])){
            $index++;
            $tcm_pharmacy['patternContractLineList'][$index]['PatternContractLine']['@attributes']['id']=$index;
            $tcm_pharmacy['patternContractLineList'][$index]['PatternContractLine']['id']=$index;;
            $tcm_pharmacy['patternContractLineList'][$index]['PatternContractLine']['contract']['@attributes']['reference']=$contractIndex[1];
            $tcm_pharmacy['patternContractLineList'][$index]['PatternContractLine']['contract']['@data']='';
            
            $tcm_pharmacy['patternContractLineList'][$index]['PatternContractLine']['pattern']['@attributes']['class']='ShiftWorkNumberAssignedforPergnantPattern';
            $tcm_pharmacy['patternContractLineList'][$index]['PatternContractLine']['pattern']['@attributes']['reference']=$patternIndex['ShiftWorkNumberAssignedforPergnantPattern'];
            $tcm_pharmacy['patternContractLineList'][$index]['PatternContractLine']['pattern']['@data']='';
        }

        if(isset($patternIndex['ShiftDifferentInAWeekPattern'])){
            $index++;
            $tcm_pharmacy['patternContractLineList'][$index]['PatternContractLine']['@attributes']['id']=$index;
            $tcm_pharmacy['patternContractLineList'][$index]['PatternContractLine']['id']=$index;;
            $tcm_pharmacy['patternContractLineList'][$index]['PatternContractLine']['contract']['@attributes']['reference']=$contractIndex[1];
            $tcm_pharmacy['patternContractLineList'][$index]['PatternContractLine']['contract']['@data']='';
            
            $tcm_pharmacy['patternContractLineList'][$index]['PatternContractLine']['pattern']['@attributes']['class']='ShiftDifferentInAWeekPattern';
            $tcm_pharmacy['patternContractLineList'][$index]['PatternContractLine']['pattern']['@attributes']['reference']=$patternIndex['ShiftDifferentInAWeekPattern'];
            $tcm_pharmacy['patternContractLineList'][$index]['PatternContractLine']['pattern']['@data']='';
        }


        //只是得到第一天和最后一天信息，同时将创建的shiftTypeId 和shiftDateID存储起来
        $index++;
        $tcm_pharmacy['shiftDateList']['@attributes']['id']=$index;
        $exit_shiftDate=array_flip($shiftDateIndex);

        foreach ($shiftDates as $key => $shiftDate) {
           if(in_array($shiftDate['id'], $exit_shiftDate)){
                $tcm_pharmacy['shiftDateList'][$key]['ShiftDate']['@attributes']['reference']=$shiftDateIndex[$shiftDate['id']];
                $tcm_pharmacy['shiftDateList'][$key]['ShiftDate']['@data']='';
           }else{

                $date_format=getFormatDate($shiftDate['shift_date']);

                $index++;
                $dayIndex++;
                $tcm_pharmacy['shiftDateList'][$key]['ShiftDate']['@attributes']['id']=$index;
                $shiftDateIndex[$shiftDate['id']]=$index;
                $tcm_pharmacy['shiftDateList'][$key]['ShiftDate']['id']=$shiftDate['id'];
                $tcm_pharmacy['shiftDateList'][$key]['ShiftDate']['dayIndex']=$shiftDate['id'];
                $index++;
                $tcm_pharmacy['shiftDateList'][$key]['ShiftDate']['date']['@attributes']['id']=$index;
                $tcm_pharmacy['shiftDateList'][$key]['ShiftDate']['date']['@attributes']['resolves-to']='java.time.Ser';
    
                $tcm_pharmacy['shiftDateList'][$key]['ShiftDate']['date'][]['byte']='3';
                $tcm_pharmacy['shiftDateList'][$key]['ShiftDate']['date'][]['int']=$date_format['y'];
                $tcm_pharmacy['shiftDateList'][$key]['ShiftDate']['date'][]['byte']=$date_format['m'];
                $tcm_pharmacy['shiftDateList'][$key]['ShiftDate']['date'][]['byte']=$date_format['d'];
                $index++;
                $tcm_pharmacy['shiftDateList'][$key]['ShiftDate']['shiftList']['@attributes']['id']=$index;
                if(isset($shiftDates[$key]['shift'])&&count($shiftDates[$key]['shift'])>0){
                    foreach($shiftDates[$key]['shift'] as $k=>$shift){
                        $type_id=$shift['shift_type_id'];
                        if(isset($shiftTypes[$type_id])){
                            $shiftType=$shiftTypes[$type_id];
                            $index++;
                            $shiftListIndex[$shift['id']]=$index;
                            $tcm_pharmacy['shiftDateList'][$key]['ShiftDate']['shiftList'][$k]['Shift']['@attributes']['id']=$index;
                            $tcm_pharmacy['shiftDateList'][$key]['ShiftDate']['shiftList'][$k]['Shift']['id']=$index;
                            $tcm_pharmacy['shiftDateList'][$key]['ShiftDate']['shiftList'][$k]['Shift']['shiftDate']['@attributes']['reference']= $shiftDateIndex[$shiftDate['id']];
                            $tcm_pharmacy['shiftDateList'][$key]['ShiftDate']['shiftList'][$k]['Shift']['shiftDate']['@data']='';

                            //记录shiftTypeID
                            if(!isset($shiftTypeIndex[$type_id])){
                                $index++;
                                //记录shiftTypeID
                                $tcm_pharmacy['shiftDateList'][$key]['ShiftDate']['shiftList'][$k]['Shift']['shiftType']['@attributes']['id']=$index;
                                $shiftTypeIndex[$type_id]=$index;
                                $tcm_pharmacy['shiftDateList'][$key]['ShiftDate']['shiftList'][$k]['Shift']['shiftType']['id']=$index;
                                $tcm_pharmacy['shiftDateList'][$key]['ShiftDate']['shiftList'][$k]['Shift']['shiftType']['code']=$type_id;
                                $tcm_pharmacy['shiftDateList'][$key]['ShiftDate']['shiftList'][$k]['Shift']['shiftType']['index']=$type_id;
                                $tcm_pharmacy['shiftDateList'][$key]['ShiftDate']['shiftList'][$k]['Shift']['shiftType']['startTimeString']=$shiftType['start_time'];
                                $tcm_pharmacy['shiftDateList'][$key]['ShiftDate']['shiftList'][$k]['Shift']['shiftType']['endTimeString']=$shiftType['end_time'];
                                $tcm_pharmacy['shiftDateList'][$key]['ShiftDate']['shiftList'][$k]['Shift']['shiftType']['night']='false';
                                $tcm_pharmacy['shiftDateList'][$key]['ShiftDate']['shiftList'][$k]['Shift']['shiftType']['description']=$index;
                            }else{
                                $tcm_pharmacy['shiftDateList'][$key]['ShiftDate']['shiftList'][$k]['Shift']['shiftType']['@attributes']['reference']=$shiftTypeIndex[$type_id];
                                $tcm_pharmacy['shiftDateList'][$key]['ShiftDate']['shiftList'][$k]['Shift']['shiftType']['@data']='';
                            }
                            $tcm_pharmacy['shiftDateList'][$key]['ShiftDate']['shiftList'][$k]['Shift']['index']=$shift['id'];;
                            $tcm_pharmacy['shiftDateList'][$key]['ShiftDate']['shiftList'][$k]['Shift']['requiredEmployeeSize']=$shift['required_employee'];
                        }
                       

                    }
                }else{
                    $tcm_pharmacy['shiftDateList'][$key]['ShiftDate']['shiftList']['@data']='';
                }
                
                
           }
        }
 
        $index++;
        $tcm_pharmacy['employeeList']['@attributes']['id']=$index;



        //获取员工具体哪天不安排班次
        $no_day_emp=array();
        $noDayForEmp=array();
        $no_day_arr=array();




        foreach ($patternList['noShiftDay'] as $dayNoK => $dayNoV) {

            if(isset($dayNoV['noShiftDay'])&&isset($dayNoV['noShiftDayEmp'])&&!empty($dayNoV['noShiftDay'])&&!empty($dayNoV['noShiftDayEmp'])){
                $ndlist=$dayNoV['noShiftDay'];
                $nelist=$dayNoV['noShiftDayEmp'];
                if(isset($dayNoV['noShiftDay'])){
                    foreach ($ndlist as $key_nd => $value_nd) {
                        foreach ($date_to_week[$value_nd] as $key_week => $value_week) {
                            //将日期转换为
                            foreach ($nelist as $key_ne => $value_ne) {

                                $date_id=$date_id_list2[$value_week['shiftdate']];
                                $no_day_arr_1[$dayNoK]= $date_id;//不上班的天
                                $no_day_arr=array_merge($no_day_arr,$no_day_arr_1);

                                $no_day_emp_1[$dayNoK]=$value_ne;//不上班的员工
                                $no_day_emp=array_merge($no_day_emp,$no_day_emp_1);

                                $data[$dayNoK]['date']= $date_id;
                                $data[$dayNoK]['emp_number']=$value_ne;
                                $noDayForEmp=array_merge($noDayForEmp,$data);
                            }
                        }
                        
                    }
                    
                }
            }
            

        }

    
        $str=array();
        $new_array=array();

        $totalNoShiftDay=array_merge_recursive($noDayForEmp,$emp_leave);



        foreach ($totalNoShiftDay as $kd => $vd) {
            $str[$kd]=implode("-", $vd);
        }


        $str=array_unique($str);
        foreach ($str as $key_s => $value_s) {
            $array=explode("-", $value_s);
            $new_array[$key_s]['date']=$array[0];
            $new_array[$key_s]['emp_number']=$array[1];

        }
        foreach ($new_array as $kd => $vd) {
            if(isset($vd['emp_number'])){
                $sd[$vd['emp_number']][]=$vd;
            }
            
        }

        $leave_date_list=array_column($emp_leave, 'date',null);
        $leave_emp_list=array_column($emp_leave, 'emp_number',null);

        $no_day_emp=array_merge($no_day_emp,$leave_emp_list);
        $no_day_arr=array_merge($no_day_arr,$leave_date_list);

        $no_day_emp=array_filter( $no_day_emp);
        $no_day_arr=array_filter( $no_day_arr);

 
        //查找具体那一天哪些班不分配给该员工；
        $shiftNoForEmp=array();
        $shiftNoForEmptmp=array();
        $data1=array();
        $data2=array();
        $shiftIfExist1=array();

        foreach ($patternList['shiftNotForEmployee'] as $shifEmKey => $shifEmVal) {

            if(isset($shifEmVal['shiftNotForEmployeeShiftSelect'])&&isset($shifEmVal['shiftNotForEmployee'])){
                $emp_no_shifttype=$shifEmVal['shiftNotForEmployeeShiftSelect'];
                $emp_no_shiftstatus=$shifEmVal['shiftNotForEmployeeStatus'];
                $emp_no_shiftemp=$shifEmVal['shiftNotForEmployee'];
                $emp_no_shiftweight=$shifEmVal['shiftNotForEmployeeWeight'];
                $emp_no_shiftdate=$shifEmVal['shiftDate'];
                if($emp_no_shiftstatus==1){
                    foreach ($emp_no_shiftemp as $key_10 => $value_10) {
                        $key_new=$value_10;
                        if (array_key_exists($key_new, $shiftNoForEmptmp)) {
                            foreach ($emp_no_shiftdate as $key_11 => $value_11) {    
                                foreach ($date_to_week[$value_11] as $key_12 => $value_12) {
                                    //判断这条规则是否开启状态.同时判断这个班是否在当时建立的时候分配了这一天
                                    $shiftIfExist1=$shiftmodel->getShiftByTypeAndDate($emp_no_shifttype,$value_12['id']);
                                    if(isset($shiftIfExist1)){
                                        $tmpArray3[$key_10][$key_12]['shiftType']=$emp_no_shifttype;
                                        $tmpArray3[$key_10][$key_12]['empNumber']=$key_new;
                                        $tmpArray3[$key_10][$key_12]['shifDate']=$date_id_list2[$value_12['shiftdate']];
                                        $tmpArray3[$key_10][$key_12]['weight']=$emp_no_shiftweight;
                                        $shiftNoForEmptmp[$key_new]=array_merge($shiftNoForEmptmp[$key_new],$tmpArray3);
                                    }
                                }
                            }
                         }else{
                             foreach ($emp_no_shiftdate as $key_13 => $value_13) {
                                foreach ($date_to_week[$value_13] as $key_14 => $value_14) {
                                    $shiftIfExist1=$shiftmodel->getShiftByTypeAndDate($emp_no_shifttype,$value_14['id']);
                                    if(isset($shiftIfExist1)){
                                        $shiftNoForEmptmp[$key_new][$key_13][$key_14]['shiftType']=$emp_no_shifttype;
                                        $shiftNoForEmptmp[$key_new][$key_13][$key_14]['empNumber']=$key_new;
                                        $shiftNoForEmptmp[$key_new][$key_13][$key_14]['shifDate']=$date_id_list2[$value_14['shiftdate']];
                                        $shiftNoForEmptmp[$key_new][$key_13][$key_14]['weight']=$emp_no_shiftweight;
                                    }
                                }
                                
                            }
                         }
                    }
                }
            }
            
        }


        foreach ($shiftNoForEmptmp as $key_20 => $value_20) {
            $k=0;
            foreach ($value_20 as $key_21 => $value_21) {
               foreach ($value_21 as $key_22 => $value_22) {
                 $shiftNoForEmp[$key_20][$k]=$value_22;
                 $k++;
               }
            }
        }


        //查找指定某个班次分配给某该员工；
        $shiftOnForEmps = array();
        $shiftIfExist=array();
        $shiftOnForEmpstmp = array();


        if(isset($patternList['shiftForEmployee'])){
            foreach ($patternList['shiftForEmployee'] as $shifEmKey => $shifEmVal) {
                $emp_shifttype=$shifEmVal['shiftForEmployeeShiftSelect'];
                $emp_shiftstatus=$shifEmVal['shiftForEmployeeStatus'];
                $emp_shiftemp=isset($shifEmVal['shiftForEmployee'])?$shifEmVal['shiftForEmployee']:'';
                $emp_shiftweight=$shifEmVal['shiftForEmployeeWeight'];
                $emp_shiftdate=isset($shifEmVal['shiftDateForEmployee'])?$shifEmVal['shiftDateForEmployee']:'';;
       
                if($emp_shiftstatus==1){
                    foreach ($emp_shiftemp as $key_5 => $value_5) {

                        $key_new=$value_5;
                        if (array_key_exists($key_new, $shiftOnForEmpstmp)) {
                            foreach ($emp_shiftdate as $key_6 => $value_6) {    
                                foreach ($date_to_week[$value_6] as $key_9 => $value_9) {
                                    //判断这条规则是否开启状态.同时判断这个班是否在当时建立的时候分配了这一天
                                    $shiftIfExist=$shiftmodel->getShiftByTypeAndDate($emp_shifttype,$value_9['id']);
                                    if(isset($shiftIfExist)){
                                        $tmpArray2[$key_5][$key_9]['shiftType']=$emp_shifttype;
                                        $tmpArray2[$key_5][$key_9]['empNumber']=$key_new;
                                        $tmpArray2[$key_5][$key_9]['shifDate']=$date_id_list2[$value_9['shiftdate']];
                                        $tmpArray2[$key_5][$key_9]['weight']=$emp_shiftweight;
                                        $shiftOnForEmpstmp[$key_new]=array_merge($shiftOnForEmpstmp[$key_new],$tmpArray2);
                                    }
                                }
                            }
                         }else{
                             foreach ($emp_shiftdate as $key_7 => $value_7) {
                                foreach ($date_to_week[$value_7] as $key_8 => $value_8) {
                                    $shiftIfExist=$shiftmodel->getShiftByTypeAndDate($emp_shifttype,$value_8['id']);
                                    if(isset($shiftIfExist)>0){
                                        $shiftOnForEmpstmp[$key_new][$key_7][$key_8]['shiftType']=$emp_shifttype;
                                        $shiftOnForEmpstmp[$key_new][$key_7][$key_8]['empNumber']=$key_new;
                                        $shiftOnForEmpstmp[$key_new][$key_7][$key_8]['shifDate']=$date_id_list2[$value_8['shiftdate']];
                                        $shiftOnForEmpstmp[$key_new][$key_7][$key_8]['weight']=$emp_shiftweight;
                                    }
                                }
                            }
                            
                         }
                    }
                }
            }
        }
        
        
        foreach ($shiftOnForEmpstmp as $key_17 => $value_17) {
            $k=0;
            foreach ($value_17 as $key_18 => $value_18) {
               foreach ($value_18 as $key_19 => $value_19) {
                 $shiftOnForEmps[$key_17][$k]=$value_19;
                 $k++;
               }
            }
        }

        foreach ($employeeList as $key => $employee) {

            $empNum=$employee['emp_number'];
            $date_now=date('Y-m-d',time());
            $joined_date='1970-01-01';
            $birthday='1970-01-01';
            $joined_date=isset($employee['joined_date'])?$employee['joined_date']:$joined_date;
            $birthday=isset($employee['emp_birthday'])?$employee['emp_birthday']:$birthday;
            $age=birthday($birthday);
            $workMonth=getMonthNum($joined_date,$date_now,'-');
            $restHoliday=0;
            $index++;
            $tcm_pharmacy['employeeList'][$key]['Employee']['@attributes']['id']=$index;
            $employeeIndex[$empNum]=$index;
            $tcm_pharmacy['employeeList'][$key]['Employee']['id']=$empNum;
            $tcm_pharmacy['employeeList'][$key]['Employee']['code']=$empNum;
            $tcm_pharmacy['employeeList'][$key]['Employee']['name']=$empNum;
            $tcm_pharmacy['employeeList'][$key]['Employee']['identityLabel']=$employee['special_personnel'];
            $tcm_pharmacy['employeeList'][$key]['Employee']['age']=$age;
            $tcm_pharmacy['employeeList'][$key]['Employee']['title']=$employee['eeo_cat_code'];
            $tcm_pharmacy['employeeList'][$key]['Employee']['gender']=$employee['emp_gender'];
            $tcm_pharmacy['employeeList'][$key]['Employee']['workMonth']= $workMonth;
            $tcm_pharmacy['employeeList'][$key]['Employee']['education']=$employee['education_id'];
            $tcm_pharmacy['employeeList'][$key]['Employee']['freeDays']=$restHoliday;
            $tcm_pharmacy['employeeList'][$key]['Employee']['mutexName']=$employee['mutual_exclusion'];

            $tcm_pharmacy['employeeList'][$key]['Employee']['currDepartment']=$employee['work_station'];
           
            $tcm_pharmacy['employeeList'][$key]['Employee']['alloDepartment']=$employee['work_station'];
        
            $tcm_pharmacy['employeeList'][$key]['Employee']['contract']['@attributes']['reference']=$contractIndex[1];
            $tcm_pharmacy['employeeList'][$key]['Employee']['contract']['@data']='';

            $index++;
            $tcm_pharmacy['employeeList'][$key]['Employee']['dayOffRequestMap']['@attributes']['id']=$index;

            //指定员工不上某天的班
            if(!in_array($empNum, $no_day_emp)){
                $tcm_pharmacy['employeeList'][$key]['Employee']['dayOffRequestMap']['@data']="";
            }else{
                foreach ($sd[$employee['emp_number']] as $dayOffKey => $day) {
                    //根据日期id查找该日期及该天所有班次
                    $dayOff=$day['date'];
                    $shiftDateOff=$shiftdatemodel->getShiftDateById($dayOff);
                    if(null!=$shiftDateOff->id){//如果排班日期中有该天
                        //首先判断是否已经创建过该天和该类型,如果没有创建过，则运用下面模式，同时存储shiftDataindex中；
                        $exit_shiftDate=array_flip($shiftDateIndex);
                        if(!in_array($dayOff, $exit_shiftDate)){//如果没有创建过该天的信息
                            $date_format=getFormatDate($shiftDateOff->shift_date);
                            $index++;
                            $tcm_pharmacy['employeeList'][$key]['Employee']['dayOffRequestMap'][$dayOffKey]['entry']['ShiftDate']['@attributes']['id']=$index;
                            $shiftDateIndex[$dayOff]=$index;
                            $tcm_pharmacy['employeeList'][$key]['Employee']['dayOffRequestMap'][$dayOffKey]['entry']['ShiftDate']['id']=$dayOff;
                            $tcm_pharmacy['employeeList'][$key]['Employee']['dayOffRequestMap'][$dayOffKey]['entry']['ShiftDate']['dayIndex']=$dayOff;
                            $index++;
                            $tcm_pharmacy['employeeList'][$key]['Employee']['dayOffRequestMap'][$dayOffKey]['entry']['ShiftDate']['date']['@attributes']['id']=$index;
                            $tcm_pharmacy['employeeList'][$key]['Employee']['dayOffRequestMap'][$dayOffKey]['entry']['ShiftDate']['date']['@attributes']['resolves-to']='java.time.Ser';
                            $tcm_pharmacy['employeeList'][$key]['Employee']['dayOffRequestMap'][$dayOffKey]['entry']['ShiftDate']['date'][]['byte']='3';
                            $tcm_pharmacy['employeeList'][$key]['Employee']['dayOffRequestMap'][$dayOffKey]['entry']['ShiftDate']['date'][]['int']=$date_format['y'];
                            $tcm_pharmacy['employeeList'][$key]['Employee']['dayOffRequestMap'][$dayOffKey]['entry']['ShiftDate']['date'][]['byte']=$date_format['m'];
                            $tcm_pharmacy['employeeList'][$key]['Employee']['dayOffRequestMap'][$dayOffKey]['entry']['ShiftDate']['date'][]['byte']=$date_format['d'];

                            $index++;
                            $tcm_pharmacy['employeeList'][$key]['Employee']['dayOffRequestMap'][$dayOffKey]['entry']['ShiftDate']['shiftList']['@attributes']['id']=$index;
                            foreach ($shiftDateOff->shiftList as $k => $shift) {
                                $index++;
                                $tcm_pharmacy['employeeList'][$key]['Employee']['dayOffRequestMap'][$dayOffKey]['entry']['ShiftDate']['shiftList'][$k]['Shift']['@attributes']['id']=$index;
                                $shiftListIndex[$shift->id]=$index;
                                $tcm_pharmacy['employeeList'][$key]['Employee']['dayOffRequestMap'][$dayOffKey]['entry']['ShiftDate']['shiftList'][$k]['Shift']['id']=$index;
                                $tcm_pharmacy['employeeList'][$key]['Employee']['dayOffRequestMap'][$dayOffKey]['entry']['ShiftDate']['shiftList'][$k]['Shift']['shiftDate']['@attributes']['reference']=$shiftDateIndex[$dayOff];
                                $tcm_pharmacy['employeeList'][$key]['Employee']['dayOffRequestMap'][$dayOffKey]['entry']['ShiftDate']['shiftList'][$k]['Shift']['shiftDate']['@data']='';
                                if(null==$shiftTypeIndex[$shift->shift_type_id]){
                                    $index++;
                                    $tcm_pharmacy['employeeList'][$key]['Employee']['dayOffRequestMap'][$dayOffKey]['entry']['ShiftDate']['shiftList'][$k]['Shift']['shiftType']['@attributes']['id']=$index;
                                    $shiftTypeIndex[$shift->shift_type_id]=$index;
                                    $tcm_pharmacy['employeeList'][$key]['Employee']['dayOffRequestMap'][$dayOffKey]['entry']['ShiftDate']['shiftList'][$k]['Shift']['shiftType']['id']=$index;
                                    $tcm_pharmacy['employeeList'][$key]['Employee']['dayOffRequestMap'][$dayOffKey]['entry']['ShiftDate']['shiftList'][$k]['Shift']['shiftType']['code']=$shiftType->id;
                                    $tcm_pharmacy['employeeList'][$key]['Employee']['dayOffRequestMap'][$dayOffKey]['entry']['ShiftDate']['shiftList'][$k]['Shift']['shiftType']['index']=$shiftType->id;
                                    $tcm_pharmacy['employeeList'][$key]['Employee']['dayOffRequestMap'][$dayOffKey]['entry']['ShiftDate']['shiftList'][$k]['Shift']['shiftType']['startTimeString']=$shiftType->start_time;
                                    $tcm_pharmacy['employeeList'][$key]['Employee']['dayOffRequestMap'][$dayOffKey]['entry']['ShiftDate']['shiftList'][$k]['Shift']['shiftType']['endTimeString']=$shiftType->end_time;
                                    $tcm_pharmacy['employeeList'][$key]['Employee']['dayOffRequestMap'][$dayOffKey]['entry']['ShiftDate']['shiftList'][$k]['Shift']['shiftType']['night']='false';
                                    $tcm_pharmacy['employeeList'][$key]['Employee']['dayOffRequestMap'][$dayOffKey]['entry']['ShiftDate']['shiftList'][$k]['Shift']['shiftType']['description']=$index;
                                }else{
                                    $tcm_pharmacy['employeeList'][$key]['Employee']['dayOffRequestMap'][$dayOffKey]['entry']['ShiftDate']['shiftList'][$k]['Shift']['shiftType']['@attributes']['reference']=$shiftTypeIndex[$shift->shift_type_id];
                                    $tcm_pharmacy['employeeList'][$key]['Employee']['dayOffRequestMap'][$dayOffKey]['entry']['ShiftDate']['shiftList'][$k]['Shift']['shiftType']['@data']='';
                                }
                                //如果班次类型已经创建，直接引用
                                $tcm_pharmacy['employeeList'][$key]['Employee']['dayOffRequestMap'][$dayOffKey]['entry']['ShiftDate']['shiftList'][$k]['Shift']['index']=$shift->id;
                                $tcm_pharmacy['employeeList'][$key]['Employee']['dayOffRequestMap'][$dayOffKey]['entry']['ShiftDate']['shiftList'][$k]['Shift']['requiredEmployeeSize']=$shift->required_employee;

                            }
                            $index++;
                            $tcm_pharmacy['employeeList'][$key]['Employee']['dayOffRequestMap'][$dayOffKey]['entry']['DayOffRequest']['@attributes']['id']=$index;
                            $dayOffRequest[$index]=$index;
                            $tcm_pharmacy['employeeList'][$key]['Employee']['dayOffRequestMap'][$dayOffKey]['entry']['DayOffRequest']['id']=$index;
                            $tcm_pharmacy['employeeList'][$key]['Employee']['dayOffRequestMap'][$dayOffKey]['entry']['DayOffRequest']['employee']['@attributes']['reference']=$employeeIndex[$employee['emp_number']];
                            $tcm_pharmacy['employeeList'][$key]['Employee']['dayOffRequestMap'][$dayOffKey]['entry']['DayOffRequest']['employee']['@data']='';
                            $tcm_pharmacy['employeeList'][$key]['Employee']['dayOffRequestMap'][$dayOffKey]['entry']['DayOffRequest']['shiftDate']['@attributes']['reference']=$shiftDateIndex[$dayOff];
                            $tcm_pharmacy['employeeList'][$key]['Employee']['dayOffRequestMap'][$dayOffKey]['entry']['DayOffRequest']['shiftDate']['@data']='';
                            $tcm_pharmacy['employeeList'][$key]['Employee']['dayOffRequestMap'][$dayOffKey]['entry']['DayOffRequest']['weight']='10';

                        }else{//如果已经创建过该天信息，直接引用

                            $tcm_pharmacy['employeeList'][$key]['Employee']['dayOffRequestMap'][$dayOffKey]['entry']['ShiftDate']['@attributes']['reference']=$shiftDateIndex[$dayOff];
                            $tcm_pharmacy['employeeList'][$key]['Employee']['dayOffRequestMap'][$dayOffKey]['entry']['ShiftDate']['@data']='';
                            $index++;
                            $tcm_pharmacy['employeeList'][$key]['Employee']['dayOffRequestMap'][$dayOffKey]['entry']['DayOffRequest']['@attributes']['id']=$index;
                            $dayOffRequest[$index]=$index;
                            $tcm_pharmacy['employeeList'][$key]['Employee']['dayOffRequestMap'][$dayOffKey]['entry']['DayOffRequest']['id']=$index;
                            $tcm_pharmacy['employeeList'][$key]['Employee']['dayOffRequestMap'][$dayOffKey]['entry']['DayOffRequest']['employee']['@attributes']['reference']=$employeeIndex[$employee['emp_number']];
                            $tcm_pharmacy['employeeList'][$key]['Employee']['dayOffRequestMap'][$dayOffKey]['entry']['DayOffRequest']['employee']['@data']='';
                            $tcm_pharmacy['employeeList'][$key]['Employee']['dayOffRequestMap'][$dayOffKey]['entry']['DayOffRequest']['shiftDate']['@attributes']['reference']=$shiftDateIndex[$dayOff];
                            $tcm_pharmacy['employeeList'][$key]['Employee']['dayOffRequestMap'][$dayOffKey]['entry']['DayOffRequest']['shiftDate']['@data']='';
                            $tcm_pharmacy['employeeList'][$key]['Employee']['dayOffRequestMap'][$dayOffKey]['entry']['DayOffRequest']['weight']='10';

                        }
                    }else{//如果计划中的排班计划日期中没有该天信息，则为空值
                        $tcm_pharmacy['employeeList'][$key]['Employee']['dayOffRequestMap']['@data']="";
                    }
                }
            }
            //查找具体那一天哪些班不分配给该员工；
            $index++;
            $tcm_pharmacy['employeeList'][$key]['Employee']['shiftOffRequestMap']['@attributes']['id']=$index;
            if(empty($shiftNoForEmp[$empNum])){//如果没有指定某员工不上某天某个班
                $tcm_pharmacy['employeeList'][$key]['Employee']['shiftOffRequestMap']['@data']='';
            }else{//如果指定某员工不上某天某个班 ，或者不上所有天的这个班
                foreach ($shiftNoForEmp[$empNum] as $sneKey => $emNoShift) {
                    //单独某一天不上这个班
                    $shiftDateEm=$emNoShift['shifDate'];
                    $shifTypeEm=$emNoShift['shiftType'];
                    //循环这些天
                    $ifCreate=true;
                    $shiftEmtity=$shiftmodel->getShiftByTypeAndDate($shifTypeEm,$shiftDateEm);

                    //查看这一天是否在已经存在,如果存在，查找这一天的这个班；
                    if(!empty($shiftDateIndex[$shiftDateEm])&& null != $shiftEmtity->id){
       
                        $tcm_pharmacy['employeeList'][$key]['Employee']['shiftOffRequestMap'][$sneKey]['entry']['Shift']['@attributes']['reference']=$shiftListIndex[$shiftEmtity->id];
                        $tcm_pharmacy['employeeList'][$key]['Employee']['shiftOffRequestMap'][$sneKey]['entry']['Shift']['@data']='';
                        $index++;
                        $tcm_pharmacy['employeeList'][$key]['Employee']['shiftOffRequestMap'][$sneKey]['entry']['ShiftOffRequest']['@attributes']['id']=$index;
                        $shiftOffRequest[$index]=$index;
                        $tcm_pharmacy['employeeList'][$key]['Employee']['shiftOffRequestMap'][$sneKey]['entry']['ShiftOffRequest']['id']=$index;
                        $tcm_pharmacy['employeeList'][$key]['Employee']['shiftOffRequestMap'][$sneKey]['entry']['ShiftOffRequest']['employee']['@attributes']['reference']=$employeeIndex[$employee['emp_number']];
                        $tcm_pharmacy['employeeList'][$key]['Employee']['shiftOffRequestMap'][$sneKey]['entry']['ShiftOffRequest']['employee']['@data']='';
                        $tcm_pharmacy['employeeList'][$key]['Employee']['shiftOffRequestMap'][$sneKey]['entry']['ShiftOffRequest']['shift']['@attributes']['reference']=$shiftListIndex[$shiftEmtity->id];
                        $tcm_pharmacy['employeeList'][$key]['Employee']['shiftOffRequestMap'][$sneKey]['entry']['ShiftOffRequest']['shift']['@data']='';
                        $tcm_pharmacy['employeeList'][$key]['Employee']['shiftOffRequestMap'][$sneKey]['entry']['ShiftOffRequest']['weight']=$emNoShift['weight'];
                    }else if(empty($shiftDateIndex[$shiftDateEm]) && null != $shiftEmtity->id ){

                        //罗列出这一天所有的班
                        $empNoShifts=$shiftmodel->getShiftByDate($shiftDateEm,$scheduleID);
                        $date_format=getFormatDate($shiftDatesByIndex[$shiftDateEm]['shift_date']);
                        $entry_shift=array();
                        $index++;
                        $tcm_pharmacy['employeeList'][$key]['Employee']['shiftOffRequestMap'][$sneKey]['entry']['Shift']['@attributes']['id']=$index;
                        $entry_shift[$shiftEmtity->id]=$index;
                        $shiftListIndex[$shiftEmtity->id]=$index;
                        $tcm_pharmacy['employeeList'][$key]['Employee']['shiftOffRequestMap'][$sneKey]['entry']['Shift']['id']=$index;
                        $index++;
                        $tcm_pharmacy['employeeList'][$key]['Employee']['shiftOffRequestMap'][$sneKey]['entry']['Shift']['shiftDate']['@attributes']['id']=$index;
                        $shiftDateIndex[$shiftDateEm]=$index;
                        $tcm_pharmacy['employeeList'][$key]['Employee']['shiftOffRequestMap'][$sneKey]['entry']['Shift']['shiftDate']['id']=$shiftDateEm;
                        $tcm_pharmacy['employeeList'][$key]['Employee']['shiftOffRequestMap'][$sneKey]['entry']['Shift']['shiftDate']['dayIndex']=$shiftDateEm;
                        $index++;
                        $tcm_pharmacy['employeeList'][$key]['Employee']['shiftOffRequestMap'][$sneKey]['entry']['Shift']['shiftDate']['date']['@attributes']['id']=$index;
                        $tcm_pharmacy['employeeList'][$key]['Employee']['shiftOffRequestMap'][$sneKey]['entry']['Shift']['shiftDate']['date']['@attributes']['resolves-to']='java.time.Ser';                      
                        $tcm_pharmacy['employeeList'][$key]['Employee']['shiftOffRequestMap'][$sneKey]['entry']['Shift']['shiftDate']['date'][]['byte']='3';
                        $tcm_pharmacy['employeeList'][$key]['Employee']['shiftOffRequestMap'][$sneKey]['entry']['Shift']['shiftDate']['date'][]['int']=$date_format['y'];
                        $tcm_pharmacy['employeeList'][$key]['Employee']['shiftOffRequestMap'][$sneKey]['entry']['Shift']['shiftDate']['date'][]['byte']=$date_format['m'];
                        $tcm_pharmacy['employeeList'][$key]['Employee']['shiftOffRequestMap'][$sneKey]['entry']['Shift']['shiftDate']['date'][]['byte']=$date_format['d'];
                        $index++;
                        $tcm_pharmacy['employeeList'][$key]['Employee']['shiftOffRequestMap'][$sneKey]['entry']['Shift']['shiftDate']['shiftList']['@attributes']['id']=$index;

                        //循环这一天所有班
                        foreach ($empNoShifts as $eskey => $empNoShift) {

                            if($shiftEmtity->id==$empNoShift['id']){
                                $tcm_pharmacy['employeeList'][$key]['Employee']['shiftOffRequestMap'][$sneKey]['entry']['Shift']['shiftDate']['shiftList'][$eskey]['Shift']['@attributes']['reference']=  $entry_shift[$shiftEmtity->id];
                                
                                $tcm_pharmacy['employeeList'][$key]['Employee']['shiftOffRequestMap'][$sneKey]['entry']['Shift']['shiftDate']['shiftList'][$eskey]['Shift']['@data']= '';
                            }else{
                                $index++;
                                $tcm_pharmacy['employeeList'][$key]['Employee']['shiftOffRequestMap'][$sneKey]['entry']['Shift']['shiftDate']['shiftList'][$eskey]['Shift']['@attributes']['id']=$index;
                                $shiftListIndex[$empNoShift['id']]=$index;
                                $tcm_pharmacy['employeeList'][$key]['Employee']['shiftOffRequestMap'][$sneKey]['entry']['Shift']['shiftDate']['shiftList'][$eskey]['Shift']['id']=$index;
                                $tcm_pharmacy['employeeList'][$key]['Employee']['shiftOffRequestMap'][$sneKey]['entry']['Shift']['shiftDate']['shiftList'][$eskey]['Shift']['shiftDate']['@attributes']['reference']=$shiftDateIndex[$empNoShift['shiftdate_id']];
                                $tcm_pharmacy['employeeList'][$key]['Employee']['shiftOffRequestMap'][$sneKey]['entry']['Shift']['shiftDate']['shiftList'][$eskey]['Shift']['shiftDate']['@data']='';
                                $tcm_pharmacy['employeeList'][$key]['Employee']['shiftOffRequestMap'][$sneKey]['entry']['Shift']['shiftDate']['shiftList'][$eskey]['Shift']['shiftType']['@attributes']['reference']=$shiftTypeIndex[$empNoShift['shift_type_id']];
                                $tcm_pharmacy['employeeList'][$key]['Employee']['shiftOffRequestMap'][$sneKey]['entry']['Shift']['shiftDate']['shiftList'][$eskey]['Shift']['shiftType']['@data']='';
                                $tcm_pharmacy['employeeList'][$key]['Employee']['shiftOffRequestMap'][$sneKey]['entry']['Shift']['shiftDate']['shiftList'][$eskey]['Shift']['index']=$empNoShift['id'];
                                $tcm_pharmacy['employeeList'][$key]['Employee']['shiftOffRequestMap'][$sneKey]['entry']['Shift']['shiftDate']['shiftList'][$eskey]['Shift']['requiredEmployeeSize']=$empNoShift['required_employee'];
                            }
              
                        }


                        $tcm_pharmacy['employeeList'][$key]['Employee']['shiftOffRequestMap'][$sneKey]['entry']['Shift']['shiftType']['@attributes']['reference']=$shiftTypeIndex[$shiftEmtity->shift_type_id];
                        $tcm_pharmacy['employeeList'][$key]['Employee']['shiftOffRequestMap'][$sneKey]['entry']['Shift']['shiftType']['@data']='';
                        $tcm_pharmacy['employeeList'][$key]['Employee']['shiftOffRequestMap'][$sneKey]['entry']['Shift']['index']=$shiftEmtity->id;
                        $tcm_pharmacy['employeeList'][$key]['Employee']['shiftOffRequestMap'][$sneKey]['entry']['Shift']['requiredEmployeeSize']=$shiftEmtity->required_employee;
                        $index++;
                        $tcm_pharmacy['employeeList'][$key]['Employee']['shiftOffRequestMap'][$sneKey]['entry']['ShiftOffRequest']['@attributes']['id']=$index;
                        $shiftOffRequest[$index]=$index;
                        $tcm_pharmacy['employeeList'][$key]['Employee']['shiftOffRequestMap'][$sneKey]['entry']['ShiftOffRequest']['id']=$index;
                        $tcm_pharmacy['employeeList'][$key]['Employee']['shiftOffRequestMap'][$sneKey]['entry']['ShiftOffRequest']['employee']['@attributes']['reference']=$employeeIndex[$employee['emp_number']];
                        $tcm_pharmacy['employeeList'][$key]['Employee']['shiftOffRequestMap'][$sneKey]['entry']['ShiftOffRequest']['employee']['@data']='';
                        $tcm_pharmacy['employeeList'][$key]['Employee']['shiftOffRequestMap'][$sneKey]['entry']['ShiftOffRequest']['shift']['@attributes']['reference']=$entry_shift[$shiftEmtity->id];
                        $tcm_pharmacy['employeeList'][$key]['Employee']['shiftOffRequestMap'][$sneKey]['entry']['ShiftOffRequest']['shift']['@data']='';
                        $tcm_pharmacy['employeeList'][$key]['Employee']['shiftOffRequestMap'][$sneKey]['entry']['ShiftOffRequest']['weight']=$emNoShift['weight'];
                    }else{
                         $tcm_pharmacy['employeeList'][$key]['Employee']['shiftOffRequestMap']['@data']='';
                    }
                   
                }
            }

            $index++;
            $tcm_pharmacy['employeeList'][$key]['Employee']['dayOnRequestMap']['@attributes']['id']=$index;
            $tcm_pharmacy['employeeList'][$key]['Employee']['dayOnRequestMap']['@data']='';

            //查找指定某个班次分配给某该员工；
            $shiftOnForEmp=array();
           
            if(isset($shiftOnForEmps[$empNum])){
                $shiftOnForEmp=$shiftOnForEmps[$empNum];
            }
            $index++;
            $tcm_pharmacy['employeeList'][$key]['Employee']['shiftOnRequestMap']['@attributes']['id']=$index;

            if(0==count($shiftOnForEmp)){//如果没有指定某员工上某天某个班
                $tcm_pharmacy['employeeList'][$key]['Employee']['shiftOnRequestMap']['@data']='';
            }else{
                foreach ($shiftOnForEmp as $sOnKey => $emForShift) {
        
                    $shiftDateForEm=$emForShift['shifDate'];
                    $shifTypeForEm=$emForShift['shiftType'];
                    //根据日期和班次类型获取班信息
                    $shiftForEmtity=$shiftmodel->getShiftByTypeAndDate($shifTypeForEm,$shiftDateForEm);

                    //查看这一天是否在已经存在,如果存在，查找这一天的这个班；
                    if(!empty($shiftDateIndex[$shiftDateForEm])&& null != $shiftForEmtity->id){
                        $shiftOnRequest['entry']['Shift']['@attributes']['reference']=$shiftListIndex[$shiftForEmtity->id];
                        $shiftOnRequest['entry']['Shift']['@data']='';
                
                        $index++;
                        $shiftOnRequest['entry']['ShiftOnRequest']['@attributes']['id']=$index;

                        $shiftOnRequests[$index]=$index;
                        $shiftOnRequest['entry']['ShiftOnRequest']['id']=$index;
                        $shiftOnRequest['entry']['ShiftOnRequest']['employee']['@attributes']['reference']=$employeeIndex[$employee['emp_number']];
                        $shiftOnRequest['entry']['ShiftOnRequest']['employee']['@data']='';
                        $shiftOnRequest['entry']['ShiftOnRequest']['shift']['@attributes']['reference']=$shiftListIndex[$shiftForEmtity->id];
                        $shiftOnRequest['entry']['ShiftOnRequest']['shift']['@data']='';
                        $shiftOnRequest['entry']['ShiftOnRequest']['weight']=$emForShift['weight'];
                        $tcm_pharmacy['employeeList'][$key]['Employee']['shiftOnRequestMap'][$sOnKey]=$shiftOnRequest;

                    }else if(empty($shiftDateIndex[$shiftDateForEm])&& null != $shiftForEmtity->id){
                        //罗列出这一天所有的班.$shiftDateForEm表示日期ID
                        $empForShifts=$shiftmodel->getShiftByDateAgo($shiftDateForEm);

                        if(null==$empForShifts){
                            $tcm_pharmacy['employeeList'][$key]['Employee']['shiftOnRequestMap']['@data']='';
                        }else{
                            //根据日期ID获取日期
                            $currrent_date=$shiftdatemodel->getShiftDateAgoById($shiftDateForEm)->shift_date;
                            $date_format_on=getFormatDate($currrent_date);  
                            $entry_shift_on=array();                   
                            $index++;
                            $tcm_pharmacy['employeeList'][$key]['Employee']['shiftOnRequestMap'][$sOnKey]['entry']['Shift']['@attributes']['id']=$index;
                            $entry_shift_on[$shiftForEmtity->id]=$index;
                            $shiftListIndex[$shiftForEmtity->id]=$index;
                            $tcm_pharmacy['employeeList'][$key]['Employee']['shiftOnRequestMap'][$sOnKey]['entry']['Shift']['id']=$index;
                            $index++;
                            $tcm_pharmacy['employeeList'][$key]['Employee']['shiftOnRequestMap'][$sOnKey]['entry']['Shift']['shiftDate']['@attributes']['id']=$index;
                            $shiftDateIndex[$emForShift['shifDate']]=$index;                    
                            $tcm_pharmacy['employeeList'][$key]['Employee']['shiftOnRequestMap'][$sOnKey]['entry']['Shift']['shiftDate']['id']=$emForShift['shifDate'];
                            $tcm_pharmacy['employeeList'][$key]['Employee']['shiftOnRequestMap'][$sOnKey]['entry']['Shift']['shiftDate']['dayIndex']=$emForShift['shifDate'];
                            $index++;
                            $tcm_pharmacy['employeeList'][$key]['Employee']['shiftOnRequestMap'][$sOnKey]['entry']['Shift']['shiftDate']['date']['@attributes']['id']=$index;
                            $tcm_pharmacy['employeeList'][$key]['Employee']['shiftOnRequestMap'][$sOnKey]['entry']['Shift']['shiftDate']['date']['@attributes']['resolves-to']='java.time.Ser';
                            $tcm_pharmacy['employeeList'][$key]['Employee']['shiftOnRequestMap'][$sOnKey]['entry']['Shift']['shiftDate']['date'][]['byte']='3';
                            $tcm_pharmacy['employeeList'][$key]['Employee']['shiftOnRequestMap'][$sOnKey]['entry']['Shift']['shiftDate']['date'][]['int']=$date_format_on['y'];
                            $tcm_pharmacy['employeeList'][$key]['Employee']['shiftOnRequestMap'][$sOnKey]['entry']['Shift']['shiftDate']['date'][]['byte']=$date_format_on['m'];
                            $tcm_pharmacy['employeeList'][$key]['Employee']['shiftOnRequestMap'][$sOnKey]['entry']['Shift']['shiftDate']['date'][]['byte']=$date_format_on['d'];

                            $index++;
                            $tcm_pharmacy['employeeList'][$key]['Employee']['shiftOnRequestMap'][$sOnKey]['entry']['Shift']['shiftDate']['shiftList']['@attributes']['id']=$index;
  
                            //循环这一天所有班；shiftForEmtity为根据班次和日期获取的班信息，empForShifts罗列出这一天所有的班
                            foreach ($empForShifts as $eskey => $empOnShift) {
                                //如果已经创建过shift，直接引用
                                if($shiftForEmtity->id==$empOnShift['id']&& null!= $entry_shift_on[$shiftForEmtity->id]){
                                    $tcm_pharmacy['employeeList'][$key]['Employee']['shiftOnRequestMap'][$sOnKey]['entry']['Shift']['shiftDate']['shiftList'][$eskey]['Shift']['@attributes']['reference']=  $entry_shift_on[$shiftForEmtity->id];
                                    $tcm_pharmacy['employeeList'][$key]['Employee']['shiftOnRequestMap'][$sOnKey]['entry']['Shift']['shiftDate']['shiftList'][$eskey]['Shift']['@data']= '';
                                }else{
                                    $index++;
                                    $tcm_pharmacy['employeeList'][$key]['Employee']['shiftOnRequestMap'][$sOnKey]['entry']['Shift']['shiftDate']['shiftList'][$eskey]['Shift']['@attributes']['id']=$index;

                                    $shiftListIndex[$empOnShift['id']]=$index;

                                    $tcm_pharmacy['employeeList'][$key]['Employee']['shiftOnRequestMap'][$sOnKey]['entry']['Shift']['shiftDate']['shiftList'][$eskey]['Shift']['id']=$index;
                                    $tcm_pharmacy['employeeList'][$key]['Employee']['shiftOnRequestMap'][$sOnKey]['entry']['Shift']['shiftDate']['shiftList'][$eskey]['Shift']['shiftDate']['@attributes']['reference']=$shiftDateIndex[$empOnShift['shiftdate_id']];
                                    $tcm_pharmacy['employeeList'][$key]['Employee']['shiftOnRequestMap'][$sOnKey]['entry']['Shift']['shiftDate']['shiftList'][$eskey]['Shift']['shiftDate']['@data']='';
                                    $tcm_pharmacy['employeeList'][$key]['Employee']['shiftOnRequestMap'][$sOnKey]['entry']['Shift']['shiftDate']['shiftList'][$eskey]['Shift']['shiftType']['@attributes']['reference']=$shiftTypeIndex[$empOnShift['shift_type_id']];
                                    $tcm_pharmacy['employeeList'][$key]['Employee']['shiftOnRequestMap'][$sOnKey]['entry']['Shift']['shiftDate']['shiftList'][$eskey]['Shift']['shiftType']['@data']='';
                                    $tcm_pharmacy['employeeList'][$key]['Employee']['shiftOnRequestMap'][$sOnKey]['entry']['Shift']['shiftDate']['shiftList'][$eskey]['Shift']['index']=$empOnShift['id'];
                                    $tcm_pharmacy['employeeList'][$key]['Employee']['shiftOnRequestMap'][$sOnKey]['entry']['Shift']['shiftDate']['shiftList'][$eskey]['Shift']['requiredEmployeeSize']=$empOnShift['required_employee'];
                                }

                            }
                 
                        }
                           
                        $tcm_pharmacy['employeeList'][$key]['Employee']['shiftOnRequestMap'][$sOnKey]['entry']['Shift']['shiftType']['@attributes']['reference']=$shiftTypeIndex[$shiftForEmtity->shift_type_id];
                        $tcm_pharmacy['employeeList'][$key]['Employee']['shiftOnRequestMap'][$sOnKey]['entry']['Shift']['shiftType']['@data']='';
                        $tcm_pharmacy['employeeList'][$key]['Employee']['shiftOnRequestMap'][$sOnKey]['entry']['Shift']['index']=$shiftForEmtity->id;
                        $tcm_pharmacy['employeeList'][$key]['Employee']['shiftOnRequestMap'][$sOnKey]['entry']['Shift']['requiredEmployeeSize']=$shiftForEmtity->required_employee;

                        $index++;
                        $tcm_pharmacy['employeeList'][$key]['Employee']['shiftOnRequestMap'][$sOnKey]['entry']['ShiftOnRequest']['@attributes']['id']=$index;
                        $shiftOnRequests[$index]=$index;
                        $tcm_pharmacy['employeeList'][$key]['Employee']['shiftOnRequestMap'][$sOnKey]['entry']['ShiftOnRequest']['id']=$index;
                        $tcm_pharmacy['employeeList'][$key]['Employee']['shiftOnRequestMap'][$sOnKey]['entry']['ShiftOnRequest']['employee']['@attributes']['reference']=$employeeIndex[$employee['emp_number']];
                        $tcm_pharmacy['employeeList'][$key]['Employee']['shiftOnRequestMap'][$sOnKey]['entry']['ShiftOnRequest']['employee']['@data']='';
                        $tcm_pharmacy['employeeList'][$key]['Employee']['shiftOnRequestMap'][$sOnKey]['entry']['ShiftOnRequest']['shift']['@attributes']['reference']=$entry_shift_on[$shiftForEmtity->id];
                        $tcm_pharmacy['employeeList'][$key]['Employee']['shiftOnRequestMap'][$sOnKey]['entry']['ShiftOnRequest']['shift']['@data']='';
                        $tcm_pharmacy['employeeList'][$key]['Employee']['shiftOnRequestMap'][$sOnKey]['entry']['ShiftOnRequest']['weight']=$emForShift['weight'];

                    }else{
                        $tcm_pharmacy['employeeList'][$key]['Employee']['shiftOnRequestMap']['@data']='';
                    }

                }
            }
            
        }
       
        $index++;
        $tcm_pharmacy['shiftTypeSkillRequirementList']['@attributes']['id']=$index;

        $sa=false;
        foreach ($shiftTypeToSkillList as $key => $shiftTypeToSkill) {
            if(isset($shiftTypeIndex[$shiftTypeToSkill['shift_type_id']]) && isset($skillListIndex[$shiftTypeToSkill['skill_id']])){
                $sa=true;
                $index++;
                $tcm_pharmacy['shiftTypeSkillRequirementList'][$key]['ShiftTypeSkillRequirement']['@attributes']['id']=$index;
                $tcm_pharmacy['shiftTypeSkillRequirementList'][$key]['ShiftTypeSkillRequirement']['id']=$shiftTypeToSkill['id'];
                $tcm_pharmacy['shiftTypeSkillRequirementList'][$key]['ShiftTypeSkillRequirement']['shiftType']['@attributes']['reference']=$shiftTypeIndex[$shiftTypeToSkill['shift_type_id']];
                $tcm_pharmacy['shiftTypeSkillRequirementList'][$key]['ShiftTypeSkillRequirement']['shiftType']['@data']='';
                $tcm_pharmacy['shiftTypeSkillRequirementList'][$key]['ShiftTypeSkillRequirement']['skill']['@attributes']['reference']=$skillListIndex[$shiftTypeToSkill['skill_id']];
                $tcm_pharmacy['shiftTypeSkillRequirementList'][$key]['ShiftTypeSkillRequirement']['skill']['@data']='';
            }
            
        }
        if($sa==false){
            $tcm_pharmacy['shiftTypeSkillRequirementList']['@data']='';
        }
        //shiftType列表；
        $index++;
        $tcm_pharmacy['shiftTypeList']['@attributes']['id']=$index;
        foreach ($shiftTypeIndex as $key => $shiftType) {
           $tcm_pharmacy['shiftTypeList'][$key]['ShiftType']['@attributes']['reference']=$shiftType;
           $tcm_pharmacy['shiftTypeList'][$key]['ShiftType']['@data']='';
        }

        //排班列表
        $index++;
        $tcm_pharmacy['shiftList']['@attributes']['id']=$index;
        if(empty($shifts)){
            $tcm_pharmacy['shiftList']['@data']='';

        }else{
            foreach ($shiftListIndex as $key => $shift) {
                $tcm_pharmacy['shiftList'][$key]['Shift']['@attributes']['reference']=$shift;
                $tcm_pharmacy['shiftList'][$key]['Shift']['@data']='';
            }
        }

         
        $s3=false;
        //技能列表
        if(null!=$employeeSkillList){
            $index++;
            $tcm_pharmacy['skillProficiencyList']['@attributes']['id']=$index;
            foreach ($employeeSkillList as $key => $employeeSkill) {

                if(isset($employeeIndex[$employeeSkill['emp_number']])&& isset($skillListIndex[$employeeSkill['skill_id']])){
                    $s3=true;
                    $index++;
                    $tcm_pharmacy['skillProficiencyList'][$key]['SkillProficiency']['@attributes']['id']=$index;
                    $tcm_pharmacy['skillProficiencyList'][$key]['SkillProficiency']['id']=$employeeSkill['id'];
                    $tcm_pharmacy['skillProficiencyList'][$key]['SkillProficiency']['employee']['@attributes']['reference']=$employeeIndex[$employeeSkill['emp_number']];
                    $tcm_pharmacy['skillProficiencyList'][$key]['SkillProficiency']['employee']['@data']='';

                    $tcm_pharmacy['skillProficiencyList'][$key]['SkillProficiency']['skill']['@attributes']['reference']=$skillListIndex[$employeeSkill['skill_id']];
                    $tcm_pharmacy['skillProficiencyList'][$key]['SkillProficiency']['skill']['@data']='';

                }
                
            }
        }

        if($s3==false){
            $tcm_pharmacy['skillProficiencyList']['@data']='';
        }
            
        //不上班
        if(!empty($dayOffRequest)){
            $index++;
            $tcm_pharmacy['dayOffRequestList']['@attributes']['id']=$index;
            foreach ($dayOffRequest as $key => $dayOff) {
            
                $tcm_pharmacy['dayOffRequestList'][$key]['DayOffRequest']['@attributes']['reference']=$dayOff;
                $tcm_pharmacy['dayOffRequestList'][$key]['DayOffRequest']['@data']='';

            }

        }else{
            $tcm_pharmacy['dayOffRequestList']['@attributes']['class']="empty-list";
            $tcm_pharmacy['dayOffRequestList']['@data']='';
        }
        
        $tcm_pharmacy['dayOnRequestList']['@attributes']['class']="empty-list";
        $tcm_pharmacy['dayOnRequestList']['@data']='';
        if(!empty($shiftOffRequest)){
            $index++;
            $tcm_pharmacy['dayOffRequestList']['@attributes']['id']=$index;
            foreach ($shiftOffRequest as $key => $shiftOff) {
            
                $tcm_pharmacy['shiftOffRequestList'][$key]['ShiftOffRequest']['@attributes']['reference']=$shiftOff;
                $tcm_pharmacy['shiftOffRequestList'][$key]['ShiftOffRequest']['@data']='';

            }

        }else{
            $tcm_pharmacy['shiftOffRequestList']['@attributes']['class']="empty-list";
            $tcm_pharmacy['shiftOffRequestList']['@data']='';
        }


        if(!empty($shiftOnRequests)){
            $index++;
            $tcm_pharmacy['shiftOnRequestList']['@attributes']['id']=$index;
            foreach ($shiftOnRequests as $key => $shiftOn) {
            
                $tcm_pharmacy['shiftOnRequestList'][$key]['ShiftOnRequest']['@attributes']['reference']=$shiftOn;
                $tcm_pharmacy['shiftOnRequestList'][$key]['ShiftOnRequest']['@data']='';

            }

        }else{
            $tcm_pharmacy['shiftOnRequestList']['@attributes']['class']="empty-list";
            $tcm_pharmacy['shiftOnRequestList']['@data']='';
        }

        $index++;
        $tcm_pharmacy['shiftAssignmentList']['@attributes']['id']=$index;
      
        foreach($shiftAssignments as $key=>$shiftAssignment){
            if(isset($shiftListIndex[$shiftAssignment['shift_id']])&&isset($shiftAssignment['shift_index'])){
                $index++;
                $tcm_pharmacy['shiftAssignmentList'][$key]['ShiftAssignment']['@attributes']['id']=$index;
                $tcm_pharmacy['shiftAssignmentList'][$key]['ShiftAssignment']['id']=$key;
                $tcm_pharmacy['shiftAssignmentList'][$key]['ShiftAssignment']['shift']['@attributes']['reference']=$shiftListIndex[$shiftAssignment['shift_id']];
                $tcm_pharmacy['shiftAssignmentList'][$key]['ShiftAssignment']['shift']['@data']='';
                $tcm_pharmacy['shiftAssignmentList'][$key]['ShiftAssignment']['indexInShift']=$shiftAssignment['shift_index'];
            }
        }

        $array_name="roster".$scheduleID;
         //将数据转化为xml格式；
        $toXmData=array_to_xml($tcm_pharmacy);
 
        //将XML存储为静态文件
        $lastin=substr($scheduleID, -1);
        $fname='xml_'.$lastin.'/';
        $a=cacheData($xml_name,$toXmData,$fname,$scheduleID);
        if($a==true){
            return true;
        }else{
            return false;
        }


    }
}
