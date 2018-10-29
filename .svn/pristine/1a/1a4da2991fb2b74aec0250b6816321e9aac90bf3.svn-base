<?php

namespace common\models\hold;

use common\models\attachment\Attachment;
use Yii;
use \common\models\hold\base\Hold as BaseHold;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "hs_hr_hold".
 */
class Hold extends BaseHold
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

    public function holdlist($data){
        $emp_number = $data['emp_number'];
        $society = isset($data['society']) ? $data['society']:'';
        $job = isset($data['job']) ? $data['job']:'';
        $term = isset($data['term']) ? $data['term']:'';
        $start_time = isset($data['start_time']) ? $data['start_time']:'';


        $where = "emp_number = '$emp_number'";
        if($society != ''){
            $where .= " and society = '$society'";
        }
        if($job != ''){
            $where .= " and job = '$job'";
        }
        if($term != ''){
            $where .= " and term = '$term'";
        }
        if($start_time != ''){
            $where .= " and start_time = '$start_time'";
        }

        $query = Hold::find()->asArray()->where($where)->all();
        return $query;

    }

    public function holdadd($data){
        $emp_number = $data['emp_number'];
        $society = isset($data['society']) ? $data['society']:'';
        $job = isset($data['job']) ? $data['job']:'';
        $term = isset($data['term']) ? $data['term']:'';
        $start_time = isset($data['start_time']) ? $data['start_time']:'';
        $start_time=strtotime($start_time);
        $start_time=date('Y-m-d',$start_time);
        $remark = isset($data['remark']) ? $data['remark']:'';

        $hold = new Hold();
        $hold->emp_number = $emp_number;
        $hold->society = $society;
        $hold->job = $job;
        $hold->term = $term;
        $hold->start_time = $start_time;
        $hold->remark = $remark;
        $query = $hold->save();
        return $query;
    }


    public function holdsel($id){
        $query['hold'] = Hold::find()->asArray()->where(['id'=>$id])->one();
        $query['atta'] = Attachment::find()->asArray()->where(['sort_id'=>$id,'screen'=>'hold'])->all();
        return $query;
    }

    public function holdupdate($data){
        $id = $data['id'];
        $society = isset($data['society']) ? $data['society']:'';
        $job = isset($data['job']) ? $data['job']:'';
        $term = isset($data['term']) ? $data['term']:'';
        $start_time = isset($data['start_time']) ? $data['start_time']:'';
        $start_time=strtotime($start_time);
        $start_time=date('Y-m-d',$start_time);
        $remark = isset($data['remark']) ? $data['remark']:'';

        $hold = Hold::find()->where(['id'=>$id])->one();
        $hold->society = $society;
        $hold->job = $job;
        $hold->term = $term;
        $hold->start_time = $start_time;
        $hold->remark = $remark;
        $query = $hold->save();

        return $query;
    }


    public function holddel($id){
        $hold = Hold::deleteAll(['id'=>$id]);
        $attachment = new Attachment();
        $arr = $attachment::find()->where(['sort_id'=>$id,'screen'=>'hold'])->all();
        if ($arr){
            foreach ($id as $k=>$v){
                $url = $attachment::find()->select(['eattach_attachment_url'])->where(['sort_id'=>$v,'screen'=>'hold'])->one();
                if($url != ''){
                    $delurl = '/data/wwwroot/uploadfile/'.$url['eattach_attachment_url'];
                    unlink($delurl);
                }

            }
            $query = $attachment::deleteAll(['sort_id'=>$id,'screen'=>'hold']);
            return $query;
        }
    }







}
