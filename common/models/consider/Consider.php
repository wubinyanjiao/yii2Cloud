<?php

namespace common\models\consider;

use common\models\attachment\Attachment;
use Yii;
use \common\models\consider\base\Consider as BaseConsider;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "hs_hr_consider".
 */
class Consider extends BaseConsider
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

    public function considerlist($emp_number){

        $consider = Consider::find()->asArray()->where(['emp_number'=>$emp_number])->all();
        return $consider;
    }

    public function consideradd($data){
        $consider = new Consider();
        $consider->emp_number = $data['emp_number'];
        $consider->research = $data['research'];
        $query = $consider->save();
        return $query;
    }

    public function considerupdate($data){
        $consider = Consider::find()->where(['id'=>$data['id']])->one();
        $consider->research = $data['research'];
        $query = $consider->save();
        return $query;
    }

    public function considersel($id){
        $teach = Consider::find()->asArray()->where(['id'=>$id])->one();
        $attachment = new Attachment();
        $atta = $attachment::find()->asArray()->where(['sort_id'=>$id,'screen'=>'consider'])->all();
        $query['consider'] = $teach;
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


    public function considerdel($id){
        $consider = Consider::deleteAll(['id'=>$id]);
        $attachment = new Attachment();
        $arr = $attachment::find()->where(['sort_id'=>$id,'screen'=>'consider'])->all();
        if ($arr){
            foreach ($id as $k=>$v){
                $url = $attachment::find()->select(['eattach_attachment_url'])->where(['sort_id'=>$v,'screen'=>'consider'])->one();
                if($url != ''){
                    unlink($url['eattach_attachment_url']);
                }
            }
            $query = $attachment::deleteAll(['sort_id'=>$id,'screen'=>'consider']);
            return $query;
        }
    }


}
