<?php

namespace common\models;

use Yii;
use \common\models\base\Education as BaseEducation;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "hs_hr_education".
 */
class Education extends BaseEducation
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


    public function addeducation($data){
        $employee = new Employee();
        $emp = $employee::find()->asArray()->select(['emp_number'])->where(['emp_firstname'=>$data['student']])->one();
        if($emp == ''){
            return false;
        }
        $education = new Education();
        $education->emp_number = $data['emp_number'];
        $education->student_id = $emp['emp_number'];
        $education->type_id = $data['type_id'];
        $education->start_time = $data['start_time'];
        $education->end_time = $data['end_time'];
        $education->content = $data['content'];
        $query = $education->save();
        return $query;
    }


    public function educationtype(){
        $query = EducationType::find()->all();
        return $query;
    }


    public function list($data){
        $student = $data['student'];
        if($student != ''){
            $employee = new Employee();
            $emp = $employee::find()->asArray()->select(['emp_number'])->where(['emp_firstname'=>$student])->one();
            if($emp == ''){
                $student == '';
            }else{
                $student = $emp['emp_number'];
            }
        }else{
            $student == '';
        }

        //$page = $data['page'];
        $emp_number = $data['emp_number'];
        $student_id = $student;
        $type_id = $data['type_id'];
        $start_time = $data['start_time'];
        $end_time = $data['end_time'];
        $where = '1 = 1';
        if($emp_number != ''){
            $where .=" and a.emp_number = '$emp_number'";
        }
        if($student_id != ''){
            $where .=" and a.student_id = '$student_id'";
        }
        if($type_id != ''){
            $where .=" and a.type_id = '$type_id'";
        }
        if($start_time != ''){
            $where .=" and a.start_time > '$start_time'";
        }
        if($end_time != ''){
            $where .=" and a.end_time < '$end_time'";
        }


        /*$pagesize = 20;
        $startrow = ($page-1)*$pagesize;*/

        $query = (new yii\db\Query())
            ->select(['a.*','b.emp_firstname','c.type'])
            ->from('orangehrm_mysql.hs_hr_education a')
            ->leftJoin('orangehrm_mysql.hs_hr_employee b','a.emp_number=b.emp_number')
            ->leftJoin('orangehrm_mysql.hs_hr_education_type c','a.type_id=c.id')
           /* ->offset($startrow)
            ->limit($pagesize)*/
            ->where($where)
            ->all();

        $employee = new Employee();
        foreach ($query as $k => $v){
            $arr = $employee::find()->select(['emp_firstname'])->where(['emp_number'=>$v['student_id']])->one();
            $query[$k]['student'] = $arr['emp_firstname'];
        }


       /* $query[]['count'] = $this->pagecount($data);
        $query[]['pagesize'] = $pagesize;*/
        return $query;
    }


    public function pagecount($data){
        $student = $data['student'];
        if($student != ''){
            $employee = new Employee();
            $emp = $employee::find()->asArray()->select(['emp_number'])->where(['emp_firstname'=>$student])->one();
            if($emp == ''){
                $student == '';
            }else{
                $student = $emp['emp_number'];
            }
        }else{
            $student == '';
        }

        $page = $data['page'];
        $emp_number = $data['emp_number'];
        $student_id = $student;
        $type_id = $data['type_id'];
        $start_time = $data['start_time'];
        $end_time = $data['end_time'];
        $where = '1 = 1';
        if($emp_number != ''){
            $where .=" and a.emp_number = '$emp_number'";
        }
        if($student_id != ''){
            $where .=" and a.student_id = '$student_id'";
        }
        if($type_id != ''){
            $where .=" and a.type_id = '$type_id'";
        }
        if($start_time != ''){
            $where .=" and a.start_time > '$start_time'";
        }
        if($end_time != ''){
            $where .=" and a.end_time < '$end_time'";
        }

        $query = (new yii\db\Query())
            ->select(['a.*','b.emp_firstname','c.type'])
            ->from('orangehrm_mysql.hs_hr_education a')
            ->leftJoin('orangehrm_mysql.hs_hr_employee b','a.emp_number=b.emp_number')
            ->leftJoin('orangehrm_mysql.hs_hr_education_type c','a.type_id=c.id')
            ->where($where)
            ->count();

        return $query;
    }


    public function educationdel($id){
        $info = Education::deleteAll(['id'=>$id]);
        if($info){
            $attachment = new Attachment();
            $info = $attachment::find()->where(['sort_id'=>$id])->one();
            if ($info){
                foreach ($id as $k=>$v){
                    $url = $attachment::find()->select(['eattach_attachment_url'])->where(['sort_id'=>$v,'screen'=>'education'])->one();
                    if($url != ''){
                        unlink($url['eattach_attachment_url']);
                    }
                }
                $query = $attachment::deleteAll(['sort_id'=>$id]);
                return $query;
            }
        }else{
            return $info;
        }
    }



    public function attachmentdel($emp_number,$id){
        foreach ($id as $k=>$v){
            $arr[$k]['id'] = $v;
            $arr[$k]['emp_number'] = $emp_number[$k];
        }
        $attachment = new Attachment();
        foreach ($arr as $k=>$v){
            $url = $attachment::find()->select(['eattach_attachment_url'])->where(['eattach_id'=>$v['id'],'emp_number'=>$v['emp_number']])->one();
            if($url != ''){
                unlink($url['eattach_attachment_url']);
            }
            $query = $attachment::deleteAll(['eattach_id'=>$v['id'],'emp_number'=>$v['emp_number']]);
        }
        return $query;
    }

    public function scorelist($emp_number,$month){
        $where ="a.emp_number = '$emp_number'";
        $query = (new yii\db\Query())
            ->select('b.emp_firstname,a.student_id')
            ->from('orangehrm_mysql.hs_hr_education a')
            ->leftJoin('orangehrm_mysql.hs_hr_employee b','a.student_id = b.emp_number')
            ->where($where)
            ->all();
        foreach ($query as $k=>$v){
            $arr = EducationScore::find()->select('month,score')->where(['month'=>$month,'student_id'=>$v['student_id']])->one();
            $query[$k]['month'] = $arr['month'];
            $query[$k]['score'] = $arr['score'];
        }
        return $query;
    }


    public function score($data){
        $month = $data['month'];
        $student_id = $data['student_id'];
        $student_score = $data['score'];
        $score = new EducationScore();

        foreach ($student_id as $k =>$v){
            $arr = $score::find()->where(['month'=>$month,'student_id'=>$v])->one();
            if($arr == ''){
                $score->score = $student_score[$k];
                $score->student_id = $v;
                $score->month = $month;
                $query = $score->save();

            }else{
                $arr->score = $student_score[$k];
                $arr->month = $month;
                $query = $arr->save();
            }
        }
        return true;
    }

    public function educationsel($id){
        $arr = Education::find()->asArray()->where(['id'=>$id])->one();
        $teacher = Employee::find()->asArray()->select(['emp_firstname'])->where(['emp_number'=>$arr['emp_number']])->one();
        $student = Employee::find()->asArray()->select(['emp_firstname'])->where(['emp_number'=>$arr['student_id']])->one();
        $data = EducationScore::find()->asArray()->select('month,score')->where(['student_id'=>$arr['student_id']])->all();
        $type = EducationType::find()->asArray()->select('type')->where(['id'=>$arr['type_id']])->one();
        $atta = Attachment::find()->select(['emp_number','eattach_id','eattach_desc','eattach_filename','eattach_filename','attached_by_name'])->where(['sort_id'=>$id,'screen'=>'edcuation'])->all();
        $arr['teacher'] = $teacher['emp_firstname'];
        $arr['student'] = $student['emp_firstname'];
        $arr['type'] = $type['type'];
        $arr['data'] = $data;
        $arr['atta'] = $atta;
        return $arr;
    }

}
