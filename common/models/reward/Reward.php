<?php

namespace common\models\reward;

use common\models\employee\Employee;
use common\models\subunit\Subunit;
use common\models\user\User;
use common\models\workload\WorkContent;
use common\models\workload\WorkLoad;
use function GuzzleHttp\Promise\queue;
use Mpdf\Tag\NewColumn;
use Yii;
use \common\models\reward\base\Reward as BaseReward;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "hs_hr_emp_reward".
 */
class Reward extends BaseReward
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

    public function rewardadd($data){
        $reward = new Reward();
        $name = $data['emp_name'];
       /* $employee = Employee::find()->select(['emp_number'])->where(['emp_firstname'=>$name])->one();
        if(empty($employee)){
            return 2;
        }*/

        $reward->emp_number = $name;
        $reward->is_reward = $data['is_reward'];
        $reward->info = $data['info'];
        $reward->result = $data['result'];
        $reward->time = date('Y-m-d',time());
        return($reward->save());
    }


    public function rewardlist($user_name,$work_station,$page){
        if($user_name == 'admin'){
            $where = '';
        }else{
            $employee = Employee::find()->select('emp_number')->where(['work_station'=>$work_station])->all();
            $arr = array_column($employee,'emp_number');
            $arr = implode(",", $arr);
            $where = "a.emp_number in ($arr)";
        }

        $pagesize = 20;
        $startrow = ($page-1)*$pagesize;
        $query = (new \yii\db\Query())
            ->select(['a.*','b.emp_firstname','c.user_name'])
            ->from('orangehrm_mysql.hs_hr_emp_reward a')
            ->leftJoin('orangehrm_mysql.hs_hr_employee b','a.emp_number=b.emp_number')
            ->leftJoin('orangehrm_mysql.ohrm_user c','a.emp_number=c.emp_number')
            ->offset($startrow)
            ->limit($pagesize)
            ->where($where);
        $info['count'] = (int)$query->count();
        $info['pagesize'] = $pagesize;
        $info['page'] = (int)$page;
        $info['data'] = $query->all();
        return $info;
    }



    public function rewardsel($id){
        $query = (new \yii\db\Query())
            ->select(['a.*','b.emp_firstname'])
            ->from('orangehrm_mysql.hs_hr_emp_reward a')
            ->leftJoin('orangehrm_mysql.hs_hr_employee b','a.emp_number=b.emp_number')
            ->where(['a.id'=>$id])
            ->one();
        return $query;
    }

    public function rewardupdate($data){
        $reward = Reward::find()->where(['id'=>$data['id']])->one();
        //$reward->emp_number = $employee['emp_number'];
        $reward->is_reward = $data['is_reward'];
        $reward->info = $data['info'];
        $reward->result = $data['result'];
        $reward->time = date('Y-m-d',time());
        return($reward->save());
    }





    public function rewardsum($workStation,$customer_id,$start_time,$end_time){
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
            $employee = Employee::find()->select('emp_number')->where(['work_station'=>$val['id']])->all();
            $arr = array_column($employee,'emp_number');

            $where = "time >= '$start_time' and time <= '$end_time'";
            $reward = Reward::find()->where(['in','emp_number',$arr])->andWhere(['is_reward'=>1])->andWhere($where)->count();
            $punishment = Reward::find()->where(['in','emp_number',$arr])->andWhere(['is_reward'=>2])->andWhere($where)->count();

            $info[$key]['num'] = $key+1;
            $info[$key]['start_time'] = $start_time;
            $info[$key]['end_time'] = $end_time;
            $info[$key]['time'] = $start_time.'~'.$end_time;
            $info[$key]['subunit_id'] = $val['id'];
            $info[$key]['subunit_name'] = $val['name'];
            $info[$key]['reward'] = $reward;
            $info[$key]['punishment'] = $punishment;
        }
        return $info;
    }



    public function rewardsumsel($workStation,$start_time,$end_time){
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
        $employee = Employee::find()->select('emp_number')->where(['work_station'=>$workStation])->all();

        $subunit = Subunit::find()->select(['id','name'])->where(['id'=>$workStation])->one();
        foreach ($employee as $key =>$v){
            $user_name = User::find()->select(['user_name'])->where(['emp_number'=>$v['emp_number']])->one();
            $emp_name = Employee::find()->select(['emp_firstname'])->where(['emp_number'=>$v['emp_number']])->one();
            $where = "time >= '$start_time' and time <= '$end_time'";
            $reward = Reward::find()->where(['emp_number'=>$v['emp_number']])->andWhere(['is_reward'=>1])->andWhere($where)->count();
            $punishment = Reward::find()->where(['emp_number'=>$v['emp_number']])->andWhere(['is_reward'=>2])->andWhere($where)->count();

            $info[$key]['num'] = $key+1;
            $info[$key]['subunit_name'] = $subunit['name'];
            $info[$key]['user_name'] = $user_name['user_name'];
            $info[$key]['emp_name'] = $emp_name['emp_firstname'];
            $info[$key]['start_time'] = $start_time;
            $info[$key]['end_time'] = $end_time;
            $info[$key]['time'] = $start_time.'~'.$end_time;
            $info[$key]['reward'] = $reward;
            $info[$key]['punishment'] = $punishment;
        }

        return $info;
    }

    /*********************************************************工作量*******************************************************************************/

    public function workloadsumtu($workStation,$start_time,$end_time,$customer_id){
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
                    $arr[] = $vv;
                }
            }
        }else{
            $arr[] = Subunit::find()->select(['id','name'])->where(['id'=>$workStation])->one();
        }



        $work_content = WorkContent::find()->select(['id','name'])->all();
        foreach ($work_content as $key => $value){
            foreach ($arr as $k =>$v){
                $employee = Employee::find()->select(['emp_number'])->where(['work_station'=>$v])->all();
                $emp_number = array_column($employee,'emp_number');

                $where = "work_date > '$start_time' and work_date < '$end_time'";
                $work_load = WorkLoad::find()->where(['in','employee_id',$emp_number])->andWhere($where)->andWhere(['workcontent_id'=>$value['id']])->sum('workload');
                if(empty($work_load)){
                    $work_load = 0;
                }
                $data[$key]['name'] = $value['name'];
                $data[$key]['load'][] = $work_load;
            }
        }
        $array = array();
        foreach ($data as $kk => $vv){
            $count = array_count_values($vv['load']);
            if($count[0] != 9){
                $array[] = $vv;
            }

        }

        $info['workload'] = $array;
        $info['subunit'] = $arr;
        return $info;

    }


    public function workloadsum($work_station,$start_time,$end_time,$customer_id){
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
        if($work_station == ''){
            $sub_where = " id != 1 and customer_id = '$customer_id'";
            $subunit = Subunit::find()->asArray()->select(['id','name'])->where($sub_where)->all();
            foreach ($subunit as $kk => $vv){
                $sub = Subunit::find()->asArray()->where(['unit_id'=>$vv['id'],'customer_id'=>$customer_id])->one();
                if($sub == ''){
                    $data_sub[] = $vv;
                }
            }
        }else{
            $data_sub[] = Subunit::find()->asArray()->select(['id','name'])->where(['id'=>$work_station])->one();
        }

        foreach ($data_sub as $k=>$v){
            $work_load_arr = array();
            //$subunit = Subunit::find()->select(['name','id'])->where(['id'=>$v])->one();
            $work_content = WorkContent::find()->all();
            $str = '';
            foreach ($work_content as $key=>$value){
                $employee = Employee::find()->select(['emp_number'])->where(['work_station'=>$v])->all();
                $emp_number = array_column($employee,'emp_number');
                $where = "work_date > '$start_time' and work_date < '$end_time'";
                $work_load = WorkLoad::find()->asArray()->where(['in','employee_id',$emp_number])->andWhere($where)->andWhere(['workcontent_id'=>$value['id']])->sum('workload');

                if(!empty($work_load)){
                    $str .= $value['name'].':'.$work_load;
                }else{
                    $str .='';
                }
            }
            $work_load_arr['sum'] = $k+1;
            $work_load_arr['start_time'] = $start_time;
            $work_load_arr['end_time'] = $end_time;
            $work_load_arr['subunit_name'] = $v['name'];
            $work_load_arr['subunit_id'] = $v['id'];
            $work_load_arr['work_load'] = $str;
            $work_load_arr['time'] = $start_time.'~'.$end_time;
            $data[]  =$work_load_arr;
        }
        $data = array_values($data);
        return $data;
    }



    public function workloadsunsel($subunit_id,$start_time,$end_time){
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


        $employee = Employee::find()->select(['emp_number'])->where(['work_station'=>$subunit_id])->all();
        $emp_number = array_column($employee,'emp_number');
        $work_content = WorkContent::find()->where(['work_station'=>$subunit_id])->all();
        foreach ($emp_number as $k => $v){
            $arr = array();
            $user_name = User::find()->select(['user_name'])->where(['emp_number'=>$v])->one();
            $emp_name = Employee::find()->select(['emp_firstname'])->where(['emp_number'=>$v])->one();
            $info[$k]['sum'] = $k+1;
            $info[$k]['time'] = $start_time.'~'.$end_time;
            $info[$k]['user_name'] = $user_name['user_name'];
            $info[$k]['emp_name'] = $emp_name['emp_firstname'];
            $info[$k]['emp_number'] = $v;
            foreach ($work_content as $key => $value){
                $where = "work_date > '$start_time' and work_date < '$end_time'";
                $work_load = WorkLoad::find()->where(['employee_id'=>$v])->andWhere($where)->andWhere(['workcontent_id'=>$value['id']])->sum('workload');
                $arr[$key]['name'] = $value['name'];
                $arr[$key]['work_load'] = $work_load;
            }
            $info[$k]['data'] = $arr;
        }
        return $info;
    }



    public function workloadlist($emp_name,$subunit_id,$start_time,$end_time,$page){
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

        $where = "a.work_date >= '$start_time' and a.work_date <= '$end_time'";
        if($emp_name != ''){
            $where .= " and a.employee_id = '$emp_name'";
        }
        if($subunit_id != '-1'){
            $employee = Employee::find()->select('emp_number')->where(['work_station'=>$subunit_id])->all();
            $arr = array_column($employee,'emp_number');
            $arr = implode(",", $arr);
            $where .= " and a.employee_id in ($arr)";
        }
        $pagesize = 20;
        $startrow = ($page-1)*$pagesize;
        $query = (new \yii\db\Query())
            ->select(['a.*','b.emp_firstname','c.user_name','d.name as work_name','e.name as subunit_name'])
            ->from('orangehrm_mysql.ohrm_work_load a')
            ->leftJoin('orangehrm_mysql.hs_hr_employee b','a.employee_id=b.emp_number')
            ->leftJoin('orangehrm_mysql.ohrm_user c','a.employee_id=c.emp_number')
            ->leftJoin('orangehrm_mysql.ohrm_work_content d','a.workcontent_id=d.id')
            ->leftJoin('orangehrm_mysql.ohrm_subunit e','b.work_station=e.id')
            ->offset($startrow)
            ->limit($pagesize)
            ->where($where);

        $info['count'] = (int)$query->count();
        $info['pagesize'] = $pagesize;
        $info['page'] = (int)$page;
        $info['data'] = $query->all();
        return $info;
    }




}
