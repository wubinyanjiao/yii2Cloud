<?php

namespace common\models\project;

use common\models\attachment\Attachment;
use function PHPSTORM_META\type;
use Yii;
use \common\models\project\base\Project as BaseProject;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "hs_hr_project".
 */
class Project extends BaseProject
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

    public function projectlist($data){
        $emp_number = $data['emp_number'];
        $project_name = isset($data['project_name']) ? $data['project_name']:'';
        $project_number = isset($data['project_number']) ? $data['project_number']:'';
        $status = isset($data['status']) ? $data['status']:'';
        $leading = isset($data['leading']) ? $data['leading']:'';
        $participant = isset($data['participant']) ? $data['participant']:'';
        $ranking = isset($data['ranking']) ? $data['ranking']:'';
        $support_unit = isset($data['support_unit']) ? $data['support_unit']:'';
        $level_id = isset($data['level_id']) ? $data['level_id']:'';
        $source_id = isset($data['source_id']) ? $data['source_id']:'';
        $type_id = isset($data['type_id']) ? $data['type_id']:'';
        $apply_time = isset($data['apply_time']) ? $data['apply_time']:'';
        if($apply_time != ''){
            $apply_time=strtotime($apply_time);
            $apply_time=date('Y-m-d',$apply_time);
        }
        $start_time = isset($data['start_time']) ? $data['start_time']:'';
        if($start_time != ''){
            $start_time=strtotime($start_time);
            $start_time=date('Y-m-d',$start_time);
        }
        $end_time = isset($data['end_time']) ? $data['end_time']:'';
        if($end_time != ''){
            $end_time=strtotime($end_time);
            $end_time=date('Y-m-d',$end_time);
        }
        $money = isset($data['money']) ? $data['money']:'';

        $where = "emp_number = '$emp_number'";

        if($project_name != '' ){
            $where .= " and project_name = '$project_name' ";
        }
        if($project_number != ''){
            $where .=" and project_number = '$project_number'";
        }
        if($leading != ''){
            $where .=" and leading = '$leading'";
        }
         if($status != ''){
            $where .=" and status = '$status'";
        }
        if($participant != ''){
            $where .=" and participant = '$participant'";
        }
        if($ranking != ''){
            $where .=" and ranking = '$ranking'";
        }
        if($support_unit != ''){
            $where .=" and support_unit = '$support_unit'";
        }
        if($level_id != ''){
            $where .=" and level_id = '$level_id'";
        }
        if($source_id != ''){
            $where .=" and source_id = '$source_id'";
        }
        if($type_id != ''){
            $where .=" and type_id = '$type_id'";
        }
        if($apply_time != ''){
            $where .=" and apply_time = '$apply_time'";
        }
        if($start_time != ''){
            $where .=" and start_time = '$start_time'";
        }
        if($end_time != ''){
            $where .=" and end_time = '$end_time'";
        }
        if($money != ''){
            $where .=" and money = '$money'";
        }

        $query = Project::find()->asArray()->where($where)->all();
        foreach ($query as $k =>$v){
            $level = Level::find()->asArray()->where(['id'=>$v['level_id']])->one();
            $source = Source::find()->asArray()->where(['id'=>$v['source_id']])->one();
            $type = \common\models\project\Type::find()->where(['id'=>$v['type_id']])->one();
            $query[$k]['level'] = $level['level'];
            $query[$k]['source'] = $source['source'];
            $query[$k]['type'] = $type['type'];
        }
        return $query;

    }


    public function projectadd($data){
        $emp_number = $data['emp_number'];
        $project_name = isset($data['project_name']) ? $data['project_name']:'';
        $project_number = isset($data['project_number']) ? $data['project_number']:'';
        $status = isset($data['status']) ? $data['status']:'';
        $leading = isset($data['leading']) ? $data['leading']:'';
        $participant = isset($data['participant']) ? $data['participant']:'';
        $ranking = isset($data['ranking']) ? $data['ranking']:'';
        $support_unit = isset($data['support_unit']) ? $data['support_unit']:'';
        $level_id = isset($data['level_id']) ? $data['level_id']:'';
        $source_id = isset($data['source_id']) ? $data['source_id']:'';
        $type_id = isset($data['type_id']) ? $data['type_id']:'';
        $apply_time = isset($data['apply_time']) ? $data['apply_time']:'';
        $remark = isset($data['remark']) ? $data['remark']:'';
        if($apply_time != ''){
            $apply_time=strtotime($apply_time);
            $apply_time=date('Y-m-d',$apply_time);
        }
        $start_time = isset($data['start_time']) ? $data['start_time']:'';
        if($start_time != ''){
            $start_time=strtotime($start_time);
            $start_time=date('Y-m-d',$start_time);
        }
        $end_time = isset($data['end_time']) ? $data['end_time']:'';
        if($end_time != ''){
            $end_time=strtotime($end_time);
            $end_time=date('Y-m-d',$end_time);
        }
        $money = isset($data['money']) ? $data['money']:'';

        $project = new Project();
        $project->emp_number = $emp_number;
        $project->project_name = $project_name;
        $project->status = $status;
        $project->project_number = $project_number;
        $project->leading = $leading;
        $project->participant = $participant;
        $project->ranking = $ranking;
        $project->support_unit = $support_unit;
        $project->level_id = $level_id;
        $project->source_id = $source_id;
        $project->type_id = $type_id;
        $project->apply_time = $apply_time;
        $project->start_time = $start_time;
        $project->end_time = $end_time;
        $project->money = $money;
        $project->remark = $remark;
        $query = $project->save();
        return $query;
    }

    public function projectupdate($data){
        $id = $data['id'];
        $project_name = isset($data['project_name']) ? $data['project_name']:'';
        $project_number = isset($data['project_number']) ? $data['project_number']:'';
        $status = isset($data['status']) ? $data['status']:'';
        $leading = isset($data['leading']) ? $data['leading']:'';
        $participant = isset($data['participant']) ? $data['participant']:'';
        $ranking = isset($data['ranking']) ? $data['ranking']:'';
        $support_unit = isset($data['support_unit']) ? $data['support_unit']:'';
        $level_id = isset($data['level_id']) ? $data['level_id']:'';
        $source_id = isset($data['source_id']) ? $data['source_id']:'';
        $type_id = isset($data['type_id']) ? $data['type_id']:'';
        $apply_time = isset($data['apply_time']) ? $data['apply_time']:'';
        $remark = isset($data['remark']) ? $data['remark']:'';
        if($apply_time != ''){
            $apply_time=strtotime($apply_time);
            $apply_time=date('Y-m-d',$apply_time);
        }
        $start_time = isset($data['start_time']) ? $data['start_time']:'';
        if($start_time != ''){
            $start_time=strtotime($start_time);
            $start_time=date('Y-m-d',$start_time);
        }
        $end_time = isset($data['end_time']) ? $data['end_time']:'';
        if($end_time != ''){
            $end_time=strtotime($end_time);
            $end_time=date('Y-m-d',$end_time);
        }
        $money = isset($data['money']) ? $data['money']:'';

        $project = Project::find()->where(['id'=>$id])->one();
        $project->project_name = $project_name;
        $project->project_number = $project_number;
        $project->status = $status;
        $project->leading = $leading;
        $project->participant = $participant;
        $project->ranking = $ranking;
        $project->support_unit = $support_unit;
        $project->level_id = $level_id;
        $project->source_id = $source_id;
        $project->type_id = $type_id;
        $project->apply_time = $apply_time;
        $project->start_time = $start_time;
        $project->end_time = $end_time;
        $project->money = $money;
        $project->remark = $remark;
        $query = $project->save();
        return $query;
    }


    public function projectsel($id){
        $project = Project::find()->asArray()->where(['id'=>$id])->one();
        $attachment = new Attachment();
        $atta = $attachment::find()->asArray()->where(['sort_id'=>$id,'screen'=>'project'])->all();
        $query['project'] = $project;
        $query['atta'] = $atta;
        return $query;
    }

    public function attadel($eattach_id,$emp_number){
        $attachment = new Attachment();
        $url = $attachment::find()->select(['eattach_attachment_url'])->where(['emp_number'=>$emp_number,'screen'=>'project','eattach_id'=>$eattach_id])->all();
        if($url != ''){
            foreach ($url as $k => $v){
                unlink($v['eattach_attachment_url']);
            }
        }
        $query = $attachment::deleteAll(['emp_number'=>$emp_number,'screen'=>'project','eattach_id'=>$eattach_id]);
        return $query;
    }


    public function projectdel($id){
        $project = Project::deleteAll(['id'=>$id]);
        $attachment = new Attachment();
        $arr = $attachment::find()->where(['sort_id'=>$id,'screen'=>'project'])->all();
        if ($arr){
            foreach ($id as $k=>$v){
                $url = $attachment::find()->select(['eattach_attachment_url'])->where(['sort_id'=>$v,'screen'=>'project'])->one();
                if($url != ''){
                    $delurl = '/data/wwwroot/uploadfile/'.$url['eattach_attachment_url'];
                    unlink($delurl);
                }

            }
            $query = $attachment::deleteAll(['sort_id'=>$id,'screen'=>'project']);
            return $query;
        }
    }


}
