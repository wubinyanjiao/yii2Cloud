<?php

namespace common\models\shift;

use Yii;
use \common\models\shift\base\EmpSkill as BaseEmpSkill;
use yii\helpers\ArrayHelper;
use \common\models\Employee;

/**
 * This is the model class for table "hs_hr_emp_skill".
 */
class EmpSkill extends BaseEmpSkill
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

    public function getEmpSkillList($work_station){
        $query = (new \yii\db\Query())
        ->select(['b.id','a.emp_number','a.emp_firstname','c.name','c.id skill_id'])
        ->from('orangehrm_mysql.hs_hr_employee a')
        ->leftJoin('orangehrm_mysql.hs_hr_emp_skill b','a.emp_number = b.emp_number')
        ->leftJoin('orangehrm_mysql.ohrm_skill c','b.skill_id = c.id')
        ->where(['a.work_station'=>$work_station])
        ->all();
        return $query;
    }

    public function getEmpSkillListPage($work_station,$page=null,$pageSize=null){

        $query = (new \yii\db\Query())
        ->select(['b.id','a.emp_number','a.emp_firstname','c.name','c.id skill_id'])
        ->from('orangehrm_mysql.hs_hr_employee a')
        ->leftJoin('orangehrm_mysql.hs_hr_emp_skill b','a.emp_number = b.emp_number')
        ->leftJoin('orangehrm_mysql.ohrm_skill c','b.skill_id = c.id')
        ->where(['a.work_station'=>$work_station]);
        $count=$query->count();
        $data['totalCount']=(int)$count;
        $data['pageSize']=(int)$pageSize;
        $data['current_page']=(int)$page;
        $startrow = ($page-1)*$pageSize;

        $data['data']=$query->offset($startrow)->limit($pageSize)->all();

        return $data;
    }

    public function getEmpSkillListByStation($work_station){
        $query = self::find()->where('work_station = :work_station', [':work_station' => $work_station])->orderBy('id desc')->asArray()->all();
        return $query;
    }

    public function getEmpSkillListByEmp($emp){
        $query = self::find()->select('skill_id')->where('emp_number = :emp', [':emp' => $emp])->orderBy('id desc')->asArray()->all();
        return $query;
    }

    public function delEmpSkillList($emp_number,$skill_id){
        $empskillmodel = self::find()
        ->where('emp_number = :emp', [':emp' => $emp_number])
        ->andWhere('skill_id = :skill_id', [':skill_id' => $skill_id])
        ->orderBy('id desc')
        ->one();

        if($empskillmodel){
            if($empskillmodel->delete()){
                return true;
            }else{
                return false;
            }
        }else{
            return false;
        }

        

    }

    
}
