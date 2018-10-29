<?php

namespace common\models\subunit;

use Yii;
use \common\models\subunit\base\Subunit as BaseSubunit;
use yii\helpers\ArrayHelper;
use \common\models\employee\Employee;

/**
 * This is the model class for table "ohrm_subunit".
 */
class Subunit extends BaseSubunit
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

    public function getAllWorkStation($customerId = null){
        $query = Subunit::find();
        if($customerId){
            $query->where('customer_id = :customerId',[':customerId' => $customerId]);
        }
        
        $list  = $query->all();
        return $list;
    }


    /*
     *  
     * */
    public function getDepartmentName($id)
    {
        $query = Subunit::find();
        $query->where("id = :id",[':id'=>$id]);
        $query->select('id,name');
        $list = $query->one();
        return $list['name'];
    }

     public function getSubunitByName($name)
    {
        $query = Subunit::find();
        $query->where("name = :name",[':name'=>trim($name)]);
        
        $list = $query->one();
        return $list;
    }
    public function getWorkStationById($id){
        $query = Subunit::find();
        $query->where('id = :id',[':id' => $id]);
        $list  = $query->one();
        return $list;
    }

    public function getSubunitByCustomerId($customerId,$isTop = 0){
        $query = Subunit::find();
        $query->where('customer_id = :customerId',[':customerId' => $customerId]);
        if($isTop){
            $query->andWhere('unit_id = 0');
            $query->andWhere('level = 1');
        }
        $list  = $query->one();
        return $list;
    }

    public function getWorkStationNameById($customerId){
        $query = Subunit::find();
        $query->select('id,name');
        $query->where('customer_id = :customerId',[':customerId' => $customerId]);
        $list  = $query->asArray()->all();
        $backArr = array();
        foreach ($list as $key => $value) {
            $backArr[$value['id']] = $value['name'];
        }
        return $backArr;
    }

}
