<?php

namespace common\models\folder;

use Yii;
use \common\models\folder\base\Folder as BaseFolder;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "hs_hr_folder".
 */
class Folder extends BaseFolder
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

    public function folderlist($folder_id,$customerId,$page){

        $pagesize = 20;
        $startrow = ($page-1)*$pagesize;

        $arr = FileList::find()->asArray()->select(['file_id','is_folder','file_name','file_real_name','file_url','file_size','file_time'])->where(['fu_id'=>$folder_id,'status'=>0])->offset($startrow)->limit($pagesize)->orderBy('is_folder DESC');
        $list = $arr->all();
        $count = $arr->count();
        if($folder_id == 0){
            $array['level_id'] = '';
            $path = array('全部文件');
        }else{
            $array = Folder::find()->select(['url','level_id'])->where(['id'=>$folder_id])->one();
            $path = explode("/",$array['url']);
            array_unshift($path,'全部文件');
            //$path[0] = '全部文件';
        }
        foreach ($list as $k =>$v){
            //$list[$k]['url'] = env('STORAGE_HOST_INFO').$v['file_url'];
            if($v['is_folder'] == 0){
                $list[$k]['url'] = env('STORAGE_HOST_INFO').'netdisk/'.$customerId.'/'.$v['file_name'];
            }
            if($v['is_folder'] == 1){
                $list[$k]['is_folder'] = true;
            }else{
                $list[$k]['is_folder'] = false;
            }
        }


        $info['level_id'] =  $array['level_id'];
        $info['count'] =  (int)$count;
        $info['pagesize'] = (int)$pagesize;
        $info['page'] = (int)$page;
        $info['data'] = $list;
        $info['path'] = $path;
        return $info;
    }




    public function filedel($data){
        foreach ($data as $k=>$v){
            if($v['is_folder'] == 1){
                $where = "file_id = '$v[file_id]' and is_folder = 1";
                $filelist = FileList::find()->select(['file_url'])->where($where)->one();
                $where1 = "file_url like '$filelist[file_url]%'";
                $del_data = FileList::find()->select(['id'])->where($where1)->all();
                FileList::updateAll(['status'=>1],['id'=>$del_data]);
            }else{
                FileList::updateAll(['status'=>1],['file_id'=>$v['file_id'],'is_folder'=>0]);
            }
        }

        return true;
    }
}
