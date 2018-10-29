<?php
namespace common\models;

use \common\models\base\Employee as BaseEmployee;
use yii;

class Employee extends BaseEmployee
{
    public static function tableName()
    {
        return '{{hs_hr_employee}}';
    }


    public function selempphone($Token){
        $info = (new yii\db\Query())
            ->select('*')
            ->from('member_token')
            ->where(['token'=>$Token])
            ->one();
        if($info){
            $query = (new yii\db\Query())
                ->select(['a.emp_firstname','a.emp_mobile','b.name'])
                ->from('orangehrm_mysql.hs_hr_employee a')
                ->leftJoin('orangehrm_mysql.ohrm_subunit b','a.work_station=b.id')
                ->orderBy('b.id')
                ->all();
            return $query;
        }else{
            return false;
        }
    }




}
