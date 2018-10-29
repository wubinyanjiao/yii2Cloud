<?php

namespace common\models\overtime;

use common\models\employee\Employee;
use common\models\leave\LeaveEntitlement;
use common\models\shift\ShiftType;
use common\models\subunit\Subunit;
use Yii;
use \common\models\overtime\base\Overtime as BaseOvertime;
use yii\helpers\ArrayHelper;
use common\models\leave\LeaveEntitlementLog;
use common\models\user\User;
use common\models\attendance\ApproverTab;


/**
 * This is the model class for table "ohrm_overtime_list".
 */
class Overtime extends BaseOvertime
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
    public function addovertime($data,$firstName,$work_station,$userId){
        $emp_name = isset($data['emp_name']) ? $data['emp_name'] :'';
        $emp = Employee::find()->select(['emp_number','work_station'])->where(['emp_firstname'=>$emp_name])->one();
        if($firstName != 'admin'){
            if($emp['work_station'] != $work_station){
                return 2;
            }
        }

        $emp_number = $emp['emp_number'];
        $current_day = isset($data['current_day']) ? $data['current_day'] :'';
        if($current_day != ''){
            $current_day=strtotime($current_day);
            $current_day=date('Y-m-d',$current_day);
        }
        $stat_time = isset($data['stat_time']) ? $data['stat_time'] :'';
        $end_time = isset($data['end_time']) ? $data['end_time'] :'';
        $is_holiday = isset($data['is_holiday']) ? $data['is_holiday'] :'';
        $content = isset($data['content']) ? $data['content'] :'';


        $hour_differ = $this->return_hour_differ($stat_time,$end_time);
        $time_differ = $this->return_time_differ($stat_time,$end_time);

        $overtime = new Overtime();
        $overtime->emp_number = $emp_number;
        $overtime->creat_time = date('Y-m-d H:i:s',time());
        $overtime->stat_time = $stat_time;
        $overtime->end_time = $end_time;
        $overtime->time_differ = $time_differ;
        $overtime->hour_differ = $hour_differ;
        $overtime->current_day = $current_day;
        $overtime->is_holiday = $is_holiday;
        $overtime->status = 2;
        $overtime->is_pro = 1;
        $overtime->operation_name = $firstName;
        $overtime->content = $content;
        $query = $overtime->save();

        if($query){
            if($is_holiday == 1){
                if($time_differ > 0.5){
                    $days = 1;
                }else{
                    $days = 0.5;
                }
                $leave = new LeaveEntitlement();
                $info = $leave->changeEntitlementDays($emp_number,$days,1,$note='加班调休',$firstName,$userId);
                if(!$info){
                    return false;
                }
            }

            if($content == ''){
                return $query;
            }else{
                $overtime_id = $overtime->attributes['id'];
                $overtimecomment = new OvertimeComment();
                $overtimecomment->overtime_id = $overtime_id;
                $overtimecomment->created = date('Y-m-d H:i:s',time());
                $overtimecomment->created_by_name = $firstName;
                $overtimecomment->created_by_id = $emp_number;
                $overtimecomment->created_by_emp_number = $emp_number;
                $overtimecomment->comments = $content;
                $query = $overtimecomment->save();
                if($query){
                    return true;
                }else{
                    return false;
                }
            }
        }else{
            return false;
        }
    }

    public function return_time_differ($stat,$end){
        if($stat>$end){
            $end_time = strtotime('+1 days',strtotime($end));
            $stat_time= strtotime($stat);
        }else{

            $end_time = strtotime($end);
            $stat_time= strtotime($stat);
        }
        $diff = sprintf("%.2f",($end_time-$stat_time)%86400/3600/8);
        return $diff;
    }
    public function return_hour_differ($stat,$end){
        if($stat>$end){
            $end_time = strtotime('+1 days',strtotime($end));
            $stat_time= strtotime($stat);
        }else{

            $end_time = strtotime($end);
            $stat_time= strtotime($stat);
        }
        $diff = sprintf("%.2f",($end_time-$stat_time)%86400/3600);
        return $diff;
    }



    public function list($data,$first_name){
        $is_excel = isset($data['is_excel']) ? $data['is_excel'] :'0';
        $stat_time = isset($data['stat_time']) ? $data['stat_time'] :'';
        $end_time = isset($data['end_time']) ? $data['end_time'] :'';
        $emp_name = isset($data['emp_name']) ? $data['emp_name'] :'';
        $is_quit = isset($data['is_quit']) ? $data['is_quit'] :'';
        $work_station = isset($data['work_station']) ? $data['work_station'] :'';
        $page = isset($data['page']) ? $data['page'] :'1';
        if($emp_name != ''){
            $emp = Employee::find()->select(['emp_number','work_station'])->where(['emp_firstname'=>$emp_name])->one();
            if($first_name != 'admin'){
                if($emp['work_station'] != $work_station){
                    return 2;
                }
            }
            $emp_number = $emp['emp_number'];
        }else{
            $emp_number = '';
        }

        $status = isset($data['status']) ? $data['status'] :'';
        if($status != ''){
            $a = array();
            foreach ($status as $k => $v){
                $a[] = $v;
            }
            $status = join(',', $a);
        }

        $employee_number = Employee::find()->select(['emp_number'])->asArray()->where(['work_station'=>$work_station])->all();
        $arr = array();
        foreach ($employee_number as $k => $v){
            $arr[] = $v['emp_number'];
        }
        $arr_string = join(',', $arr);

        if($work_station == '-1'){
            $where = "1=1";
        }else{
            $where = "emp_number in ($arr_string)";
        }
        if($stat_time != '' ){
            $where .= " and current_day > '$stat_time' ";
        }
        if($end_time != ''){
            $where .=" and current_day < '$end_time'";
        }
        if($emp_number != ''){
            $where .= " and emp_number = '$emp_number'";
        }
        if($status != ''){
            $where .= " and status in ($status)";
        }

        $pagesize = 20;
        $startrow = ($page-1)*$pagesize;

        $overtime = new Overtime();
        if($is_excel == 1){
            $info['name'] = date('Y-m',time()).'加班表';
            $query = $overtime::find()->asArray()->where($where)->all();
        }else{
            $query = $overtime::find()->asArray()->where($where)->orderBy('creat_time DESC')->offset($startrow)->limit($pagesize)->all();
        }

        $count = $overtime::find()->asArray()->where($where)->count();
        foreach ($query as $k => $v){
            $comment = OvertimeComment::find()->select(['comments'])->where(['overtime_id'=>$v['id']])->orderBy('created DESC')->one();
            $name = Employee::find()->select(['emp_firstname'])->where(['emp_number'=>$v['emp_number']])->one();
            $query[$k]['emp_name'] = $name['emp_firstname'];
            $query[$k]['content'] = $comment['comments'];
            if($v['is_holiday'] == '1'){
                $query[$k]['is_holiday'] = '是';
            }else{
                $query[$k]['is_holiday'] = '否';
            }

            if($v['status'] == -1){
                $query[$k]['status_name'] = '已拒绝';
            }elseif ($v['status'] == 0){
                $query[$k]['status_name'] = '已取消';
            }elseif ($v['status'] == 1){
                $query[$k]['status_name'] = '等待批准';
            }elseif ($v['status'] == 2){
                $query[$k]['status_name'] = '已安排';
            }elseif ($v['status'] == 3){
                $query[$k]['status_name'] = '已使用';
            }

        }
        $info['pagesize'] = $pagesize;
        $info['count'] = (int)$count;
        $info['query'] = $query;
        return $info;
    }


    public function myexcel($data,$emp_number){

        $stat_time = isset($data['stat_time']) ? $data['stat_time'] :'';
        $end_time = isset($data['end_time']) ? $data['end_time'] :'';
        $status = isset($data['status']) ? $data['status'] :'';
        if($status != ''){
            $a = array();
            foreach ($status as $k => $v){
                $a[] = $v;
            }
            $status = join(',', $a);
        }


        $where = "emp_number = '$emp_number'";

        if($stat_time != '' ){
            $where .= " and current_day > '$stat_time' ";
        }
        if($end_time != ''){
            $where .=" and current_day < '$end_time'";
        }
        if($status != ''){
            $where .= " and status in ($status)";
        }

        $overtime = new Overtime();

        $info['name'] = date('Y-m',time()).'加班表';
        $query = $overtime::find()->asArray()->where($where)->all();


        foreach ($query as $k => $v){
            $comment = OvertimeComment::find()->select(['comments'])->where(['overtime_id'=>$v['id']])->orderBy('created DESC')->one();
            $name = Employee::find()->select(['emp_firstname'])->where(['emp_number'=>$v['emp_number']])->one();
            $query[$k]['emp_name'] = $name['emp_firstname'];
            $query[$k]['content'] = $comment['comments'];
            if($v['is_holiday'] == '1'){
                $query[$k]['is_holiday'] = '是';
            }else{
                $query[$k]['is_holiday'] = '否';
            }

            if($v['status'] == -1){
                $query[$k]['status_name'] = '已拒绝';
            }elseif ($v['status'] == 0){
                $query[$k]['status_name'] = '已取消';
            }elseif ($v['status'] == 1){
                $query[$k]['status_name'] = '等待批准';
            }elseif ($v['status'] == 2){
                $query[$k]['status_name'] = '已安排';
            }elseif ($v['status'] == 3){
                $query[$k]['status_name'] = '已使用';
            }

        }
        $info['query'] = $query;
        return $info;
    }

    public function myovertime($data,$emp_number){
        $sta_time = isset($data['start_time']) ? $data['start_time'] :'';
        $end_time = isset($data['end_time']) ? $data['end_time'] :'';
        $status = isset($data['status']) ? $data['status'] :'';
        if($status != ''){
            $a = array();
            foreach ($status as $k => $v){
                $a[] = $v;
            }
            $status = join(',', $a);
        }
        $page = isset($data['page']) ? $data['page'] :'';

        $where = "emp_number = '$emp_number'";
        if($sta_time != '' ){
            $where .= " and current_day > '$sta_time' ";
        }
        if($end_time != ''){
            $where .=" and current_day < '$end_time'";
        }
        if($status != ''){
            $where .= " and status in ($status)";
        }
        $pagesize = 20;
        $startrow = ($page-1)*$pagesize;

        $query = Overtime::find()->asArray()->where($where);
        $count = $query->count();
        $query = $query->offset($startrow)->limit($pagesize)->all();
        foreach ($query as $k => $v){
            $comment = OvertimeComment::find()->select(['comments'])->where(['overtime_id'=>$v['id']])->orderBy('created DESC')->one();
            $name = Employee::find()->select(['emp_firstname'])->where(['emp_number'=>$v['emp_number']])->one();
            $query[$k]['emp_name'] = $name['emp_firstname'];
            $query[$k]['content'] = $comment['comments'];
            if($v['is_holiday'] == '1'){
                $query[$k]['is_holiday'] = '是';
            }else{
                $query[$k]['is_holiday'] = '否';
            }

            if($v['status'] == -1){
                $query[$k]['status_name'] = '已拒绝';
            }elseif ($v['status'] == 0){
                $query[$k]['status_name'] = '已取消';
            }elseif ($v['status'] == 1){
                $query[$k]['status_name'] = '等待批准';
            }elseif ($v['status'] == 2){
                $query[$k]['status_name'] = '已安排';
            }elseif ($v['status'] == 3){
                $query[$k]['status_name'] = '已使用';
            }

        }
        $arr['count'] = (int)$count;
        $arr['pagesize'] = $pagesize;
        $arr['query'] = $query;
        return $arr;

    }


    public function addcomment($data,$user_id,$first_name){
        $comment = new OvertimeComment();
        $comment->overtime_id = $data['overtime_id'];
        $comment->created = date('Y-m_d H:i:s',time());
        $comment->created_by_name = $first_name;
        $comment->created_by_id = $user_id;
        $comment->created_by_id = $user_id;
        $comment->created_by_emp_number = $data['emp_number'];
        $comment->comments = $data['comments'];
        $query = $comment->save();
        return $query;
    }


    public function mytimecardlist($data,$emp_number){
        $page = isset($data['page']) ? $data['page'] :'1';
        $start_time = isset($data['start_time']) ? $data['start_time'] :'';
        if($start_time != ''){
            $start_time=strtotime($start_time);
            $start_time=date('Y-m-d',$start_time);
        }else{
            $start_time = date('Y-m-d',time());
        }
        $end_time = isset($data['end_time']) ? $data['end_time'] :'';
        if($end_time != ''){
            $end_time=strtotime($end_time);
            $end_time=date('Y-m-d',$end_time);
        }else{
            $end_time = date('Y-m-d',time());
        }
        $pagesize = 20;
        $startrow = ($page-1)*$pagesize;
        $where = "employee_id = '$emp_number' and is_in_status = 0";
        if($start_time != ''){
            $where .= " and first_daka_time >= '$start_time'";
        }
        if($end_time != ''){
            $where .= " and first_daka_time <= '$end_time'";
        }


        $query = AttendanceRecord::find()->asArray()->offset($startrow)->limit($pagesize)->where($where);
        $data = $query->all();
        $count = $query->count();

        foreach ($data as $k => $v){
            $time = date('Y-m-d',strtotime($v['first_daka_time']));
            $where = "employee_id = '$v[employee_id]' and first_daka_time like '$time%' and is_in_status > 0";
            $arr = AttendanceRecord::find()->where($where)->one();
            if($arr != ''){
                $data[$k]['forget_forenoon'] = $arr['punch_in_actual_time'];
                $data[$k]['forget_afternoon'] = $arr['punch_out_actual_time'];
                $data[$k]['punch_in_note'] = $arr['punch_in_note'];
            }else{
                $data[$k]['forget_forenoon'] ='';
                $data[$k]['forget_afternoon'] = '';
                $data[$k]['punch_in_note'] = '';
            }

            $work = Employee::find()->select(['work_station'])->where(['emp_number'=>$emp_number])->one();
            $work_station = Subunit::find()->select(['name'])->where(['id'=>$work['work_station']])->one();
            $data[$k]['work_station'] = $work_station['name'];
        }


        foreach ($data as $k => $v){
            if($v['work_start_time'] != ''){
                $data[$k]['work_start_time'] =date('H:i',strtotime($v['work_start_time']));
            }
            if($v['work_middstart_time'] != ''){
                $data[$k]['work_middstart_time'] =date('H:i',strtotime($v['work_middstart_time']));
            }
            if($v['work_middend_time'] != ''){
                $data[$k]['work_middend_time'] =date('H:i',strtotime($v['work_middend_time']));
            }
            if($v['work_end_time'] != ''){
                $data[$k]['work_end_time'] =date('H:i',strtotime($v['work_end_time']));
            }
            if($v['punch_in_actual_time'] != ''){
                $data[$k]['punch_in_actual_time'] =date('m-d H:i',strtotime($v['punch_in_actual_time']));
            }
            if($v['punch_out_actual_time'] != ''){
                $data[$k]['punch_out_actual_time'] =date('m-d H:i',strtotime($v['punch_out_actual_time']));
            }
            if($v['end_time_afternoon'] != ''){
                $data[$k]['end_time_afternoon'] =date('m-d H:i',strtotime($v['end_time_afternoon']));
            }
            if($v['start_time_afternoon'] != ''){
                $data[$k]['start_time_afternoon'] =date('m-d H:i',strtotime($v['start_time_afternoon']));
            }
            if($v['forget_forenoon'] != ''){
                $data[$k]['forget_forenoon'] =date('m-d H:i',strtotime($v['forget_forenoon']));
            }
            if($v['forget_afternoon'] != ''){
                $data[$k]['forget_afternoon'] =date('m-d H:i',strtotime($v['forget_afternoon']));
            }

        }

        $arr['pagesize'] = $pagesize;
        $arr['count'] = (int)$count;
        $arr['query'] = $data;
        return $arr;
    }




    public function employeetimecardlist($data,$work_station){

        $emp_name = isset($data['emp_name'])?$data['emp_name']:'';
        $page = isset($data['page']) ? $data['page'] :'1';
        $start_time = isset($data['start_time']) ? $data['start_time'] :'';
        if($start_time != ''){
            $start_time=strtotime($start_time);
            $start_time=date('Y-m-d',$start_time);
        }else{
            $start_time = date('Y-m-d',time());
        }
        $end_time = isset($data['end_time']) ? $data['end_time'] :'';
        if($end_time != ''){
            $end_time=strtotime($end_time);
            $end_time=date('Y-m-d',$end_time);
        }else{
            $end_time = date('Y-m-d',time());
        }

        if($emp_name != ''){
            $emp = Employee::find()->select(['emp_number'])->where(['emp_firstname'=>$emp_name])->one();
            $emp_number = $emp['emp_number'];
        }else{
            $employee_number = Employee::find()->select(['emp_number'])->asArray()->where(['work_station'=>$work_station])->all();
            $arr = array();
            foreach ($employee_number as $k => $v){
                $arr[] = $v['emp_number'];
            }

            $emp_number = join(',', $arr);
        }
        $start_time = $start_time.' '.'00:00:00';
        $end_time = $end_time.' '.'23:59:59';
        $where = 'is_in_status = 0';
        if($start_time != ''){
            $where .= " and first_daka_time >= '$start_time'";
        }
        if($end_time != ''){
            $where .= " and first_daka_time <= '$end_time'";
        }
        if($emp_number != ''){
            $where .= " and employee_id in ($emp_number)";
        }

        $pagesize = 20;
        $startrow = ($page-1)*$pagesize;

        $query = AttendanceRecord::find()->asArray()->offset($startrow)->limit($pagesize)->where($where);
        $count = $query->count();
        $info = $query->all();
        foreach ($info as $k => $v){
            if($v['daka_status'] == 0){
                $info[$k]['daka_status_name'] = '正常';
            }elseif ($v['daka_status'] == 1){
                $info[$k]['daka_status_name'] = '迟到';
            }elseif ($v['daka_status'] == 2){
                $info[$k]['daka_status_name'] = '早退';
            }else{
                $info[$k]['daka_status_name'] = '其他';
            }
            $time = date('Y-m-d',strtotime($v['first_daka_time']));

            $where = "employee_id = '$v[employee_id]' and first_daka_time like '$time%' and is_in_status > 0";
            $arr = AttendanceRecord::find()->where($where)->one();
            if($arr != ''){
                $info[$k]['forget_forenoon'] = $arr['punch_in_actual_time'];
                $info[$k]['forget_afternoon'] = $arr['punch_out_actual_time'];
                $info[$k]['punch_in_note'] = $arr['punch_in_note'];
            }else{
                $info[$k]['forget_forenoon'] ='';
                $info[$k]['forget_afternoon'] = '';
                $info[$k]['punch_in_note'] = '';
            }

            $name = Employee::find()->select(['emp_firstname'])->where(['emp_number'=>$v['employee_id']])->one();
            $info[$k]['emp_name'] = $name['emp_firstname'];

            $work = Employee::find()->select(['work_station'])->where(['emp_number'=>$v['employee_id']])->one();
            $work_station = Subunit::find()->select(['name'])->where(['id'=>$work['work_station']])->one();
            $info[$k]['work_station'] = $work_station['name'];
        }

        foreach ($info as $k => $v){

            if($v['forget_forenoon'] != ''){
                $info[$k]['forget_forenoon'] =date('m-d H:i',strtotime($v['forget_forenoon']));
            }
            if($v['forget_afternoon'] != ''){
                $info[$k]['forget_afternoon'] =date('m-d H:i',strtotime($v['forget_afternoon']));
            }

            //1
            if(($v['work_start_time'] != '00:00:00' && $v['work_start_time'] != null) && ($v['work_middend_time'] != '00:00:00' && $v['work_middend_time'] != null)){
                $info[$k]['time1'] = date('H:i',strtotime($v['work_start_time'])).'-'.date('H:i',strtotime($v['work_middend_time']));
            }else{
                $info[$k]['time1'] = '-';
            }
            //2
            if(($v['work_middstart_time'] != '00:00:00' && $v['work_middstart_time'] != null) && ($v['work_end_time'] != '00:00:00' && $v['work_end_time'] != null)){
                $info[$k]['time2'] = date('H:i',strtotime($v['work_middstart_time'])).'-'.date('H:i',strtotime($v['work_end_time']));
            }else{
                $info[$k]['time2'] = '-';
            }
            //3
            if(($v['work_start_third'] != null && $v['work_start_third'] != '00:00:00') && ($v['work_end_third'] != null && $v['work_end_third'] != '00:00:00')){
                $info[$k]['time3'] = date('H:i',strtotime($v['work_start_third'])).'-'.date('H:i',strtotime($v['work_end_third']));
            }else{
                $info[$k]['time3'] = '-';
            }
            //4
            if($v['punch_in_user_time'] != '' && $v['end_time_afternoon'] != ''){
                $info[$k]['time4'] = date('m-d H:i',strtotime($v['punch_in_user_time'])).'-'.date('m-d H:i',strtotime($v['end_time_afternoon']));
            }else if ($v['punch_in_user_time'] != ''){
                //$info[$k]['time4'] = '-';
                $info[$k]['time4'] = date('m-d H:i',strtotime($v['punch_in_user_time'])).'-';
            }
            //5
            if($v['start_time_afternoon'] != '' && $v['punch_out_user_time'] != ''){
                $info[$k]['time5'] = date('m-d H:i',strtotime($v['start_time_afternoon'])).'-'.date('m-d H:i',strtotime($v['punch_out_user_time']));
            }else if ($v['punch_out_user_time'] != ''){
                $info[$k]['time5'] = '-'.date('m-d H:i',strtotime($v['punch_out_user_time']));
            }else{
                $info[$k]['time5'] = '-';
            }
            //6
            if($v['start_time_third'] != '' && $v['end_time_third'] != ''){
                $info[$k]['time6'] = date('m-d H:i',strtotime($v['start_time_third'])).'-'.date('m-d H:i',strtotime($v['end_time_third']));
            }else{
                $info[$k]['time6'] = '-';
            }

        }
        $date['count'] = (int)$count;
        $date['pagesize'] = $pagesize;
        $date['arr']  = $info;
        return $date;
    }


    public function worklist($data,$first_name){
        $page = isset($data['page'])?$data['page']:1;

        $shift_time = isset($data['shift_time'])?$data['shift_time']:'';
        if($shift_time != ''){
            $shift_time=strtotime($shift_time);
            $shift_time=date('Y-m-d',$shift_time);
        }else{
            $shift_time = date('Y-m-d',time());
        }
        $work_station = isset($data['work_station'])?$data['work_station']:'';
        $emp_name = isset($data['emp_name']) ? $data['emp_name'] :'';
        if($emp_name != ''){
            $emp = Employee::find()->select(['emp_number','work_station'])->where(['emp_firstname'=>$emp_name])->one();
            if($first_name != 'admin'){
                if($emp['work_station'] != $work_station){
                    return 2;
                }
            }
            $emp_number = $emp['emp_number'];
        }else{
            $emp_number = '';
        }



        $where = "shift_date = '$shift_time' and leave_type = '0' and rest_type= '0'";

        if($emp_number != ''){
            $where .= " and a.emp_number = '$emp_number'";
        }
        if($work_station != ''){
            $where .= " and b.work_station = '$work_station'";
        }
        $pagesize = 20;
        $startrow = ($page-1)*$pagesize;
        $query = (new \yii\db\Query())
            ->select(['a.*','b.emp_firstname','b.work_station','c.start_time','c.end_time_afternoon','c.start_time_afternoon','c.end_time','c.name'])
            ->from('orangehrm_mysql.ohrm_work_shift_result a')
            ->leftJoin('orangehrm_mysql.hs_hr_employee b','a.emp_number=b.emp_number')
            ->leftJoin('orangehrm_mysql.ohrm_work_shift_type c','a.shift_type_id=c.id')
            ->offset($startrow)
            ->limit($pagesize)
            ->where($where);
        $arr = $query->all();
        $count = $query->count();
        foreach ($arr as $k=>$v){
            $shiftType = new ShiftType();
            if(($v['frist_type_id'] !='' && $v['second_type_id'] !='')&&($v['frist_type_id'] == $v['second_type_id'])){
                $ban = $shiftType::find()->select(['name'])->where(['id'=>$v['frist_type_id']])->one();
            }
            if(($v['frist_type_id'] !='' && $v['second_type_id'] !='')&&($v['frist_type_id'] != $v['second_type_id'])){
                $ban1 = $shiftType::find()->select(['name'])->where(['id'=>$v['frist_type_id']])->one();
                $ban2 = $shiftType::find()->select(['name'])->where(['id'=>$v['second_type_id']])->one();
                $ban['name'] = $ban1['name'].'/'.$ban2['name'];
            }
            if($v['frist_type_id'] == '' && $v['second_type_id'] != ''){
                $ban = $shiftType::find()->select(['name'])->where(['id'=>$v['second_type_id']])->one();
            }
            if($v['third_type_id'] != ''){
                $ban = $shiftType::find()->select(['name'])->where(['id'=>$v['third_type_id']])->one();

            }
            //return $ban['name'];
            $arr[$k]['name'] = $ban['name'];
            $timewhere = "employee_id = '$v[emp_number]' and first_daka_time like '$shift_time%' and is_in_status = 0";
            $date = AttendanceRecord::find()->select(['punch_in_actual_time','end_time_afternoon','start_time_afternoon','punch_out_actual_time','punch_in_note'])->where($timewhere)->one();
            if(count($date) > 1){
                foreach ($date as $key => $val){
                    $where = "employee_id = '$val[employee_id]' and first_daka_time like '$shift_time%' and is_in_status > 0";
                    $ar = AttendanceRecord::find()->where($where)->one();
                    if($ar != ''){
                        $date[$k]['forget_forenoon'] = $ar['punch_in_actual_time'];
                        $date[$k]['forget_afternoon'] = $ar['punch_out_actual_time'];
                        $date[$k]['punch_in_note'] = $ar['punch_in_note'];
                    }else{
                        $date[$k]['forget_forenoon'] ='';
                        $date[$k]['forget_afternoon'] = '';
                        $date[$k]['punch_in_note'] = '';
                    }
                }
            }
            $arr[$k]['data'] = $date;
        }


        foreach ($arr as $k=>$v){
            if($v['start_time'] != ''){
                $arr[$k]['start_time'] =date('H:i',strtotime($v['start_time']));
            }
            if($v['end_time_afternoon'] != ''){
                $arr[$k]['end_time_afternoon'] =date('H:i',strtotime($v['end_time_afternoon']));
            }
            if($v['start_time_afternoon'] != ''){
                $arr[$k]['start_time_afternoon'] =date('H:i',strtotime($v['start_time_afternoon']));
            }
            if($v['end_time'] != ''){
                $arr[$k]['end_time'] =date('H:i',strtotime($v['end_time']));
            }

            if($v['data'] != null){
                if($v['data']['punch_in_actual_time'] != ''){
                    $arr[$k]['data']['punch_in_actual_time'] =date('m-d H:i',strtotime($v['data']['punch_in_actual_time']));
                }
                if($v['data']['end_time_afternoon'] != ''){
                    $arr[$k]['data']['end_time_afternoon'] =date('m-d H:i',strtotime($v['data']['end_time_afternoon']));
                }
                if($v['data']['start_time_afternoon'] != ''){
                    $arr[$k]['data']['start_time_afternoon'] =date('m-d H:i',strtotime($v['data']['start_time_afternoon']));
                }
                if($v['data']['punch_out_actual_time'] != ''){
                    $arr[$k]['data']['punch_out_actual_time'] =date('m-d H:i',strtotime($v['data']['punch_out_actual_time']));
                }
               /* if($v['data']['forget_forenoon'] != ''){
                    $arr[$k]['data']['forget_forenoon'] =date('m-d H:i',strtotime($v['data']['forget_forenoon']));
                }
                if($v['data']['forget_afternoon'] != ''){
                    $arr[$k]['data']['forget_afternoon'] =date('m-d H:i',strtotime($v['data']['forget_afternoon']));
                }*/
            }
        }




        $info['count'] = (int)$count;
        $info['pagesize'] = $pagesize;
        $info['data'] = $arr;
        return $info;
    }






    public function getOvertimeListByAPI($arr) {


        $empNumber = !empty($arr['empNumber'])?$arr['empNumber']:null;
        $queryType = isset($arr['queryType'])?$arr['queryType']:null;
        $is_in_status = !empty($arr['is_in_status'])?$arr['is_in_status']:0;
        $id = !empty($arr['id'])?$arr['id']:null;

        $q = Overtime::find();
        $q->joinWith('employee');

        if( !empty($empNumber)){
            if(is_array($empNumber)){
                $q->andWhere(['in',"ohrm_overtime_list.emp_number", $empNumber]);
            } else {
                $q->andWhere(" ohrm_overtime_list.emp_number = :empNumber",[':empNumber'=>$empNumber]);
            }            
        } 

        if(!is_null($queryType)){
            $q->andWhere('ohrm_overtime_list.status = :status',[':status'=>$queryType]);
           
        }else{
            //$q->andWhere('o.status = ?', 0 );
        }

        if($id){
            if (is_numeric($id) && $id > 0) {
                $q->andWhere('ohrm_overtime_list.id = :id', (int) $id);
                $record = $q->One();
            }else if (is_array($id)) {
                $q->andWhere(['in','ohrm_overtime_list.id',$id]);
                $q->orderBy('ohrm_overtime_list.creat_time DESC');
                $record = $q->all();
            }
            
        }else{
            $q->orderBy('ohrm_overtime_list.creat_time DESC');
            $record = $q->all();
        }

        if($record){
            return $record;
        }else{
            return false;
        }
      
    }


    public function worksum($data,$first_name){
        $work_station = isset($data['work_station']) ? $data['work_station'] :'-1';
        $emp_name = isset($data['emp_name']) ? $data['emp_name'] :'';

        if($emp_name != ''){
            $emp = Employee::find()->select(['emp_number','work_station'])->where(['emp_firstname'=>$emp_name])->one();
            $emp_number = $emp['emp_number'];
        }else{
            $emp_number = '';
        }

        $start_time = isset($data['start_time']) ? $data['start_time'] :'';
        if($start_time != ''){
            $start_time=strtotime($start_time);
            $start_time=date('Y-m-d',$start_time);
        }else{
            $start_time =date('Y-m-01', strtotime(date("Y-m-d")));
        }
        $end_time = isset($data['end_time']) ? $data['end_time'] :'';
        if($end_time != ''){
            $end_time=strtotime($end_time);
            $end_time=date('Y-m-d',$end_time);
        }else{
            $end_time = date('Y-m-d',time());
        }

        $where = '1=1';
        if($work_station != '-1'){
            $where .= " and a.work_station = '$work_station'";
        }
        if($emp_number != ''){
            $where .=" and a.emp_number = '$emp_number'";
        }

        $user = (new \yii\db\Query())
            ->select(['a.emp_number','a.emp_firstname','b.user_name'])
            ->from('orangehrm_mysql.hs_hr_employee a')
            ->leftJoin('orangehrm_mysql.ohrm_user b','a.emp_number = b.emp_number')
            ->where($where)
            ->all();
        foreach ($user as $k=>$v){
            $where1 = "employee_id = '$v[emp_number]' and first_daka_time >= '$start_time' and first_daka_time <= '$end_time'";
            $arr = AttendanceRecord::find()->where($where1);
            $card = $arr->andWhere(['is_in_status'=>0])->count();
            $late = $arr->andWhere(['daka_status'=>1])->count();
            $early = $arr->andWhere(['daka_status'=>2])->count();

            $where2 = "emp_number = '$v[emp_number]' and shift_date >= '$start_time' and shift_date <= '$end_time' ";
            $arr1 = ShiftResult::find()->where($where2);
            $attendance = $arr1->andWhere(['rest_type'=>0,'leave_type'=>0])->count();
            $where3 = "emp_number = '$v[emp_number]' and date >= '$start_time' and date <= '$end_time' and status > 0";
            $vacation = Leave::find()->select(['length_days'])->where($where3)->all();
            $vacation_sum = 0;
            if($vacation != ''){
                foreach ($vacation as $key => $val){
                    $vacation_sum += $val['length_days'];
                }
            }

            $where4 = "emp_number = '$v[emp_number]' and current_day >= '$start_time' and current_day <= '$end_time'";
            $voertime = Overtime::find()->select(['hour_differ'])->where($where4)->all();
            $voertime_sum = 0;
            if($voertime != ''){
                foreach ($voertime as $key => $val){
                    $voertime_sum += $val['hour_differ'];
                }
            }

            $info = AttendanceRecord::find()->asArray()->where($where1)->andWhere(['is_in_status'=>0])->all();
            $time_sum = 0;
            if($info != ''){
                foreach ($info as $kk => $vv){
                    $time = strtotime($vv['first_daka_time']);
                    $time = date('Y-m-d',$time);

                    if($vv['punch_in_user_time'] == ''){
                        $time_sum += 0;
                    }else{
                        if($vv['work_middstart_time'] != '00:00:00'){
                            if($vv['end_time_afternoon'] != ''){
                                $time1 = (strtotime($vv['end_time_afternoon'])-strtotime($vv['punch_in_user_time']))/3600;
                                if($time1 <0 ){
                                    $time1 = 0;
                                }
                            }else{
                                $str_time = $time.' '.$vv['work_middend_time'];
                                $time1 = (strtotime($str_time)-strtotime($vv['punch_in_user_time']))/3600;
                                if($time1 <0 ){
                                    $time1 = 0;
                                }
                            }

                            if($vv['start_time_afternoon'] != '' && $vv['punch_out_user_time'] != ''){
                                $time2 = (strtotime($vv['punch_out_user_time'])-strtotime($vv['start_time_afternoon']))/3600;
                                if($time2 <0 ){
                                    $time2 = 0;
                                }
                            }else{
                                $time2 = (strtotime($vv['work_end_time'])-strtotime($vv['work_middstart_time']))/3600;
                                if($time2 <0 ){
                                    $time2 = 0;
                                }
                            }

                            if($vv['end_time_third'] != '' && $vv['start_time_third'] != ''){
                                $time3 = (strtotime($vv['end_time_third'])-strtotime($vv['start_time_third']))/3600;
                                if($time3 <0 ){
                                    $time3 = 0;
                                }
                            }else{
                                $time3 = 0;
                            }
                            $time_sum +=$time1+$time2+$time3;

                        }else{
                            if($vv['punch_out_user_time'] == ''){
                                $str_time = $time.''.$vv['work_middend_time'];
                                $time1 = (strtotime($str_time)-strtotime($vv['punch_in_user_time']))/3600;
                                $time_sum += $time1;
                            }else{
                                $time1 = (strtotime($vv['punch_out_user_time'])-strtotime($vv['punch_in_user_time']))/3600;
                                $time_sum += $time1;
                            }
                        }
                    }
                }
            }


            $user[$k]['work_time'] = round($time_sum + $voertime_sum,2);
            $user[$k]['card_sum'] = $card; //打卡
            $user[$k]['card_late'] = $late; //迟到
            $user[$k]['card_early'] = $early; //早退
            $user[$k]['attendance'] = $attendance; //应出勤
            $user[$k]['absence'] = $attendance - $card; //缺勤
            $user[$k]['vacation'] = $vacation_sum; //假期
            $user[$k]['overtime'] = $voertime_sum; //加班

        }

        return $user;

    }

    public function getOvertimeById($id) {

        $q = Overtime::find();
        
        $q->where('id = :id',[':id'=>$id]);
        $record = $q->one();
            

        if($record){
            return $record;
        }else{
            return false;
        }
      
    }

    public function updateOvertimeStatus($id,$status,$power=null,$note = null){
        $data = self::getOvertimeById($id);
        if($data){
            if($data->hour_differ<=4){
                $leaveDay = 0.5;
            }else {
                $leaveDay = 1;
            }
            $empNumber = $data->emp_number;
            if($data->is_holiday==1){  //转休假
                $LeaveEntitlement = new LeaveEntitlement();
                $holiday = $LeaveEntitlement->getEmpLeaveEntitlementByType($empNumber,1);
                if($status==2){  //同意                       
                   if(!empty($holiday)){
                       $holiday->no_of_days = $holiday->no_of_days+$leaveDay;
                       $da = ceil($leaveDay);
                       $holiday->to_date = date('Y-m-d',(strtotime($holiday->to_date)+3600*24*$da)) ;
                       $holiday->save();
                       $no_of_days = $holiday->no_of_days;
                       $enId = $holiday->id;
                   }else{   
                        $LeaveEntitlement->emp_number = $empNumber;
                        $LeaveEntitlement->no_of_days = $leaveDay;
                        $LeaveEntitlement->leave_type_id = 1;
                        $LeaveEntitlement->from_date ='1900-01-01';
                        $LeaveEntitlement->to_date = '2100-01-01';
                        $LeaveEntitlement->note = '加班转调休';
                        $LeaveEntitlement->credited_date = date('Y-m-d H:i:s');
                        $LeaveEntitlement->entitlement_type = 1;
                        $LeaveEntitlement->save();
                        $enId = $LeaveEntitlement->id;
                        $no_of_days = $no_of_days;
                    }
                    

                    $entitlementLog =new LeaveEntitlementLog();
                    $entitlementLog->emp_number = $empNumber;
                    $entitlementLog->entitlement_type = 1;
                    $entitlementLog->entitlement_id = $enId;
                    $entitlementLog->create_time = date('Y-m-d H:i:s');
                    $entitlementLog->status = 1;
                    $entitlementLog->days = $leaveDay;
                    $entitlementLog->no_of_days = $no_of_days;
                    $entitlementLog->note = '加班转调休';
                    $entitlementLog->emp_number = $empNumber;
                        $User = new User();
                        $user = $User->getSystemUsersByEmpNumber($empNumber);
                    $entitlementLog->create_by_name = $user->employee->emp_firstname;
                    $entitlementLog->create_by_id = $user->id;
                    $entitlementLog->save();
                }else{  //取消
                    if($data->status==2||$data->status==3){
                        if(!empty($holiday)){
                            $holiday->no_of_days = $holiday->no_of_days-$leaveDay;
                            $holiday->save();
                            $entitlementLog =new LeaveEntitlementLog();
                            $entitlementLog->emp_number = $empNumber;
                            $entitlementLog->entitlement_type = 1;
                            $entitlementLog->entitlement_id = $holiday->id;
                            $entitlementLog->create_time = date('Y-m-d H:i:s');
                            $entitlementLog->status = 2;
                            $entitlementLog->days = -abs($leaveDay);
                            $entitlementLog->no_of_days = $holiday->no_of_days;
                            $entitlementLog->note = '取消加班,加班转调休的假自动减';
                            $entitlementLog->emp_number = $empNumber;
                                $User = new User();
                                $user = $User->getSystemUsersByEmpNumber($empNumber);
                            $entitlementLog->create_by_name = $user->employee->emp_firstname;
                            $entitlementLog->create_by_id = $user->id;
                            $entitlementLog->save();
                        }
                    }

                }

            }
            $data->status = $status;
            $data->is_pro = $status;
            if($note){
                $data->content = $note;
            }
            $data->save();
            $ApproverTab = new ApproverTab();
            $ApproverTab->updateStatusById($id,2,$status,$power);


            return true;
        }else{
            return  false;
        }
    }

    /**
     * 根据时间获取加班信息
     * @param  [type] $empNumber [description]
     * @param  [type] $date      日期
     * @param  [type  $status    状态  
     * @return [type] $statTime  开始时间
     */
    public function getOvertimeByDate($empNumber,$date,$status,$statTime = null){
        $q = Overtime::find();
        
        $q->where('emp_number = :empNumber',[':empNumber'=>$empNumber]);
        $q->andWhere('current_day = :date',[':date'=>$date]);
        if($statTime){
            $q->andWhere('stat_time >= :statTime',[':statTime'=>$statTime]);
        }
        if(is_array($status)){
            $q->andWhere(['in','status',$status]);
        }else{
            $q->andWhere('status = :status',[':status'=>$status]);
        }
        $record = $q->one();
        return $record;
    }


    public function overtimesum($data,$customer_id,$workStation){
        $start_time = isset($data['start_time']) ? $data['start_time'] :'';
        if($start_time != ''){
            $start_time=strtotime($start_time);
            $start_time=date('Y-m-d',$start_time);
        }else{
            $start_time =date('Y-m-01', strtotime(date("Y-m-d")));
        }
        $end_time = isset($data['end_time']) ? $data['end_time'] :'';
        if($end_time != ''){
            $end_time=strtotime($end_time);
            $end_time=date('Y-m-d',$end_time);
        }else{
            $end_time = date('Y-m-d',time());
        }

        if($workStation == 0){
            $sub_where = " id != 1 and customer_id = '$customer_id'";
            $subunit = Subunit::find()->select(['id','name'])->where($sub_where)->all();
            foreach ($subunit as $kk => $vv){
                $sub = Subunit::find()->where(['unit_id'=>$vv['id'],'customer_id'=>$customer_id])->one();
                if($sub == ''){
                    $data_sub[] = $vv;
                }
            }
        }else{
            $data_sub[] = Subunit::find()->select(['id','name'])->where(['id'=>$workStation])->one();
        }


        foreach ($data_sub as $key =>$val){
            $employee = Employee::find()->select(['emp_number'])->where(['work_station'=>$val['id']])->all();
            $emp_number = array_column($employee,'emp_number');
            $emp_number = implode(",", $emp_number);
            if($emp_number != ''){
                $where1 = "employee_id in ($emp_number) and first_daka_time >= '$start_time' and first_daka_time <= '$end_time'";
                $arr = AttendanceRecord::find()->where($where1);
                $card = $arr->andWhere(['is_in_status'=>0])->count();
                $late = $arr->andWhere(['daka_status'=>1])->count();
                $early = $arr->andWhere(['daka_status'=>2])->count();



                $where2 = "emp_number in ($emp_number) and shift_date >= '$start_time' and shift_date <= '$end_time' ";
                $arr1 = ShiftResult::find()->where($where2);
                $attendance = $arr1->andWhere(['rest_type'=>0,'leave_type'=>0])->count();


                $where3 = "emp_number in ($emp_number) and date >= '$start_time' and date <= '$end_time' and status > 0";
                $vacation = Leave::find()->select(['length_days'])->where($where3)->all();
                $vacation_sum = 0;
                if($vacation != ''){
                    foreach ($vacation as $k => $v){
                        $vacation_sum += $v['length_days'];
                    }
                }


                $where4 = "emp_number in ($emp_number) and current_day >= '$start_time' and current_day <= '$end_time'";
                $voertime = Overtime::find()->select(['hour_differ'])->where($where4)->all();
                $voertime_sum = 0;
                if($voertime != ''){
                    foreach ($voertime as $k => $v){
                        $voertime_sum += $v['hour_differ'];
                    }
                }


                $info = AttendanceRecord::find()->asArray()->where($where1)->andWhere(['is_in_status'=>0])->all();
                $time_sum = 0;
                if($info != ''){
                    foreach ($info as $kk => $vv){
                        $time = strtotime($vv['first_daka_time']);
                        $time = date('Y-m-d',$time);

                        if($vv['punch_in_user_time'] == ''){
                            $time_sum += 0;
                        }else{
                            if($vv['work_middstart_time'] != '00:00:00'){
                                if($vv['end_time_afternoon'] != ''){
                                    $time1 = (strtotime($vv['end_time_afternoon'])-strtotime($vv['punch_in_user_time']))/3600;
                                    if($time1 <0 ){
                                        $time1 = 0;
                                    }
                                }else{
                                    $str_time = $time.' '.$vv['work_middend_time'];
                                    $time1 = (strtotime($str_time)-strtotime($vv['punch_in_user_time']))/3600;
                                    if($time1 <0 ){
                                        $time1 = 0;
                                    }
                                }

                                if($vv['start_time_afternoon'] != '' && $vv['punch_out_user_time'] != ''){
                                    $time2 = (strtotime($vv['punch_out_user_time'])-strtotime($vv['start_time_afternoon']))/3600;
                                    if($time2 <0 ){
                                        $time2 = 0;
                                    }
                                }else{
                                    $time2 = (strtotime($vv['work_end_time'])-strtotime($vv['work_middstart_time']))/3600;
                                    if($time2 <0 ){
                                        $time2 = 0;
                                    }
                                }

                                if($vv['end_time_third'] != '' && $vv['start_time_third'] != ''){
                                    $time3 = (strtotime($vv['end_time_third'])-strtotime($vv['start_time_third']))/3600;
                                    if($time3 <0 ){
                                        $time3 = 0;
                                    }
                                }else{
                                    $time3 = 0;
                                }
                                $time_sum +=$time1+$time2+$time3;

                            }else{
                                if($vv['punch_out_user_time'] == ''){
                                    $str_time = $time.''.$vv['work_middend_time'];
                                    $time1 = (strtotime($str_time)-strtotime($vv['punch_in_user_time']))/3600;
                                    $time_sum += $time1;
                                }else{
                                    $time1 = (strtotime($vv['punch_out_user_time'])-strtotime($vv['punch_in_user_time']))/3600;
                                    $time_sum += $time1;
                                }
                            }
                        }
                    }
                }
            }
            $absence = $attendance - $card;
            if($absence < 0){
                $absence = 0;
            }
            $user[$key]['sum'] = $key+1;
            $user[$key]['id'] = $val['id'];
            $user[$key]['name'] = $val['name'];
            $user[$key]['work_time'] = round($time_sum + $voertime_sum,2);
            $user[$key]['card_sum'] = $card;
            $user[$key]['card_late'] = $late;
            $user[$key]['card_early'] = $early;
            $user[$key]['attendance'] = $attendance;
            $user[$key]['absence'] = $absence;
            $user[$key]['vacation'] = $vacation_sum;
            $user[$key]['overtime'] = $voertime_sum;
            $user[$key]['start_time'] = $start_time;
            $user[$key]['end_time'] = $end_time;
            $user[$key]['time'] = $start_time.'~'.$end_time;
        }

        return $user;

    }




}
