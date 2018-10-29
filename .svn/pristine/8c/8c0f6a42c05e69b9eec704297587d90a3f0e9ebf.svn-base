<?php

namespace common\models\writings;

use common\models\attachment\Attachment;
use Yii;
use \common\models\writings\base\Writings as BaseWritings;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "hs_hr_writings".
 */
class Writings extends BaseWritings
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

    public function list($data){
        $emp_number = $data['emp_number'];
        $writings_name = isset($data['writings_name']) ? $data['writings_name'] : '';
        $editor = isset($data['editor']) ? $data['editor'] : '';
        $subeditor = isset($data['subeditor']) ? $data['subeditor'] : '';
        $partake_editor = isset($data['partake_editor']) ? $data['partake_editor'] : '';
        $publish_unit = isset($data['publish_unit']) ? $data['publish_unit'] : '';
        $publish_time = isset($data['publish_time']) ? $data['publish_time'] : '';
        if($publish_time != ''){
            $publish_time=strtotime($publish_time);
            $publish_time=date('Y-m-d',$publish_time);
        }
        $type_id = isset($data['type_id']) ? $data['type_id'] : '';

        $where = "emp_number = '$emp_number'";
        if($writings_name != ''){
            $where .= " and writings_name = '$writings_name'";
        }
        if($editor != ''){
            $where .= " and editor = '$editor'";
        }
        if($subeditor != ''){
            $where .= " and subeditor = '$subeditor'";
        }
        if($partake_editor != ''){
            $where .= " and partake_editor = '$partake_editor'";
        }
        if($publish_unit != ''){
            $where .= " and publish_unit = '$publish_unit'";
        }
        if($publish_time != ''){
            $where .= " and publish_time = '$publish_time'";
        }
        if($type_id != ''){
            $where .= " and type_id = '$type_id'";
        }

        $data = Writings::find()->asArray()->where($where)->all();
        foreach ($data as $k => $v){
            $arr = WritingsType::find()->where(['id'=>$v['type_id']])->one();
            $data[$k]['type'] = $arr['type'];
        }
        return $data;
    }

    public function writingsadd($data){
        $emp_number = $data['emp_number'];
        $writings_name = isset($data['writings_name']) ? $data['writings_name'] : '';
        $editor = isset($data['editor']) ? $data['editor'] : '';
        $subeditor = isset($data['subeditor']) ? $data['subeditor'] : '';
        $partake_editor = isset($data['partake_editor']) ? $data['partake_editor'] : '';
        $publish_unit = isset($data['publish_unit']) ? $data['publish_unit'] : '';
        $publish_time = isset($data['publish_time']) ? $data['publish_time'] : '';
        if($publish_time != ''){
            $publish_time=strtotime($publish_time);
            $publish_time=date('Y-m-d',$publish_time);
        }
        $type_id = isset($data['type_id']) ? $data['type_id'] : '';
        $all_count = isset($data['all_count']) ? $data['all_count'] : '';
        $editor_count = isset($data['editor_count']) ? $data['editor_count'] : '';
        $remark = isset($data['remark']) ? $data['remark'] : '';

        $writings = new Writings();
        $writings->emp_number = $emp_number;
        $writings->writings_name = $writings_name;
        $writings->editor = $editor;
        $writings->subeditor = $subeditor;
        $writings->partake_editor = $partake_editor;
        $writings->publish_unit = $publish_unit;
        $writings->publish_time = $publish_time;
        $writings->type_id = $type_id;
        $writings->all_count = $all_count;
        $writings->editor_count = $editor_count;
        $writings->remark = $remark;
        $query = $writings->save();
        return $query;
    }

    public function writingsupdate($data){
        $id = $data['id'];
        $writings_name = isset($data['writings_name']) ? $data['writings_name'] : '';
        $editor = isset($data['editor']) ? $data['editor'] : '';
        $subeditor = isset($data['subeditor']) ? $data['subeditor'] : '';
        $partake_editor = isset($data['partake_editor']) ? $data['partake_editor'] : '';
        $publish_unit = isset($data['publish_unit']) ? $data['publish_unit'] : '';
        $publish_time = isset($data['publish_time']) ? $data['publish_time'] : '';
        if($publish_time != ''){
            $publish_time=strtotime($publish_time);
            $publish_time=date('Y-m-d',$publish_time);
        }
        $type_id = isset($data['type_id']) ? $data['type_id'] : '';
        $all_count = isset($data['all_count']) ? $data['all_count'] : '';
        $editor_count = isset($data['editor_count']) ? $data['editor_count'] : '';
        $remark = isset($data['remark']) ? $data['remark'] : '';

        $writings = Writings::find()->where(['id'=>$id])->one();
        $writings->writings_name = $writings_name;
        $writings->editor = $editor;
        $writings->subeditor = $subeditor;
        $writings->partake_editor = $partake_editor;
        $writings->publish_unit = $publish_unit;
        $writings->publish_time = $publish_time;
        $writings->type_id = $type_id;
        $writings->all_count = $all_count;
        $writings->editor_count = $editor_count;
        $writings->remark = $remark;
        $query = $writings->save();
        return $query;
    }


    public function writingssel($id){
        $query['writings'] = Writings::find()->asArray()->where(['id'=>$id])->one();
        $query['atta'] = Attachment::find()->asArray()->where(['sort_id'=>$id,'screen'=>'writings'])->all();
        return $query;

    }

    public function writingsdel($id){
        $writings = Writings::deleteAll(['id'=>$id]);
        $attachment = new Attachment();
        $arr = $attachment::find()->where(['sort_id'=>$id,'screen'=>'writings'])->all();
        if ($arr){
            foreach ($id as $k=>$v){
                $url = $attachment::find()->select(['eattach_attachment_url'])->where(['sort_id'=>$v,'screen'=>'writings'])->one();
                if($url != ''){
                    $delurl = '/data/wwwroot/uploadfile/'.$url['eattach_attachment_url'];
                    unlink($delurl);
                }

            }
            $query = $attachment::deleteAll(['sort_id'=>$id,'screen'=>'writings']);
            return $query;
        }
    }
}
