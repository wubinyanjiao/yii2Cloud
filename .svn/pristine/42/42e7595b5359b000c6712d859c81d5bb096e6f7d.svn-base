<?php
namespace frontend\controllers\v1;

use common\models\curriculum\Curriculum;
use common\models\curriculum\CurriculumAnswer;
use common\models\curriculum\CurriculumFile;
use common\models\curriculum\CurriculumQuestions;
use yii\web\Response;
use yii;

class CurriculumController extends \common\rest\Controller
{


    /**
     * @var string
     */
    public $modelClass = 'common\models\Curriculum';

    /**
     * @var array
     */
    public $serializer = [
        'class' => 'common\rest\Serializer',    // 返回格式数据化字段
        'collectionEnvelope' => 'data',       // 制定数据字段名称
        'message' => '操作成功',                      // 文本提示
    ];


    /**
     * @param  [action] yii\rest\IndexAction
     * @return [type]
     */
    public function beforeAction($action)
    {
        $format = \Yii::$app->getRequest()->getQueryParam('format', 'json');

        if ($format == 'xml') {
            \Yii::$app->response->format = \yii\web\Response::FORMAT_XML;
        } else {
            \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        }

        return $action;
    }

    /**
     * @param  [type]
     * @param  [type]
     * @return [type]
     */
    public function afterAction($action, $result)
    {
        $result = parent::afterAction($action, $result);

        return $result;
    }

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['contentNegotiator']['formats']['text/html'] = Response::FORMAT_HTML;
        return $behaviors;
    }


    public function actions()
    {
        $actions = parent::actions();
        unset($actions['index']);
        return $actions;
    }



    /**
     * @SWG\Get(path="/curriculum/updata",
     *     tags={"云平台-curriculum-培训"},
     *     summary="是否开始培训",
     *     description="是否开始培训",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "query",
     *        name = "Token",
     *        description = "Token",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "query",
     *        name = "id",
     *        description = "课程id",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "培训列表"
     *     ),
     *     @SWG\Response(
     *         response = 403,
     *         description = "操作失败",
     *     )
     * )
     *
     **/
    /*
     * 是否开始培训
     * get
     * 课程id
     * **/
    public function actionUpdata(){
        $id = Yii::$app->request->get('id');
        $result = Curriculum::find()->where(['id'=>$id])->one();
        if ($result['is_start'] == 0){
            $result->is_start = 1;
        }else{
            $result->is_start = 0;
        }
        $query = $result->save();

        return $query;
    }





    /**
     * @SWG\Get(path="/curriculum/index",
     *     tags={"云平台-curriculum-培训"},
     *     summary="课程列表",
     *     description="课程列表",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "query",
     *        name = "Token",
     *        description = "Token",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "query",
     *        name = "name",
     *        description = "课程名",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "query",
     *        name = "trainer",
     *        description = "讲师",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "query",
     *        name = "cu_type",
     *        description = "课程类型",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "query",
     *        name = "page",
     *        description = "页数",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "培训列表"
     *     ),
     *
     *
     *
     *     @SWG\Response(
     *         response = 403,
     *         description = "操作失败",
     *     )
     * )
     *
     **/
    /*
     * 课程列表以及查询
     * get
     * 课程名name  讲师trainer  课程类型cu_type 页数page
     * **/
    public function actionIndex()
    {

        $curriculum = new Curriculum();
        $work_station = $this->workStation;
        $username = $this->userName;
        $name = Yii::$app->request->get("name");
        $trainer = Yii::$app->request->get("trainer");
        $cu_type = Yii::$app->request->get("cu_type");
        $page = Yii::$app->request->get("page");
        $model = $curriculum->serch($name,$trainer,$cu_type,$page,$work_station,$username);

        return  $model;

    }



    /*
     * 添加课程
     * post
     * **/
    public function actionAddcurriculum(){
        $workstation = $this->workStation;
        $data = Yii::$app->request->post();
        $curriculum = new Curriculum();
        $model = $curriculum->addcurriculum($data,$workstation);
        return $model;
    }





    /**
     * @SWG\Post(path="/curriculum/filelist",
     *     tags={"云平台-curriculum-培训"},
     *     summary="课程文件列表",
     *     description="课程文件列表",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "Token",
     *        description = "Token",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "课程文件列表"
     *     ),
     *     @SWG\Response(
     *         response = 403,
     *         description = "操作失败",
     *     )
     * )
     *
     **/
    /*
     * 课程文件列表
     * **/
    public function actionFilelist(){
        $curriculumfile =new CurriculumFile();
        $query = $curriculumfile::find()->select(['id','cur_name'])->asArray()->all();
        return $query;
    }



    /**
     * @SWG\Get(path="/curriculum/selcur",
     *     tags={"云平台-curriculum-培训"},
     *     summary="查找要修改课程",
     *     description="查找要修改课程",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "query",
     *        name = "Token",
     *        description = "Token",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "query",
     *        name = "id",
     *        description = "id",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "课程文件列表"
     *     ),
     *     @SWG\Response(
     *         response = 403,
     *         description = "操作失败",
     *     )
     * )
     *
     **/
    /*
     * 查找要修改课程
     *get
     * id
     * **/
    public function actionSelcur(){
        $id = Yii::$app->request->get('id');
        $curriculum = new Curriculum();
        $model = $curriculum->selcur($id);
        return $model;
    }




    /*
     * 修改查找的课程
     * **/
    public function actionUpcur(){
        $workstation = $this->workStation;
        $data = Yii::$app->request->post();
        $curriculum = new Curriculum();
        $model = $curriculum->upcurriculum($data,$workstation);
        return $model;
    }






    /**
     * @SWG\Get(path="/curriculum/delcur",
     *     tags={"云平台-curriculum-培训"},
     *     summary="删除课程",
     *     description="删除课程",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "query",
     *        name = "Token",
     *        description = "Token",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "query",
     *        name = "id",
     *        description = "id",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "课程文件列表"
     *     ),
     *     @SWG\Response(
     *         response = 403,
     *         description = "操作失败",
     *     )
     * )
     *
     **/
    /*
     * 删除课程
     * get
     * id
     * **/
    public function actionDelcur(){
        $id = Yii::$app->request->get('id');
        $curriculum = new Curriculum();
        $model = $curriculum->delcur($id);
        return $model;
    }




    /**
     * @SWG\Get(path="/curriculum/delfile",
     *     tags={"云平台-curriculum-培训"},
     *     summary="删除课件",
     *     description="删除课件",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "query",
     *        name = "Token",
     *        description = "Token",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "query",
     *        name = "id",
     *        description = "课件id",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "课程文件列表"
     *     ),
     *     @SWG\Response(
     *         response = 403,
     *         description = "操作失败",
     *     )
     * )
     *
     **/
    /*
     * 删除课件
     * get
     * 文件id
     * **/
    public function actionDelfile(){
        $id = Yii::$app->request->get('id');
        $file = new Curriculumfile();
        $model = $file->delfile($id);
        return $model;
    }



    /**
     * @SWG\Get(path="/curriculum/begin",
     *     tags={"云平台-curriculum-培训"},
     *     summary="开始考试列表",
     *     description="开始考试列表",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "query",
     *        name = "Token",
     *        description = "Token",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "query",
     *        name = "emp_number",
     *        description = "员工id",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "query",
     *        name = "name",
     *        description = "课程名",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "query",
     *        name = "trainer",
     *        description = "讲师",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "query",
     *        name = "cu_type",
     *        description = "课程类型",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "课程文件列表"
     *     ),
     *     @SWG\Response(
     *         response = 403,
     *         description = "操作失败",
     *     )
     * )
     *
     **/
    /*
     * 开始考试列表以及查询
     * get
     * 员工emp_number  课程名name  讲师trainer  课程类型cu_type
     * **/
    public function actionBegin(){
        $work_station = $this->workStation;
        $emp_number = $this->empNumber;
        $name = Yii::$app->request->get("name");
        $trainer = Yii::$app->request->get("trainer");
        $cu_type = Yii::$app->request->get("cu_type");
        $page = Yii::$app->request->get("page");
        $curriculum = new Curriculum();
        $model = $curriculum->begin($emp_number,$name,$trainer,$cu_type,$page,$work_station);
        return $model;
    }



    /**
     * @SWG\Get(path="/curriculum/begincur",
     *     tags={"云平台-curriculum-培训"},
     *     summary="开始考试",
     *     description="开始考试",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "query",
     *        name = "Token",
     *        description = "Token",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "query",
     *        name = "emp_number",
     *        description = "员工id",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "query",
     *        name = "cur_id",
     *        description = "课程id",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "课程文件列表"
     *     ),
     *     @SWG\Response(
     *         response = 403,
     *         description = "操作失败",
     *     )
     * )
     *
     **/
    /*
     * 开始考试
     * get
     * 课程cur_id  员工emp_number
     * **/
    public function actionBegincur(){
        $cur_id = Yii::$app->request->get('cur_id');
        $emp_number = Yii::$app->request->get('emp_number');
        $curriculum = new Curriculum();
        $model = $curriculum->begincur($cur_id,$emp_number);
        if($model === 4){
            $this->serializer['status'] = false;
            $this->serializer['message'] = '考试间隔还没到';
            return false;
        }
        if($model === 3){
            $this->serializer['status'] = false;
            $this->serializer['message'] = '你已经答过本课程';
            return false;
        }
        return $model;

    }

    /*
     * 考试提交
     * post
     * 员工id  课程id 考试内容
     * **/
    public function actionSubcur(){
        $data = yii::$app->request->post();
        $curriculum = new Curriculum();
        $model = $curriculum->subcur($data);
        return $model;
    }



    /**
     * @SWG\Get(path="/curriculum/examinelist",
     *     tags={"云平台-curriculum-培训"},
     *     summary="审核考试列表",
     *     description="审核考试列表",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "query",
     *        name = "Token",
     *        description = "Token",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "query",
     *        name = "name",
     *        description = "课程名称",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "query",
     *        name = "emp_name",
     *        description = "考试人",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "query",
     *        name = "cu_type",
     *        description = "课程类型",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "query",
     *        name = "page",
     *        description = "页数",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "课程文件列表"
     *     ),
     *     @SWG\Response(
     *         response = 403,
     *         description = "操作失败",
     *     )
     * )
     *
     **/
    /*
     * 审核考试列表
     * get
     * 课程名称 name   考试人emp_name   课程类型cu_type
     * **/
    public function actionExaminelist(){
        $user_name = $this->userName;
        $work_station = $this->workStation;
        $name = Yii::$app->request->get('name');
        $emp_name = Yii::$app->request->get('emp_name');
        $cu_type = Yii::$app->request->get('cu_type');
        $page = Yii::$app->request->get('page');

        $curriculum = new Curriculum();
        $model = $curriculum->examinelist($name,$emp_name,$cu_type,$page,$user_name,$work_station);

        return $model;
    }



    /**
     * @SWG\Get(path="/curriculum/examine",
     *     tags={"云平台-curriculum-培训"},
     *     summary="审核考试",
     *     description="审核考试",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "query",
     *        name = "Token",
     *        description = "Token",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "query",
     *        name = "emp_number",
     *        description = "员工id",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "query",
     *        name = "cur_id",
     *        description = "课程id",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "课程文件列表"
     *     ),
     *     @SWG\Response(
     *         response = 403,
     *         description = "操作失败",
     *     )
     * )
     *
     **/
    /*
     * 审核考试
     * get
     * 课程ID cur_id  员工ID emp_number
     * **/
    public function actionExamine(){
        $cur_id = Yii::$app->request->get('cur_id');
        $emp_number = Yii::$app->request->get('emp_number');
        $curriculum = new Curriculum();
        $model = $curriculum->examine($cur_id,$emp_number);
        return $model;

    }


    /**
     * @SWG\Post(path="/curriculum/subexamine",
     *     tags={"云平台-curriculum-培训"},
     *     summary="提交审核",
     *     description="提交审核",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "Token",
     *        description = "Token",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "emp_number",
     *        description = "员工id",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "cur_id",
     *        description = "课程id",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "emp_credit",
     *        description = "分数",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "课程文件列表"
     *     ),
     *     @SWG\Response(
     *         response = 403,
     *         description = "操作失败",
     *     )
     * )
     *
     **/
    /*
     * 提交审核
     * post
     * 员工id emp_number  课程id  cur_id  分数 emp_credit
     * **/
    public function actionSubexamine(){
        $cur_id = Yii::$app->request->post('cur_id');
        $emp_number = Yii::$app->request->post('emp_number');
        $emp_credit = Yii::$app->request->post('emp_credit');

        $curriculum = new Curriculum();
        $model = $curriculum->subexamin($cur_id,$emp_number,$emp_credit);
        return $model;
    }



    /**
     * @SWG\Post(path="/curriculum/del-question",
     *     tags={"云平台-curriculum-培训"},
     *     summary="删除题",
     *     description="删除题",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "Token",
     *        description = "Token",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "question_id",
     *        description = "课程id",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "课程文件列表"
     *     ),
     *     @SWG\Response(
     *         response = 403,
     *         description = "操作失败",
     *     )
     * )
     *
     **/
    public function actionDelQuestion(){
        $question_id = yii::$app->request->get('id');
        $query = CurriculumQuestions::deleteAll(['id'=>$question_id]);
        return $query;
    }


    /**
     * @SWG\Post(path="/curriculum/del-answer",
     *     tags={"云平台-curriculum-培训"},
     *     summary="删除答案",
     *     description="删除答案",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "Token",
     *        description = "Token",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "answer_id",
     *        description = "答案id",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "课程文件列表"
     *     ),
     *     @SWG\Response(
     *         response = 403,
     *         description = "操作失败",
     *     )
     * )
     *
     **/
    public function actionDelAnswer(){
        $answer_id = yii::$app->request->get('answer_id');
        $query = CurriculumAnswer::deleteAll(['id'=>$answer_id]);
        return $query;
    }








}

