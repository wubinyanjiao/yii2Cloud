<?php

namespace common\models\employee;

use common\base\PasswordHash;
use common\models\system\UniqueId;
use common\models\user\User;
use common\models\user\UserRole;
use Yii;
use \common\models\employee\base\Employee as BaseEmployee;
use yii\helpers\ArrayHelper;
use yii\data\Pagination;
use common\models\subunit\Subunit;

use common\models\shift\ShiftResult;
use common\models\shift\Schedule;
use common\models\shift\ShiftType;
/**
 * This is the model class for table "hs_hr_employee".
 */
class Agreeordinate extends BaseEmployee
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

    
    public function selempphone(){
            $data = $array = array(
                array('name'=>'门诊药房','emp_firstname'=>'急诊药房','emp_mobile'=>'3604'),
                array('name'=>'门诊药房','emp_firstname'=>'门诊二级库','emp_mobile'=>'4174'),
                array('name'=>'门诊药房','emp_firstname'=>'门诊13号咨询窗口','emp_mobile'=>'3510'),
                array('name'=>'门诊中药房','emp_firstname'=>'调剂区','emp_mobile'=>'4175'),
                array('name'=>'住院药房','emp_firstname'=>'窗口咨询','emp_mobile'=>'3244'),
                array('name'=>'住院药房','emp_firstname'=>'单剂量室','emp_mobile'=>'4178'),
                array('name'=>'住院药房','emp_firstname'=>'组长办公室','emp_mobile'=>'3591'),
                array('name'=>'住院药房','emp_firstname'=>'窗口（护工外用）','emp_mobile'=>'3712'),
                array('name'=>'静配中心','emp_firstname'=>'审方区','emp_mobile'=>'3711'),
                array('name'=>'静配中心','emp_firstname'=>'组长办公室','emp_mobile'=>'4126'),
                array('name'=>'药品组','emp_firstname'=>'片剂和针剂库','emp_mobile'=>'3404'),
                array('name'=>'药品组','emp_firstname'=>'中成药和冷库','emp_mobile'=>'3245'),
                array('name'=>'药品组','emp_firstname'=>'输液库','emp_mobile'=>'3509'),
                array('name'=>'药品组','emp_firstname'=>'采购办公室','emp_mobile'=>'3240'),
                array('name'=>'临床药学','emp_firstname'=>'用药咨询','emp_mobile'=>'3537'),
                array('name'=>'临床药学','emp_firstname'=>'基因检测','emp_mobile'=>'3243'),
                array('name'=>'临床药学','emp_firstname'=>'主任办公室','emp_mobile'=>'3242'),
                array('name'=>'临床药学','emp_firstname'=>'董亚琳主任办公室','emp_mobile'=>'3241'),
                array('name'=>'临床药学','emp_firstname'=>'副主任办公室','emp_mobile'=>'3508'),
            );
            $arr = (new yii\db\Query())
                ->select(['a.emp_firstname','a.emp_mobile','b.name'])
                ->from('orangehrm_mysql.hs_hr_employee a')
                ->leftJoin('orangehrm_mysql.ohrm_subunit b','a.work_station=b.id')
                ->where(['termination_id' =>null])
                ->orderBy('b.id')
                ->all();

        $query=array_merge($data,$arr);

            return $query;
    }



    public function useradd($data,$emp_number){
        $info = User::find()->select('customer_id')->where(['emp_number'=>$emp_number])->one();
        if($info == ''){
            return false;
        }else{
            $pass = new PasswordHash();
            $password = $pass->hash($data['password']);
            $emp_number = Employee::find()->select('emp_number')->orderBy('emp_number desc')->one();
            $number = $emp_number['emp_number']+1;
            $id = '0'.$number;
            $employee  = new Employee();
            $employee->emp_number = $number;
            $employee->employee_id = $id;
            $employee->contract_status = $data['contract_status'];
            if($employee->save()){
                $user = new User();
                $user->user_role_id = 2;
                $user->emp_number = $number;
                $user->user_name = $data['user_name'];
                $user->user_password = $password;
                $user->customer_id = $info['customer_id'];
                $query = $user->save();

                $UniqueId = new UniqueId();

                $uniqId = $UniqueId->getTableIdByName('hs_hr_employee');
                $uid = $uniqId['last_id']+1;
                $uniqId->last_id = $uid;
                $uniqId->save();


                return $number;
            }
        }


    }

    /*
     * 用户管理列表
     * **/
    public function index($emp_firstname,$user_role_id,$user_name,$status,$page){


        $where = 'deleted != 1';
        if($user_name != '' ){
            $where .= " and user_name like '%$user_name%' ";
        }
        if($user_role_id != '' && $user_role_id != '-1'){
            $where .=" and user_role_id = '$user_role_id'";
        }
        if($emp_firstname != ''){
            $where .= " and emp_firstname like '%$emp_firstname%'";
        }
        if($status != '' && $status != '-1'){
            $where .= " and status = '$status'";
        }

        $pagesize = 20;
        $startrow = ($page-1)*$pagesize;

        $query = (new \yii\db\Query())
            ->select(['a.emp_number','b.user_name','c.display_name','a.emp_firstname','b.status','c.id'])
            ->from('orangehrm_mysql.hs_hr_employee a')
            ->leftJoin('orangehrm_mysql.ohrm_user b','a.emp_number = b.emp_number')
            ->leftJoin('orangehrm_mysql.ohrm_user_role c','b.user_role_id = c.id')
            ->orderBy('a.contract_status','b.user_name')
            ->offset($startrow)
            ->limit($pagesize)
            ->where($where)
            ->all();
        $slecount = $this->count($emp_firstname,$user_role_id,$user_name,$status,$page);
        foreach ($query as $k =>$v){
            $query[$k]['num'] = (($page-1)*$pagesize)+$k+1;
        }

        $model['count'] =  (int)$slecount;
        $model['pagesize'] = (int)$pagesize;
        $model['result'] = $query;
        return $model;
    }

    /*
     * 查找要修改的员工
     * **/
    public function sel($emp_number){
        $query = (new \yii\db\Query())
            ->select(['a.emp_number','b.user_name','c.id','a.emp_firstname','b.status'])
            ->from('orangehrm_mysql.hs_hr_employee a')
            ->leftJoin('orangehrm_mysql.ohrm_user b','a.emp_number = b.emp_number')
            ->leftJoin('orangehrm_mysql.ohrm_user_role c','b.user_role_id = c.id')
            ->where(['a.emp_number'=>$emp_number])
            ->one();
        return $query;
    }


    /*
     * 查询组织结构
     * **/
    public function selsub(){
        $subunit = new Subunit();
        $arr = $subunit::find()->asArray()->all();
        $query = $this->GetTree($arr,0);
        return $query;
    }

    /*
    * 递归查找
    * **/
    public function getTree($arr, $pId)
    {
        $tree = array();
        foreach($arr as $k => $v)
        {
            if($v['unit_id'] == $pId)
            {        //父亲找到儿子
                $v['children'] = $this->getTree($arr, $v['id']);
                $tree[] = $v;
                //unset($data[$k]);
            }
        }
        return $tree;
    }

    /*
     * 组织的修改查找
     * **/
    public function selup($id){
        $subunit = new Subunit();
        $query = $subunit::find()->asArray()->where(['id'=>$id])->one();
        //return $arr['leader_id'];
        if($query['leader_id'] != ''){
            $employee = new Employee();
            $emp = $employee::find()->asArray()->select('emp_firstname')->where(['emp_number'=>$query['leader_id']])->one();
            $query['leader'] = $emp['emp_firstname'];

            $dleader = $employee::find()->asArray()->select('emp_number,emp_firstname')->where(['work_station'=>$id,'is_leader'=>2])->all();
            $query['dleader'] = $dleader;
        }

        return $query;
    }

    /*
     * 修改组织结构
     * **/
    public function upsub($data){
        $employee = new Employee();
        $emp_number = isset($data['emp_number']) ? $data['emp_number'] : '';
        $demp_number = isset($data['demp_number']) ? $data['demp_number'] : '';
        $info = Employee::updateAll(['is_leader'=>0],['work_station'=>$data['id']]);


        if($emp_number != ''){
            $name = $employee::find()->select('emp_number')->asArray()->where(['emp_firstname'=>$emp_number])->one();
            if($name == ''){
                return 1;
            }else{
                $info = Employee::updateAll(['is_leader'=>1],['emp_number'=>$name['emp_number']]);
                if(!$info){
                    return 2;
                }
            }
        }

        if($demp_number != ''){
            foreach ($demp_number as $k =>$v){
                $dname = $employee::find()->select('emp_number')->asArray()->where(['emp_firstname'=>$v])->one();
                if($dname == ''){
                    return 3;
                }else{
                    $info = Employee::updateAll(['is_leader'=>2],['emp_number'=>$dname['emp_number']]);
                    if(!$info){
                        return 4;
                    }
                }
            }
        }


        $subunit = new Subunit();
        $subunit = $subunit::find()->where(['id'=>$data['id']])->one();
        $subunit->unit_id = isset($data['unit_id']) ? $data['unit_id'] :1;
        $subunit->name = isset($data['name']) ? $data['name'] : '';
        $subunit->is_link_leave = isset($data['is_holiday']) ? $data['is_holiday'] : 0;
        $subunit->description = isset($data['description']) ? $data['description'] : '';
        $subunit->leader_id = $name['emp_number'];
        $query = $subunit->save();

        if($query){
            return (['result'=>$query,"code"=>'200',"message"=>'修改成功',"isSuccess"=>true]);
        }else{
            return (['result'=>$query,"code"=>'403',"message"=>'修改失败',"isSuccess"=>false]);
        }

    }


    /*
     * 添加组织结构
     * **/
    public function addsub($data){
        $employee = new Employee();
        $emp_number = isset($data['emp_number']) ? $data['emp_number'] : '';
        $demp_number = isset($data['demp_number']) ? $data['demp_number'] : '';
        if($emp_number != ''){
            $name = $employee::find()->select('emp_number')->asArray()->where(['emp_firstname'=>$emp_number])->one();
            if($name == ''){
                return 1;
            }else{
                $info = Employee::updateAll(['is_leader'=>1],['emp_number'=>$name['emp_number']]);
                if(!$info){
                    return 2;
                }
            }
        }

        if($demp_number != ''){
            foreach ($demp_number as $k =>$v){
                $dname = $employee::find()->select('emp_number')->asArray()->where(['emp_firstname'=>$v])->one();
                if($dname == ''){
                    return 3;
                }else{
                    $info = Employee::updateAll(['is_leader'=>2],['emp_number'=>$dname['emp_number']]);
                    if(!$info){
                        return 4;
                    }
                }
            }
        }

        $subunit = new Subunit();
        $subunit->unit_id = isset($data['unit_id']) ? $data['unit_id'] :1;
        $subunit->name = isset($data['name']) ? $data['name'] : '';
        $subunit->is_link_leave = isset($data['is_holiday']) ? $data['is_holiday'] : 0;
        $subunit->description = isset($data['description']) ? $data['description'] : '';
        $subunit->leader_id = $name['emp_number'];
        $query = $subunit->save();
        return $query;
    }



    public function count($emp_firstname,$user_role_id,$user_name,$status){
        $where = '1=1';
        if($user_name != '' ){
            $where .= " and employee_id like '%$user_name%' ";
        }
        if($user_role_id != '' && $user_role_id != '-1'){
            $where .=" and user_role_id = '$user_role_id'";
        }
        if($emp_firstname != ''){
            $where .= " and emp_firstname = '$emp_firstname'";
        }
        if($status != '' && $status != '-1'){
            $where .= " and status = '$status'";
        }

        $count = Employee::find()->count();
        $page = new Pagination(['totalCount' => $count,'pageSize'=>'20']);
        $query = (new \yii\db\Query())
            ->select(['a.emp_number','b.user_name','c.display_name','a.emp_firstname','b.status','c.id'])
            ->from('orangehrm_mysql.hs_hr_employee a')
            ->leftJoin('orangehrm_mysql.ohrm_user b','a.emp_number = b.emp_number')
            ->leftJoin('orangehrm_mysql.ohrm_user_role c','b.user_role_id = c.id')
            ->offset($page->offset)
            ->limit($page->limit)
            ->where($where)
            ->count();
        return $query;
    }


    public function group($id){
        $query = Employee::find()->select(['emp_number','emp_firstname'])->where(['work_station'=>$id])->all();
        return $query;
    }

    public function getEmpByWorkStation($work_station){
        $query = Employee::find()
            ->select(['emp_number','emp_firstname','joined_date','emp_birthday','special_personnel','eeo_cat_code','emp_gender','education_id','mutual_exclusion','work_station','is_leader'])
            ->where(['work_station'=>$work_station])
            ->andWhere(['termination_id' => null])
            ->asArray()
            ->all();

        return $query;
    }

    public function getEmpByNum($emp_num){
        $query = Employee::find()->select(['emp_number','emp_firstname'])->where(['emp_number'=>$emp_num])->asArray()->one();
        return $query;
    }

    public function getEmpByNum2($emp_num){
        $query = Employee::find()->select(['emp_number','emp_firstname','joined_date','emp_birthday','special_personnel','eeo_cat_code','emp_gender','education_id','mutual_exclusion','work_station','is_leader'])->where(['emp_number'=>$emp_num])->asArray()->one();
        return $query;
    }


    public function delsub($id){
        $subunit = new Subunit();
        $query = $subunit::deleteAll(['id'=>$id]);
        $employee = Employee::updateAll(['is_leader'=>0,'work_station'=>''],['work_station'=>$id]);
        return $employee;
    }


    public function role(){
        $role = new UserRole();
        $query = $role::find()->select(['id','display_name'])->where(['is_assignable'=>1])->asArray()->all();
        return $query;
    }

    public function getEmployeeWorkShift($empList,$date,$isopenId = false){
        $query = ShiftResult::find();

        $query->joinWith('user');
        $query->joinWith('schedule');
        $query->joinWith('shiftType');
 
        $query->andWhere('ohrm_work_schedule.is_show = 1');
        $query->andWhere('ohrm_work_schedule.is_confirm = 1');

        if($empList){
            $query->andWhere(['in','ohrm_user.emp_number', $empList]);
        }
        if($isopenId){
            $query->andWhere(['not',['ohrm_user.open_id' => null]]);
        }
        if($date){
            $query->andWhere('ohrm_work_shift_result.shift_date = :date', [':date'=>$date]);
        }
        $query->andWhere('ohrm_work_shift_result.shift_type_id > 0');
        $list = $query->asArray()->all();
        return $list;
    }

    /**
     * 根据条件查询empNumber
     * @param  [type] $search [description]
     * @return [type]         [description]
     */
    public function getSubunitBySearch($search){
        $query = Employee::find();
        //$query->select('emp_number');
        $query->joinWith('subunit');
        $query->joinWith('user');
        
        $customer_id = !empty($search['customer_id'])?$search['customer_id']:0;
        $notSubunit = !empty($search['notSubunit'])?$search['notSubunit']:null;

        $subunit = !empty($search['subunit'])?$search['subunit']:null;
        if($customer_id){
            //$query->andWhere('ohrm_subunit.customer_id = :customer_id',[':customer_id' => $customer_id]);
            $query->andWhere('ohrm_user.customer_id = :customer_id',[':customer_id' => $customer_id]);
        }

        if($notSubunit){
            $query->andWhere(['not in','ohrm_subunit.id', $notSubunit]);
        }

        if($subunit){
            $query->andWhere(['work_station'=>$subunit]);
        }
        $query->andWhere(['termination_id'=>null]);
        
        $list  = $query->asArray()->all();

        return $list;
    }

    public function getEmployeeWorkShiftByDate($empList,$statDate,$denDate){
        $query = ShiftResult::find();
        $query->joinWith('schedule');

 
        $query->andWhere('ohrm_work_schedule.is_show = 1');
        $query->andWhere('ohrm_work_schedule.is_confirm = 1');

        if($empList){
            $query->andWhere(['in','ohrm_work_shift_result.emp_number', $empList]);
        }

        if($statDate){
            $query->andWhere('ohrm_work_shift_result.shift_date >= :statDate', [':statDate'=>$statDate]);
        }
        if($denDate){
            $query->andWhere('ohrm_work_shift_result.shift_date <= :denDate', [':denDate'=>$denDate]);
        }
        //echo $query->createCommand()->getRawSql();die;
        $list = $query->asArray()->all();
        return $list;
    }

    /*
     * 查找要修改的员工
     * **/
    public function getEmpBySubunit($workStation,$customer_id = null){
        $query = (new \yii\db\Query());
            $query->select(['a.emp_number','a.is_leader','a.work_station','b.user_name','a.emp_firstname','b.status','b.customer_id','b.open_id']);
            $query->from('orangehrm_mysql.hs_hr_employee a');
            $query->leftJoin('orangehrm_mysql.ohrm_user b','a.emp_number = b.emp_number');
 
            $query->where(['a.work_station'=>$workStation]);
            if($customer_id){
                $query->andWhere(['b.customer_id'=>$customer_id]);
            }
            $list = $query->all();
        return $list;
    }


    /**
     * @author 吴斌  2018/7/19 修改 
     * 获取部门中所有小组下的员工
     * @param array $work_station_list 该部门所有小组的id
     * @return array | 班次统计
     */

    public function getEmployeeByDocument($work_station_list){ 

        $query=self::find()
        ->select(['emp_number','emp_firstname','work_station'])
        ->where(['in','work_station',$work_station_list])
        ->asArray()
        ->all();
        return $query;

    }

    public function getEmpByNumNber($empNumber){
        $query = Employee::find()->where(['emp_number'=>$empNumber])->one();
        return $query;
    }
    /*
     * 根据小组ID 获取所有员工ID;
     */
    public function getEmpIdBySubunit($workStation){
        $query = Employee::find()->select('emp_number');
        if($workStation){
            $query->where(['work_station'=>$workStation]);;
        }
        $query->andWhere('termination_id is null');
        $list = $query->asArray()->all();
         $empArr = array();
         foreach ($list as $key => $value) {
             array_push($empArr, $value['emp_number']);
         }
        return $empArr;
    }


    public function getEmpNumberByFirstName($firstName,$workStation = null){
        $query = Employee::find()->select('emp_number');
        $query->where('termination_id is null');
        if($workStation){
            $query->andWhere(['work_station'=>$workStation]);;
        }
        if($firstName){
            $query->andWhere(['like','emp_firstname',$firstName]);;
        }
        
        $list = $query->asArray()->all();
         $empArr = array();
         foreach ($list as $key => $value) {
             array_push($empArr, $value['emp_number']);
         }
        return $empArr;
    }
    public function getEmpNumberByFirstNameSubunit($firstName,$workStation = null){
        $query = Employee::find();
        $query->where('termination_id is null');
        if($workStation){
            $query->andWhere(['work_station'=>$workStation]);;
        }
        if($firstName){
            $query->andWhere(['emp_firstname'=>$firstName]);;
        }
        
        $list = $query->asArray()->one();
         
        return $list;
    }

}
