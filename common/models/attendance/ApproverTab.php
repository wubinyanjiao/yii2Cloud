<?php

namespace common\models\attendance;

use Yii;
use \common\models\attendance\base\ApproverTab as BaseApproverTab;
use yii\helpers\ArrayHelper;
use \common\models\employee\Employee;
use \common\models\shift\ShiftChangeApply;
/**
 * This is the model class for table "ohrm_approver_tab".
 */
class ApproverTab extends BaseApproverTab
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
     * @author 吴斌  2018/1/11 修改 
     * 添加审核
     * @param int $data 审核数据
     * @return object | 获取结果数组
     */
    public function addApprover($data){
      if ($this->load($data) && $this->save()){
            return true;
        }else {
             return false;
        }
    }


    /**
     * @author 吴斌  2018/3/6 创建 
     * 根据员工id获取表名
     * @param string $table 表前缀名
     * @param string $userid 用户绑定的员工编号
     * @param string $content   消息内容
     * @return int | 返回新建数据id
     */
    public function getMessageTable($table,$userid){
        $str = crc32($userid);  
        if($str<0){  
            $hash = "0".substr(abs($str), -1);  
        }else{  
            $hash = substr($str,-1);  
        }  
        return $table.$hash;  
    }

    /**
     * 根据申请人ID 获取 审核人
     * 
     */
    public function getApplicantBySubEmployee($sub,$id,$type,$queryType=false){
        $list = self::getApplicantBySubEmployeeByType($sub,$id,$type,$queryType);

        if($queryType!==false){

            return $list;
        }else{
   
            if($list){
                $sup = '';
                $wit = '';
                $gre = '';
                $sub = '';
                $Employee = new Employee();
                foreach($list as $key=>$val){
                    if($key==0){
                        if($val['witness_id']){
                            $emps = explode(',', $val['witness_id']);
                            $witness = $Employee->getEmpByNumNber($emps);
                            if($witness){
                                foreach ($witness as $k => $v) {
                                    $wit .= $v['emp_firstname'].','; 
                                }
                            }
                        }
                    }


                    $chaoName = $val['chao_name'];
                    $sup .=$val['supervisor']['emp_firstname'].',';
                    if(!empty($val['agreeordinate']['emp_firstname'])){
                        $gre = $val['agreeordinate']['emp_firstname'] ;
                    }
                    $sub =$val['subordinate']['emp_firstname'];
                }
            }else{
                return false;
            }

            
            $chao = trim($chaoName,',');
            $sup = trim($sup,',');
            $wit = trim($wit,',');

            return array('sup'=>$sup,'wit'=>$wit,'gre'=>$gre,'chao'=>$chao,'sub'=>$sub);
        }
        
    }

    public function getApplicantBySubEmployeeByType($sub,$id,$type,$queryType=null){

         if(empty($type)){
              return false;
         }

         $q = ApproverTab::find();

                $q->joinWith("supervisor as s");
                $q->joinWith("subordinate as r");
                $q->joinWith("agreeordinate as t");
                $q->orderBy('ohrm_approver_tab.id DESC');
              if(!empty($sub)){

                
                if(is_array($sub)){
                    $q->andWhere(['in','ohrm_approver_tab.sub_employee',$sub]);
                  
                }else{
                   $q->andWhere('ohrm_approver_tab.sub_employee = :sub',[':sub'=>$sub]);
                }
              }
              if($queryType){
                   $q->andWhere('ohrm_approver_tab.status = :queryType',[':queryType'=>$queryType]);
              }else{
                  if($queryType===0){
                     $q->andWhere('ohrm_approver_tab.status = :status',[':status'=>$queryType]);
                  }
              }

            //  $q->where('a.sub_employee = ?',$sub);
              if($type ==1){
                 $q->andWhere('ohrm_approver_tab.leave_id = :id',[':id'=>$id]);
              }else if($type ==2){
                 $q->andWhere('ohrm_approver_tab.overtime_id = :id',[':id'=>$id]);
              }else if($type ==3){
                 $q->andWhere('ohrm_approver_tab.attend_id = :id',[':id'=>$id]);
              }else if($type==4){
                 if($id){
                    $q->andWhere('ohrm_approver_tab.shift_apply_id = :id',[':id'=>$id]);
                 }else{
                    $q->andWhere('ohrm_approver_tab.app_type = :type',[':type'=>$type]);
                 }
                 
              }
              

        $result = $q->all();      
        //var_dump($result);die;
        // $result = $q->execute();
        return $result;
      
    }

    public function getWorkShiftChangeApplyById($id){
         if(empty($id)){
              return false;
         }

         $q = ShiftChangeApply::find();

        $q->where('id = :id',[':id'=>$id]);
        $result = $q->one();      
        // $result = $q->execute();
        return $result;
      
    }

    public function getApplicantById($sub,$id,$type){

         if(empty($type)){
              return false;
         }

         $q = ApproverTab::find();

              if(!empty($sub)){

                
                if(is_array($sub)){
                    $q->andWhere(['in','sub_employee',$sub]);
                  
                }else{
                   $q->andWhere('sub_employee = :sub',[':sub'=>$sub]);
                }
              }
              
            //  $q->where('a.sub_employee = ?',$sub);
              if($type ==1){
                 $q->andWhere('leave_id = :id',[':id'=>$id]);
              }else if($type ==2){
                 $q->andWhere('overtime_id = :id',[':id'=>$id]);
              }else if($type ==3){
                 $q->andWhere('attend_id = :id',[':id'=>$id]);
              }else if($type==4){
                 if($id){
                    $q->andWhere('shift_apply_id = :id',[':id'=>$id]);
                 }else{
                    $q->andWhere('app_type = :type',[':type'=>$type]);
                 }
                 
              }
              

        $result = $q->all();      
        //var_dump($result);die;
        // $result = $q->execute();
        return $result;
      
    }

    public function getAllList(){
        $q = ApproverTab::find()->all();
        return $q;
    }

    public function getAllListCa(){
        $q = ApproverTab::find()->where('id > 4000')->all();
        return $q;
    }

    public function deleteById($id,$type){
        if(empty($type)||empty($id)){
            return false;
        }
        if($type==1){
            $q = ApproverTab::deleteAll(' leave_id= :id ', [':id' => $id] );
        }else if($type==2){
            $q = ApproverTab::deleteAll(' overtime_id= :id ', [':id' => $id] );
        }else if($type==3){
             $q = ApproverTab::deleteAll(' attend_id= :id ', [':id' => $id] ); 
        }else if($type==4){
            $q = ApproverTab::deleteAll('shift_apply_id = :id ', [':id' => $id] );
        }
        
    }

    /**
     * *
     * @param  [type]  $empNumber [description]
     * @param  integer $queryType 状态 1 申请中 2同意 0取消 -1拒绝
     * @param  integer $type      类型 1休假 2加班 3漏打卡 4调班
     * @param  integer $isSub     1 empNumber 为申请人  2empNumber为审批人
     * @return [type]             [description]
     */
    public function getApplicationListByEmp($empNumber,$queryType = 1,$type = 0,$isSub=1,$offset = 0,$limit=0){
        $q = ApproverTab::find();

        // $q->joinWith("supervisor as s");
        // $q->joinWith("subordinate as r");
        // $q->joinWith("agreeordinate as t");

        if($isSub==1){
            $q->andWhere('ohrm_approver_tab.sub_employee = :empNumber',[':empNumber'=>$empNumber]);
        }else{
            $q->andWhere('ohrm_approver_tab.sup_employee = :empNumber',[':empNumber'=>$empNumber]);
        }

        if($queryType!==null){
            $queryType = (int) $queryType;
            $q->andWhere('ohrm_approver_tab.status = :status',[':status'=>$queryType]);
        }
        if($type){
            $type = (int) $type;
            $q->andWhere('ohrm_approver_tab.app_type = :app_type',[':app_type'=>$type]);
        }
        $q->groupBy('ohrm_approver_tab.leave_id,ohrm_approver_tab.shift_apply_id,ohrm_approver_tab.overtime_id,ohrm_approver_tab.attend_id');
        $q->orderBy('ohrm_approver_tab.id DESC');

        $count = $q->count();
   
        if($limit){
            $q->offset($offset);
            $q->limit($limit);
        }

        $list = $q->all();

        return array('data'=>$list,'count'=>$count);


    }

    public function getApplicationListAll($empNumber,$queryType = 1,$type = 0){
        $q = ApproverTab::find();
        if($empNumber){       
            $q->andWhere('ohrm_approver_tab.sup_employee = :empNumber',[':empNumber'=>$empNumber]);
        }

        if($queryType!==null){
            $queryType = (int) $queryType;
            $q->andWhere('ohrm_approver_tab.status = :status',[':status'=>$queryType]);
        }
        if($type){
            $type = (int) $type;
            $q->andWhere('ohrm_approver_tab.app_type = :app_type',[':app_type'=>$type]);
    
        }
        $q->groupBy('leave_id,shift_apply_id,overtime_id,attend_id');
        $list = $q->all();
        //echo $q->createCommand()->getRawSql();die;
//var_dump($list);die;
        return $list;


    }


    public function updateStatusById($id,$jsdType,$status,$agree = null){
        $list = self::getApplicantById(null,$id,$jsdType);

        if($list){
            if(!$agree){
                $agree = $list[0]->sup_employee;
            }
            
            foreach ($list as $key => $value) {
                $value->agree_employee = $agree;
                $value->status = $status;
                $value->save();
            }
        }
        return true;

    }

    public function saveApproverTabRecod($sup,$sub,$id,$witness=false,$type,$status,$chaoName,$chaoId){

        if(empty($sup)||empty($sub)||empty($id)){
            return false;
        }
        if($type==3){
             $this->deleteById($id,3);
        }
        $TabArr = array();
        foreach ($sup as $key => $val) {
           if(empty($val)){
                continue;
           }
           $ApproverTab = new ApproverTab();
           $ApproverTab->sup_employee = $val;
           $ApproverTab->sub_employee = $sub;
           $ApproverTab->app_type = $type;
           $ApproverTab->witness_id = $witness;
           $ApproverTab->chao_name = $chaoName;
           $ApproverTab->chao_id = $chaoId;
           $ApproverTab->status = $status;
           $ApproverTab->create_time = date('Y-m-d H:i:s');
           if($type==1){
                $ApproverTab->leave_id = $id;
           }else if($type==2){
                $ApproverTab->overtime_id = $id;
           }else if($type==3){
                $ApproverTab->attend_id = $id;
           }else if($type==4){
                $ApproverTab->shift_apply_id = $id;
           }   
           $ApproverTab->save();

           $TabArr[] = array('tabId'=>$ApproverTab->id,'supId'=>$val);


        } 
        return $TabArr;

    }

}
