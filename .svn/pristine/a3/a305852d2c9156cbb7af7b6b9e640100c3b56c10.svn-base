<?php

namespace common\models\overtime;

use common\models\leave\LeaveEntitlement;
use common\models\leave\LeaveType;
use common\models\subunit\Subunit;
use common\models\employee\Employee;
use common\models\user\User;
use Yii;
use \common\models\overtime\base\ShiftResult as BaseShiftResult;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "ohrm_work_shift_result".
 */
class ShiftResult extends BaseShiftResult
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

    public function scheduling($time,$customer_id,$workStation){
        if($time != ''){
            $time=strtotime($time);
            $time=date('Y-m-d',$time);
        }else{
            $time = date('Y-m-d',time());
        }


        if($workStation == ''){
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
        foreach ($data_sub as $k => $v){
            $user_arr = array();
            $where1 = "work_station = '$v[id]' and termination_id is NULL";
            $arr = Employee::find()->asArray()->select(['emp_number'])->where($where1);
            $user = $arr->all();
            $num = $arr->count();

            foreach ($user as $key=>$val){
                $user_arr[] = $val['emp_number'];
            }

            $user_str = join(',', $user_arr);

            if($user_str != ''){
                $where = "emp_number in ($user_str) and shift_date = '$time' and shift_type_id > 0";
                $shangban = ShiftResult::find()->asArray()->where($where)->count();

                $other = $num-$shangban;
                $info[$k]['number'] = $k+1;
                $info[$k]['id'] = $v['id'];
                $info[$k]['subunit_name'] = $v['name'];
                $info[$k]['work'] = $shangban;
                $info[$k]['other'] = $other;
                $info[$k]['num'] = $num;
                $info[$k]['time'] = $time;
            }

        }
        $data = array_values($info);
        return $data;
    }


    public function schedulingdetails($time,$id){
        if($time != ''){
            $time=strtotime($time);
            $time=date('Y-m-d',$time);
        }else{
            $time = date('Y-m-d',time());
        }

        $where = "work_station = '$id' and termination_id is NULL";
        $arr = Employee::find()->select(['emp_number'])->where($where)->all();
        foreach ($arr as $key=>$val){
            $where1 = "a.emp_number = '$val[emp_number]'";

            $query[$key] = (new \yii\db\Query())
                ->select(['a.emp_firstname','c.name','b.user_name'])
                ->from('orangehrm_mysql.hs_hr_employee a')
                ->leftJoin('orangehrm_mysql.ohrm_user b','a.emp_number=b.emp_number')
                ->leftJoin('orangehrm_mysql.ohrm_subunit c','a.work_station=c.id')
                ->where($where1)
                ->all();

            $shiftresult = ShiftResult::find()->select(['shift_type_id'])->where(['emp_number'=>$val['emp_number'],'shift_date'=>$time])->one();
            $shitftypedetail = ShiftTypeDetail::find()->asArray()->select(['shift_type_id'])->where(['emp_number'=>$val['emp_number'],'shift_date'=>$time])->all();



           if($shiftresult['shift_type_id'] != 0){
            $type = ShiftType::find()->select(['name'])->where(['id'=>$shiftresult['shift_type_id']])->one();
            $query[$key][0]['work_name'] = $type['name'];
            }else{
                $query[$key][0]['work_name'] = '休假';
            }
            /*if(!empty($shitftypedetail)){
                $type_name = array();
                foreach ($shitftypedetail as $k =>$v){
                    $type = ShiftType::find()->select(['name'])->where(['id'=>$v['shift_type_id']])->one();
                    $type_name[] = $type['name'];
                }
                $t_name = implode(',',$type_name);
                $query[$key][0]['work_name'] = $t_name;
            }else{
                $query[$key][0]['work_name'] = '休假';
            }*/
        }

        $backArr = array();
        foreach ($query as $k=>$v){
            $v[0]['num'] = $k+1;
            $backArr[] = $v[0];
        }
        return $backArr;

    }

    public function vacation($start_time,$end_time,$workStation,$customer_id){

        if($start_time != ''){
            $start_time=strtotime($start_time);
            $start_time=date('Y-m-d',$start_time);
        }else{
            $start_time = date('Y-m-d',time());
        }

        if($end_time != ''){
            $end_time=strtotime($end_time);
            $end_time=date('Y-m-d',$end_time);
        }else{
            $end_time = date('Y-m-d',time());
        }



        if($workStation == ''){
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

        foreach ($data_sub as $key => $val){
            $user_arr = array();
            $where1 = "work_station = '$val[id]' and termination_id is NULL";
            $user = Employee::find()->asArray()->select(['emp_number'])->where($where1)->all();
            foreach ($user as $ke=>$va){
                $user_arr[] = $va['emp_number'];
            }
            $user_str = join(',', $user_arr);
            if($user_str != ''){
                $where2 = "emp_number in ($user_str) and date >= '$start_time' and date <= '$end_time' and leave_type_id in (1,2,3,4)";
                $where3 = "emp_number in ($user_str) and date >= '$start_time' and date <= '$end_time' and leave_type_id = 5 ";
                $where4 = "emp_number in ($user_str) and date >= '$start_time' and date <= '$end_time' and leave_type_id = 6";
                $where5 = "emp_number in ($user_str) and date >= '$start_time' and date <= '$end_time' and leave_type_id = 7";
                $where6 = "emp_number in ($user_str) and date >= '$start_time' and date <= '$end_time' and leave_type_id = 8";
                $where7 = "emp_number in ($user_str) and date >= '$start_time' and date <= '$end_time' and leave_type_id = 9";
                $where8 = "emp_number in ($user_str) and date >= '$start_time' and date <= '$end_time' and leave_type_id = 10";
                $where9 = "emp_number in ($user_str) and date >= '$start_time' and date <= '$end_time' and leave_type_id = 11";
                $where10 = "emp_number in ($user_str) and date >= '$start_time' and date <= '$end_time' and leave_type_id = 12";
                $where11 = "emp_number in ($user_str) and date >= '$start_time' and date <= '$end_time' and leave_type_id = 13";
                $data = Leave::find()->asArray();

                $xiujia = $data->where($where2)->count();
                $hunjia = $data->where($where3)->count();
                $chanjia = $data->where($where4)->count();
                $sangjia = $data->where($where5)->count();
                $tanqin = $data->where($where6)->count();
                $shijia = $data->where($where7)->count();
                $bingjia = $data->where($where8)->count();
                $tuochanxuexi = $data->where($where9)->count();
                $chuchai = $data->where($where10)->count();
                $canjiahoudong = $data->where($where11)->count();


                $arr[$key]['num'] = $key+1;
                $arr[$key]['name'] = $val['name'];
                $arr[$key]['id'] = $val['id'];
                $arr[$key]['xiujia'] = $xiujia;
                $arr[$key]['hunjia'] = $hunjia;
                $arr[$key]['chanjia'] = $chanjia;
                $arr[$key]['sangjia'] = $sangjia;
                $arr[$key]['tanqin'] = $tanqin;
                $arr[$key]['shijia'] = $shijia;
                $arr[$key]['bingjia'] = $bingjia;
                $arr[$key]['tuochanxuexi'] = $tuochanxuexi;
                $arr[$key]['chuchai'] = $chuchai;
                $arr[$key]['canjiahoudong'] = $canjiahoudong;
                $arr[$key]['start_time'] = $start_time;
                $arr[$key]['end_time'] = $end_time;
                $count = $xiujia+$hunjia+$chanjia+$sangjia+$tanqin+$shijia+$bingjia+$tuochanxuexi+$chuchai+$canjiahoudong;
                $arr[$key]['count'] = $count;

            }
        }
        if($workStation == ''){
            $arr_new =  array_values($arr);
            $info['is_admin'] = '0';
        }else{
            $arr_new =  $arr;
            $info['is_admin'] = '1';
        }
        $leavetype = $this->leavetype();
        $info['title'] = $leavetype;
        $info['data'] = $arr_new;
        return $info;
    }


    public function vacationtable($start_time,$end_time,$workStation,$customer_id){
        $data = $this->vacation($start_time,$end_time,$workStation,$customer_id);
        //return $data;
        $title = $data['title'];
        $type = array('name'=>'序号');
        $t_time = array('name'=>'统计时间');
        $subunit_name = array('name'=>'组别');
        $count = array('name'=>'合计');
        array_unshift ($title,$subunit_name);
        array_unshift ($title,$t_time);
        array_unshift ($title,$type);
        array_push($title,$count);

        //return $title;
        foreach ($data['data'] as $k => $v){
            $arr[$k]['id'] = $v['id'];
            $arr[$k]['start_time'] = $v['start_time'];
            $arr[$k]['end_time'] = $v['end_time'];
            unset($v['id']);

            $arr[$k]['data'][]['name'] = $v['num'];
            $arr[$k]['data'][]['name'] = $v['start_time'].' ~ '.$v['end_time'];
            $arr[$k]['data'][]['name'] = $v['name'];
            $arr[$k]['data'][]['name'] = $v['xiujia'];
            $arr[$k]['data'][]['name'] = $v['hunjia'];
            $arr[$k]['data'][]['name'] = $v['chanjia'];
            $arr[$k]['data'][]['name'] = $v['sangjia'];
            $arr[$k]['data'][]['name'] = $v['tanqin'];
            $arr[$k]['data'][]['name'] = $v['shijia'];
            $arr[$k]['data'][]['name'] = $v['bingjia'];
            $arr[$k]['data'][]['name'] = $v['tuochanxuexi'];
            $arr[$k]['data'][]['name'] = $v['chuchai'];
            $arr[$k]['data'][]['name'] = $v['canjiahoudong'];
            $arr[$k]['data'][]['name'] = $v['count'];

            unset($v['start_time']);
            unset($v['end_time']);
        }
        $info['title'] = $title;
        $info['data'] = $arr;
        return $info;
    }


    public function vacationdetails($start_time,$end_time,$id){
        if($start_time != ''){
            $start_time=strtotime($start_time);
            $start_time=date('Y-m-d',$start_time);
        }else{
            $start_time = date('Y-m-d',time());
        }

        if($end_time != ''){
            $end_time=strtotime($end_time);
            $end_time=date('Y-m-d',$end_time);
        }else{
            $end_time = date('Y-m-d',time());
        }

        $where1 = "work_station = '$id' and termination_id is NULL";
        $user = Employee::find()->asArray()->select(['emp_number','emp_firstname'])->where($where1)->all();
        foreach ($user as $k => $v){
            $where2 = "emp_number = '$v[emp_number]' and date >= '$start_time' and date <= '$end_time' and leave_type_id in (1,2,3,4)";
            $where3 = "emp_number = '$v[emp_number]' and date >= '$start_time' and date <= '$end_time' and leave_type_id = 5 ";
            $where4 = "emp_number = '$v[emp_number]' and date >= '$start_time' and date <= '$end_time' and leave_type_id = 6";
            $where5 = "emp_number = '$v[emp_number]' and date >= '$start_time' and date <= '$end_time' and leave_type_id = 7";
            $where6 = "emp_number = '$v[emp_number]' and date >= '$start_time' and date <= '$end_time' and leave_type_id = 8";
            $where7 = "emp_number = '$v[emp_number]' and date >= '$start_time' and date <= '$end_time' and leave_type_id = 9";
            $where8 = "emp_number = '$v[emp_number]'and date >= '$start_time' and date <= '$end_time' and leave_type_id = 10";
            $where9 = "emp_number = '$v[emp_number]'and date >= '$start_time' and date <= '$end_time' and leave_type_id = 11";
            $where10 = "emp_number = '$v[emp_number]' and date >= '$start_time' and date <= '$end_time' and leave_type_id = 12";
            $where11 = "emp_number = '$v[emp_number]' and date >= '$start_time' and date <= '$end_time' and leave_type_id = 13";
            $data = Leave::find()->asArray();
            $user[$k]['num'] = $k+1;
            $user[$k]['xiujia'] = $data->where($where2)->count();
            $user[$k]['hunjia'] = $data->where($where3)->count();
            $user[$k]['chanjia'] = $data->where($where4)->count();
            $user[$k]['sangjia'] = $data->where($where5)->count();
            $user[$k]['tanqin'] = $data->where($where6)->count();
            $user[$k]['shijia'] = $data->where($where7)->count();
            $user[$k]['bingjia'] = $data->where($where8)->count();
            $user[$k]['tuochanxuexi'] = $data->where($where9)->count();
            $user[$k]['chuchai'] = $data->where($where10)->count();
            $user[$k]['canjiahoudong'] = $data->where($where11)->count();
            $user[$k]['start_time'] = $start_time;
            $user[$k]['end_time'] = $end_time;
            $count = $user[$k]['xiujia']+$user[$k]['hunjia']+$user[$k]['chanjia']+$user[$k]['sangjia']+$user[$k]['tanqin']+$user[$k]['shijia']+$user[$k]['bingjia']+$user[$k]['tuochanxuexi']+$user[$k]['chuchai']+$user[$k]['canjiahoudong'];
            $user[$k]['count'] = $count;
        }
        $title = $this->leavetype();
        $type = array('name'=>'序号');
        $t_time = array('name'=>'统计时间');
        $subunit_name = array('name'=>'姓名');
        $count = array('name'=>'合计');
        array_unshift ($title,$subunit_name);
        array_unshift ($title,$t_time);
        array_unshift ($title,$type);
        array_push($title,$count);

        foreach ($user as $k => $v){
            $arr[$k]['data'][]['name'] = $v['num'];
            $arr[$k]['data'][]['name'] = $v['start_time'].' ~ '.$v['end_time'];
            $arr[$k]['data'][]['name'] = $v['emp_firstname'];
            $arr[$k]['data'][]['name'] = $v['xiujia'];
            $arr[$k]['data'][]['name'] = $v['hunjia'];
            $arr[$k]['data'][]['name'] = $v['chanjia'];
            $arr[$k]['data'][]['name'] = $v['sangjia'];
            $arr[$k]['data'][]['name'] = $v['tanqin'];
            $arr[$k]['data'][]['name'] = $v['shijia'];
            $arr[$k]['data'][]['name'] = $v['bingjia'];
            $arr[$k]['data'][]['name'] = $v['tuochanxuexi'];
            $arr[$k]['data'][]['name'] = $v['chuchai'];
            $arr[$k]['data'][]['name'] = $v['canjiahoudong'];
            $arr[$k]['data'][]['name'] = $v['count'];

            unset($v['start_time']);
            unset($v['end_time']);
        }
        $info['title'] = $title;
        $info['data'] = $arr;
        return $info;
    }


    public function leavetype(){
        $leavetype = LeaveType::find()->asArray()->select(['name'])->all();
        $type = array('name'=>'休假');
        array_shift($leavetype);
        array_shift($leavetype);
        array_shift($leavetype);
        array_shift($leavetype);
        array_unshift ($leavetype,$type);
        return $leavetype;
    }
























    /*
     * 查询组织结构
     * **/
    public function subunit($customer_id){
        $subunit = new Subunit();
        $where = "id != 1 and customer_id = '$customer_id'";
        $arr = $subunit::find()->asArray()->select(['id','name','unit_id'])->where($where)->all();
        $query = $this->GetTree($arr,1);
        return $query;
    }

    public function getTree($arr, $pId)
    {
        $tree = array();
        foreach($arr as $k => $v)
        {
            if($v['unit_id'] == $pId)
            {        //父亲找到儿子
                $v['children'] = $this->getTree($arr, $v['id']);
                $tree[] = $v;
            }
        }
        return $tree;
    }
}
