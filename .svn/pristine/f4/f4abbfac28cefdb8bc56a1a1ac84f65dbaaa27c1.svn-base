<?php

namespace common\models\patent;

use common\models\attachment\Attachment;
use Yii;
use \common\models\patent\base\Patent as BasePatent;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "hs_hr_patent".
 */
class Patent extends BasePatent
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

    public function patentlist($data){
        $emp_number = $data['emp_number'];
        $patent_name = isset($data['patent_name'])?$data['patent_name']:'';
        $patent_class = isset($data['patent_class'])?$data['patent_class']:'';
        $patent_type = isset($data['patent_type'])?$data['patent_type']:'';
        $department = isset($data['department'])?$data['department']:'';
        $applicant = isset($data['applicant'])?$data['applicant']:'';
        $ranking = isset($data['ranking']) ? $data['ranking']:'';
        $patentee = isset($data['patentee'])?$data['patentee']:'';
        $authorization_time = isset($data['authorization_time'])?$data['authorization_time']:'';
        if($authorization_time != ''){
            $authorization_time=strtotime($authorization_time);
            $authorization_time=date('Y-m-d',$authorization_time);
        }
        $accept_time = isset($data['accept_time'])?$data['accept_time']:'';
        if($accept_time != ''){
            $accept_time=strtotime($accept_time);
            $accept_time=date('Y-m-d',$accept_time);
        }
        $patent_number = isset($data['patent_number'])?$data['patent_number']:'';
        $apply_number = isset($data['apply_number'])?$data['apply_number']:'';

        $where ="emp_number = '$emp_number'";
        if($patent_name != ''){
            $where .=" and patent_name = '$patent_name'";
        }
        if($patent_class != ''){
            $where .=" and patent_class = '$patent_class'";
        }
        if($patent_type != ''){
            $where .=" and patent_type = '$patent_type'";
        }
        if($department != ''){
            $where .=" and department = '$department'";
        }
        if($applicant != ''){
            $where .=" and applicant = '$applicant'";
        }
        if($ranking != ''){
            $where .=" and ranking = '$ranking'";
        }
        if($patentee != ''){
            $where .=" and patentee = '$patentee'";
        }
        if($authorization_time != ''){
            $where .=" and authorization_time = '$authorization_time'";
        }
        if($accept_time != ''){
            $where .=" and accept_time = '$accept_time'";
        }
        if($patent_number != ''){
            $where .=" and patent_number = '$patent_number'";
        }
        if($apply_number != ''){
            $where .=" and apply_number = '$apply_number'";
        }

        $patent = Patent::find()->where($where)->all();
        return $patent;
    }

    public function patentadd($data){
        $emp_number = $data['emp_number'];
        $patent_name = isset($data['patent_name'])?$data['patent_name']:'';
        $patent_class = isset($data['patent_class'])?$data['patent_class']:'';
        $patent_type = isset($data['patent_type'])?$data['patent_type']:'';
        $department = isset($data['department'])?$data['department']:'';
        $applicant = isset($data['applicant'])?$data['applicant']:'';
        $ranking = isset($data['ranking']) ? $data['ranking']:'';
        $patentee = isset($data['patentee'])?$data['patentee']:'';


        $authorization_time = isset($data['authorization_time'])?$data['authorization_time']:'';
        if($authorization_time != ''){
            $authorization_time=strtotime($authorization_time);
            $authorization_time=date('Y-m-d',$authorization_time);
        }
        $accept_time = isset($data['accept_time'])?$data['accept_time']:'';
        if($accept_time != ''){
            $accept_time=strtotime($accept_time);
            $accept_time=date('Y-m-d',$accept_time);
        }



        $patent_number = isset($data['patent_number'])?$data['patent_number']:'';
        $apply_number = isset($data['apply_number'])?$data['apply_number']:'';
        $remarks = isset($data['remarks'])?$data['remarks']:'';

        $patent = new Patent();
        $patent -> emp_number = $emp_number;
        $patent -> patent_name = $patent_name;
        $patent -> patent_class = $patent_class;
        $patent -> patent_type = $patent_type;
        $patent -> department = $department;
        $patent -> applicant = $applicant;
        $patent -> ranking = $ranking;
        $patent -> patentee = $patentee;
        $patent -> authorization_time = $authorization_time;
        $patent -> accept_time = $accept_time;
        $patent -> patent_number = $patent_number;
        $patent -> apply_number = $apply_number;
        $patent -> remarks = $remarks;
        $query = $patent -> save();
        return $query;
    }


    public function patentupdate($data){
        $id = $data['id'];
        $patent_name = isset($data['patent_name'])?$data['patent_name']:'';
        $patent_class = isset($data['patent_class'])?$data['patent_class']:'';
        $patent_type = isset($data['patent_type'])?$data['patent_type']:'';
        $department = isset($data['department'])?$data['department']:'';
        $applicant = isset($data['applicant'])?$data['applicant']:'';
        $ranking = isset($data['ranking']) ? $data['ranking']:'';
        $patentee = isset($data['patentee'])?$data['patentee']:'';
        $authorization_time = isset($data['authorization_time'])?$data['authorization_time']:'';
        if($authorization_time != ''){
            $authorization_time=strtotime($authorization_time);
            $authorization_time=date('Y-m-d',$authorization_time);
        }
        $accept_time = isset($data['accept_time'])?$data['accept_time']:'';
        if($accept_time != ''){
            $accept_time=strtotime($accept_time);
            $accept_time=date('Y-m-d',$accept_time);
        }
        $patent_number = isset($data['patent_number'])?$data['patent_number']:'';
        $apply_number = isset($data['apply_number'])?$data['apply_number']:'';
        $remarks = isset($data['remarks'])?$data['remarks']:'';

        $patent = Patent::find()->where(['id'=>$id])->one();
        $patent -> patent_name = $patent_name;
        $patent -> patent_class = $patent_class;
        $patent -> patent_type = $patent_type;
        $patent -> department = $department;
        $patent -> applicant = $applicant;
        $patent -> ranking = $ranking;
        $patent -> patentee = $patentee;
        $patent -> authorization_time = $authorization_time;
        $patent -> accept_time = $accept_time;
        $patent -> patent_number = $patent_number;
        $patent -> apply_number = $apply_number;
        $patent -> remarks = $remarks;
        $query = $patent -> save();
        return $query;
    }

    public function patentsel($id){
        $patent = Patent::find()->asArray()->where(['id'=>$id])->one();
        $atta = Attachment::find()->asArray()->where(['sort_id'=>$id,'screen'=>'patent'])->all();
        $query['patent'] = $patent;
        $query['atta'] = $atta;
        return $query;
    }

    public function patentdel($id){
        $patnet = Patent::deleteAll(['id'=>$id]);
        $attachment = new Attachment();
        $arr = $attachment::find()->where(['sort_id'=>$id,'screen'=>'patent'])->all();
        if ($arr){
            foreach ($id as $k=>$v){
                $url = $attachment::find()->select(['eattach_attachment_url'])->where(['sort_id'=>$v,'screen'=>'patent'])->one();
                if($url != ''){
                    $delurl = '/data/wwwroot/uploadfile/'.$url['eattach_attachment_url'];
                    unlink($delurl);
                }
            }
            $query = $attachment::deleteAll(['sort_id'=>$id,'screen'=>'patent']);
            return $patnet;
        }
    }
}
