<?php

namespace common\models\thesis;

use common\models\attachment\Attachment;
use Yii;
use \common\models\thesis\base\Thesis as BaseThesis;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "hs_hr_thesis".
 */
class Thesis extends BaseThesis
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


    public function thesisadd($data){
        $emp_number = $data['emp_number'];
        $thesis_name = isset($data['thesis_name']) ? $data['thesis_name']:'';
        $author = isset($data['author']) ? $data['author']:'';
        $ranking = isset($data['ranking']) ? $data['ranking']:'';
        $first_author_type = isset($data['first_author_type']) ? $data['first_author_type']:'';
        $first_author = isset($data['first_author']) ? $data['first_author']:'';
        $first_author_unit = isset($data['first_author_unit']) ? $data['first_author_unit']:'';
        $corresponding_author_type = isset($data['corresponding_author_type']) ? $data['corresponding_author_type']:'';
        $corresponding_author = isset($data['corresponding_author']) ? $data['corresponding_author']:'';
        $corresponding_author_unit = isset($data['corresponding_author_unit']) ? $data['corresponding_author_unit']:'';
        $publication = isset($data['publication']) ? $data['publication']:'';
        $volume = isset($data['volume']) ? $data['volume']:'';
        $ISSN = isset($data['issn']) ? $data['issn']:'';
        $influence = isset($data['influence']) ? $data['influence']:'';
        $url = isset($data['url']) ? $data['url']:'';
        $is_include = isset($data['is_include']) ? $data['is_include']:'';
        $thesis_type_id = isset($data['thesis_type_id']) ? $data['thesis_type_id']:'';
        $article_type_id = isset($data['article_type_id']) ? $data['article_type_id']:'';
        $publication_type_id = isset($data['publication_type_id']) ? $data['publication_type_id']:'';
        $remarks = isset($data['remarks']) ? $data['remarks']:'';


        $thesis = new Thesis();
        $thesis->emp_number = $emp_number;
        $thesis->thesis_name = $thesis_name;
        $thesis->author = $author;
        $thesis->ranking = $ranking;
        $thesis->first_author_type = $first_author_type;
        $thesis->first_author = $first_author;
        $thesis->first_author_unit = $first_author_unit;
        $thesis->corresponding_author_type = $corresponding_author_type;
        $thesis->corresponding_author = $corresponding_author;
        $thesis->corresponding_author_unit = $corresponding_author_unit;
        $thesis->publication = $publication;
        $thesis->volume = $volume;
        $thesis->issn = $ISSN;
        $thesis->influence = $influence;
        $thesis->url = $url;
        $thesis->is_include = $is_include;
        $thesis->thesis_type_id = $thesis_type_id;
        $thesis->article_type_id = $article_type_id;
        $thesis->publication_type_id = $publication_type_id;
        $thesis->remarks = $remarks;
        $query = $thesis->save();
        return $query;
    }

    public function thesisupdate($data){
        $id = $data['id'];
        $thesis_name = isset($data['thesis_name']) ? $data['thesis_name']:'';
        $author = isset($data['author']) ? $data['author']:'';
        $ranking = isset($data['ranking']) ? $data['ranking']:'';
        $first_author_type = isset($data['first_author_type']) ? $data['first_author_type']:'';
        $first_author = isset($data['first_author']) ? $data['first_author']:'';
        $first_author_unit = isset($data['first_author_unit']) ? $data['first_author_unit']:'';
        $corresponding_author_type = isset($data['corresponding_author_type']) ? $data['corresponding_author_type']:'';
        $corresponding_author = isset($data['corresponding_author']) ? $data['corresponding_author']:'';
        $corresponding_author_unit = isset($data['corresponding_author_unit']) ? $data['corresponding_author_unit']:'';
        $publication = isset($data['publication']) ? $data['publication']:'';
        $volume = isset($data['volume']) ? $data['volume']:'';
        $ISSN = isset($data['issn']) ? $data['issn']:'';
        $influence = isset($data['influence']) ? $data['influence']:'';
        $url = isset($data['url']) ? $data['url']:'';
        $is_include = isset($data['is_include']) ? $data['is_include']:'';
        $thesis_type_id = isset($data['thesis_type_id']) ? $data['thesis_type_id']:'';
        $article_type_id = isset($data['article_type_id']) ? $data['article_type_id']:'';
        $publication_type_id = isset($data['publication_type_id']) ? $data['publication_type_id']:'';
        $remarks = isset($data['remarks']) ? $data['remarks']:'';


        $thesis = Thesis::find()->where(['id'=>$id])->one();
        $thesis->thesis_name = $thesis_name;
        $thesis->author = $author;
        $thesis->ranking = $ranking;
        $thesis->first_author_type = $first_author_type;
        $thesis->first_author = $first_author;
        $thesis->first_author_unit = $first_author_unit;
        $thesis->corresponding_author_type = $corresponding_author_type;
        $thesis->corresponding_author = $corresponding_author;
        $thesis->corresponding_author_unit = $corresponding_author_unit;
        $thesis->publication = $publication;
        $thesis->volume = $volume;
        $thesis->issn = $ISSN;
        $thesis->influence = $influence;
        $thesis->url = $url;
        $thesis->is_include = $is_include;
        $thesis->thesis_type_id = $thesis_type_id;
        $thesis->article_type_id = $article_type_id;
        $thesis->publication_type_id = $publication_type_id;
        $thesis->remarks = $remarks;
        $query = $thesis->save();
        return $query;
    }


    public function thesislist($data){
        $emp_number = $data['emp_number'];
        $thesis_name = isset($data['thesis_name']) ? $data['thesis_name']:'';
        $author = isset($data['author']) ? $data['author']:'';
        $ranking = isset($data['ranking']) ? $data['ranking']:'';
        $first_author_type = isset($data['first_author_type']) ? $data['first_author_type']:'';
        $first_author = isset($data['first_author']) ? $data['first_author']:'';
        $first_author_unit = isset($data['first_author_unit']) ? $data['first_author_unit']:'';
        $corresponding_author_type = isset($data['corresponding_author_type']) ? $data['corresponding_author_type']:'';
        $corresponding_author = isset($data['corresponding_author']) ? $data['corresponding_author']:'';
        $corresponding_author_unit = isset($data['corresponding_author_unit']) ? $data['corresponding_author_unit']:'';
        $publication = isset($data['publication']) ? $data['publication']:'';
        $volume = isset($data['volume']) ? $data['volume']:'';
        $ISSN = isset($data['issn']) ? $data['issn']:'';
        $influence = isset($data['influence']) ? $data['influence']:'';
        $url = isset($data['url']) ? $data['url']:'';
        $is_include = isset($data['is_include']) ? $data['is_include']:'';
        $thesis_type_id = isset($data['thesis_type_id']) ? $data['thesis_type_id']:'';
        $article_type_id = isset($data['article_type_id']) ? $data['article_type_id']:'';
        $publication_type_id = isset($data['publication_type_id']) ? $data['publication_type_id']:'';
        $remarks = isset($data['remarks']) ? $data['remarks']:'';


        $where ="emp_number = '$emp_number'";
        if($thesis_name != ''){
            $where .=" and thesis_name = '$thesis_name'";
        }
        if($author != ''){
            $where .=" and author = '$author'";
        }
        if($ranking != ''){
            $where .=" and ranking = '$ranking'";
        }
        if($first_author_type != ''){
            $where .=" and first_author_type = '$first_author_type'";
        }
        if($first_author != ''){
            $where .=" and first_author = '$first_author'";
        }
        if($first_author_unit != ''){
            $where .=" and first_author_unit = '$first_author_unit'";
        }
        if($corresponding_author_type != ''){
            $where .=" and corresponding_author_type = '$corresponding_author_type'";
        }
        if($corresponding_author != ''){
            $where .=" and corresponding_author = '$corresponding_author'";
        }
        if($corresponding_author_unit != ''){
            $where .=" and corresponding_author_unit = '$corresponding_author_unit'";
        }
        if($publication != ''){
            $where .=" and publication = '$publication'";
        }
        if($volume != ''){
            $where .=" and volume = '$volume'";
        }
        if($ISSN != ''){
            $where .=" and issn = '$ISSN'";
        }
        if($influence != ''){
            $where .=" and influence = '$influence'";
        }
        if($url != ''){
            $where .=" and url = '$url'";
        }
        if($is_include != ''){
            $where .=" and is_include = '$is_include'";
        }
        if($thesis_type_id != ''){
            $where .=" and thesis_type_id = '$thesis_type_id'";
        }
        if($article_type_id != ''){
            $where .=" and article_type_id = '$article_type_id'";
        }
        if($publication_type_id != ''){
            $where .=" and publication_type_id = '$publication_type_id'";
        }
        if($remarks != ''){
            $where .=" and remarks = '$remarks'";
        }
        $thesis = Thesis::find()->asArray()->where($where)->all();
        foreach ($thesis as $k => $v){
            $type = ThesisType::find()->asArray()->select('thesis_type')->where(['id'=>$v['thesis_type_id']])->one();
            $article = Article::find()->asArray()->select('article_type')->where(['id'=>$v['article_type_id']])->one();
            $publication = Publication::find()->asArray()->select('publication_type')->where(['id'=>$v['publication_type_id']])->one();
            $thesis[$k]['thesis_type'] = $type['thesis_type'];
            $thesis[$k]['article_type'] = $article['article_type'];
            $thesis[$k]['publication_type'] = $publication['publication_type'];
        }
        return $thesis;

    }


    public function sel($id){
        $query['thesis'] = Thesis::find()->where(['id'=>$id])->one();
        $query['atta'] = Attachment::find()->where(['sort_id'=>$id,'screen'=>'thesis'])->all();
        return $query;
    }


    public function attadel($eattach_id,$emp_number){
        $attachment = new Attachment();
        $url = $attachment::find()->select(['eattach_attachment_url'])->where(['emp_number'=>$emp_number,'screen'=>'thesis','eattach_id'=>$eattach_id])->all();
        if($url != ''){
            foreach ($url as $k => $v){
                unlink($v['eattach_attachment_url']);
            }
        }
        $query = $attachment::deleteAll(['emp_number'=>$emp_number,'screen'=>'thesis','eattach_id'=>$eattach_id]);
        return $query;
    }

    public function thesisdel($id){
        $thesis = Thesis::deleteAll(['id'=>$id]);
        $attachment = new Attachment();
        $arr = $attachment::find()->where(['sort_id'=>$id,'screen'=>'thesis'])->all();
        if ($arr){
            foreach ($id as $k=>$v){
                $url = $attachment::find()->select(['eattach_attachment_url'])->where(['sort_id'=>$v,'screen'=>'thesis'])->one();
                if($url != ''){
                    $delurl = '/data/wwwroot/uploadfile/'.$url['eattach_attachment_url'];
                    unlink($delurl);
                }

            }
            $query = $attachment::deleteAll(['sort_id'=>$id,'screen'=>'thesis']);
            return $query;
        }
    }




}
