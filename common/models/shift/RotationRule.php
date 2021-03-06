<?php

namespace common\models\shift;


use common\models\User;
use Yii;
use \common\models\shift\base\RotationRule as BaseRotationRule;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "ohrm_rotationRule".
 */
class RotationRule extends BaseRotationRule
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
    public function getRuleAll($rotationId,$groupId,$ruleType)
    {
        $data = self::find()
            ->where(['rotationId'=>$rotationId,'groupId'=>$groupId,'ruleType'=>$ruleType])
            ->asArray()
            ->all();
        return $data;
    }
    public function checkRuleOut($ruleAll,$groupId)
    {
        //调出规则
        foreach ($ruleAll as $k=>$v){
            switch ($v['rotationRuleWarehouseId']){
                case 1://职称等级满多长时间轮转
                    $select1 = json_decode($v['select1']);
                    $input1 = json_decode($v['input1']);
                    foreach ($select1 as $key=>$val){
//                        $rule[$val[0]] = $input1[$key];
                        $title_id = (int)$val[0];
                        RotationEmployee::updateAll(['isDel'=>2],['and',['title_id'=>$title_id],['work_station'=>(int)$groupId],['<','month',$input1[$key]]]);
                    }
                    break;
                case 2://组长副组长不参与轮转
//                    RotationEmployee::updateAll(['isDel'=>2],['and',['in','is_leader',[1,2]],['work_station'=>(int)$groupId]]);
                    break;
                case 3://在组内满几年轮转
                    $select1 = json_decode($v['select1']);
                    $work_time = $select1[0][0]*12;
                    RotationEmployee::updateAll(['isDel'=>2],['and',['<','work_time',$work_time],['work_station'=>(int)$groupId]]);
                    break;
                case 4://仅男生女生
//                    $select1 = json_decode($v['select1']);
//                    $emp_gender = $select1[0][0];
//                    RotationEmployee::updateAll(['isDel'=>2],['and',['!=','emp_gender',$emp_gender],['work_station'=>(int)$groupId]]);
                    break;
                case 5://学历范围
                    //将二维数组转换为以为数组
//                    $select2 = json_decode($v['select2']);
//                    $result = array_reduce($select2, 'array_merge', array());
//                    RotationEmployee::updateAll(['isDel'=>2],['and',['not in','record_id',$result],['work_station'=>(int)$groupId]]);
                    break;
                case 6://年龄范围
                    /*$input1 = json_decode($v['input1']);
                    $input1 = $input1[0];
                    $input2 = json_decode($v['input2']);
                    $input2 = $input2[0];
                    if(empty($input1)){
                        RotationEmployee::updateAll(['isDel'=>2],['and',['>','age',$input2],['work_station'=>(int)$groupId]]);
                    }
                    if(empty($input2)){
                        RotationEmployee::updateAll(['isDel'=>2],['and',['<','age',$input1],['work_station'=>(int)$groupId]]);
                    }
                    if(!empty($input2) && !empty($input2)){
                        RotationEmployee::updateAll(['isDel'=>2],['and',['not between','age',$input1,$input2],['work_station'=>(int)$groupId]]);
                    }*/
//
                    break;
            }
        }


    }
    /**
     *先循环 目的组的全部数据 全部调入人员 所有属性,比如职称,学历
     * 内循环 目的组的调入规则循环, 取出每个人的属性判断和本组调入规则对比,存储错误信息
     */
    public function checkRuleIn($SecondGroupRull,$secondGroup)
    {
        //全部调出人员
        $secondOut = json_decode($secondGroup->usersPrepareOut,true);
        //全部调入人员
        $secondIn = json_decode($secondGroup->usersPrepareIn,true);

        if(!empty($secondOut)){
            foreach ($secondOut as $key=>$out){
                //循环取出单个调出人员
                $outemp = RotationEmployee::findOne(['emp_number'=>$out['userId']]);
                //如果调入人员不为空,取出单个调入人员
                if(!empty($secondIn[$key])){
                    $inemp = RotationEmployee::findOne(['emp_number'=>$secondIn[$key]['userId']]);
                }
                //目的组的调入规则
                $message = [];
                foreach ($SecondGroupRull as $k=>$rull)
                {
                    switch ($rull['rotationRuleWarehouseId']){
                        case 7://职称等级平均分配
                            /*$select1 = json_decode($rull['select1']);
                            $result = array_reduce($select1, 'array_merge', array());
                            $outTitle_id = $outemp->title_id;

                            $inTitle_id = !empty($secondIn[$k]) ? $inemp->title_id : '';
                            //调出的人员和本组调入规则比较
                            if(in_array($outTitle_id,$result)){
                                //单个调入人职称和调出人职称比较
                                if(!empty($inTitle_id) && $inTitle_id < $outTitle_id){
                                    $message[] = "职称等级为平均分配";
                                }
                            }*/
                            break;

                        case 8://性别平均分配
                            /*$select1 = json_decode($rull['select1']);
                            $result = array_reduce($select1, 'array_merge', array());
                            $outEmp_gender = $outemp->emp_gender;

                            if(in_array($outEmp_gender,$result)){
                                $inEmp_gender = !empty($secondIn[$k]) ? $inemp->emp_gender : '';
                                if($inEmp_gender != $outEmp_gender){
                                    $message[] = "性别未平均分配";
                                }
                            }*/

                            break;
                        case 9://学历平均分配
                            $select1 = json_decode($rull['select1']);
                            $result = array_reduce($select1, 'array_merge', array());

                            $outRecord_id = $outemp->record_id;
                            $inRecord_id = $inemp->record_id;
                            if(in_array($outRecord_id,$result)){
                                if(!empty($inRecord_id) && $inRecord_id < $outRecord_id){
                                    $message[] = "学历未平均分配";
                                }
                            }
                            break;
                        case 10://年龄平均分配
                            $input1 = json_decode($rull['input1']);
                            $input2 = json_decode($rull['input2']);
                            $result = array_merge($input1,$input2);
                            $outAge = $outemp->age;
                            $inAge = $inemp->age;
                            if(in_array($outAge,$result)){
                                if($inAge <$input1 || $inAge > $input2){
                                    $message[] = "年龄未平均分配";
                                }
                            }
                            break;
                        case 11://夫妻不能在一组
                            //调入的人的夫妻关系
                            $outMutual_exclusion = $outemp->mutual_exclusion;
                            $inMutual_exclusion = $inemp->mutual_exclusion;
                            $name = $inemp->emp_firstname;//姓名
                            //第二组的剩余人串全部数据,如果他对象调出去了就不会显示夫妻在一组
                            $RecommendUnselected = json_decode($secondGroup->usersRecommendUnselected,true);
//                            $PrepareIn = json_decode($secondGroup->usersPrepareIn,true);
                            foreach ($RecommendUnselected as $k1=>$now)
                            {
                                if($inMutual_exclusion == $now['userId']){
                                    $message[] = $name."和".$now['userName'].'是夫妻关系';
                                }
                            }
                            break;
                    }

                }
                if(!empty($message)){
                    $message = implode(',',$message);
                }else{
                    $message = '';
                }

                if(!empty($secondIn[$key]) && $message){


                    foreach ($secondIn as $k2=>$in)
                    {
                        if($inemp->emp_number == $in['userId']){
                            $in['isError'] = true;
                            $in['errorInfo'] = $message;
                            $secondIn[$k2] = $in;
                        }
                    }

                }else{
                    foreach ($secondIn as $k2=>$in)
                    {
                        if($inemp->emp_number == $in['userId']){
                            $in['isError'] = false;
                            $in['errorInfo'] = '';
                            $secondIn[$k2] = $in;
                        }
                    }
                }
                    $secondGroup->usersPrepareIn = json_encode($secondIn,JSON_UNESCAPED_UNICODE);
                    $secondGroup->save();

            }
        }


    }

}
