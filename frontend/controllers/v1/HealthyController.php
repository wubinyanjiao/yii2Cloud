<?php
namespace frontend\controllers\v1;

use common\models\Employee;
use common\models\healthy\Healthy;
use yii\rest\ActiveController;
use yii\web\Response;
use yii;
use PHPExcel;

class HealthyController extends \common\rest\Controller
{

    public $modelClass = 'common\models\healthy\Healthy';

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

    //获取员工图片
    public function actionEmpName(){
        $id = yii::$app->request->post('emp_number');
        $healthy = new Healthy();
        $model = $healthy->empname($id);
        return $model;
    }

    /**
     * @SWG\Post(path="/healthy/healthylist",
     *     tags={"云平台-Healthy-健康管理"},
     *     summary="员工体检列表",
     *     description="员工体检列表",
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
     *        name = "s_years",
     *        description = "开始时间",
     *        required = false,
     *        type = "string",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "b_years",
     *        description = "结束时间",
     *        required = false,
     *        type = "string",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "healthy_name",
     *        description = "体检项目名称",
     *        required = false,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "is_qualified",
     *        description = "是否合格，1是合格，0是不合格",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *        enum = {0,1}
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "返回个人体检项目列表"
     *     ),
     *     @SWG\Response(
     *         response = 403,
     *         description = "查询失败",
     *     )
     * )
     *
     **/
    public function actionHealthylist()
    {
        $data = yii::$app->request->post();
        if(!isset($data['emp_number'])){
            $data['emp_number'] = $this->empNumber;
        }
        $healthy = new Healthy();
        $model = $healthy->sellist($data);
        return $model;
    }

    //页数
    public function actionHealthyPage()
    {
        $data = yii::$app->request->post();
        $healthy = new Healthy();
        $pagenum = $healthy->pagenum($data);
        return $pagenum;
    }

    //管理员页数
    public function actionHealthylistPage()
    {
        $data = yii::$app->request->post();
        $healthy = new Healthy();
        $pagenum = $healthy->listpagenum($data);
        return $pagenum;
    }




    /**
     * @SWG\Post(path="/healthy/list",
     *     tags={"云平台-Healthy-健康管理"},
     *     summary="管理员健康管理列表",
     *     description="管理员健康管理列表",
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
     *        name = "emp_name",
     *        description = "员工姓名",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "s_years",
     *        description = "开始时间",
     *        required = false,
     *        type = "string",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "b_years",
     *        description = "结束时间",
     *        required = false,
     *        type = "string",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "healthy_name",
     *        description = "体检项目名称",
     *        required = false,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "page",
     *        description = "页数",
     *        required = true,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "is_qualified",
     *        description = "是否合格，1是合格，0是不合格",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *        enum = {0,1}
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "返回个人体检项目列表"
     *     ),
     *     @SWG\Response(
     *         response = 403,
     *         description = "Data Validation Failed 查询失败",
     *     )
     * )
     *
     **/
    public function actionList()
    {
        $data = yii::$app->request->post();
        $healthy = new Healthy();
        $model = $healthy->managementlist($data);
        return $model;
    }

    /**
     * @SWG\Post(path="/healthy/healthy-add",
     *     tags={"云平台-Healthy-健康管理"},
     *     summary="添加体检项目",
     *     description="添加体检项目",
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
     *        name = "healthy_years",
     *        description = "体检年份",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "healthy_name",
     *        description = "体检项目",
     *        required = true,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "is_qualified",
     *        description = "是否合格，1是合格，0时不合格",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *        enum = {0,1}
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "添加成功"
     *     ),
     *     @SWG\Response(
     *         response = 403,
     *         description = "失败"
     *     ),
     * )
     *
     **/
    public function actionHealthyAdd(){
        $data = yii::$app->request->post();
        $healthy = new Healthy();
        $model = $healthy->healthyadd($data);
        if($model == false){
            $this->serializer['message'] = '操作失败';
        }
        return $model;
    }




    /**
     * @SWG\Post(path="/healthy/healthy-listadd",
     *     tags={"云平台-Healthy-健康管理"},
     *     summary="管理员添加体检项目",
     *     description="管理员添加体检项目",
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
     *        name = "emp_name",
     *        description = "员工姓名",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "healthy_years",
     *        description = "体检年份",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "healthy_name",
     *        description = "体检项目",
     *        required = true,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "is_qualified",
     *        description = "是否合格，1是合格，0时不合格",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *        enum = {0,1}
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "添加成功"
     *     ),
     *     @SWG\Response(
     *         response = 403,
     *         description = "失败"
     *     ),
     * )
     *
     **/
    public function actionHealthyListadd(){
        $data = yii::$app->request->post();
        $healthy = new Healthy();
        $model = $healthy->healthylistadd($data);
        if($model == false){
            $this->serializer['errno'] = '403';
            $this->serializer['status'] = false;
            $this->serializer['message'] = '请填写正确的姓名';
        }
        return $model;
    }

    /**
     * @SWG\Post(path="/healthy/healthy-del",
     *     tags={"云平台-Healthy-健康管理"},
     *     summary="删除体检项目",
     *     description="删除体检项目",
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
     *        description = "体检项目id",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "成功"
     *     ),
     *     @SWG\Response(
     *         response = 403,
     *         description = "删除失败"
     *     ),
     * )
     *
     **/
    public function actionHealthyDel(){
        $id = yii::$app->request->post('id');
        $healthy = new Healthy();
        $model = $healthy->healthydel($id);
        return $model;
    }









    /**
     * @SWG\Post(path="/healthy/healthy-attachment",
     *     tags={"云平台-Healthy-健康管理"},
     *     summary="体检项目附件列表",
     *     description="附件列表",
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
     *        name = "healthy_id",
     *        description = "体检项目id",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "附件列表"
     *     ),
     * )
     *
     **/
    public function actionHealthyAttachment(){
        $emp_number = yii::$app->request->post('emp_number');
        if($emp_number == ''){
            $emp_number = $this->empNumber;
        }
        $healthy_id = yii::$app->request->post('healthy_id');
        $healthy = new Healthy();
        $model = $healthy->healthyattachment($emp_number,$healthy_id);
        return $model;
    }



    public function actionHealthylistAttachment(){
        $healthy = new Healthy();
        $model = $healthy->healthylistattachment();
        return $model;
    }


    /**
     * @SWG\Post(path="/healthy/emphealthy-sel",
     *     tags={"云平台-Healthy-健康管理"},
     *     summary="员工体检项目详情",
     *     description="员工体检项目详情",
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
     *        name = "healthy_id",
     *        description = "体检项目id",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "返回添加体检项目页"
     *     ),
     * )
     *
     **/
    public function actionEmphealthySel(){
        $healthy_id = yii::$app->request->post('healthy_id');
        $healthy = new Healthy();
        $model = $healthy->emphealthsel($healthy_id);
        return $model;
    }


    /**
     * @SWG\Post(path="/healthy/emphealthy-update",
     *     tags={"云平台-Healthy-健康管理"},
     *     summary="员工体检项目修改",
     *     description="员工体检项目修改",
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
     *        name = "healthy_id",
     *        description = "项目id",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "healthy_name",
     *        description = "项目名称",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "healthy_years",
     *        description = "体检时间",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "is_qualified",
     *        description = "是否合格，1是合格，0时不合格",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *        enum = {0,1}
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "返回添加体检项目页"
     *     ),
     * )
     *
     **/
    public function actionEmphealthyUpdate(){
        $data = yii::$app->request->post();
        $healthy = new Healthy();
        $model = $healthy->emphealthyupdate($data);
        return $model;
    }


    /**
     * @SWG\Post(path="/healthy/attachment-del",
     *     tags={"云平台-Healthy-健康管理"},
     *     summary="删除附件",
     *     description="删除附件",
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
     *        name = "eattach_id[]",
     *        description = "附件id 数组",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "返回添加体检项目页"
     *     ),
     * )
     *
     **/
    public function actionAttachmentDel(){
        $emp_number = yii::$app->request->post('emp_number');
        $eattach_id = yii::$app->request->post('eattach_id');
        $healthy = new Healthy();
        $model = $healthy->attachmentdel($emp_number,$eattach_id);
        return $model;
    }



    /**
     * @SWG\Post(path="/healthy/attachmentlist-del",
     *     tags={"云平台-Healthy-健康管理"},
     *     summary="管理员删除附件",
     *     description="管理员删除附件",
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
     *        description = "员工id 数组",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "id[]",
     *        description = "附件id 数组",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "返回添加体检项目页"
     *     ),
     * )
     *
     **/
    public function actionAttachmentlistDel(){
        $emp_number = yii::$app->request->post('emp_number');
        $eattach_id = yii::$app->request->post('eattach_id');
        $healthy = new Healthy();
        $model = $healthy->attachmentlistdel($emp_number,$eattach_id);
        return $model;
    }




    /**
     * @SWG\Post(path="/healthy/update-desc",
     *     tags={"云平台-Healthy-健康管理"},
     *     summary="仅保存评论",
     *     description="仅保存评论",
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
     *        name = "desc",
     *        description = "评论",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "eattach_id",
     *        description = "附件id",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "返回添加体检项目页"
     *     ),
     * )
     *
     **/
    public function actionUpdateDesc(){
        $data = yii::$app->request->post();
        $healthy = new Healthy();
        $model = $healthy->updatedessc($data);
        return $model;
    }





    /**
     * @SWG\Post(path="/healthy/excel",
     *     tags={"云平台-Healthy-健康管理"},
     *     summary="导出excel",
     *     description="导出excel",
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
     *         description = "返回添加体检项目页"
     *     ),
     * )
     *
     **/
    public function actionExcel()
    {
        $healthy = new Healthy();
        $arr = $healthy->selexcel();
        return $arr;
    }


}