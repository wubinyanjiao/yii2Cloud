<?php

namespace common\models\Journal;

use common\models\attachment\Attachment;
use Yii;
use \common\models\Journal\base\Journal as BaseJournal;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "hs_hr_journal".
 */
class Journal extends BaseJournal
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

    public function journallist($data){
        $emp_number = $data['emp_number'];
        $journal_name = isset($data['journal_name']) ? $data['journal_name']:'';
        $job = isset($data['job']) ? $data['job']:'';
        $start_time = isset($data['start_time']) ? $data['start_time']:'';
        if($start_time != ''){
            $start_time=strtotime($start_time);
            $start_time=date('Y-m-d',$start_time);
        }

        $where = "emp_number = '$emp_number'";
        if($journal_name != ''){
            $where .= " and journal_name = '$journal_name'";
        }
        if($job != ''){
            $where .= " and job = '$job'";
        }
        if($start_time != ''){
            $where .= " and start_time = '$start_time'";
        }

        $journal = Journal::find()->asArray()->where($where)->all();
        return $journal;
    }


    public function journaladd($data){
        $emp_number = $data['emp_number'];
        $journal_name = isset($data['journal_name']) ? $data['journal_name']:'';
        $job = isset($data['job']) ? $data['job']:'';
        $start_time = isset($data['start_time']) ? $data['start_time']:'';
        if($start_time != ''){
            $start_time=strtotime($start_time);
            $start_time=date('Y-m-d',$start_time);
        }
        $remark = isset($data['remark']) ? $data['remark']:'';

        $journal = new Journal();
        $journal->emp_number = $emp_number;
        $journal->journal_name = $journal_name;
        $journal->job = $job;
        $journal->start_time = $start_time;
        $journal->remark = $remark;
        $query = $journal->save();
        return $query;
    }

    public function journalsel($id){
        $query['journal'] = Journal::find()->asArray()->where(['id'=>$id])->one();
        $query['atta'] = Attachment::find()->asArray()->where(['sort_id'=>$id,'screen'=>'journal'])->all();
        return $query;
    }

    public function holdupdate($data){
        $id = $data['id'];
        $journal_name = isset($data['journal_name']) ? $data['journal_name']:'';
        $job = isset($data['job']) ? $data['job']:'';
        $start_time = isset($data['start_time']) ? $data['start_time']:'';
        if($start_time != ''){
            $start_time=strtotime($start_time);
            $start_time=date('Y-m-d',$start_time);
        }
        $remark = isset($data['remark']) ? $data['remark']:'';

        $journal = Journal::find()->where(['id'=>$id])->one();
        $journal->journal_name = $journal_name;
        $journal->job = $job;
        $journal->start_time = $start_time;
        $journal->remark = $remark;
        $query = $journal->save();
        return $query;
    }

    public function journaldel($id){
        $journal = Journal::deleteAll(['id'=>$id]);
        $attachment = new Attachment();
        $arr = $attachment::find()->where(['sort_id'=>$id,'screen'=>'journal'])->all();
        if ($arr){
            foreach ($id as $k=>$v){
                $url = $attachment::find()->select(['eattach_attachment_url'])->where(['sort_id'=>$v,'screen'=>'journal'])->one();
                if($url != ''){
                    $delurl = '/data/wwwroot/uploadfile/'.$url['eattach_attachment_url'];
                    unlink($delurl);
                }

            }
            $query = $attachment::deleteAll(['sort_id'=>$id,'screen'=>'journal']);
            return $journal;
        }
    }


}
