<?php
namespace frontend\controllers\v1;

use common\models\Employee;
use common\models\honor\Honor;
use common\models\attachment\Attachment;
use common\models\honor\HonorClass;
use common\models\honor\HonorType;
use common\models\user\User;
use yii\rest\ActiveController;
use yii\web\Response;
use common\helps\tools;
use yii;

class HonorController extends \common\rest\Controller
{


    public $modelClass = 'common\models\honor\Honor';


    /**
     * @var array
     */
    public $serializer = [
        'class' => 'common\rest\Serializer',    // 返回格式数据化字段
        'collectionEnvelope' => 'result',       // 制定数据字段名称
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
     * @SWG\Post(path="/honor/honor-list",
     *     tags={"云平台-Honor-科室荣誉"},
     *     summary="科室荣誉列表",
     *     description="科室荣誉列表",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "Token",
     *        description = "Token",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "awardee",
     *        description = "获奖者",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "honor_name",
     *        description = "获奖名称",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "item_name",
     *        description = "项目名称",
     *        required = false,
     *        type = "string",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "accept_award",
     *        description = "获奖单位",
     *        required = false,
     *        type = "string",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "grant_award",
     *        description = "颁奖单位",
     *        required = false,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "reward",
     *        description = "奖励",
     *        required = false,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "reward_class",
     *        description = "奖励类别",
     *        required = false,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "reward_type",
     *        description = "奖励种类",
     *        required = false,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "reward_number",
     *        description = "奖项编号",
     *        required = false,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "reward_time",
     *        description = "报奖、获奖时间",
     *        required = false,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "status",
     *        description = "状态，0是报奖，1是 获奖",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *        enum = {0,1}
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "page",
     *        description = "页数",
     *        required = true,
     *        type = "integer",
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "返回科室荣誉列表"
     *     ),
     *     @SWG\Response(
     *         response = 403,
     *         description = "查询失败",
     *     )
     * )
     *
     **/
    public function actionHonorList(){
        $data = yii::$app->request->post();
        if(!isset($data['emp_number'])){
            $data['emp_number'] = $this->empNumber;
        }
        $honor = new Honor();
        $model = $honor->honorlist($data);
        return $model;
    }



    /**
     * @SWG\Post(path="/honor/add-honor",
     *     tags={"云平台-Honor-科室荣誉"},
     *     summary="添加科室荣誉",
     *     description="添加科室荣誉",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "Token",
     *        description = "Token",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "awardee",
     *        description = "获奖者 （注意：多个获奖者或单位用中文逗号隔开）",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "honor_name",
     *        description = "获奖名称",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "item_name",
     *        description = "项目名称",
     *        required = false,
     *        type = "string",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "done_unit",
     *        description = "完成单位",
     *        required = false,
     *        type = "string",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "grant_award",
     *        description = "颁奖单位",
     *        required = false,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "reward",
     *        description = "奖励",
     *        required = false,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "reward_class",
     *        description = "奖励类别",
     *        required = false,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "reward_type",
     *        description = "奖励种类",
     *        required = false,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "reward_number",
     *        description = "奖项编号",
     *        required = false,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "reward_time",
     *        description = "报奖、获奖时间",
     *        required = false,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "remark",
     *        description = "备注",
     *        required = false,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "status",
     *        description = "状态，0是报奖，1是 获奖",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *        enum = {0,1}
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "返回科室荣誉列表"
     *     ),
     *     @SWG\Response(
     *         response = 403,
     *         description = "查询失败",
     *     )
     * )
     *
     **/
    public function actionAddHonor(){
        $data = yii::$app->request->post();
        $honor = new Honor();
        $model = $honor->addhonor($data);
        return $model;
    }



    /**
     * @SWG\Post(path="/honor/sel-honor",
     *     tags={"云平台-Honor-科室荣誉"},
     *     summary="科室荣誉详情",
     *     description="科室荣誉详情",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "Token",
     *        description = "Token",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "honor_id",
     *        description = "id",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "返回科室荣誉列表"
     *     ),
     *     @SWG\Response(
     *         response = 403,
     *         description = "查询失败",
     *     )
     * )
     *
     **/
    public function actionSelHonor(){
        $honor_id = yii::$app->request->post('honor_id');
        $honor = new Honor();
        $model = $honor->selhonor($honor_id);
        return $model;
    }




    /**
     * @SWG\Post(path="/honor/update-honor",
     *     tags={"云平台-Honor-科室荣誉"},
     *     summary="修改科室荣誉",
     *     description="修改科室荣誉",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "Token",
     *        description = "Token",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "honor_id",
     *        description = "荣誉id",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "awardee",
     *        description = "获奖者",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "honor_name",
     *        description = "获奖名称",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "item_name",
     *        description = "项目名称",
     *        required = true,
     *        type = "string",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "done_unit",
     *        description = "完成单位",
     *        required = true,
     *        type = "string",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "grant_award",
     *        description = "颁奖单位",
     *        required = true,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "reward",
     *        description = "奖励",
     *        required = true,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "reward_class",
     *        description = "奖励类别",
     *        required = true,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "reward_type",
     *        description = "奖励种类",
     *        required = true,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "reward_number",
     *        description = "奖项编号",
     *        required = true,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "reward_time",
     *        description = "报奖、获奖时间",
     *        required = true,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "remark",
     *        description = "备注",
     *        required = true,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "status",
     *        description = "状态，0是报奖，1是 获奖",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *        enum = {0,1}
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "返回科室荣誉列表"
     *     ),
     *     @SWG\Response(
     *         response = 403,
     *         description = "查询失败",
     *     )
     * )
     *
     **/
    public function actionUpdateHonor(){
        $data  = yii::$app->request->post();
        $honor = new Honor();
        $model = $honor->uphonor($data);
        return $model;
    }



    /**
     * @SWG\Post(path="/honor/del-honor",
     *     tags={"云平台-Honor-科室荣誉"},
     *     summary="删除科室荣誉",
     *     description="删除科室荣誉",
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
     *        name = "honor_id",
     *        description = "id (是一个数组)",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "返回科室荣誉列表"
     *     ),
     *     @SWG\Response(
     *         response = 403,
     *         description = "查询失败",
     *     )
     * )
     *
     **/
    public function actionDelHonor(){
        $honor_id = yii::$app->request->post('honor_id');
        $honor = new  Honor();
        $model = $honor->delhonor($honor_id);
        return $model;
    }




    /**
     * @SWG\Post(path="/honor/reward-class",
     *     tags={"云平台-Honor-科室荣誉"},
     *     summary="奖励类别",
     *     description="奖励类别",
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
     *         description = "返回科室荣誉列表"
     *     ),
     *     @SWG\Response(
     *         response = 403,
     *         description = "查询失败",
     *     )
     * )
     *
     **/
    public function actionRewardClass(){
        $data = HonorClass::find()->asArray()->all();
        return $data;
    }


    /**
     * @SWG\Post(path="/honor/reward-type",
     *     tags={"云平台-Honor-科室荣誉"},
     *     summary="奖励种类",
     *     description="奖励种类",
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
     *         description = "返回科室荣誉列表"
     *     ),
     *     @SWG\Response(
     *         response = 403,
     *         description = "查询失败",
     *     )
     * )
     *
     **/
    public function actionRewardType(){
        $data = HonorType::find()->asArray()->all();
        return $data;
    }






    /**
     * @SWG\Post(path="/honor/empadd-honor",
     *     tags={"云平台-Honor-科室荣誉"},
     *     summary="员工添加科室荣誉",
     *     description="员工添加科室荣誉",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "Token",
     *        description = "Token",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "emp_number",
     *        description = "员工",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "honor_name",
     *        description = "获奖名称",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "item_name",
     *        description = "项目名称",
     *        required = false,
     *        type = "string",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "ranking",
     *        description = "排名  N/M格式",
     *        required = false,
     *        type = "string",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "done_unit",
     *        description = "完成单位",
     *        required = false,
     *        type = "string",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "grant_award",
     *        description = "颁奖单位",
     *        required = false,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "reward",
     *        description = "奖励",
     *        required = false,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "reward_class",
     *        description = "奖励类别",
     *        required = false,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "reward_type",
     *        description = "奖励种类",
     *        required = false,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "reward_number",
     *        description = "奖项编号",
     *        required = false,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "reward_time",
     *        description = "报奖、获奖时间",
     *        required = false,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "remark",
     *        description = "备注",
     *        required = false,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "status",
     *        description = "状态，0是报奖，1是 获奖",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *        enum = {0,1}
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "返回科室荣誉列表"
     *     ),
     *     @SWG\Response(
     *         response = 403,
     *         description = "查询失败",
     *     )
     * )
     *
     **/
    public function actionEmpaddHonor(){
        $data = yii::$app->request->post();
        if($data['emp_number'] == ''){
            $data['emp_number'] = $this->empNumber;
        }
        $honor = new Honor();
        $model = $honor->empaddhonor($data);
        return $model;
    }


    /**
     * @SWG\Post(path="/honor/empsel-honor",
     *     tags={"云平台-Honor-科室荣誉"},
     *     summary="员工科室荣誉详情",
     *     description="员工科室荣誉详情",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "Token",
     *        description = "Token",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "id",
     *        description = "id",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "返回科室荣誉列表"
     *     ),
     *     @SWG\Response(
     *         response = 403,
     *         description = "查询失败",
     *     )
     * )
     *
     **/
    public function actionEmpselHonor(){
        $id = yii::$app->request->post('id');
        $honor = new Honor();
        $model = $honor->empselhonor($id);
        return $model;
    }




    /**
     * @SWG\Post(path="/honor/empupdate-honor",
     *     tags={"云平台-Honor-科室荣誉"},
     *     summary="员工修改科室荣誉",
     *     description="员工修改科室荣誉",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "Token",
     *        description = "Token",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "id",
     *        description = "荣誉表id",
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
     *        name = "honor_name",
     *        description = "获奖名称",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "ranking",
     *        description = "排名  N/M格式",
     *        required = false,
     *        type = "string",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "item_name",
     *        description = "项目名称",
     *        required = true,
     *        type = "string",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "done_unit",
     *        description = "完成单位",
     *        required = true,
     *        type = "string",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "grant_award",
     *        description = "颁奖单位",
     *        required = true,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "reward",
     *        description = "奖励",
     *        required = true,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "reward_class",
     *        description = "奖励类别",
     *        required = true,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "reward_type",
     *        description = "奖励种类",
     *        required = true,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "reward_number",
     *        description = "奖项编号",
     *        required = true,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "reward_time",
     *        description = "报奖、获奖时间",
     *        required = true,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "remark",
     *        description = "备注",
     *        required = true,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "status",
     *        description = "状态，0是报奖，1是 获奖",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *        enum = {0,1}
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "返回科室荣誉列表"
     *     ),
     *     @SWG\Response(
     *         response = 403,
     *         description = "查询失败",
     *     )
     * )
     *
     **/
    public function actionEmpupdateHonor(){
        $data  = yii::$app->request->post();
        $honor = new Honor();
        $model = $honor->empuphonor($data);
        return $model;
    }



    /**
     * @SWG\Post(path="/honor/emphonor-list",
     *     tags={"云平台-Honor-科室荣誉"},
     *     summary="员工科室荣誉列表",
     *     description="员工科室荣誉列表",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "Token",
     *        description = "Token",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "emp_number",
     *        description = "员工id",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "honor_name",
     *        description = "获奖名称",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "item_name",
     *        description = "项目名称",
     *        required = false,
     *        type = "string",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "accept_award",
     *        description = "获奖单位",
     *        required = false,
     *        type = "string",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "grant_award",
     *        description = "颁奖单位",
     *        required = false,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "reward",
     *        description = "奖励",
     *        required = false,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "reward_class",
     *        description = "奖励类别",
     *        required = false,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "reward_type",
     *        description = "奖励种类",
     *        required = false,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "reward_number",
     *        description = "奖项编号",
     *        required = false,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "reward_time",
     *        description = "报奖、获奖时间",
     *        required = false,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "status",
     *        description = "状态，0是报奖，1是 获奖",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *        enum = {0,1}
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "返回科室荣誉列表"
     *     ),
     *     @SWG\Response(
     *         response = 403,
     *         description = "查询失败",
     *     )
     * )
     *
     **/
    public function actionEmphonorList(){
        $data = yii::$app->request->post();
        if($data['emp_number'] == ''){
            $data['emp_number'] = $this->empNumber;
        }
        $honor = new Honor();
        $model = $honor->emphonorlist($data);
        return $model;
    }



    /**
     * @SWG\Post(path="/honor/empdel-honor",
     *     tags={"云平台-Honor-科室荣誉"},
     *     summary="员工删除科室荣誉",
     *     description="员工删除科室荣誉",
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
     *        description = "id (是一个数组)",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "返回科室荣誉列表"
     *     ),
     *     @SWG\Response(
     *         response = 403,
     *         description = "查询失败",
     *     )
     * )
     *
     **/
    public function actionEmpdelHonor(){
        $id = yii::$app->request->post('id');
        $honor = new  Honor();
        $model = $honor->empdelhonor($id);
        return $model;
    }




    /**
     * @SWG\Get(path="/honor/research-sel",
     *     tags={"云平台-Research-科研"},
     *     summary="科研详情",
     *     description="科研详情",
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
     *     @SWG\Response(
     *         response = 200,
     *         description = "返回科室荣誉列表"
     *     ),
     *     @SWG\Response(
     *         response = 403,
     *         description = "查询失败",
     *     )
     * )
     *
     **/
    public function actionResearchSel(){
        $emp_number = yii::$app->request->get();
        $honor = new Honor();
        $data = $honor->selresearch($emp_number);

        $str ='<body>
        <h4>个人信息</h4>
        &nbsp;&nbsp;'.$data['user']['emp_firstname'].'&nbsp;&nbsp;'.$data['user']['education'].','.$data['user']['role'].'<br>
        &nbsp;&nbsp;'.$data['user']['emp_gender'].' ，'.$data['user']['minzu'].'，'.$data['user']['emp_birthday'].'， '.$data['user']['emp_other_id'].'&nbsp;&nbsp;&nbsp;&nbsp;<br>
        &nbsp;&nbsp;西安交通大学第一附属医院   '.$data['user']['work_station'].''.$data['user']['role'].'
        <table border="0" cellpadding="3" cellspacing="0" width="100%" style="table-layout:fixed;border-collapse:separate;border-spacing:10px 20px;">
        <tr >  
        <td width="100" valign="center" colspan="100" ><h4>联系方式</h4></td>  
        </tr>  
        <tr >
        <td width="100" valign="center" colspan="100" >&nbsp;&nbsp;'.$data['user']['custom2'].'</td>  
        </tr>  
        <tr >
        <td width="100" valign="center" colspan="100" >&nbsp;&nbsp;西安交通大学第一附属医院药学部    邮编：'.$data['user']['emp_street2'].'</td>  
        </tr>';


        if(!empty($data['user']['emp_mobile'])){
            $str.='<tr >
        <td width="100" valign="center" colspan="100" >&nbsp;&nbsp;电话：'.$data['user']['emp_mobile'].'</td>
        </tr>';
        }

        if(!empty($data['user']['emp_work_email'])){
            $str.='<tr >
        <td width="100" valign="center" colspan="100" >&nbsp;&nbsp;email：'.$data['user']['emp_work_email'].'</td>
        </tr>';
        }

        if(!empty($data['user']['weixin_code'])){
            $str.='<tr >
        <td width="100" valign="center" colspan="100" >&nbsp;&nbsp;微信：'.$data['user']['weixin_code'].'</td>
        </tr>';
        }

        if(!empty($data['consider'])){
            $str.='<tr ><td width="100" valign="center" colspan="100" ><h4>研究方向</h4></td></tr>';
            $num = 1;
            foreach ($data['consider'] as $k=>$v){
                $str.='<tr ><td width="100" valign="center" colspan="100" >&nbsp;&nbsp;'.$num.'、'.$v['research'].'</td></tr>';
                $num ++;
            }

        }

        $num = 1;

        if(!empty($data['teach'])){
            $str.='<tr ><td width="100" valign="center" colspan="100" ><h4>教育</h4></td></tr>';
            foreach ($data['teach'] as $k=>$v){
                $str.='<tr ><td width="100" valign="center" colspan="100" >&nbsp;&nbsp;'.$num.'、'.$v['start_time'].'至'.$v['end_time'].''.$v['school'].'</td></tr>';
                $num ++;
            }

        }
        $num = 1;

        if(!empty($data['work'])){
            $str.='<tr><td width="100" valign="center" colspan="100" ><h4>工作</h4></td></tr>';
            foreach ($data['work'] as $k=>$v){
                $str.='<tr ><td width="100" valign="center" colspan="100" >&nbsp;&nbsp;'.$num.'、'.$v['time_in'].'至'.$v['time_out'].''.$v['new_department'].'</td></tr>';
                $num ++;
            }

        }
        $num = 1;

        if(!empty($data['project'])){
            $str.=' <tr ><td width="100" valign="center" colspan="100" ><h4>科研</h4></td></tr>';
            foreach ($data['project'] as $k=>$v){
                $str.='<tr ><td width="100" valign="center" colspan="100" >&nbsp;&nbsp;'.$num.'、'.$v['project_name'].'（项目批准号：'.$v['project_number'].'；批准金额：'.$v['money'].'万元；起止年限：'.$v['start_time'].'至'.$v['end_time'].'）</td></tr>';
                $num ++;
            }

        }
        $num = 1;

        if(!empty($data['thesis'])){
            $str.=' <tr ><td width="100" valign="center" colspan="100" ><h4>论文</h4></td></tr>';
            foreach ($data['thesis'] as $k=>$v){
                $str.='<tr ><td width="100" valign="center" colspan="100" >&nbsp;&nbsp;'.$num.'、'.$v['first_author'].'，'.$v['corresponding_author'].','.$v['thesis_name'].','.$v['publication'].','.$v['volume'].'</td></tr>';
                $num ++;
            }

        }
        $num = 1;

        if(!empty($data['meeting'])){
            $str.=' <tr ><td width="100" valign="center" colspan="100" ><h4>会议</h4></td></tr>';
            foreach ($data['meeting'] as $k=>$v){
                $str.='<tr ><td width="100" valign="center" colspan="100" >&nbsp;&nbsp;'.$num.'、'.$data['user']['emp_firstname'].'，'.$v['first_author'].','.$v['corresponding_author'].','.$v['meeting_name'].','.$v['meeting_time'].'</td></tr>';
                $num ++;
            }

        }
        $num = 1;

        if(!empty($data['honor'])){
            $str.=' <tr ><td width="100" valign="center" colspan="100" ><h4>获奖</h4></td></tr>';
            foreach ($data['honor'] as $k=>$v){
                $str.='<tr ><td width="100" valign="center" colspan="100" >&nbsp;&nbsp;'.$num.'、'.$v['reward_time'].''.$v['honor_name'].'</td></tr>';
                $num ++;
            }

        }
        $num = 1;
        if(!empty($data['patent'])){
            $str.=' <tr ><td width="100" valign="center" colspan="100" ><h4>专利</h4></td></tr>';
            foreach ($data['patent'] as $k=>$v){
                $str.='<tr ><td width="100" valign="center" colspan="100" >&nbsp;&nbsp;'.$num.'、'.$v['applicant'].''.$v['patentee'].'.'.$v['patent_name'].'.专利类型：'.$v['patent_type'].'；申请号：'.$v['apply_number'].'；申请日期：'.$v['accept_time'].'；公开日：'.$v['authorization_time'].'</td></tr>';
                $num ++;
            }

        }
        $num = 1;


        if(!empty($data['hold'])){
            $str.=' <tr ><td width="100" valign="center" colspan="100" ><h4>社会兼职</h4></td></tr>';
            foreach ($data['hold'] as $k=>$v){
                $str.='<tr ><td width="100" valign="center" colspan="100" >&nbsp;&nbsp;'.$num.'、'.$v['society'].''.$v['job'].'</td></tr>';
                $num ++;
            }

        }
        $num = 1;



        if(!empty($data['journal'])){
            $str.=' <tr ><td width="100" valign="center" colspan="100" ><h4>杂志编委</h4></td></tr>';
            foreach ($data['journal'] as $k=>$v){
                $str.='<tr ><td width="100" valign="center" colspan="100" >&nbsp;&nbsp;'.$num.'、'.$v['journal_name'].''.$v['job'].'</td></tr>';
                $num ++;
            }

        }
        $num = 1;

        if(!empty($data['writings'])){
            $str.=' <tr ><td width="100" valign="center" colspan="100" ><h4>专著</h4></td></tr>';
            foreach ($data['writings'] as $k=>$v){
                $str.='<tr ><td width="100" valign="center" colspan="100" >&nbsp;&nbsp;'.$num.'、'.$v['editor'].'主编.'.$v['writings_name'].'.'.$v['publish_unit'].'总字数：'.$v['all_count'].'参编字数：'.$v['editor_count'].'</td></tr>';
                $num ++;
            }
        }


         $str.='</table></body>';
        $data['errno'] = "0";
        $data['status'] = true;
        $data['message'] = '查找成功';
        $data['result'] = $str;
        return $data;
    }

    /**
     * @SWG\Post(path="/honor/word",
     *     tags={"云平台-Research-科研"},
     *     summary="科研详情下载",
     *     description="科研详情下载",
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
     *     @SWG\Response(
     *         response = 200,
     *         description = "返回科室荣誉列表"
     *     ),
     *     @SWG\Response(
     *         response = 403,
     *         description = "查询失败",
     *     )
     * )
     *
     **/
    public function actionWord(){
        $emp_number = yii::$app->request->post('emp_number');
        $url = 'https://dev.xajdyfyyxb.api.ebangong365.com/frontend/web/v1/word/upload-word?emp_number='.$emp_number;
        $data['errno'] = "0";
        $data['status'] = true;
        $data['message'] = '查找成功';
        $data['result'] = $url;
        return $data;
    }


    /**
     * @SWG\Get(path="/honor/upload-word",
     *     tags={"云平台-Research-科研"},
     *     summary="下载",
     *     description="下载",
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
     *     @SWG\Response(
     *         response = 200,
     *         description = "返回科室荣誉列表"
     *     ),
     *     @SWG\Response(
     *         response = 403,
     *         description = "查询失败",
     *     )
     * )
     *
     **/
    public function actionUploadWord(){
        $emp_number = yii::$app->request->get();
        $honor = new Honor();
        $data = $honor->selresearch($emp_number);
        ob_start(); //打开缓冲区
        echo '  
        <html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word" >  
        <head>  
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>  
        <xml><w:WordDocument><w:View>Print</w:View></xml>  
        </head>';
        $str ='<body>
        '.$data['user']['emp_firstname'].'&nbsp;&nbsp;'.$data['user']['education'].','.$data['user']['role'].'<br>
        &nbsp;&nbsp;'.$data['user']['emp_gender'].' ，'.$data['user']['minzu'].'，'.$data['user']['emp_birthday'].'， '.$data['user']['emp_other_id'].'&nbsp;&nbsp;&nbsp;&nbsp;<br>
        &nbsp;&nbsp;西安交通大学第一附属医院   '.$data['user']['work_station'].''.$data['user']['role'].'
        <table border="0" cellpadding="3" cellspacing="0" width="100%" style="table-layout:fixed;border-collapse:separate;border-spacing:10px 20px;">
        <tr >  
        <td width="100" valign="center" colspan="100" ><h4>联系方式</h4></td>  
        </tr>  
        <tr >
        <td width="100" valign="center" colspan="100" >&nbsp;&nbsp;'.$data['user']['custom2'].'</td>  
        </tr>  
        <tr >
        <td width="100" valign="center" colspan="100" >&nbsp;&nbsp;西安交通大学第一附属医院药学部    邮编：'.$data['user']['emp_street2'].'</td>  
        </tr>';

        if(!empty($data['user']['emp_mobile'])){
            $str.='<tr >
        <td width="100" valign="center" colspan="100" >&nbsp;&nbsp;电话：'.$data['user']['emp_mobile'].'</td>
        </tr>';
        }

        if(!empty($data['user']['emp_work_email'])){
            $str.='<tr >
        <td width="100" valign="center" colspan="100" >&nbsp;&nbsp;电话：'.$data['user']['emp_work_email'].'</td>
        </tr>';
        }

        if(!empty($data['user']['weixin_code'])){
            $str.='<tr >
        <td width="100" valign="center" colspan="100" >&nbsp;&nbsp;电话：'.$data['user']['weixin_code'].'</td>
        </tr>';
        }

        if(!empty($data['consider'])){
            $str.='<tr ><td width="100" valign="center" colspan="100" ><h4>研究方向</h4></td></tr>';
            $num = 1;
            foreach ($data['consider'] as $k=>$v){
                $str.='<tr ><td width="100" valign="center" colspan="100" >&nbsp;&nbsp;'.$num.'、'.$v['research'].'</td></tr>';
                $num ++;
            }

        }
        $num = 1;


        if(!empty($data['teach'])){
            $str.='<tr ><td width="100" valign="center" colspan="100" ><h4>教育</h4></td></tr>';
            foreach ($data['teach'] as $k=>$v){
                $str.='<tr ><td width="100" valign="center" colspan="100" >&nbsp;&nbsp;'.$num.'、'.$v['start_time'].'至'.$v['end_time'].''.$v['school'].'</td></tr>';
                $num ++;
            }

        }
        $num = 1;

        if(!empty($data['work'])){
            $str.='<tr><td width="100" valign="center" colspan="100" ><h4>工作</h4></td></tr>';
            foreach ($data['work'] as $k=>$v){
                $str.='<tr ><td width="100" valign="center" colspan="100" >&nbsp;&nbsp;'.$num.'、'.$v['time_in'].'至'.$v['time_out'].''.$v['new_department'].'</td></tr>';
                $num ++;
            }

        }
        $num = 1;

        if(!empty($data['project'])){
            $str.=' <tr ><td width="100" valign="center" colspan="100" ><h4>科研</h4></td></tr>';
            foreach ($data['project'] as $k=>$v){
                $str.='<tr ><td width="100" valign="center" colspan="100" >&nbsp;&nbsp;'.$num.'、'.$v['project_name'].'（项目批准号：'.$v['project_number'].'；批准金额：'.$v['money'].'万元；起止年限：'.$v['start_time'].'至'.$v['end_time'].'）</td></tr>';
                $num ++;
            }

        }
        $num = 1;

        if(!empty($data['thesis'])){
            $str.=' <tr ><td width="100" valign="center" colspan="100" ><h4>论文</h4></td></tr>';
            foreach ($data['thesis'] as $k=>$v){
                $str.='<tr ><td width="100" valign="center" colspan="100" >&nbsp;&nbsp;'.$num.'、'.$v['first_author'].'，'.$v['corresponding_author'].','.$v['thesis_name'].','.$v['publication'].','.$v['volume'].'</td></tr>';
                $num ++;
            }

        }
        $num = 1;

        if(!empty($data['meeting'])){
            $str.=' <tr ><td width="100" valign="center" colspan="100" ><h4>会议</h4></td></tr>';
            foreach ($data['meeting'] as $k=>$v){
                $str.='<tr ><td width="100" valign="center" colspan="100" >&nbsp;&nbsp;'.$num.'、'.$data['user']['emp_firstname'].'，'.$v['first_author'].','.$v['corresponding_author'].','.$v['meeting_name'].','.$v['meeting_time'].'</td></tr>';
                $num ++;
            }

        }
        $num = 1;

        if(!empty($data['honor'])){
            $str.=' <tr ><td width="100" valign="center" colspan="100" ><h4>获奖</h4></td></tr>';
            foreach ($data['honor'] as $k=>$v){
                $str.='<tr ><td width="100" valign="center" colspan="100" >&nbsp;&nbsp;'.$num.'、'.$v['reward_time'].''.$v['honor_name'].'</td></tr>';
                $num ++;
            }

        }$num = 1;

        if(!empty($data['patent'])){
            $str.=' <tr ><td width="100" valign="center" colspan="100" ><h4>专利</h4></td></tr>';
            foreach ($data['patent'] as $k=>$v){
                $str.='<tr ><td width="100" valign="center" colspan="100" >&nbsp;&nbsp;'.$num.'、'.$v['applicant'].''.$v['patentee'].'.'.$v['patent_name'].'.专利类型：'.$v['patent_type'].'；申请号：'.$v['apply_number'].'；申请日期：'.$v['accept_time'].'；公开日：'.$v['authorization_time'].'</td></tr>';
                $num ++;
            }

        }
        $num = 1;


        if(!empty($data['hold'])){
            $str.=' <tr ><td width="100" valign="center" colspan="100" ><h4>社会兼职</h4></td></tr>';
            foreach ($data['hold'] as $k=>$v){
                $str.='<tr ><td width="100" valign="center" colspan="100" >&nbsp;&nbsp;'.$num.'、'.$v['society'].''.$v['job'].'</td></tr>';
                $num ++;
            }

        }
        $num = 1;



        if(!empty($data['journal'])){
            $str.=' <tr ><td width="100" valign="center" colspan="100" ><h4>杂志编委</h4></td></tr>';
            foreach ($data['journal'] as $k=>$v){
                $str.='<tr ><td width="100" valign="center" colspan="100" >&nbsp;&nbsp;'.$num.'、'.$v['journal_name'].''.$v['job'].'</td></tr>';
                $num ++;
            }

        }
        $num = 1;

        if(!empty($data['writings'])){
            $str.=' <tr ><td width="100" valign="center" colspan="100" ><h4>专著</h4></td></tr>';
            foreach ($data['writings'] as $k=>$v){
                $str.='<tr ><td width="100" valign="center" colspan="100" >&nbsp;&nbsp;'.$num.'、'.$v['editor'].'主编.'.$v['writings_name'].'.'.$v['publish_unit'].'总字数：'.$v['all_count'].'参编字数：'.$v['editor_count'].'</td></tr>';
                $num ++;
            }
        }


        echo $str.'</table></body>';
        header("Cache-Control: no-store"); //所有缓存机制在整个请求/响应链中必须服从的指令
        Header("Content-type: application/octet-stream");  //用于定义网络文件的类型和网页的编码，决定文件接收方将以什么形式、什么编码读取这个文件
        Header("Accept-Ranges: bytes");  //Range防止断网重新请求 。
        if (strpos($_SERVER["HTTP_USER_AGENT"],'MSIE')) {
            header('Content-Disposition: attachment; filename=test.doc');
        }else if (strpos($_SERVER["HTTP_USER_AGENT"],'Firefox')) {
            Header('Content-Disposition: attachment; filename=test.doc');
        } else {
            header('Content-Disposition: attachment; filename=test.doc');
        }
        header("Pragma:no-cache"); //不能被浏览器缓存
        header("Expires:0");  //页面从浏览器高速缓存到期的时间分钟数，设定expires属性为0，将使对一页面的新的请求从服务器产生
        ob_end_flush();//输出全部内容到浏览器

    }





    /**
     * @SWG\Post(path="/honor/research",
     *     tags={"云平台-Research-科研"},
     *     summary="科研",
     *     description="科研",
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
     *        name = "emp_firstname",
     *        description = "员工姓名",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "work_station",
     *        description = "所在小组",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "consider_research",
     *        description = "研究方向",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "teach_school",
     *        description = "学校名称",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "teach_school_type",
     *        description = "学校类型",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "teach_major",
     *        description = "专业",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "teach_record_id",
     *        description = "学历id",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "project_status",
     *        description = "状态",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "project_name",
     *        description = "项目名称",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "project_number",
     *        description = "项目编号",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "project_leading",
     *        description = "负责人",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "project_participant",
     *        description = "参与人",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "project_support_unit",
     *        description = "依托单位",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "project_level_id",
     *        description = "项目级别",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "project_source_id",
     *        description = "项目来源",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "project_type_id",
     *        description = "项目类别",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "thesis_type_id",
     *        description = "论文类别",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "thesis_article_type_id",
     *        description = "文章类型",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "thesis_publication_type_id",
     *        description = "刊物类型",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "thesis_name",
     *        description = "论文题目",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "thesis_first_author_type",
     *        description = "第一作者类型",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "thesis_first_author",
     *        description = "第一作者",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "thesis_first_author_unit",
     *        description = "第一作者单位",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "thesis_corresponding_author_type",
     *        description = "通讯作者类型",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "thesis_corresponding_author",
     *        description = "通讯作者",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "thesis_corresponding_author_unit",
     *        description = "通讯作者单位",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "thesis_publication",
     *        description = "发表刊物论文集",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "thesis_is_include",
     *        description = "是否收录",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "meeting_language",
     *        description = "会议语言",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "meeting_name",
     *        description = "会议名称",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "meeting_time",
     *        description = "会议时间",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "meeting_host_unit",
     *        description = "主办单位",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "meeting_thesis_type",
     *        description = "论文类型",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "meeting_thesis_name",
     *        description = "论文题目",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "meeting_is_exchange",
     *        description = "大会交流",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "honor_status",
     *        description = "状态",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "honor_reward_type",
     *        description = "奖励种类",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "honor_emp_name",
     *        description = "获奖者",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "honor_item_name",
     *        description = "项目名称",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "honor_accept_award",
     *        description = "完成单位",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "honor_name",
     *        description = "获奖名称",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "honor_reward_class",
     *        description = "奖励类别",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "patent_class",
     *        description = "专利种类",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "patent_department",
     *        description = "科室",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "patent_name",
     *        description = "专利名称",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "patent_type",
     *        description = "专利类型",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "patent_applicant",
     *        description = "申请人",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "patent_patentee",
     *        description = "专利权人",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "patent_number",
     *        description = "专利号",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "patent_apply_number",
     *        description = "专利申请号",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "hold_society",
     *        description = "协会/学会名称",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "hold_job",
     *        description = "职务",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "journal_name",
     *        description = "杂志名称",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "journal_job",
     *        description = "职务",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "writings_name",
     *        description = "著作名称",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "writings_editor",
     *        description = "主编",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "writings_subeditor",
     *        description = "副主编",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "writings_partake_editor",
     *        description = "参编",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "writings_publish_unit",
     *        description = "出版单位",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "writings_type_id",
         *        description = "著作类别",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "返回科研列表"
     *     ),
     *     @SWG\Response(
     *         response = 403,
     *         description = "查询失败",
     *     )
     * )
     *
     **/
    public function actionResearch(){
        $data = yii::$app->request->post();
        $honor = new Honor();
        $model = $honor->research($data);
        return $model;
    }






    



}