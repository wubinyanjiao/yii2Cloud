<?php

namespace common\models\curriculum;

use common\models\employee\Employee;
use Yii;
use \common\models\curriculum\base\Curriculum as BaseCurriculum;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "ohrm_curriculum".
 */
class Curriculum extends BaseCurriculum
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


    /*
    * 课程列表 和查询
    * **/
    public function serch($name,$trainer,$cu_type,$page,$work_station,$username){

        $where = '1=1';
        if($username != 'admin'){
            $where .= " and work_station = '$work_station' ";
        }
        if($name != '' ){
            $where .= " and name like '%$name%' ";
        }
        if($trainer != ''){
            $where .=" and trainer = '$trainer'";
        }
        if($cu_type != ''){
            $where .= " and cu_type = '$cu_type'";
        }
        $pagesize = 20;
        $startrow = ($page-1)*$pagesize;
        $query = Curriculum::find()->select(['id','name','is_start','cu_type','trainer'])->offset($startrow)
            ->limit($pagesize)->where($where)->all();


        $slecount = Curriculum::find()->where($where)->count();
        //$slecount = $this->count($name,$trainer,$cu_type,$page);



        $model['count'] =  (int)$slecount;
        $model['pagesize'] = (int)$pagesize;
        $model['result'] = $query;
        return $model;
    }


    /*
     * 添加课程
     *
     * **/
    public $documentPath = '/data/wwwroot/uploadfile/curriculumfile/';//图片上传路径
    public function addcurriculum($data,$workstation){
        //添加课程信息
        $curriculum = new Curriculum();
        $curriculum -> name = isset($data['curriculum']['name']) ? $data['curriculum']['name'] : '';
        $curriculum -> cu_type = isset($data['curriculum']['cu_type']) ? $data['curriculum']['cu_type'] : '';
        $curriculum -> trainer = isset($data['curriculum']['trainer']) ? $data['curriculum']['trainer'] : '';
        $curriculum -> credit = isset($data['curriculum']['credit']) ? $data['curriculum']['credit'] : '';
        $curriculum -> pass_score = isset($data['curriculum']['pass_score']) ? $data['curriculum']['pass_score'] : '';
        $curriculum -> interval_jian = isset($data['curriculum']['interval_jian']) ? $data['curriculum']['interval_jian'] : '';
        $curriculum -> answer_time = isset($data['curriculum']['answer_time']) ? $data['curriculum']['answer_time'] : '';
        $curriculum -> cu_describe = isset($data['curriculum']['cu_describe']) ? $data['curriculum']['cu_describe'] : '';
        //$curriculum -> file_id = $file_id;
        $curriculum -> videourl = isset($data['curriculum']['videourl']) ? $data['curriculum']['videourl'] : '';
        $curriculum -> work_station = $workstation;
        $curriculum -> create_time = date('Y-m-d H:i:s');
        $info = $curriculum->save();
        if ($info){
            $id = $curriculum->attributes['id'];//获取添加后的 课程id

            //课程文件入库
            $file = isset($data['curriculum']['file']) ? $data['curriculum']['file'] :'';
            $isfile = isset($data['curriculum']['file'][0]['title']) ? $data['curriculum']['file'][0]['title'] :'';
            if($isfile){
                foreach ($file as $k=>$v){
                    $image = $v['title'];
                    $imageName = $v['name'];
                    if (strstr($image,",")){
                        $image = explode(',',$image);
                        $image = $image[1];
                    }

                    $path = "/data/wwwroot/uploadfile/curriculumfile";
                    if (!is_dir($path)){ //判断目录是否存在 不存在就创建
                        mkdir($path,0777,true);
                    }
                    $imageSrc=  'curriculumfile'."/". $imageName;  //图片名字

                    $r = file_put_contents($path.'/'.$imageName, base64_decode($image));//返回的是字节数
                    if (!$r) {
                        return (['data'=>null,"code"=>1,"msg"=>"图片生成失败"]);
                    }else{
                        $curriculumfile = new CurriculumFile();
                        $curriculumfile -> cur_name = $imageName;
                        $curriculumfile -> cur_url = $imageSrc;
                        $curriculumfile -> create_time= date('y-m-d h:i:s',time());
                        $curriculumfile -> cur_id= $id;
                        $curriculumfile->save();
                        $file_id[] = $curriculumfile->attributes['id'];
                    }
                }
            }

            //添加试题
            $questions = isset($data['questions']) ? $data['questions'] :'';
            if($questions != ''){
                foreach ($questions as $k => $v){

                    $curriculumquestions = new CurriculumQuestions();
                    $curriculumquestions->name = isset($v['name']) ? $v['name'] : '';
                    $curriculumquestions->fraction = isset($v['fraction']) ? $v['fraction'] : '';
                    $curriculumquestions->question_type = isset($v['question_type']) ? $v['question_type'] : '';
                    $curriculumquestions->firstuplod_id = isset($v['firstuplod_id']) ? $v['firstuplod_id'] : '';
                    $curriculumquestions->create_time = date("Y-m-d H:i:s");;
                    $info = $curriculumquestions->save();
                    $questions_id = $curriculumquestions->attributes['id']; //问题id

                    //将试题与课件入关联表
                    $curriculumproblems = new CurriculumProblems();
                    $curriculumproblems -> curriculum_id = $id;
                    $curriculumproblems -> question_id = $questions_id;
                    $curriculumproblems ->save();

                    //普通试题入库
                    if($questions[$k]['question_type'] == 1 || $questions[$k]['question_type'] == 2){

                        if($info){
                            //添加试题内容
                            $answer = $data['questions'][$k]['answer'];
                            //return $answer;
                            foreach ($answer as $key => $val){
                                //return $val;
                                $curriculumanswer = new CurriculumAnswer();
                                $curriculumanswer -> question_id = $questions_id;
                                $curriculumanswer -> option_answer = $val['option_answer'];
                                $curriculumanswer -> default_answer= isset($val['default_answer']) ? $val['default_answer']: '' ;
                                $curriculumanswer->save();
                            }
                        }else{
                            return false;
                        }
                        //图片试题上传并入库
                    }else if ($questions[$k]['question_type'] == 3 || $questions[$k]['question_type'] == 4){

                        $answer = $data['questions'][$k]['answer'];
                        foreach ($answer as $item => $value){
                            $image = $value['option_answer'];
                            $imageName = "25220_".date("His",time())."_".rand(1111,9999).'.png';
                            if (strstr($image,",")){
                                $image = explode(',',$image);
                                $image = $image[1];
                            }

                            $path = "/data/wwwroot/uploadfile/curriculumfile";
                            if (!is_dir($path)){ //判断目录是否存在 不存在就创建
                                mkdir($path,0777,true);
                            }
                            $imageSrc=  'curriculumfile'."/". $imageName;  //图片名字

                            $r = file_put_contents($path.'/'.$imageName, base64_decode($image));//返回的是字节数
                            if (!$r) {
                                return (['data'=>null,"code"=>1,"msg"=>"图片生成失败"]);
                            }else{
                                //图片地址入库
                                $curriculumanswer = new CurriculumAnswer();
                                $curriculumanswer -> question_id = $questions_id;
                                $curriculumanswer -> option_answer = $imageSrc;
                                $curriculumanswer -> default_answer= isset($value['default_answer']) ? $value['default_answer'] : '';
                                $curriculumanswer->save();
                            }
                        }

                    }

                }
            }

        }else{
            return false;
        }

        return true;

    }



    /*
     * 修改课程
     * **/
    public function upcurriculum($data,$workstation){
        //return $data['questions'];
        //修改课程信息
        $curriculum = new Curriculum();
        $curriculum = $curriculum::find()->where(['id'=>$data['curriculum']['id']])->one();
        $curriculum -> name = isset($data['curriculum']['name']) ? $data['curriculum']['name'] : '';
        $curriculum -> cu_type = isset($data['curriculum']['cu_type']) ? $data['curriculum']['cu_type'] : '';
        $curriculum -> trainer = isset($data['curriculum']['trainer']) ? $data['curriculum']['trainer'] : '';
        $curriculum -> credit = isset($data['curriculum']['credit']) ? $data['curriculum']['credit'] : '';
        $curriculum -> pass_score = isset($data['curriculum']['pass_score']) ? $data['curriculum']['pass_score'] : '';
        $curriculum -> interval_jian = isset($data['curriculum']['interval_jian']) ? $data['curriculum']['interval_jian'] : '';
        $curriculum -> answer_time = isset($data['curriculum']['answer_time']) ? $data['curriculum']['answer_time'] : '';
        $curriculum -> cu_describe = isset($data['curriculum']['cu_describe']) ? $data['curriculum']['cu_describe'] : '';
        //$curriculum -> file_id = isset($data['curriculum']['file_id']) ? $data['curriculum']['file_id'] : '';
        $curriculum -> videourl = isset($data['curriculum']['videourl']) ? $data['curriculum']['videourl'] : '';
        $info = $curriculum->save();
        if($info){

            //课程文件入库
            $file = isset($data['curriculum']['file']) ? $data['curriculum']['file'] :'';
            $isfile = isset($data['curriculum']['file'][0]['title']) ? $data['curriculum']['file'][0]['title'] :'';
            if($isfile){
                foreach ($file as $k=>$v){
                    $image = $v['title'];
                    $imageName = $v['name'];
                    if (strstr($image,",")){
                        $image = explode(',',$image);
                        $image = $image[1];
                    }

                    $path = "/data/wwwroot/uploadfile/curriculumfile";
                    if (!is_dir($path)){ //判断目录是否存在 不存在就创建
                        mkdir($path,0777,true);
                    }
                    $imageSrc=  'curriculumfile'."/". $imageName;  //图片名字

                    $r = file_put_contents($path.'/'.$imageName, base64_decode($image));//返回的是字节数
                    if (!$r) {
                        return (['data'=>null,"code"=>1,"msg"=>"图片生成失败"]);
                    }else{
                        $curriculumfile = new CurriculumFile();
                        $curriculumfile -> cur_name = $imageName;
                        $curriculumfile -> cur_url = $imageSrc;
                        $curriculumfile -> create_time= date('y-m-d h:i:s',time());
                        $curriculumfile -> cur_id= $data['curriculum']['id'];
                        $curriculumfile->save();
                        $file_id[] = $curriculumfile->attributes['id'];
                    }
                }
            }
            $questions = isset($data['questions']) ? $data['questions'] :'';

            if($questions != ''){
                foreach ($questions as $k => $v){
                    $curriculumquestions = new CurriculumQuestions();
                    $v['question_id'] = isset($v['question_id'])?$v['question_id']:0;
                    if($v['question_id'] != 0){
                        $curriculumquestions = $curriculumquestions::find()->where(['id'=>$v['question_id']])->one();
                        $curriculumquestions->name = isset($v['name']) ? $v['name'] : '';
                        $curriculumquestions->fraction = isset($v['fraction']) ? $v['fraction'] : '';
                        $curriculumquestions->question_type = isset($v['question_type']) ? $v['question_type'] : '';
                        $curriculumquestions->firstuplod_id = isset($v['firstuplod_id']) ? $v['firstuplod_id'] : '';
                        $curriculumquestions->create_time = date("Y-m-d H:i:s");
                        $curriculumquestions->save();
                        $questions_id = $v['question_id'];
                    }else{
                        $curriculumquestions->name = isset($v['name']) ? $v['name'] : '';
                        $curriculumquestions->fraction = isset($v['fraction']) ? $v['fraction'] : '';
                        $curriculumquestions->question_type = isset($v['question_type']) ? $v['question_type'] : '';
                        $curriculumquestions->firstuplod_id = isset($v['firstuplod_id']) ? $v['firstuplod_id'] : '';
                        $curriculumquestions->create_time = date("Y-m-d H:i:s");
                        $info = $curriculumquestions->save();
                        $questions_id = $curriculumquestions->attributes['id']; //问题id

                        //将试题与课件入关联表
                        $curriculumproblems = new CurriculumProblems();
                        $curriculumproblems -> curriculum_id = $data['curriculum']['id'];
                        $curriculumproblems -> question_id = $questions_id;
                        $curriculumproblems ->save();
                    }



                    //普通试题入库
                    if($questions[$k]['question_type'] == 1 || $questions[$k]['question_type'] == 2){

                        if($info){
                            //添加试题内容
                            $answer = $data['questions'][$k]['answer'];

                            //return $answer;
                            foreach ($answer as $key => $val){
                                $curriculumanswer = new CurriculumAnswer();
                                $val['id'] = isset($val['id'])?$val['id']:0;
                                if($val['id'] == 0){
                                    $curriculumanswer -> question_id = $questions_id;
                                    $curriculumanswer -> option_answer = $val['option_answer'];
                                    $curriculumanswer -> default_answer= isset($val['default_answer']) ? $val['default_answer']: '' ;
                                    $curriculumanswer->save();
                                }else{
                                    $curriculumanswer = $curriculumanswer::find()->where(['id'=>$val['id']])->one();
                                    $curriculumanswer -> question_id = $questions_id;
                                    $curriculumanswer -> option_answer = $val['option_answer'];
                                    $curriculumanswer -> default_answer= isset($val['default_answer']) ? $val['default_answer']: '' ;
                                    $curriculumanswer->save();
                                }

                            }
                        }else{

                            return false;
                        }
                        //图片试题上传并入库
                    }else if ($questions[$k]['question_type'] == 3 || $questions[$k]['question_type'] == 4){
                        $answer = $data['questions'][$k]['answer'];
                        foreach ($answer as $item => $value){
                            $image = $value['option_answer'];

                            if(strstr($image, 'base64')){
                                $imageName = "25220_".date("His",time())."_".rand(1111,9999).'.png';
                                if (strstr($image,",")){
                                    $image = explode(',',$image);
                                    $image = $image[1];
                                }

                                $path = "/data/wwwroot/uploadfile/curriculumfile";
                                if (!is_dir($path)){ //判断目录是否存在 不存在就创建
                                    mkdir($path,0777,true);
                                }
                                $imageSrc=  'curriculumfile'."/". $imageName;  //图片名字

                                $r = file_put_contents($path.'/'.$imageName, base64_decode($image));//返回的是字节数
                                if (!$r) {
                                    return (['data'=>null,"code"=>1,"msg"=>"图片生成失败"]);
                                }
                            }else{
                                $imageSrc = $value['option_answer'];
                            }

                                //图片地址入库
                                $curriculumanswer = new CurriculumAnswer();
                                $value['id'] = isset($value['id'])?$value['id']:0;
                                if($value['id'] == 0){
                                    $curriculumanswer -> question_id = $questions_id;
                                    $curriculumanswer -> option_answer = $imageSrc;
                                    $curriculumanswer -> default_answer= isset($value['default_answer']) ? $value['default_answer'] : '';
                                    $curriculumanswer->save();
                                }else{
                                    $curriculumanswer = $curriculumanswer::find()->where(['id'=>$value['id']])->one();
                                    $curriculumanswer -> question_id = $questions_id;
                                    $curriculumanswer -> option_answer = $imageSrc;
                                    $curriculumanswer -> default_answer= isset($value['default_answer']) ? $value['default_answer'] : '';
                                    $curriculumanswer->save();
                                }
                        }

                    }

                }
            }

       }else{
            return false;
        }

        return true;
    }



    /*
     * 开始考试列表
     *
     * **/
    public function begin($emp_number,$name,$trainer,$cu_type,$page,$work_station){

        $where = "work_station = '$work_station'";
        if($name != '' ){
            $where .= " and name like '%$name%' ";
        }
        if($trainer != ''){
            $where .=" and trainer = '$trainer'";
        }
        if($cu_type != ''){
            $where .= " and cu_type = '$cu_type'";
        }

        $pagesize = 20;
        $startrow = ($page-1)*$pagesize;

        $curriculum = new Curriculum();
        $curriculumemployee = new CurriculumEmployee();
        $data = $curriculum::find()->select('id,name,trainer,cu_type,pass_score,interval_jian,create_time')->asArray()->offset($startrow)
            ->limit($pagesize)->where($where)->all();
        $slecount = $this->begincount($emp_number,$name,$trainer,$cu_type,$page);

        foreach ($data as $k => $v){

            $arr = $curriculumemployee::find()->asArray()->where(['cur_id'=>$v['id'],'emp_number'=>$emp_number])->one();

            if($arr != ''){
                if($arr['emp_credit'] < $v['pass_score']){
                    $data[$k]['is_exam'] = '0';
                    $now = time();
                    $ctime = strtotime($v['create_time'])+(60*60*$v['interval_jian']);
                    if($now < $ctime){
                        $data[$k]['is_interval'] = '1';
                    }else{
                        $data[$k]['is_interval'] = '0';
                    }
                }else{
                    $data[$k]['is_exam'] = '1';
                    $data[$k]['is_interval'] = '0';
                }
            }else{
                $data[$k]['is_interval'] = '0';
                $data[$k]['is_exam'] = '0';
            }


            $employee = $curriculumemployee::find()->asArray()->where(['emp_number'=>$emp_number,'cur_id' =>$v['id'] ])->one();
            if($employee){
                $data[$k]['is_train'] = '已培训';
                $data[$k]['emp_credit'] = $employee['emp_credit'];
            }else{
                $data[$k]['is_train'] = '未培训';
                $data[$k]['emp_credit'] = $employee['emp_credit'];
            }
        }


        $model['count'] =  (int)$slecount;
        $model['pagesize'] = (int)$pagesize;
        $model['result'] = $data;
        return $model;

    }

    public function begincount($emp_number,$name,$trainer,$cu_type,$page){
        $where = '1=1';
        if($name != '' ){
            $where .= " and name = '$name' ";
        }
        if($trainer != ''){
            $where .=" and trainer = '$trainer'";
        }
        if($cu_type != ''){
            $where .= " and cu_type = '$cu_type'";
        }

        $pagesize = 20;
        $startrow = ($page-1)*$pagesize;

        $curriculum = new Curriculum();
        $curriculumemployee = new CurriculumEmployee();
        $data = $curriculum::find()->select('id,name,trainer,cu_type')->asArray()->offset($startrow)
            ->limit($pagesize)->where($where)->count();
        return $data;
    }

    /*
     * 开始考试
     * **/
    public function begincur($cur_id,$emp_number){
        $curriculum = new Curriculum();
        $data = $curriculum::find()->asArray()->where(['id'=>$cur_id])->one();
        $file = new CurriculumFile();
        $data['file'] = $file::find()->select(['cur_name','cur_url'])->where(['id'=>$data['file_id']])->all();

        $curriculumemployee = new CurriculumEmployee();
        $arr = $curriculumemployee::find()->asArray()->where(['cur_id'=>$cur_id,'emp_number'=>$emp_number])->one();
        if($arr != ''){
            if($arr['emp_credit'] < $data['pass_score']){
                $now = time();
                $ctime = strtotime($data['create_time'])+(60*60*$data['interval_jian']);
                if($now < $ctime){

                    return 4;
                }
            }else{
                return 3;
            }
        }

        $question = (new yii\db\Query())
            ->select('*')
            ->from('orangehrm_mysql.ohrm_curriculum_problems a')
            ->leftJoin('orangehrm_mysql.ohrm_curriculum_questions b','b.id = a.question_id ')
            ->where(['curriculum_id'=>$cur_id])
            ->all();

        $answer = new CurriculumAnswer();
        foreach ($question as $k =>$v){
            $question[$k]['answer'] = $answer::find()->select(['id','option_answer','default_answer'])->where(['question_id'=>$v['question_id']])->all();
        }

        $data['file'] = CurriculumFile::find()->asArray()->where(['cur_id'=>$cur_id])->all();

        $arr['curriculum'] = $data;
        $arr['answer'] = $question;




        return $arr;

    }

    /*
     * 删除课程
     * **/
    public function delcur($id){
        //删除课程
        $curriculum = new Curriculum();
        $curriculum::deleteAll(['id'=>$id]);
        //查找相关的问题答案
        $curriculum_problems = new CurriculumProblems();
        $arr = $curriculum_problems::find()->asArray()->select(['question_id'])->where(['curriculum_id'=> $id])->all();
        $num = array_column($arr,'question_id');
        //删除问题和答案
        $curriculum_questions = new CurriculumQuestions();
        $curriculum_questions::deleteAll(['id'=>$num]);
        $curriculum_answer = new CurriculumAnswer();
        $curriculum_answer::deleteAll(['question_id'=>$num]);
        //删除员工答题记录
        $curriculum_empanswer = new CurriculumEmpanswer();
        $curriculum_empanswer::deleteAll(['cur_id'=>$id]);
        $curriculum_employee = new CurriculumEmployee();
        $curriculum_employee::deleteAll(['cur_id'=>$id]);


        return true;

    }

    /*
     * 查找修改课程
     * **/
    public function selcur($id){
        $curriculum = new Curriculum();
        $data = $curriculum::find()->asArray()->where(['id'=>$id])->one();
        $question = (new yii\db\Query())
            ->select('*')
            ->from('orangehrm_mysql.ohrm_curriculum_problems a')
            ->leftJoin('orangehrm_mysql.ohrm_curriculum_questions b','b.id = a.question_id ')
            ->where(['curriculum_id'=>$id])
            ->all();
        $answer = new CurriculumAnswer();
        foreach ($question as $k =>$v){

            $question[$k]['answer'] = $answer::find()->select(['id','option_answer','default_answer'])->where(['question_id'=>$v['question_id']])->all();
            foreach ( $question[$k]['answer'] as $key => $value){
                if($value['default_answer'] == 1){
                    $question[$k]['singleChoiceQuestionChecked'][] = $key;
                }
            }
        }
        $curriculumfile = new CurriculumFile();
        $data['file'] = $curriculumfile::find()->select(['id','cur_name','cur_url'])->where(['cur_id'=>$id])->all();
        $query['curriculum'] = $data;
        $query['question'] = $question;
        return $query;
    }






    /*
     *考试提交
     ***/
    public function subcur($data){
        $file = isset($data['file']) ? $data['file'] :'';
        if($file){
            if($file[0]['title'] != ''){
                foreach ($file as $k=>$v){
                    $image = $v['title'];//
                    $imageName = $v['name'];
                    if (strstr($image,",")){
                        $image = explode(',',$image);
                        $image = $image[1];
                    }

                    $path = "/data/wwwroot/uploadfile/curriculumfile";
                    if (!is_dir($path)){ //判断目录是否存在 不存在就创建
                        mkdir($path,0777,true);
                    }
                    $imageSrc=  'curriculumfile'."/". $imageName;  //图片名字

                    $r = file_put_contents($path.'/'.$imageName, base64_decode($image));//返回的是字节数
                    if (!$r) {
                        return (['data'=>null,"code"=>1,"msg"=>"图片生成失败"]);
                    }else{
                        $curriculumempfile = new CurriculumEmpfile();
                        $curriculumempfile -> file_name = $imageName;
                        $curriculumempfile -> file_url = $imageSrc;
                        $curriculumempfile -> cur_id= $data['cur_id'];
                        $curriculumempfile -> emp_number= $data['emp_number'];
                        $curriculumempfile->save();
                    }
                }
            }

        }

        $curriculumempanswer = new CurriculumEmpanswer();
        $info = $curriculumempanswer::find()->asArray()->where(['emp_number'=>$data['emp_number'],'cur_id'=>$data['cur_id']])->one();
        if($info != ''){
            $curriculumempanswer::deleteAll(['emp_number'=>$data['emp_number'],'cur_id'=>$data['cur_id']]);
        }
        if(!empty($data['question'])){
            foreach ($data['question'] as $k => $v){

                if($v['question_type'] != 5 && $v['question_type'] != 6){
                    //查找答案判断是否是正确答案
                    $answer = new CurriculumAnswer();
                    $arr = $answer::find()->select('id')->where(['question_id'=>$v['question_id'],'default_answer'=> 1])->all();
                    $arr = array_column($arr,'id');
                    $info = array_diff($arr,$v['answer_id']);
                    if($info){
                        $question_fraction  = 0;
                    }else{
                        $question_fraction = $v['fraction'];
                    }
                    $answer_id = implode(',',$v['answer_id']);
                }else{
                    $answer_id = $v['answer_id'];
                    $question_fraction = '';
                }

                $empanswer = new CurriculumEmpanswer();
                $empanswer->emp_number = $data['emp_number'];
                $empanswer->question_id = $v['question_id'];
                $empanswer->answer_id = $answer_id;
                $empanswer->question_type = $v['question_type'];
                $empanswer->question_fraction = $question_fraction;
                $empanswer->cur_id = $data['cur_id'];
                $query = $empanswer->save();
                if(!$query){
                    return false;
                }
            }
        }


        $curriculumemployee = CurriculumEmployee::find()->where(['emp_number'=>$data['emp_number'],'cur_id'=>$data['cur_id']])->one();
        if($curriculumemployee != ''){
            CurriculumEmployee::deleteAll(['emp_number'=>$data['emp_number'],'cur_id'=>$data['cur_id']]);
        }
        $curremp = new CurriculumEmployee();
        $curremp->emp_number = $data['emp_number'];
        $curremp->cur_id = $data['cur_id'];
        $curremp->create_time = date('Y-m-d H;i:s',time());
        $query = $curremp->save();
        return $query;
    }

    /*
     * 审核考试列表
     *
     * **/
    public function examinelist($name,$emp_name,$cu_type,$page,$user_name,$work_station){
        $employee = new Employee();
        $emp_name = $employee::find()->asArray()->select(['emp_number'])->where(['emp_firstname'=>$emp_name])->one();
        $emp_number = $emp_name['emp_number'];
        if($user_name == 'admin'){
            $where = '1=1';
        }else{
            $where = "b.work_station = '$work_station'";
        }
        if($name != '' ){
            $where .= " and name like '%$name%' ";
        }
        if($emp_name != ''){
            $where .=" and a.emp_number = '$emp_number'";
        }
        if($cu_type != ''){
            $where .= " and cu_type = '$cu_type'";
        }

        $pagesize = 20;
        $startrow = ($page-1)*$pagesize;

        $query = (new \yii\db\Query())
            ->select(['b.name','b.id','b.cu_type','c.emp_firstname','a.emp_credit','a.emp_number'])
            ->from('orangehrm_mysql.ohrm_curriculum_employee a')
            ->leftJoin('orangehrm_mysql.ohrm_curriculum b','b.id=a.cur_id')
            ->leftJoin('orangehrm_mysql.hs_hr_employee c','a.emp_number = c.emp_number')
            ->offset($startrow)
            ->limit($pagesize)
            ->where($where)
            ->all();

        $slecount = $this->examinecount($name,$emp_name,$cu_type,$page);

        $model['count'] =  (int)$slecount;
        $model['pagesize'] = (int)$pagesize;
        $model['result'] = $query;
        return $model;
    }


    public function examinecount($name,$emp_name,$cu_type,$page){
        $employee = new Employee();
        $emp_name = $employee::find()->asArray()->select(['emp_number'])->where(['emp_firstname'=>$emp_name])->one();
        $emp_number = $emp_name['emp_number'];

        $where = '1=1';
        if($name != '' ){
            $where .= " and name = '$name' ";
        }
        if($emp_name != ''){
            $where .=" and a.emp_number = '$emp_number'";
        }
        if($cu_type != ''){
            $where .= " and cu_type = '$cu_type'";
        }

        $pagesize = 20;
        $startrow = ($page-1)*$pagesize;

        $arr = (new \yii\db\Query())
            ->select(['b.name','b.id','b.cu_type','c.emp_firstname','a.emp_credit','a.emp_number'])
            ->from('orangehrm_mysql.ohrm_curriculum_employee a')
            ->leftJoin('orangehrm_mysql.ohrm_curriculum b','b.id=a.cur_id')
            ->leftJoin('orangehrm_mysql.hs_hr_employee c','a.emp_number = c.emp_number')
            ->offset($startrow)
            ->limit($pagesize)
            ->where($where)
            ->count();
        return $arr;
    }


    /*
     * 审核考试
     * **/
    public function subexamin($cur_id,$emp_number,$emp_credit){
        $time = date('Y-m-d H:i:s',time());
        $curriculumemployee = new CurriculumEmployee();
        $model = $curriculumemployee::find()->where(['emp_number'=>$emp_number,'cur_id'=>$cur_id])->one();
        if($model == ''){
            $curriculumemployee->emp_number = $emp_number;
            $curriculumemployee->cur_id = $cur_id;
            $curriculumemployee->emp_credit = $emp_credit;
            $curriculumemployee->create_time = $time;
            $query = $curriculumemployee->save();


        }else{
            $model->emp_number = $emp_number;
            $model->cur_id = $cur_id;
            $model->emp_credit = $emp_credit;
            $model->create_time = $time;
            $query = $model->save();

        }
    }


    /*
     * 审核考试
     * **/
    public function examine($cur_id,$emp_number){
        $curriculum = new Curriculum();
        $data = $curriculum::find()->asArray()->where(['id'=>$cur_id])->one();
        $file = new CurriculumFile();
        $data['file'] = $file::find()->select(['cur_name','cur_url'])->where(['id'=>$data['file_id']])->all();

        $question = (new yii\db\Query())
            ->select('*')
            ->from('orangehrm_mysql.ohrm_curriculum_problems a')
            ->leftJoin('orangehrm_mysql.ohrm_curriculum_questions b','b.id = a.question_id ')
            ->where(['curriculum_id'=>$cur_id])
            ->all();

        $answer = new CurriculumAnswer();
        foreach ($question as $k =>$v){
            $question[$k]['answer'] = $answer::find()->asArray()->select(['id','option_answer','default_answer'])->where(['question_id'=>$v['question_id']])->all();
        }

        $data['file'] = CurriculumFile::find()->asArray()->where(['cur_id'=>$cur_id])->all();

        foreach ($question as $key => $val){
            $where = "emp_number = '$emp_number' and cur_id = '$cur_id' and question_id = '$val[id]'";
            $curremp = CurriculumEmpanswer::find()->asArray()->where($where)->one();
            $answer_id =  explode(",", $curremp['answer_id'] );
            $question[$key]['answer_id'] = $answer_id;
            if($curremp['question_fraction'] != 0 && $curremp['question_fraction'] !=''){
                $question[$key]['is_true'] = '1';
            }else{
                $question[$key]['is_true'] = '0';
            }
            if($val['question_type'] == 5 || $val['question_type'] == 6){
                $where = "emp_number = '$emp_number' and cur_id = '$cur_id' and question_id = '$val[id]'";
                $curremp = CurriculumEmpanswer::find()->asArray()->where($where)->one();
                $question[$key]['answer'] = $curremp['answer_id'];
            }else{
                foreach ($val['answer'] as $ke => $va){

                    if($va['default_answer'] == 1){
                        $question[$key]['singleChoiceQuestionChecked'][] = $va['option_answer'];
                    }


                    $where = "emp_number = '$emp_number' and cur_id = '$cur_id' and question_id = '$val[id]' and answer_id like '%$va[id]%'";
                    $curremp = CurriculumEmpanswer::find()->asArray()->where($where)->one();
                    if($curremp==''){
                        $emp_answer = '0';
                        $question_fraction = '0';
                    }else{
                        $emp_answer = '1';
                        $question_fraction = $curremp['question_fraction'];
                    }
                    $question[$key]['answer'][$ke]['emp_answer'] = $emp_answer;
                    $question[$key]['question_fraction'] = $question_fraction;
                }
            }

        }

        $arr['curriculum'] = $data;
        $arr['question'] = $question;


        return $arr;
    }
}
