<?php
namespace frontend\controllers\v1;

use common\models\emptitle\EmpTitle;
use common\models\emptitle\Type;
use yii\web\Response;
use yii;

class EmpTitleController extends \common\rest\Controller
{


    /**
     * @var string
     */
    public $modelClass = 'common\models\employee\EmpTitle';

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
     * @SWG\Post(path="/emp-title/type",
     *     tags={"云平台-emptitle-职称"},
     *     summary="职称层级列表",
     *     description="职称层级列表",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "Token",
     *        description = "Token",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "返回员工信息"
     *     ),
     * )
     *
     **/
    public function actionType()
    {
        $title = new EmpTitle();
        $model = $title->typelist();
        return $model;
    }




    /**
     * @SWG\Post(path="/emp-title/add",
     *     tags={"云平台-emptitle-职称"},
     *     summary="职称层级列表",
     *     description="职称层级列表",
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
     *        name = "time",
     *        description = "time",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "title",
     *        description = "职称",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "remarks",
     *        description = "备注",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "返回员工信息"
     *     ),
     * )
     *
     **/
    public function actionAdd(){
        $data = yii::$app->request->post();
        if($data['emp_number'] == ''){
            $data['emp_number'] = $this->empNumber;
        }

        $title = new EmpTitle();
        $model = $title->titleadd($data);
        return $model;
    }




    /**
     * @SWG\Post(path="/emp-title/list",
     *     tags={"云平台-emptitle-职称"},
     *     summary="职称列表",
     *     description="职称列表",
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
     *        name = "time",
     *        description = "time",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "class_id",
     *        description = "职称",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "返回员工信息"
     *     ),
     * )
     *
     **/
    public function actionList(){
        $data = yii::$app->request->post();
        if($data['emp_number'] == ''){
            $data['emp_number'] = $this->empNumber;
        }
        $title = new EmpTitle();
        $model = $title->titlelist($data);
        return $model;
    }



    /**
     * @SWG\Post(path="/emp-title/classlist",
     *     tags={"云平台-emptitle-职称"},
     *     summary="职称列表",
     *     description="职称列表",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "Token",
     *        description = "Token",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "返回员工信息"
     *     ),
     * )
     *
     **/
    public function actionClasslist(){
        $query = Type::find()->asArray()->where(['fu_id'=>1])->all();
        return $query;
    }


    /**
     * @SWG\Post(path="/emp-title/sel",
     *     tags={"云平台-emptitle-职称"},
     *     summary="详情",
     *     description="详情",
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
     *        description = "表id",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "返回员工信息"
     *     ),
     * )
     *
     **/
    public function actionSel(){
        $id = yii::$app->request->post('id');
        $title = new EmpTitle();
        $model = $title->titlesel($id);
        return $model;
    }



    /**
     * @SWG\Post(path="/emp-title/uptitle",
     *     tags={"云平台-emptitle-职称"},
     *     summary="修改",
     *     description="修改",
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
     *        description = "表id",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "time",
     *        description = "time",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "title",
     *        description = "职称",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "remarks",
     *        description = "备注",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "返回员工信息"
     *     ),
     * )
     *
     **/
    public function actionUptitle(){
        $data = yii::$app->request->post();
        $title = new EmpTitle();
        $model = $title->uptitle($data);
        return $model;
    }



    /**
     * @SWG\Post(path="/emp-title/del",
     *     tags={"云平台-emptitle-职称"},
     *     summary="删除",
     *     description="删除",
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
     *        description = "表id",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "返回员工信息"
     *     ),
     * )
     *
     **/
    public function actionDel(){
        $id = yii::$app->request->post('id');
        $title = new EmpTitle();
        $model = $title->titledel($id);
        return $model;
    }



}

