<?php

namespace common\models\honor;

use common\models\attachment\Attachment;
use common\models\consider\Consider;
use common\models\employee\Employee;
use common\models\hold\Hold;
use common\models\Journal\Journal;
use common\models\meeting\Meeting;
use common\models\patent\Patent;
use common\models\project\Level;
use common\models\project\Project;
use common\models\project\Source;
use common\models\project\Type;
use common\models\subunit\Subunit;
use common\models\teach\Degree;
use common\models\teach\Education;
use common\models\teach\Teach;
use common\models\thesis\Article;
use common\models\thesis\Publication;
use common\models\thesis\Thesis;
use common\models\thesis\ThesisType;
use common\models\user\Minzu;
use common\models\user\Picture;
use common\models\user\Role;
use common\models\user\User;
use common\models\writings\Writings;
use Yii;
use \common\models\honor\base\Honor as BaseHonor;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "hs_hr_honor".
 */
class Honor extends BaseHonor
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

    public function addhonor($data){
        $awardee = isset($data['awardee'])?$data['awardee']:'';
        $honor_name = isset($data['honor_name'])?$data['honor_name']:'';
        $item_name = isset($data['item_name'])?$data['item_name']:'';
        $done_unit = isset($data['done_unit'])?$data['done_unit']:'';
        $grant_award = isset($data['grant_award'])?$data['grant_award']:'';
        $reward = isset($data['reward'])?$data['reward']:'';
        $reward_class = isset($data['reward_class'])?$data['reward_class']:'';
        $reward_type = isset($data['reward_type'])?$data['reward_type']:'';
        $reward_number = isset($data['reward_number'])?$data['reward_number']:'';
        $reward_time = isset($data['reward_time'])?$data['reward_time']:'';
        if($reward_time != ''){
            $reward_time=strtotime($reward_time);
            $reward_time=date('Y-m-d',$reward_time);
        }
        $remark = isset($data['remark'])?$data['remark']:'';
        $status = isset($data['status'])?$data['status']:'';

            $emp_number = 1;
            $honor = new Honor();
            $honor->emp_number = $emp_number;
            $honor->awardee = $awardee;
            $honor->honor_name = $honor_name;
            $honor->item_name = $item_name;
            $honor->done_unit = $done_unit;
            $honor->grant_award = $grant_award;
            $honor->reward = $reward;
            $honor->reward_class = $reward_class;
            $honor->reward_type = $reward_type;
            $honor->reward_number = $reward_number;
            $honor->reward_time = $reward_time;
            $honor->remark = $remark;
            $honor->status = $status;
            if($query = $honor->save()){
                $data = explode('，',$awardee);
                foreach ($data as $k =>$v){
                    $emp_number = Employee::find()->asArray()->select('emp_number')->where(['emp_firstname'=>$v])->one();
                    if($emp_number != ''){
                        $emphonor = new Emphonor();
                        $emphonor->emp_number = $emp_number['emp_number'];
                        $emphonor->honor_name = $honor_name;
                        $emphonor->item_name = $item_name;
                        $emphonor->done_unit = $done_unit;
                        $emphonor->grant_award = $grant_award;
                        $emphonor->reward = $reward;
                        $emphonor->reward_class = $reward_class;
                        $emphonor->reward_type = $reward_type;
                        $emphonor->reward_number = $reward_number;
                        $emphonor->reward_time = $reward_time;
                        $emphonor->remark = $remark;
                        $emphonor->status = $status;
                        if(!$emphonor->save()){
                            return false;
                        }
                    }
                }
                return $query;

            }else{
                return $query;
            }


    }

    public function selhonor($honor_id){
        $query['honor'] = Honor::find()->asArray()->where(['honor_id'=>$honor_id])->one();
        $query['atta'] = Attachment::find()->asArray()->where(['sort_id'=>$honor_id,'screen'=>'honor'])->all();
        return $query;
    }



    public function honorlist($data){
        $page = $data['page'];
        $awardee = isset($data['awardee'])?$data['awardee']:'';
        $honor_name = isset($data['honor_name'])?$data['honor_name']:'';
        $item_name = isset($data['item_name'])?$data['item_name']:'';
        $done_unit = isset($data['done_unit'])?$data['done_unit']:'';
        $grant_award = isset($data['grant_award'])?$data['grant_award']:'';
        $reward = isset($data['reward'])?$data['reward']:'';
        $reward_class =isset($data['reward_class'])?$data['reward_class']:'';
        $reward_type = isset($data['reward_type'])?$data['reward_type']:'';
        $reward_number = isset($data['reward_number'])?$data['reward_number']:'';
        $reward_time = isset($data['reward_time'])?$data['reward_time']:'';
        $remark = isset($data['remark'])?$data['remark']:'';
        $status = isset($data['status'])?$data['status']:'';
        $where = '1 = 1';
        if($awardee != ''){
            $where .=" and awardee like '%$awardee%'";
        }
        if($honor_name != ''){
            $where .=" and honor_name = '$honor_name'";
        }
        if($item_name != ''){
            $where .=" and item_name = '$item_name'";
        }
        if($done_unit != ''){
            $where .=" and done_unit = '$done_unit'";
        }
        if($grant_award != ''){
            $where .=" and grant_award = '$grant_award'";
        }
        if($reward != ''){
            $where .=" and reward = '$reward'";
        }
        if($reward_class != ''){
            $where .=" and reward_class = '$reward_class'";
        }
        if($reward_type != ''){
            $where .=" and reward_type = '$reward_type'";
        }
        if($reward_number != ''){
            $where .=" and reward_number = '$reward_number'";
        }
        if($reward_time != ''){
            $where .=" and reward_time = '$reward_time'";
        }
        if($remark != ''){
            $where .=" and remark = '$remark'";
        }
        if($status != ''){
            $where .=" and status = '$status'";
        }

        $pagesize = 20;
        $startrow = ($page-1)*$pagesize;

        $honor = new Honor();
        $arr = $honor::find()->asArray()->where($where)->offset($startrow)->limit($pagesize)->all();
        $count = $honor::find()->where($where)->offset($startrow)->limit($pagesize)->count();
        foreach ($arr as $k=>$v){
            $type = HonorType::find()->asArray()->where(['id'=>$v['reward_type']])->one();
            $class = HonorClass::find()->asArray()->where(['id'=>$v['reward_class']])->one();
            $arr[$k]['type'] = $type['type'];
            $arr[$k]['class'] = $class['class'];
        }
        $query['data'] = $arr;
        $query['count'] = (int)$count;
        $query['pagesize'] = $pagesize;
        return $query;
    }


    public function uphonor($data){
        $awardee = isset($data['awardee'])?$data['awardee']:'';
        $honor_id = isset($data['honor_id'])?$data['honor_id']:'';
        $honor_name = isset($data['honor_name'])?$data['honor_name']:'';
        $item_name = isset($data['item_name'])?$data['item_name']:'';
        $done_unit = isset($data['done_unit'])?$data['done_unit']:'';
        $grant_award = isset($data['grant_award'])?$data['grant_award']:'';
        $reward = isset($data['reward'])?$data['reward']:'';
        $reward_class = isset($data['reward_class'])?$data['reward_class']:'';
        $reward_type = isset($data['reward_type'])?$data['reward_type']:'';
        $reward_number = isset($data['reward_number'])?$data['reward_number']:'';
        $reward_time = isset($data['reward_time'])?$data['reward_time']:'';
        if($reward_time != ''){
            $reward_time=strtotime($reward_time);
            $reward_time=date('Y-m-d',$reward_time);
        }
        $remark = isset($data['remark'])?$data['remark']:'';
        $status = isset($data['status'])?$data['status']:'';




            $emp_number = 1;
            $honor = Honor::find()->where(['honor_id'=>$honor_id])->one();
            $honor->emp_number = $emp_number;
            $honor->awardee = $awardee;
            $honor->honor_name = $honor_name;
            $honor->item_name = $item_name;
            $honor->done_unit = $done_unit;
            $honor->grant_award = $grant_award;
            $honor->reward = $reward;
            $honor->reward_class = $reward_class;
            $honor->reward_type = $reward_type;
            $honor->reward_number = $reward_number;
            $honor->reward_time = $reward_time;
            $honor->remark = $remark;
            $honor->status = $status;
            $query = $honor->save();
            return $query;


    }

    public function delhonor($honor_id){
        $info = Honor::deleteAll(['honor_id'=>$honor_id]);
        if($info){
            $attachment = new Attachment();
            $arr = $attachment::find()->where(['sort_id'=>$honor_id,'screen'=>'honor'])->all();
            if ($arr){
                foreach ($honor_id as $k=>$v){
                    $url = $attachment::find()->select(['eattach_attachment_url'])->where(['sort_id'=>$v,'screen'=>'honor'])->one();
                    if($url != ''){
                        unlink($url['eattach_attachment_url']);
                    }

                }
                $query = $attachment::deleteAll(['sort_id'=>$honor_id,'screen'=>'honor']);
                return $query;
            }
            return $info;
        }else{
            return $info;
        }
    }



    public function empaddhonor($data){
        $emp_number = isset($data['emp_number'])?$data['emp_number']:'';
        $ranking = isset($data['ranking'])?$data['ranking']:'';
        $honor_name = isset($data['honor_name'])?$data['honor_name']:'';
        $item_name = isset($data['item_name'])?$data['item_name']:'';
        $done_unit = isset($data['done_unit'])?$data['done_unit']:'';
        $grant_award = isset($data['grant_award'])?$data['grant_award']:'';
        $reward = isset($data['reward'])?$data['reward']:'';
        $reward_class = isset($data['reward_class'])?$data['reward_class']:'';
        $reward_type = isset($data['reward_type'])?$data['reward_type']:'';
        $reward_number = isset($data['reward_number'])?$data['reward_number']:'';
        $reward_time = isset($data['reward_time'])?$data['reward_time']:'';
        if($reward_time != ''){
            $reward_time=strtotime($reward_time);
            $reward_time=date('Y-m-d',$reward_time);
        }
        $remark = isset($data['remark'])?$data['remark']:'';
        $status = isset($data['status'])?$data['status']:'';



        $honor = new Emphonor();
        $honor->emp_number = $emp_number;
        $honor->ranking = $ranking;
        $honor->honor_name = $honor_name;
        $honor->item_name = $item_name;
        $honor->done_unit = $done_unit;
        $honor->grant_award = $grant_award;
        $honor->reward = $reward;
        $honor->reward_class = $reward_class;
        $honor->reward_type = $reward_type;
        $honor->reward_number = $reward_number;
        $honor->reward_time = $reward_time;
        $honor->remark = $remark;
        $honor->status = $status;
        $query = $honor->save();
        return $query;
    }


    public function empselhonor($id){
        $query['emphonor'] = Emphonor::find()->asArray()->where(['id'=>$id])->one();
        $query['atta'] = Attachment::find()->asArray()->where(['sort_id'=>$id,'screen'=>'emphonor'])->all();
        return $query;
    }

    public function empuphonor($data){
        $id = isset($data['id'])?$data['id']:'';
        $honor_name = isset($data['honor_name'])?$data['honor_name']:'';
        $ranking = isset($data['ranking'])?$data['ranking']:'';
        $item_name = isset($data['item_name'])?$data['item_name']:'';
        $done_unit = isset($data['done_unit'])?$data['done_unit']:'';
        $grant_award = isset($data['grant_award'])?$data['grant_award']:'';
        $reward = isset($data['reward'])?$data['reward']:'';
        $reward_class = isset($data['reward_class'])?$data['reward_class']:'';
        $reward_type = isset($data['reward_type'])?$data['reward_type']:'';
        $reward_number = isset($data['reward_number'])?$data['reward_number']:'';
        $reward_time = isset($data['reward_time'])?$data['reward_time']:'';
        if($reward_time != ''){
            $reward_time=strtotime($reward_time);
            $reward_time=date('Y-m-d',$reward_time);
        }
        $remark = isset($data['remark'])?$data['remark']:'';
        $status = isset($data['status'])?$data['status']:'';




        $honor = Emphonor::find()->where(['id'=>$id])->one();

        $honor->honor_name = $honor_name;
        $honor->item_name = $item_name;
        $honor->ranking = $ranking;
        $honor->done_unit = $done_unit;
        $honor->grant_award = $grant_award;
        $honor->reward = $reward;
        $honor->reward_class = $reward_class;
        $honor->reward_type = $reward_type;
        $honor->reward_number = $reward_number;
        $honor->reward_time = $reward_time;
        $honor->remark = $remark;
        $honor->status = $status;
        $query = $honor->save();
        return $query;


    }



    public function emphonorlist($data){
        $emp_number = isset($data['emp_number'])?$data['emp_number']:'';
        $honor_name = isset($data['honor_name'])?$data['honor_name']:'';
        $item_name = isset($data['item_name'])?$data['item_name']:'';
        $done_unit = isset($data['done_unit'])?$data['done_unit']:'';
        $grant_award = isset($data['grant_award'])?$data['grant_award']:'';
        $reward = isset($data['reward'])?$data['reward']:'';
        $reward_class =isset($data['reward_class'])?$data['reward_class']:'';
        $reward_type = isset($data['reward_type'])?$data['reward_type']:'';
        $reward_number = isset($data['reward_number'])?$data['reward_number']:'';
        $reward_time = isset($data['reward_time'])?$data['reward_time']:'';
        $remark = isset($data['remark'])?$data['remark']:'';
        $status = isset($data['status'])?$data['status']:'';
        $where = '1 = 1';
        if($emp_number != ''){
            $where .=" and emp_number = '$emp_number'";
        }
        if($honor_name != ''){
            $where .=" and honor_name = '$honor_name'";
        }
        if($item_name != ''){
            $where .=" and item_name = '$item_name'";
        }
        if($done_unit != ''){
            $where .=" and done_unit = '$done_unit'";
        }
        if($grant_award != ''){
            $where .=" and grant_award = '$grant_award'";
        }
        if($reward != ''){
            $where .=" and reward = '$reward'";
        }
        if($reward_class != ''){
            $where .=" and reward_class = '$reward_class'";
        }
        if($reward_type != ''){
            $where .=" and reward_type = '$reward_type'";
        }
        if($reward_number != ''){
            $where .=" and reward_number = '$reward_number'";
        }
        if($reward_time != ''){
            $where .=" and reward_time = '$reward_time'";
        }
        if($remark != ''){
            $where .=" and remark = '$remark'";
        }
        if($status != ''){
            $where .=" and status = '$status'";
        }



        $emphonor = new Emphonor();
        $arr = $emphonor::find()->asArray()->where($where)->all();
        foreach ($arr as $k=>$v){
            $type = HonorType::find()->asArray()->where(['id'=>$v['reward_type']])->one();
            $class = HonorClass::find()->asArray()->where(['id'=>$v['reward_class']])->one();
            $arr[$k]['type'] = $type['type'];
            $arr[$k]['class'] = $class['class'];
        }
        $query['emphonor'] = $arr;
        return $query;
    }


    public function empdelhonor($id){
        $info = Emphonor::deleteAll(['id'=>$id]);
        if($info){
            $attachment = new Attachment();
            $arr = $attachment::find()->where(['sort_id'=>$id,'screen'=>'emphonor'])->all();
            if ($arr){
                foreach ($id as $k=>$v){
                    $url = $attachment::find()->select(['eattach_attachment_url'])->where(['sort_id'=>$v,'screen'=>'emphonor'])->one();
                    if($url != ''){
                        $delurl = '/data/wwwroot/uploadfile/'.$url['eattach_attachment_url'];
                        unlink($delurl);
                    }

                }
                $query = $attachment::deleteAll(['sort_id'=>$id,'screen'=>'emphonor']);
                return $query;
            }
            return $info;
        }else{
            return $info;
        }
    }








    public function research($data){
    	//基础信息
        $emp_firstname = isset($data['emp_firstname'])?$data['emp_firstname']:'';
        $work_station = isset($data['work_station'])?$data['work_station']:'';

        $consider_research = isset($data['consider_research'])?$data['consider_research']:'';

        $teach_school = isset($data['teach_school'])?$data['teach_school']:'';
        $teach_school_type = isset($data['teach_school_type'])?$data['teach_school_type']:'';
        $teach_major = isset($data['teach_major'])?$data['teach_major']:'';
        $teach_record_id = isset($data['teach_record_id'])?$data['teach_record_id']:'';


        $project_status = isset($data['project_status'])?$data['project_status']:'';
        $project_name = isset($data['project_name'])?$data['project_name']:'';
        $project_number = isset($data['project_number'])?$data['project_number']:'';
        $project_leading = isset($data['project_leading'])?$data['project_leading']:'';
        $project_participant = isset($data['project_participant'])?$data['project_participant']:'';
        $project_support_unit = isset($data['project_support_unit'])?$data['project_support_unit']:'';
        $project_level_id = isset($data['project_level_id'])?$data['project_level_id']:'';
        $project_source_id = isset($data['project_source_id'])?$data['project_source_id']:'';
        $project_type_id = isset($data['project_type_id'])?$data['project_type_id']:'';


        $thesis_type_id = isset($data['thesis_type_id'])?$data['thesis_type_id']:'';
        $thesis_article_type_id = isset($data['thesis_article_type_id'])?$data['thesis_article_type_id']:'';
        $thesis_publication_type_id = isset($data['thesis_publication_type_id'])?$data['thesis_publication_type_id']:'';
        $thesis_name = isset($data['thesis_name'])?$data['thesis_name']:'';
        $thesis_first_author_type = isset($data['thesis_first_author_type'])?$data['thesis_first_author_type']:'';
        $thesis_first_author = isset($data['thesis_first_author'])?$data['thesis_first_author']:'';
        $thesis_first_author_unit = isset($data['thesis_first_author_unit'])?$data['thesis_first_author_unit']:'';
        $thesis_corresponding_author_type = isset($data['thesis_corresponding_author_type'])?$data['thesis_corresponding_author_type']:'';
        $thesis_corresponding_author = isset($data['thesis_corresponding_author'])?$data['thesis_corresponding_author']:'';
        $thesis_corresponding_author_unit = isset($data['thesis_corresponding_author_unit'])?$data['thesis_corresponding_author_unit']:'';
        $thesis_publication = isset($data['thesis_publication'])?$data['thesis_publication']:'';
        $thesis_is_include = isset($data['thesis_is_include'])?$data['thesis_is_include']:'';
        

        $meeting_language = isset($data['meeting_language'])?$data['meeting_language']:'';
        $meeting_name = isset($data['meeting_name'])?$data['meeting_name']:'';
        $meeting_time = isset($data['meeting_time'])?$data['meeting_time']:'';
        $meeting_host_unit = isset($data['meeting_host_unit'])?$data['meeting_host_unit']:'';
        $meeting_thesis_type = isset($data['meeting_thesis_type'])?$data['meeting_thesis_type']:'';
        $meeting_thesis_name = isset($data['meeting_thesis_name'])?$data['meeting_thesis_name']:'';
        $meeting_is_exchange = isset($data['meeting_is_exchange'])?$data['meeting_is_exchange']:'';
        

        $honor_status = isset($data['honor_status'])?$data['honor_status']:'';
        $honor_reward_type = isset($data['honor_reward_type'])?$data['honor_reward_type']:'';
        $honor_emp_name = isset($data['honor_emp_name'])?$data['honor_emp_name']:'';
        $honor_item_name = isset($data['honor_item_name'])?$data['honor_item_name']:'';
        $honor_accept_award = isset($data['honor_accept_award'])?$data['honor_accept_award']:'';
        $honor_name = isset($data['honor_name'])?$data['honor_name']:'';
        $honor_reward_class = isset($data['honor_reward_class'])?$data['honor_reward_class']:'';
        

        $patent_class = isset($data['patent_class'])?$data['patent_class']:'';
        $patent_department = isset($data['patent_department'])?$data['patent_department']:'';
        $patent_name = isset($data['patent_name'])?$data['patent_name']:'';
        $patent_type = isset($data['patent_type'])?$data['patent_type']:'';
        $patent_applicant = isset($data['patent_applicant'])?$data['patent_applicant']:'';
        $patent_patentee = isset($data['patent_patentee'])?$data['patent_patentee']:'';
        $patent_apply_number = isset($data['patent_apply_number'])?$data['patent_apply_number']:'';
        
        $hold_society = isset($data['hold_society'])?$data['hold_society']:'';
        $hold_job = isset($data['hold_job'])?$data['hold_job']:'';
        

        $journal_name = isset($data['journal_name'])?$data['journal_name']:'';
        $journal_job = isset($data['journal_job'])?$data['journal_job']:'';
        

        $writings_name = isset($data['writings_name'])?$data['writings_name']:'';
        $writings_editor = isset($data['writings_editor'])?$data['writings_editor']:'';
        $writings_subeditor = isset($data['writings_subeditor'])?$data['writings_subeditor']:'';
        $writings_partake_editor = isset($data['writings_partake_editor'])?$data['writings_partake_editor']:'';
        $writings_publish_unit = isset($data['writings_publish_unit'])?$data['writings_publish_unit']:'';
        $writings_type_id = isset($data['writings_type_id'])?$data['writings_type_id']:'';

        $where = '1=1';

        if($emp_firstname != ''){
        	$where .= " and a.emp_firstname like '%$emp_firstname%'";
        }
        if($work_station != ''){
            $where .= " and a.work_station = '$work_station'";
        }

        //研究方向
        if($consider_research != ''){
            $where .= " and c.research = '$consider_research'";
        }

        //教育
        if($teach_school != ''){
            $where .= " and d.school = '$teach_school'";
        }
        if($teach_school_type != ''){
            $where .= " and d.school_type = '$teach_school_type'";
        }
        if($teach_major != ''){
            $where .= " and d.major = '$teach_major'";
        }
        if($teach_record_id != ''){
            $where .= " and d.record_id = '$teach_record_id'";
        }

        //科研项目
        if($project_status != ''){
            $where .= " and e.status = '$project_status'";
        }
        if($project_name != ''){
            $where .= " and e.project_name = '$project_name'";
        }
        if($project_number != ''){
            $where .= " and e.project_number = '$project_number'";
        }
        if($project_leading != ''){
            $where .= " and e.leading = '$project_leading'";
        }
        if($project_participant != ''){
            $where .= " and e.participant = '$project_participant'";
        }
        if($project_support_unit != ''){
            $where .= " and e.support_unit = '$project_support_unit'";
        }
        if($project_level_id != ''){
            $where .= " and e.level_id = '$project_level_id'";
        }
        if($project_source_id != ''){
            $where .= " and e.source_id = '$project_source_id'";
        }
        if($project_type_id != ''){
            $where .= " and e.type_id = '$project_type_id'";
        }
        

        //论文
        if($thesis_type_id != ''){
            $where .= " and f.thesis_type_id = '$thesis_type_id'";
        }
        if($thesis_article_type_id != ''){
            $where .= " and f.article_type_id = '$thesis_article_type_id'";
        }
        if($thesis_publication_type_id != ''){
            $where .= " and f.publication_type_id = '$thesis_publication_type_id'";
        }
        if($thesis_name != ''){
            $where .= " and f.thesis_name = '$thesis_name'";
        }
        if($thesis_first_author_type != ''){
            $where .= " and f.first_author_type = '$thesis_first_author_type'";
        }
        if($thesis_first_author != ''){
            $where .= " and f.first_author = '$thesis_first_author'";
        }
        if($thesis_first_author_unit != ''){
            $where .= " and f.first_author_unit = '$thesis_first_author_unit'";
        }
        if($thesis_corresponding_author_type != ''){
            $where .= " and f.corresponding_author_type = '$thesis_corresponding_author_type'";
        }
        if($thesis_corresponding_author != ''){
            $where .= " and f.corresponding_author = '$thesis_corresponding_author'";
        }
        if($thesis_corresponding_author_unit != ''){
            $where .= " and f.corresponding_author_unit = '$thesis_corresponding_author_unit'";
        }
        if($thesis_publication != ''){
            $where .= " and f.publication = '$thesis_publication'";
        }
        if($thesis_is_include != ''){
            $where .= " and f.is_include = '$thesis_is_include'";
        }

        //会议
        if($meeting_language != ''){
            $where .= " and g.meeting_language = '$meeting_language'";
        }
        if($meeting_name!= ''){
            $where .= " and g.meeting_name = '$meeting_name'";
        }
        if($meeting_time != ''){
            $where .= " and g.meeting_time = '$meeting_time'";
        }
        if($meeting_host_unit != ''){
            $where .= " and g.host_unit = '$meeting_host_unit'";
        }
        if($meeting_thesis_type != ''){
            $where .= " and g.thesis_type = '$meeting_thesis_type'";
        }
        if($meeting_thesis_name != ''){
            $where .= " and g.thesis_name = '$meeting_thesis_name'";
        }
        if($meeting_is_exchange != ''){
            $where .= " and g.is_exchange = '$meeting_is_exchange'";
        }


        //荣誉
        if($honor_status != ''){
            $where .= " and h.status = '$honor_status'";
        }
        if($honor_reward_type != ''){
            $where .= " and h.reward_type = '$honor_reward_type'";
        }
        if($honor_emp_name != ''){
            $emp_number = Employee::find()->select(['emp_number'])->asArray()->where(['emp_firstname'=>$honor_emp_name])->one();
            $emp_number = $emp_number['emp_number'];
            $where .= " and h.honor_emp_name = '$emp_number'";
        }
        if($honor_item_name != ''){
            $where .= " and h.item_name = '$honor_item_name'";
        }
        if($honor_accept_award != ''){
            $where .= " and h.done_unit = '$honor_accept_award'";
        }
        if($honor_name != ''){
            $where .= " and h.honor_name = '$honor_name'";
        }
        if($honor_reward_class != ''){
            $where .= " and h.reward_class = '$honor_reward_class'";
        }


        //专利
        if($patent_class != ''){
            $where .= " and i.patent_class = '$patent_class'";
        }
        if($patent_department != ''){
            $where .= " and i.department = '$patent_department'";
        }
        if($patent_name != ''){
            $where .= " and i.patent_name = '$patent_name'";
        }
        if($patent_type != ''){
            $where .= " and i.patent_type = '$patent_type'";
        }
        if($patent_applicant != ''){
            $where .= " and i.applicant = '$patent_applicant'";
        }
        if($patent_patentee != ''){
            $where .= " and i.patentee = '$patent_patentee'";
        }
        if($patent_apply_number != ''){
            $where .= " and i.apply_number = '$patent_apply_number'";
        }


        //社会兼职
        if($hold_society != ''){
            $where .= " and j.society = '$hold_society'";
        }
        if($hold_job != ''){
            $where .= " and j.job = '$hold_job'";
        }

        //杂志编委
       	if($journal_name != ''){
            $where .= " and k.journal_name = '$journal_name'";
        }
        if($journal_job != ''){
            $where .= " and k.job = '$journal_job'";
        }

        //著作
        if($writings_name != ''){
            $where .= " and l.writings_name = '$writings_name'";
        }
        if($writings_editor != ''){
            $where .= " and l.editor = '$writings_editor'";
        }
        if($writings_subeditor != ''){
            $where .= " and l.subeditor = '$writings_subeditor'";
        }
        if($writings_partake_editor != ''){
            $where .= " and l.partake_editor = '$writings_partake_editor'";
        }
        if($writings_publish_unit != ''){
            $where .= " and l.publish_unit = '$writings_publish_unit'";
        }
        if($writings_type_id != ''){
            $where .= " and l.type_id = '$writings_type_id'";
        }



        $query = (new yii\db\Query())
            ->select ('a.emp_number,a.emp_firstname,b.user_name,m.name')
            ->from('orangehrm_mysql.hs_hr_employee a')
            ->leftJoin('orangehrm_mysql.ohrm_user b','a.emp_number = b.emp_number')
            ->leftJoin('orangehrm_mysql.ohrm_subunit m','a.work_station = m.id')
            ->leftJoin('orangehrm_mysql.hs_hr_consider c','a.emp_number = c.emp_number')
            ->leftJoin('orangehrm_mysql.hs_hr_teach d','a.emp_number = d.emp_number')
            ->leftJoin('orangehrm_mysql.hs_hr_project e','a.emp_number = e.emp_number')
            ->leftJoin('orangehrm_mysql.hs_hr_thesis f','a.emp_number = f.emp_number')
            ->leftJoin('orangehrm_mysql.hs_hr_meeting g','a.emp_number = g.emp_number')
            ->leftJoin('orangehrm_mysql.hs_hr_emp_honor h','a.emp_number = h.emp_number')
            ->leftJoin('orangehrm_mysql.hs_hr_patent i','a.emp_number = i.emp_number')
            ->leftJoin('orangehrm_mysql.hs_hr_hold j','a.emp_number = j.emp_number')
            ->leftJoin('orangehrm_mysql.hs_hr_journal k','a.emp_number = k.emp_number')
            ->leftJoin('orangehrm_mysql.hs_hr_writings l','a.emp_number = l.emp_number')
            ->groupBy('a.emp_firstname')
            ->where($where)
            ->all();

        return $query;

    }


    public function selresearch($emp_number){

        //个人详情
        $query['user'] = Employee::find()->asArray()->select(['emp_firstname','emp_street2','work_station','emp_work_email','weixin_code','now_academic_degree','emp_gender','minzu_code','emp_birthday','emp_other_id','custom2','emp_mobile','education_id','emp_street2'])->where(['emp_number'=>$emp_number])->one();
        if($query['user']['emp_gender'] == 1){
            $query['user']['emp_gender'] = '男';
        }else{
            $query['user']['emp_gender'] = '女';
        }
        $piction= Picture::find()->asArray()->select('epic_picture_url')->where(['emp_number'=>$emp_number])->one();
        $query['user']['piction'] = $piction['epic_picture_url'];
        $work_station= Subunit::find()->asArray()->where(['id'=>$query['user']['work_station']])->one();
        $query['user']['work_station'] = $work_station['name'];
        $role_id = User::find()->select('user_role_id')->where(['emp_number'=>$emp_number])->one();
        $role = Role::find()->where(['id'=>$role_id['user_role_id']])->one();
        $query['user']['role'] = $role['display_name'];
        $minzi = Minzu::find()->asArray()->where(['id'=>$query['user']['minzu_code']])->one();
        $query['user']['minzu'] = $minzi['name'];
        $education = Education::find()->where(['id'=>$query['user']['education_id']])->one();
        $query['user']['education'] = $education['name'];
        //研究方向
        $consider = Consider::find()->asArray()->where(['emp_number'=>$emp_number])->all();
        //教育
        $teach = Teach::find()->asArray()->where(['emp_number'=>$emp_number])->all();
        foreach ($teach as $k =>$v){
            $record = Education::find()->asArray()->where(['id'=>$v['record_id']])->one();
            $degree = Degree::find()->asArray()->where(['id'=>$v['degree_id']])->one();
            $teach[$k]['record'] = $record['name'];
            $teach[$k]['degree_id'] = $degree['name'];
        }
        //工作
        $user = new User();
        $work = $user->selrecored($emp_number);


        //科研
        $project = Project::find()->asArray()->where(['emp_number'=>$emp_number])->all();
        foreach ($project as $k => $v){
            $level = Level::find()->asArray()->where(['id'=>$v['level_id']])->one();
            $source = Source::find()->asArray()->where(['id'=>$v['source_id']])->one();
            $type = Type::find()->asArray()->where(['id'=>$v['type_id']])->one();
            $project[$k]['level']=$level['level'];
            $project[$k]['source']=$source['source'];
            $project[$k]['type']=$type['type'];
        }

        //论文
        $thesis = Thesis::find()->asArray()->where(['emp_number'=>$emp_number])->all();
        foreach ($thesis as $k=>$v){
            $article = Article::find()->where(['id'=>$v['article_type_id']])->one();
            $publication = Publication::find()->where(['id'=>$v['publication_type_id']])->one();
            $type = ThesisType::find()->where(['id'=>$v['thesis_type_id']])->one();
            $thesis[$k]['article'] = $article['article_type'];
            $thesis[$k]['publication'] = $publication['publication_type'];
            $thesis[$k]['type'] = $type['thesis_type'];
        }

        //会议
        $meeting = Meeting::find()->asArray()->where(['emp_number'=>$emp_number])->all();

        //获奖
        $honor = Emphonor::find()->asArray()->where(['emp_number'=>$emp_number])->all();

        //专利
        $patent = Patent::find()->asArray()->where(['emp_number'=>$emp_number])->all();
        foreach ($patent as $k => $v){
            if($v['patent_type'] == 0){
                $patent[$k]['leixing'] = '发明类';
            }else if ($v['patent_type'] == 1){
                $patent[$k]['leixing'] = '实用新型';
            }else{
                $patent[$k]['leixing'] = '其他类型';
            }
        }

        //兼职
        $hold = Hold::find()->asArray()->where(['emp_number'=>$emp_number])->all();

        //杂志
        $journal = Journal::find()->asArray()->where(['emp_number'=>$emp_number])->all();

        //专著
        $writings = Writings::find()->asArray()->where(['emp_number'=>$emp_number])->all();

        $query['consider'] = $consider;
        $query['teach'] = $teach;
        $query['work'] = $work;
        $query['project'] = $project;
        $query['thesis'] = $thesis;
        $query['meeting'] = $meeting;
        $query['honor'] = $honor;
        $query['patent'] = $patent;
        $query['hold'] = $hold;
        $query['journal'] = $journal;
        $query['writings'] = $writings;
        $query['ceshi'] = 112233;
        return $query;
    }

}
