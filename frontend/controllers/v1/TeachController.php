<?php
namespace frontend\controllers\v1;

use common\models\employee\Employee;
use common\models\teach\Degree;
use common\models\teach\Education;
use common\models\teach\Teach;
use common\models\user\User;
use function GuzzleHttp\Psr7\str;
use opw\react\ReactAsset;
use yii\rest\ActiveController;
use yii\web\Response;
use yii;

class TeachController extends \common\rest\Controller
{


    /**
     * @var string
     */
    public $modelClass = 'common\models\Teach';

    /**
     * @var array
     */
    public $serializer = [
        'class' => 'common\rest\Serializer',    // 返回格式数据化字段
        'collectionEnvelope' => 'data',       // 制定数据字段名称
        'message' => 'OK',                      // 文本提示
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
     * @SWG\Post(path="/teach/teach-list",
     *     tags={"云平台-Teach-教育"},
     *     summary="教育列表",
     *     description="教育列表",
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
     *        name = "school",
     *        description = "学校",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "major",
     *        description = "专业",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "record_id",
     *        description = "学历id",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "degree_id",
     *        description = "学位id",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "school_type",
     *        description = "学校类型，1是全日制，2是在职",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *        enum = {1,2}
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "start_time",
     *        description = "开始时间",
     *        required = false,
     *        type = "string",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "end_time",
     *        description = "结束时间",
     *        required = false,
     *        type = "string",
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "教育管理列表"
     *     ),
     *     @SWG\Response(
     *         response = 403,
     *         description = "添加失败",
     *     )
     * )
     *
     **/
    public function actionTeachList(){
        $data = yii::$app->request->post();
        if($data['emp_number'] == ''){
            $data['emp_number'] = $this->empNumber;
        }
        $teach = new Teach();
        $model = $teach->teachlist($data);
        return $model;
    }

    /**
     * @SWG\Post(path="/teach/teach-add",
     *     tags={"云平台-Teach-教育"},
     *     summary="添加教育",
     *     description="添加教育",
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
     *        name = "school",
     *        description = "学校",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "major",
     *        description = "专业",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "record_id",
     *        description = "学历id",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "degree_id",
     *        description = "学位id",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "school_type",
     *        description = "学校类型，1是全日制，2是在职",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *        enum = {1,2}
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "start_time",
     *        description = "开始时间",
     *        required = true,
     *        type = "string",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "end_time",
     *        description = "结束时间",
     *        required = true,
     *        type = "string",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "remarks",
     *        description = "备注",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "教育管理列表"
     *     ),
     *     @SWG\Response(
     *         response = 403,
     *         description = "添加失败",
     *     )
     * )
     *
     **/
    public function actionTeachAdd(){
        $data = yii::$app->request->post();
        if($data['emp_number'] == ''){
            $data['emp_number'] = $this->empNumber;
        }
        $teach = new Teach();
        $model = $teach->teachadd($data);
        return $model;
    }


    /**
     * @SWG\Post(path="/teach/teach-sel",
     *     tags={"云平台-Teach-教育"},
     *     summary="教育详情",
     *     description="教育详情",
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
     *        name = "id",
     *        description = "教育表id",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "教育管理列表"
     *     ),
     *     @SWG\Response(
     *         response = 403,
     *         description = "查询失败",
     *     )
     * )
     *
     **/
    public function actionTeachSel(){
        $id = yii::$app->request->post('id');
        $teach = new Teach();
        $model = $teach->teachsel($id);
        return $model;
    }


    /**
     * @SWG\Post(path="/teach/teach-update",
     *     tags={"云平台-Teach-教育"},
     *     summary="修改教育",
     *     description="修改教育",
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
     *        name = "id",
     *        description = "教育表id",
     *        required = true,
     *        type = "integer"
     *     ),  *     @SWG\Parameter(
     *        in = "formData",
     *        name = "emp_number",
     *        description = "员工id",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "school",
     *        description = "学校",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "major",
     *        description = "专业",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "record_id",
     *        description = "学历id",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "degree_id",
     *        description = "学位id",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "school_type",
     *        description = "学校类型，1是全日制，2是在职",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *        enum = {1,2}
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "start_time",
     *        description = "开始时间",
     *        required = true,
     *        type = "string",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "end_time",
     *        description = "结束时间",
     *        required = true,
     *        type = "string",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "remarks",
     *        description = "备注",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "教育管理列表"
     *     ),
     *     @SWG\Response(
     *         response = 403,
     *         description = "修改失败",
     *     )
     * )
     *
     **/
    public function actionTeachUpdate(){
        $data = yii::$app->request->post();
        $teach = new Teach();
        $model = $teach->teachupdate($data);
        return $model;
    }




    /**
     * @SWG\Post(path="/teach/atta-del",
     *     tags={"云平台-Teach-教育"},
     *     summary="附件删除",
     *     description="附件删除",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "eattach_id",
     *        description = "分类id",
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
     *     @SWG\Response(
     *         response = 200,
     *         description = "教育管理列表"
     *     ),
     *     @SWG\Response(
     *         response = 403,
     *         description = "删除失败",
     *     )
     * )
     *
     **/
    public function actionAttaDel(){
        $eattach_id = yii::$app->request->post('eattach_id');
        $emp_number = yii::$app->request->post('emp_number');
        $teach = new Teach();
        $model = $teach->attadel($eattach_id,$emp_number);
        return $model;
    }



    /**
     * @SWG\Post(path="/teach/teach-del",
     *     tags={"云平台-Teach-教育"},
     *     summary="教育删除",
     *     description="教育删除",
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
     *        name = "id",
     *        description = "教育表id",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "教育管理列表"
     *     ),
     *     @SWG\Response(
     *         response = 403,
     *         description = "删除失败",
     *     )
     * )
     *
     **/
    public function actionTeachDel(){
        $id = yii::$app->request->post('id');
        $teach = new Teach();
        $model = $teach->teachdel($id);
        return $model;
    }


    /**
     * @SWG\Post(path="/teach/education",
     *     tags={"云平台-Teach-教育"},
     *     summary="学历",
     *     description="学历",
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
     *         description = "教育管理列表"
     *     ),
     *     @SWG\Response(
     *         response = 403,
     *         description = "删除失败",
     *     )
     * )
     *
     **/
    public function actionEducation(){
        $query = Education::find()->all();
        return $query;
    }



    /**
     * @SWG\Post(path="/teach/degree",
     *     tags={"云平台-Teach-教育"},
     *     summary="学位",
     *     description="学位",
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
     *         description = "教育管理列表"
     *     ),
     *     @SWG\Response(
     *         response = 403,
     *         description = "删除失败",
     *     )
     * )
     *
     **/
    public function actionDegree(){
        $degree = Degree::find()->all();
        /*foreach ($degree as $k => $v){
            $query[$k]['id'] = string($v['id']);
        }*/
        return $degree;
    }









}

