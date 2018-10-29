<?php

namespace common\models\shift;

use Yii;
use \common\models\shift\base\RotationEmployee as BaseRotationEmployee;
use yii\base\UserException;
use yii\db\Query;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "ohrm_rotationemployee".
 */
class RotationEmployee extends BaseRotationEmployee
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
    public function EmployeeCount($rotationId)
    {
        $data = self::find()
            ->where(['rotationId'=>$rotationId])
            ->asArray()
            ->all();
//            ->count();
        return $data;
    }
    public function getRotationEmployeeAll($groupId,$rotationDate)
    {
            $data = self::find()->where(['work_station'=>$groupId])
                ->andWhere(['rotationDate'=>$rotationDate])
                ->asArray()
                ->all();
        return $data;
    }

    public function saveRotary($data){
        if ( $this->save()){
            return true;
        }else {
             return false;
        }
    }

    /**
     * @param $groupId
     * @param $rotationDate
     * 获取每个组的全部符合条件的人串
     */
    public function getempAll($rotationId,$groupId)
    {
        $data = self::find()
            ->where(['work_station'=>$groupId])
            ->andWhere(['!=','isDel',2])
            ->andWhere(['rotationId'=>$rotationId])
            ->asArray()
            ->orderBy(["count_work_time" => SORT_DESC])
            ->all();
        return $data;
    }

    public function getrotationEmployeeOne($firstgroupid,$firstemp)
    {
        $data = self::find()
            ->where(['work_station'=>$firstgroupid,'emp_number'=>$firstemp])
            ->asArray()
            ->one();
        return $data;
    }
    public function getrotationEmployeeUserId($userId)
    {
        $data = self::find()
            ->where(['emp_number'=>$userId])
            ->asArray()
            ->one();
        return $data;
    }
    public function getEmployeeOne($emp_number,$date)
    {
        $data = self::find()
            ->where(['emp_number'=>$emp_number])
            ->andWhere(['rotationDate'=>$date])
            ->asArray()
            ->one();
        return $data;
    }
    public function createEmployee($rotationDate,$userAll,$rotationId)
    {
         if(is_array($userAll)){
            foreach ($userAll as $key=>$value){
                foreach ($value as $k=>$v){
                    $month = $v['title_time'];
                    $now = date('Y-m-d');
                    $month = getMonthNum($month,$now);//职称取得的月数
                    $age = $v['emp_birthday'];
                    $age = getAge($age);//获取年龄
                    
                    //先暂时这样判断取值,后续要加上时间字段rotationDat
                     if($v['is_rotation'] == 1){//一定要不为空,现在测试数据库基本都为空,先存上

                            $RotationEmployee = new RotationEmployee;
                            $RotationEmployee->rotationId = $rotationId;
                            $RotationEmployee->rotationDate = $rotationDate;
                            $RotationEmployee->work_station = $v['work_station'];
                            $RotationEmployee->emp_firstname = $v['emp_firstname'];
                            $RotationEmployee->emp_number = $v['emp_number'];
                            $RotationEmployee->work_time = $v['work_time'];
                            $RotationEmployee->count_work_time = $v['count_work_time'];
                            $RotationEmployee->is_leader = $v['is_leader'];
                            $RotationEmployee->title_id = $v['title_id'];
                            $RotationEmployee->title_time = $v['title_time'];
                            $RotationEmployee->month = $month;
                            $RotationEmployee->emp_gender = $v['emp_gender'];
                            $RotationEmployee->record_id = $v['record_id'];
                            $RotationEmployee->emp_birthday = $v['emp_birthday'];
                            $RotationEmployee->age = $age;
                            $RotationEmployee->is_rotation = $v['is_rotation'];
                            $RotationEmployee->mutual_exclusion = $v['mutual_exclusion'];
                            $RotationEmployee->save();

                         }
                    }
                   
                }
             }
        }
}
