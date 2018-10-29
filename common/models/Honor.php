<?php
namespace common\models;

use \common\models\base\Honor as BaseHonor;

class Honor extends BaseHonor
{
    public static function tableName()
    {
        return '{{hs_hr_honor}}';
    }

    public function addhonor($data){
        $honor = new Honor();
        $honor->awardee = $data['awardee'];
        $honor->honor_name = $data['honor_name'];
        $honor->item_name = $data['item_name'];
        $honor->done_unit = $data['done_unit'];
        $honor->grant_award = $data['grant_award'];
        $honor->reward = $data['reward'];
        $honor->reward_class = $data['reward_class'];
        $honor->reward_type = (int)$data['reward_type'];
        $honor->reward_number = $data['reward_number'];
        $honor->reward_time = $data['reward_time'];
        $honor->remark = $data['remark'];
        $honor->status = $data['status'];
        $query = $honor->save();
        if ($query){
            return true;
        }else{
            return false;
        }
    }

    public function selhonor($honor_id){
        $query = Honor::find()->where(['honor_id'=>$honor_id])->one();
        return $query;
    }


    public function honorlist($data){
        $page = $data['page'];
        $awardee = $data['awardee'];
        $honor_name = $data['honor_name'];
        $item_name = $data['item_name'];
        $done_unit = $data['done_unit'];
        $grant_award = $data['grant_award'];
        $reward = $data['reward'];
        $reward_class = $data['reward_class'];
        $reward_type = $data['reward_type'];
        $reward_number = $data['reward_number'];
        $reward_time = $data['reward_time'];
        $remark = $data['remark'];
        $status = $data['status'];
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
        $arr = $honor::find()->where($where)->offset($startrow)->limit($pagesize)->all();
        $query['data'] = $arr;
        $query['count'] = $this->pagecount($data);
        $query['pagesize'] = $pagesize;
        return $query;
    }

    public function pagecount($data){
        $page = $data['page'];
        $awardee = $data['awardee'];
        $honor_name = $data['honor_name'];
        $item_name = $data['item_name'];
        $done_unit = $data['done_unit'];
        $grant_award = $data['grant_award'];
        $reward = $data['reward'];
        $reward_class = $data['reward_class'];
        $reward_type = $data['reward_type'];
        $reward_number = $data['reward_number'];
        $reward_time = $data['reward_time'];
        $remark = $data['remark'];
        $status = $data['status'];
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
        $count = $honor::find()->where($where)->offset($startrow)->limit($pagesize)->count();
        return $count;
    }


    public function uphonor($data){
        $honor = new Honor();
        $honor = $honor::find()->where(['honor_id'=>$data['honor_id']])->one();
        $honor->awardee = $data['awardee'];
        $honor->honor_name = $data['honor_name'];
        $honor->item_name = $data['item_name'];
        $honor->done_unit = $data['done_unit'];
        $honor->grant_award = $data['grant_award'];
        $honor->reward = $data['reward'];
        $honor->reward_class = $data['reward_class'];
        $honor->reward_type = $data['reward_type'];
        $honor->reward_number = $data['reward_number'];
        $honor->reward_time = $data['reward_time'];
        $honor->remark = $data['remark'];
        $honor->status = $data['status'];
        $query = $honor->save();
        if ($query){
            return true;
        }else{
            return false;
        }
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
                $query = $attachment::deleteAll(['sort_id'=>$honor_id]);
                return $query;
            }
        }else{
            return $info;
        }
    }

    public function delattachment($emp_number,$eattach_id){
        $attachment = new Attachment();
        $url = $attachment::find()->select(['eattach_attachment_url'])->where(['emp_number'=>$emp_number,'screen'=>'honor','eattach_id'=>$eattach_id])->one();
        if($url != ''){
            unlink($url['eattach_attachment_url']);
        }
        $query = $attachment::deleteAll(['emp_number'=>$emp_number,'screen'=>'honor','eattach_id'=>$eattach_id]);
        return $query;
    }

    public function updatecomment($emp_number,$eattach_id,$details){
        $attachment = new Attachment();
        $data = $attachment::find()->where(['emp_number'=>$emp_number,'screen'=>'honor','eattach_id'=>$eattach_id])->one();
        $data->eattach_desc = $details;
        $query = $data->save();
        return $query;

    }


}
