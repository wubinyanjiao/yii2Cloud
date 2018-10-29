<?php
namespace frontend\controllers\v1;

use common\models\consider\Consider;
use common\models\employee\Employee;
use common\models\teach\Teach;
use common\models\user\User;
use opw\react\ReactAsset;
use yii\rest\ActiveController;
use yii\web\Response;
use yii;

class ConsiderController extends \common\rest\Controller
{


    /**
     * @var string
     */
    public $modelClass = 'common\models\Consider';

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
     * @SWG\Post(path="/consider/consider-list",
     *     tags={"云平台-Consider-研究方向"},
     *     summary="研究方向列表",
     *     description="研究方向列表",
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
     *         description = "研究列表"
     *     ),
     *     @SWG\Response(
     *         response = 403,
     *         description = "添加失败",
     *     )
     * )
     *
     **/
    public function actionConsiderList(){
        $emp_number = yii::$app->request->post('emp_number');
        if($emp_number == null){
            $emp_number = $this->empNumber;
        }
        $consider = new Consider();
        $model = $consider->considerlist($emp_number);
        return $model;
    }


    /**
     * @SWG\Post(path="/consider/consider-add",
     *     tags={"云平台-Consider-研究方向"},
     *     summary="添加研究方向",
     *     description="添加研究方向",
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
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "research",
     *        description = "研究方向",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "研究列表"
     *     ),
     *     @SWG\Response(
     *         response = 403,
     *         description = "添加失败",
     *     )
     * )
     *
     **/
    public function actionConsiderAdd(){
        $data = yii::$app->request->post();
        if($data['emp_number'] == ''){
            $data['emp_number'] = $this->empNumber;
        }
        $consider = new Consider();
        $model = $consider->consideradd($data);
        return $model;

    }



    /**
     * @SWG\Post(path="/consider/consider-sel",
     *     tags={"云平台-Consider-研究方向"},
     *     summary="研究方向详情",
     *     description="研究方向详情",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "id",
     *        description = "研究表id",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "研究列表"
     *     ),
     *     @SWG\Response(
     *         response = 403,
     *         description = "添加失败",
     *     )
     * )
     *
     **/
    public function actionConsiderSel(){
        $id = yii::$app->request->post('id');
        $consider = new Consider();
        $model = $consider->considersel($id);
        return $model;
    }




    /**
     * @SWG\Post(path="/consider/consider-update",
     *     tags={"云平台-Consider-研究方向"},
     *     summary="修改研究方向",
     *     description="修改研究方向",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "id",
     *        description = "研究表id",
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
     *        name = "research",
     *        description = "研究方向",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "研究列表"
     *     ),
     *     @SWG\Response(
     *         response = 403,
     *         description = "添加失败",
     *     )
     * )
     *
     **/
    public function actionConsiderUpdate(){
        $data = yii::$app->request->post();
        $consider = new Consider();
        $model = $consider->considerupdate($data);
        return $model;

    }


    /**
     * @SWG\Post(path="/consider/atta-del",
     *     tags={"云平台-Consider-研究方向"},
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
     *         description = "研究方向列表"
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
        $consider = new Consider();
        $model = $consider->attadel($eattach_id,$emp_number);
        return $model;
    }


    /**
     * @SWG\Post(path="/consider/consider-del",
     *     tags={"云平台-Consider-研究方向"},
     *     summary="研究删除",
     *     description="研究删除",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "id",
     *        description = "研究表id",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "研究方向列表"
     *     ),
     *     @SWG\Response(
     *         response = 403,
     *         description = "删除失败",
     *     )
     * )
     *
     **/
    public function actionConsiderDel(){
        $id = yii::$app->request->post('id');
        $consider = new Consider();
        $model = $consider->considerdel($id);
        return $model;
    }






}

