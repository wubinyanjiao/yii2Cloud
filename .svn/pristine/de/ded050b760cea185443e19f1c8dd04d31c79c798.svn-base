<?php

namespace common\models\emptitle;

use common\models\attachment\Attachment;
use common\models\user\Title;
use Yii;
use \common\models\emptitle\base\EmpTitle as BaseEmpTitle;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "hs_hr_emp_title".
 */
class EmpTitle extends BaseEmpTitle
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

    public function typelist(){
        $arr = Type::find()->asArray()->all();
        $query = $this->GetTree($arr,1);
        return $query;
    }

    public function getTree($arr, $pId)
    {
        $tree = array();
        foreach($arr as $k => $v)
        {
            if($v['fu_id'] == $pId)
            {        //父亲找到儿子
                $v['value']= $v['id'];
                $v['label']= $v['name'];
                unset($v['fu_id']);
                unset($v['level']);
                $v['children'] = $this->getTree($arr, $v['value']);
                $tree[] = $v;
                //unset($data[$k]);
            }
        }
        return $tree;
    }

    public function titleadd($data){
        $class_id = isset($data['title'][0]) ? $data['title'][0] :'';
        $type_id = isset($data['title'][1]) ? $data['title'][1] :'';
        $time = isset($data['time']) ? $data['time'] :'';
        if($time != ''){
            $time=strtotime($time);
            $time=date('Y-m-d',$time);
        }
        $job_title_code = isset($data['job_title_code']) ? $data['job_title_code'] :'';
        $job_title_time = isset($data['job_title_time']) ? $data['job_title_time'] :'';
        if($job_title_time != ''){
            $job_title_time=strtotime($job_title_time);
            $job_title_time=date('Y-m-d',$job_title_time);
        }
        $remarks = isset($data['remarks']) ? $data['remarks'] :'';
        $emp_number = isset($data['emp_number']) ? $data['emp_number'] :'';

        $title = new EmpTitle();
        $title->emp_number = $emp_number;
        $title->time = $time;
        $title->class_id = $class_id;
        $title->type_id = $type_id;
        $title->job_title_code = $job_title_code;
        $title->job_title_time = $job_title_time;
        $title->remarks = $remarks;
        $query = $title->save();
        return $query;
    }


    public function titlelist($data){
        $emp_number = $data['emp_number'];
        $time = isset($data['time']) ? $data['time'] :'';
        if($time != ''){
            $time=strtotime($time);
            $time=date('Y-m-d',$time);
        }
        $class_id = isset($data['class_id']) ? $data['class_id'] :'';
        $job_title_code = isset($data['job_title_code']) ? $data['job_title_code'] :'';
        $job_title_time = isset($data['job_title_time']) ? $data['job_title_time'] :'';
        if($job_title_time != ''){
            $job_title_time=strtotime($job_title_time);
            $job_title_time=date('Y-m-d',$job_title_time);
        }
        $where = "emp_number = '$emp_number'";
        if($time != ''){
            $where .= " and time = '$time'";
        }
        if($class_id != ''){
            $where .= " and class_id = '$class_id'";
        }
        if($job_title_code != ''){
            $where .= " and job_title_code = '$job_title_code'";
        }
        if($job_title_time != ''){
            $where .= " and job_title_time = '$job_title_time'";
        }

        $query['title'] = EmpTitle::find()->asArray()->where($where)->all();
        foreach ($query['title'] as $k => $v){
            $class = Type::find()->select('name')->where(['id'=>$v['class_id']])->one();
            $type = Type::find()->select('name')->where(['id'=>$v['type_id']])->one();
            $arr = Title::find()->select(['job_title'])->asArray()->where(['id'=>$v['job_title_code']])->one();

            $query['title'][$k]['class_id'] = $class['name'];
            $query['title'][$k]['type_id'] = $type['name'];
            $query['title'][$k]['job_title_code'] = $arr['job_title'];

        }
        //$query['atta'] = Attachment::find()->asArray()->where(['emp_number'=>$emp_number,'screen'=>'title'])->all();
        return $query;
    }


    public function titlesel($id){
        $data = EmpTitle::find()->asArray()->where(['id'=>$id])->one();
        $data['title'] = array($data['class_id'],$data['type_id']);
        $atta = Attachment::find()->asArray()->where(['sort_id'=>$id,'screen'=>'title'])->all();
        $query['data'] = $data;
        $query['atta'] = $atta;
        return $query;
    }

    public function uptitle($data){
        $id = $data['id'];
        $class_id = isset($data['title'][0]) ? $data['title'][0] :'';
        $type_id = isset($data['title'][1]) ? $data['title'][1] :'';
        $time = isset($data['time']) ? $data['time'] :'';
        $job_title_code = isset($data['job_title_code']) ? $data['job_title_code'] :'';
        $job_title_time = isset($data['job_title_time']) ? $data['job_title_time'] :'';
        if($job_title_time != ''){
            $job_title_time=strtotime($job_title_time);
            $job_title_time=date('Y-m-d',$job_title_time);
        }
        if($time != ''){
            $time=strtotime($time);
            $time=date('Y-m-d',$time);
        }
        $remarks = isset($data['remarks']) ? $data['remarks'] :'';

        $title = EmpTitle::find()->where(['id'=>$id])->one();
        $title->time = $time;
        $title->class_id = $class_id;
        $title->type_id = $type_id;
        $title->job_title_code = $job_title_code;
        $title->job_title_time = $job_title_time;
        $title->remarks = $remarks;
        $query = $title->save();
        return $query;

    }

    public function titledel($id){
        $emptitle = EmpTitle::deleteAll(['id'=>$id]);
        $attachment = new Attachment();
        $arr = $attachment::find()->where(['sort_id'=>$id,'screen'=>'title'])->all();

        if ($arr){
            foreach ($id as $k=>$v){
                $url = $attachment::find()->select(['eattach_attachment_url'])->where(['sort_id'=>$v,'screen'=>'title'])->one();
                if($url != ''){
                    $delurl = '/data/wwwroot/uploadfile/'.$url['eattach_attachment_url'];
                    unlink($delurl);
                }

            }
            $query = $attachment::deleteAll(['sort_id'=>$id,'screen'=>'title']);
            return $query;
        }
    }


}
