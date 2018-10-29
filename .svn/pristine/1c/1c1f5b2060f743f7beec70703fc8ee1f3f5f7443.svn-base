<?php

namespace common\models\system;

use Yii;
use \common\models\system\base\SystemUsers as BaseSystemUsers;
use yii\helpers\ArrayHelper;
use yii\data\Pagination;

/**
 * This is the model class for table "ohrm_user".
 */
class SystemUsers extends BaseSystemUsers
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
     * 查询用户列表
     * @param  [type] $searchClues [description]
     * @return [type]              [description]
     */
    public function searchSystemUsers($searchClues){
        $q = SystemUsers::find();
        $q->select('u.id,u.user_role_id,u.emp_number,u.user_name,u.status,e.emp_firstname,r.display_name'); 
        $q->from('ohrm_user u');
        $q->join('LEFT JOIN','hs_hr_employee e', 'e.emp_number = u.emp_number');
        // $q->joinWith('emplyoee e');
        // $q->joinWith('systemUserRole r');
        $q->join('LEFT JOIN','ohrm_user_role r', 'r.id = u.user_role_id'); 

        if(isset($searchClues['userName'])){
             $q->andWhere(['like', 'u.user_name', $searchClues['userName']]);
        }

        if(!empty($searchClues['userRole'])){
            $q->andWhere([ 'u.user_role_id'=> $searchClues['userRole']]);
        }
        if(!empty($searchClues['firstName'])){
            $q->andWhere(['like', 'e.emp_firstname', $searchClues['firstName']]);
        }

        if(isset($searchClues['status'])){
            if(!is_null($searchClues['status'])){
                $status = (int) $searchClues['status'];
                $q->andWhere(['u.status'=> $status]);
            }
        }

        $count = $q->count();

        $data = $q->offset($searchClues['offset'])->limit($searchClues['limit'])->asArray()->all();

        return  array('list'=>$data,'pagination'=>array('count'=>$count));
    }

    /**
     * 查询用户列表
     * @param  [type] $work_station 组ID
     * @return [type] $leader       组长标识  1组长 2副组长 
     * @param  boolean $is_user   true 返回用户ID  false返回员工empNumber 
     * @param  boolean $is_status true 返回正常用户 返回所有员工(包含删除用户) 
     */
    public function searchEmployeeListByWorkStation($work_station,$leader,$is_user=true,$is_status=true)
    {
        $q = Employee::find();
        $q->select('e.emp_number,u.id'); 

        $q->from('hs_hr_employee e');
        $q->joinWith('systemUser u');

        $q->andWhere(['e.work_station'=> $work_station]);
        

        if($is_status){
            $q->andWhere(['u.status'=> 1]);
        }
        if($leader==1){
            $q->andWhere(['not in' ,'e.is_leader', $leader]);
        }else if($leader==2){
            $q->andWhere(['not in' ,'e.is_leader', [1,2]]);
        }
        $list = $q->asArray()->all();

        $list_arr = array();
        if(empty($list)){
            return $list_arr;
        }else{
            foreach($list as $k=>$v){
                if($is_user){
                    array_push($list_arr,$v['id']);
                }else{
                    array_push($list_arr,$v['emp_number']);
                }
                
            }
        }
        return $list_arr;
        
    }

    /**
     * **根据ID 或者empNumber 查询用户信息
     * @param  [type]  $id      用户表ID
     * @param  boolean $is_user $is_user  false查询用户表  true 查询员工表empNumber
     * @return [type]          
     */
    function searchSystemUsersById($id,$is_user=false){
        $q = SystemUsers::find();
        $q->from('ohrm_user u');
        $q->joinWith('employee e');
        if($is_user){
            $q->andWhere(['u.emp_number'=> $id]);
        }else{
            $q->andWhere(['u.id'=> $id]);
        }
        return $q->asArray()->one();
    }
    
    /**
     * 主任获取用户列表
     * @param  boolean $id  用户ID 
     * @param  boolean $is_user   true 返回用户ID  false返回员工empNumber 
     * @param  boolean $is_status true 返回正常用户 返回所有员工(包含删除用户) 
     * @return [type]             [description]
     */
    public function getEmployeeIdListByRole($id,$is_user = true,$is_status=true) {

        $params = Yii::$app->params;
        $employee = self::searchSystemUsersById($id);
        if(empty($employee)){
            return false;
        }

        $role = $employee['user_role_id'];

        $q = SystemUsers::find()->from('ohrm_user');
        if($is_user){
            $q->select('id');
            $strId = 'id';
        }else{
            $q->select('emp_number');
            $strId = 'emp_number';
        }
        if($is_status){
            $q->andWhere(['status'=> 1]);
        }
        $employees = array();
        if($role == $params['DIRECTOR_ID']){  //  主任
            $q->andWhere(['not in', 'user_role_id', $params['SELECT_EMPLOYEE_BY_DIRECTOR']]);
         }else if($role == $params['DEPUTYDIRECTOR']){ //副主任
            $q->andWhere(['not in', 'user_role_id', $params['SELECT_EMPLOYEE_BY_DEPUTYDIRECTOR']]);
         }else if ($role == $params['LEADER_ID']) {    //组长
             //var_dump($employee['employee']['work_station']);
             if(!empty($employee['employee']['work_station'])){
                if($employee['employee']['is_leader']==$params['GROUP_LEADER']){     //组长
                   
                   $employees = self::searchEmployeeListByWorkStation($employee['employee']['work_station'],$params['GROUP_LEADER'],$is_user,$is_status);
                }else if($employee['employee']['is_leader']==$params['DEPUTY_LEADER']){ //副组长
                    
                    $employees = self::searchEmployeeListByWorkStation($employee['employee']['work_station'],$params['DEPUTY_LEADER'],$is_user,$is_status);

                    var_dump($employees);die;
                }else{
                    echo 3;
                    $employees = array();
                }
             }else{
                $employees = array();
             }
             return $employees;
         }else if($role == $params['ADMIN_ID']){    //管理员
            // $employees = $EmployeeService->getEmployeeIdList();
         }else{         //普通员工
            $employees = array();
         }
        $employeeIds = $q->asArray()->all();

        if(empty($employeeIds)){
            return $employees;
        }else{
            foreach($employeeIds as $k=>$v){
                array_push($employees,$v[$strId]);
            }
        }


        return $employees;
 
    }

    /**
     * **根据用户名 查询用户信息
     * @param  [type]  $id      用户表ID  
     * @return [type]          
     */
    public function searchSystemUsersByName($userName){
        $q = SystemUsers::find();
        $q->from('ohrm_user u');
        $q->joinWith('employee e');

        $q->andWhere(['u.user_name'=> $userName]);

        return $q->asArray()->one();
    }

    



}
