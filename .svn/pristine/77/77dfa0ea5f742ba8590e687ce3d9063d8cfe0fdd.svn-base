<?php

namespace common\models\performance;

use Yii;
use \common\models\performance\base\BonusCalculation as BaseBonusCalculation;
use yii\helpers\ArrayHelper;
use \common\models\performance\BonusCalculationManage;
use \common\models\performance\BonusCalculationList;
use \common\models\performance\BonusCalculationManageList;
use \common\models\subunit\Subunit;
use \common\models\user\User;

/**
 * This is the model class for table "ohrm_bonus_calculation".
 */
class BonusCalculation extends BaseBonusCalculation
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

    public function getBonusCalculationBySub($bonusDate,$workStation){
        $query = self::find();
        $query->where('bonusDate = :bonusDate',[':bonusDate' => $bonusDate]);
        $query->andWhere('groupId = :workStation',[':workStation' => $workStation]);
        
        $list  = $query->one();
        return $list;
    }

    public function getBonusCalculationSearch($search){
        $subunit = !empty($search['subunit'])?$search['subunit']:null;
        $status = !empty($search['status'])?$search['status']:null;
        

        $limit = $search['limit'];   //每页数 20
        $offset = $search['offset'];   //每页数 20


        $query = self::find();
        if($subunit){
            if(is_array($subunit)){
                $query->andWhere(['in','groupId',$subunit]);
            }else{
                $query->andWhere('groupId = :subunit',[':subunit'=>$subunit]);
            }
        }
        if($status){
            if(is_array($status)){
                $query->andWhere(['in','status',$status]);
            }else{
                $query->andWhere('status = :status',[':status'=>$status]);
            }
        }


        $count = $query->count();
        $query->orderBy('bonusDate desc');

        $query->offset($offset);
        $query->limit($limit);
        $list = $query->all();
        // $sql=$query ->createCommand()->getRawSql(); 
        // var_dump($sql);die;   

        return array('list'=>$list,'count'=>$count);
        
    }

    public function getListByDate($bonusDate,$customerId){
        $query = self::find();
        $query->where('bonusDate = :bonusDate',[':bonusDate' => $bonusDate]);
        $query->andWhere('customerId = :customerId',[':customerId' => $customerId]);
        
        $list  = $query->all();
        return $list;
    }

     public function getBonusCalculationById($id){
        $query = self::find();
        $query->where('id = :id',[':id' => $id]);
        
        
        $list  = $query->one();
        return $list;
    }

    public function getBonusCalculationByBonusId($id){
        $query = self::find();
        $query->where('bonus_id = :id',[':id' => $id]);
        
        
        $list  = $query->one();
        return $list;
    }

    
    

    public function getCalContentByDate($id,$groupId,$bonusDate,$statusName){

        $query = BonusCalculationList::find();
        $query->select('count(*) as count,sum(percent20) as percent20,sum(percent80) as percent80');
        $query->where('calculation_id = :id',[':id'=>$id]);
        $query->andWhere('groupId = :groupId',[':groupId'=>$groupId]);
        
        
        $query->andWhere('bonusDate = :bonusDate',[':bonusDate'=>$bonusDate]);
        $list = $query->asArray()->one();

        $Subunit = new Subunit();
        $sub = $Subunit->getWorkStationById($groupId);
        $backArr = array();
        if(empty($list['count'])){
            return $backArr;
        }
        $backArr[]= array('title'=>'奖金日期','val'=>date('Y年m月',strtotime($bonusDate))) ;
        $backArr[]= array('title'=>'组名','val'=>$sub->name) ;
        $backArr[]= array('title'=>'人数','val'=>$list['count']) ;
        $backArr[]= array('title'=>'基本工资','val'=>$list['percent20']) ;
        $backArr[]= array('title'=>'除20%外合计','val'=>$list['percent80']) ;
        $backArr[]= array('title'=>'状态','val'=>$statusName) ;
        return $backArr;
        
    }


    public function xiaoduiCalContentByDate($id,$groupId,$bonusDate){
        $query = BonusCalculationManageList::find();
        $query->select('count(*) as count,sum(percent20) as percent20,sum(percent80) as percent80');

        $query1 = BonusCalculationList::find();
        $query1->select('count(*) as count,sum(percent20) as percent20,sum(percent80) as percent80');
        
            $query1->andWhere('groupId = :groupId',[':groupId'=>$groupId]);
            $query->andWhere('groupId = :groupId',[':groupId'=>$groupId]);
        
        $query->andWhere('bonusDate = :bonusDate',[':bonusDate'=>$bonusDate]);
        $query1->andWhere('bonusDate = :bonusDate',[':bonusDate'=>$bonusDate]);
        $list = $query->asArray()->one();

        $list1 = $query1->asArray()->one();
        $Subunit = new Subunit();
        $sub = $Subunit->getWorkStationById($groupId);

        $header[] = array('title'=>'组名','key'=>'subName','align'=>'center');
        $header[] = array('title'=>'人数(下发)','key'=>'peopleConut','align'=>'center');
        $header[] = array('title'=>'基本工资(下发)','key'=>'percent20','align'=>'center');
        $header[] = array('title'=>'除20%外合计(下发)','key'=>'percent80','align'=>'center');
        
        $header[] = array('title'=>'人数(上报)','key'=>'peopleConutSh','align'=>'center');
        $header[] = array('title'=>'基本工资(上报)','key'=>'percent20Sh','align'=>'center');
        $header[] = array('title'=>'除20%外合计(上报)','key'=>'percent80Sh','align'=>'center');
        $header[] = array('title'=>'校验结果','key'=>'checkout','align'=>'center');


        $jiao = true ;
        if($list['count']!=$list1['count']){
            $jiao = false;
        }
        if($list['percent20']!=$list1['percent20']){
            $jiao = false;
        }
        if($list['percent80']!=$list1['percent80']){
            $jiao = false;
        }

        $backArr[0]['subName'] = $sub->name;
        $backArr[0]['peopleConut'] = $list['count'];
        $backArr[0]['percent20'] = $list['percent20'];
        $backArr[0]['percent80'] = $list['percent80'];
        $backArr[0]['peopleConutSh'] = $list1['count'];
        $backArr[0]['percent20Sh'] = $list1['percent20'];
        $backArr[0]['percent80Sh'] = $list1['percent80'];
        

        $backArr[0]['checkout'] = $jiao;
        

        return array('header'=>$header,'content'=>$backArr,'checkoutStatus'=>$jiao);

    }

    public function updateBonusCalculationById($id,$groupId,$bonusDate,$status){
        $query1 = new BonusCalculationList();
        $recod1 = $query1->updateAll(array('status'=>$status),'bonusDate =:bonusDate AND groupId=:groupId',array(':bonusDate'=>$bonusDate,':groupId'=>$groupId));
    }


    public function getCalContentCountByDate($id,$groupId,$bonusDate){

        $query = BonusCalculationList::find();
        $query->select('count(*) as count,sum(percent20) as percent20,sum(percent80) as percent80');
        $query->where('calculation_id = :id',[':id'=>$id]);
        $query->andWhere('groupId = :groupId',[':groupId'=>$groupId]);
        
        
        $query->andWhere('bonusDate = :bonusDate',[':bonusDate'=>$bonusDate]);
        $list = $query->asArray()->one();

        $Subunit = new Subunit();
        $sub = $Subunit->getWorkStationById($groupId);
        $backArr = array();
        if(empty($list['count'])){
            return $backArr;
        }

        return $list;
        
    }

    public function getCalContentCountManageByDate($id = null,$groupId,$bonusDate){

        $query = BonusCalculationManageList::find();
        $query->select('count(*) as count,sum(percent20) as percent20,sum(percent80) as percent80');
        if($id){
            $query->where('bonus_id = :id',[':id'=>$id]);
        }
        
        $query->andWhere('groupId = :groupId',[':groupId'=>$groupId]);
        
        
        $query->andWhere('bonusDate = :bonusDate',[':bonusDate'=>$bonusDate]);
        $list = $query->asArray()->one();

        $Subunit = new Subunit();
        $sub = $Subunit->getWorkStationById($groupId);
        $backArr = array();
        if(empty($list['count'])){
            return $backArr;
        }

        return $list;
        
    }
}
