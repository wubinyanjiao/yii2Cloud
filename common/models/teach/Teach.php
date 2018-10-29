<?php

namespace common\models\teach;

use common\models\attachment\Attachment;
use Yii;
use \common\models\teach\base\Teach as BaseTeach;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "hs_hr_teach".
 */
class Teach extends BaseTeach
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


    public function teachlist($data){
        $emp_number = $data['emp_number'];
        $school = isset($data['school']) ? $data['school'] :'';
        $major = isset($data['major']) ? $data['major'] :'';
        $record_id = isset($data['record_id']) ? $data['record_id'] :'';
        $degree_id = isset($data['degree_id']) ? $data['degree_id'] :'';
        $school_type = isset($data['school_type']) ? $data['school_type'] :'';
        $start_time = isset($data['start_time']) ? $data['start_time'] :'';
        if($start_time != ''){
            $start_time=strtotime($start_time);
            $start_time=date('Y-m-d',$start_time);
        }
        $end_time = isset($data['end_time']) ? $data['end_time']:'';
        if($end_time != ''){
            $end_time=strtotime($end_time);
            $end_time=date('Y-m-d',$end_time);
        }

        $where = "emp_number = '$emp_number'";
        if($school != '' ){
            $where .= " and school = '$school' ";
        }
        if($major != '' ){
            $where .= " and major = '$major' ";
        }
        if($record_id != '' ){
            $where .= " and record_id = '$record_id' ";
        }
        if($degree_id != '' ){
            $where .= " and degree_id = '$degree_id' ";
        }
        if($school_type != '' ){
            $where .= " and school_type = '$school_type' ";
        }
        if($start_time != '' ){
            $where .= " and start_time = '$start_time' ";
        }
        if($end_time != '' ){
            $where .= " and end_time = '$end_time' ";
        }

        $query = Teach::find()->with('degree','education')->asArray()->where($where)->all();
        foreach ($query as $k =>$v){
            $degree  = Degree::find()->where(['id'=>$v['degree_id']])->one();
            $education = Education::find()->where(['id'=>$v['record_id']])->one();
            $query[$k]['degree'] = $degree['name'];
            $query[$k]['education'] = $education['name'];
            if($v['school_type'] == ''){
                $query[$k]['school_type'] = '';
            }
            if($v['school_type'] == 1){
                $query[$k]['school_type'] = '全日制';
            }
            if($v['school_type'] == 2){
                $query[$k]['school_type'] = '在读';
            }
            if($v['school_type'] == 3){
                $query[$k]['school_type'] = '在职';
            }
        }
        return $query;
    }

    public function teachadd($data){
        $emp_number = $data['emp_number'];
        $school = isset($data['school']) ? $data['school']:'';
        $major = isset($data['major']) ? $data['major']:'';
        $record_id= isset($data['record_id']) ? $data['record_id']:'';
        $degree_id = isset($data['degree_id']) ? $data['degree_id']:'';
        $school_type = isset($data['school_type']) ? $data['school_type']:'';
        $start_time = isset($data['start_time']) ? $data['start_time'] :'';
        if($start_time != ''){
            $start_time=strtotime($start_time);
            $start_time=date('Y-m-d',$start_time);
        }
        $end_time = isset($data['end_time']) ? $data['end_time']:'';
        if($end_time != ''){
            $end_time=strtotime($end_time);
            $end_time=date('Y-m-d',$end_time);
        }
        $remarks = isset($data['remarks']) ? $data['remarks']:'';

        $teach = new Teach();
        $teach->emp_number = $emp_number;
        $teach->school = $school;
        $teach->major = $major;
        $teach->record_id = $record_id;
        $teach->degree_id = $degree_id;
        $teach->school_type = $school_type;
        $teach->start_time = $start_time;
        $teach->end_time = $end_time;
        $teach->remarks = $remarks;
        $query = $teach->save();
        return $query;

    }


    public function teachupdate($data){
        $id = $data['id'];
        $school = isset($data['school']) ? $data['school']:'';
        $major = isset($data['major']) ? $data['major']:'';
        $record_id= isset($data['record_id']) ? $data['record_id']:'';
        $degree_id = isset($data['degree_id']) ? $data['degree_id']:'';
        $school_type = isset($data['school_type']) ? $data['school_type']:'';
        $start_time = isset($data['start_time']) ? $data['start_time'] :'';
        if($start_time != ''){
            $start_time=strtotime($start_time);
            $start_time=date('Y-m-d',$start_time);
        }
        $end_time = isset($data['end_time']) ? $data['end_time']:'';
        if($end_time != ''){
            $end_time=strtotime($end_time);
            $end_time=date('Y-m-d',$end_time);
        }
        $remarks = isset($data['remarks']) ? $data['remarks']:'';

        $teach = Teach::find()->where(['id'=>$id])->one();
        $teach->school = $school;
        $teach->major = $major;
        $teach->record_id = $record_id;
        $teach->degree_id = $degree_id;
        $teach->school_type = $school_type;
        $teach->start_time = $start_time;
        $teach->end_time = $end_time;
        $teach->remarks = $remarks;
        $query = $teach->save();
        return $query;

    }



    public function teachsel($id){
        $teach = Teach::find()->with('degree','education')->asArray()->where(['id'=>$id])->one();
        $attachment = new Attachment();
        $atta = $attachment::find()->asArray()->where(['sort_id'=>$id,'screen'=>'teach'])->all();
        $query['teach'] = $teach;
        $query['atta'] = $atta;
        return $query;
    }

    public function attadel($eattach_id,$emp_number){
        $attachment = new Attachment();
        $url = $attachment::find()->select(['eattach_attachment_url'])->where(['emp_number'=>$emp_number,'screen'=>'teach','eattach_id'=>$eattach_id])->all();
        if($url != ''){
            foreach ($url as $k => $v){
                unlink($v['eattach_attachment_url']);
            }
        }
        $query = $attachment::deleteAll(['emp_number'=>$emp_number,'screen'=>'teach','eattach_id'=>$eattach_id]);
        return $query;
    }

    public function teachdel($id){
        $teach = Teach::deleteAll(['id'=>$id]);
        $attachment = new Attachment();
        $arr = $attachment::find()->where(['sort_id'=>$id,'screen'=>'teach'])->all();
        if ($arr){
            foreach ($id as $k=>$v){
                $url = $attachment::find()->select(['eattach_attachment_url'])->where(['sort_id'=>$v,'screen'=>'teach'])->one();
                if($url != ''){
                    $delurl = '/data/wwwroot/uploadfile/'.$url['eattach_attachment_url'];
                    unlink($delurl);
                }

            }
            $query = $attachment::deleteAll(['sort_id'=>$id,'screen'=>'teach']);
            return $query;
        }
    }


}
