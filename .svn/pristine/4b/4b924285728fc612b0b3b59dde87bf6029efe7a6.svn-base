<?php
namespace frontend\controllers\v1;

use common\models\Journal\Journal;
use opw\react\ReactAsset;
use yii\rest\ActiveController;
use yii\web\Response;
use yii;

class JournalController extends \common\rest\Controller
{


    /**
     * @var string
     */
    public $modelClass = 'common\models\Journal';

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
     * @SWG\Post(path="/journal/journal-list",
     *     tags={"云平台-Journal-杂志编委"},
     *     summary="杂志编委列表",
     *     description="杂志编委列表",
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
     *        name = "journal_name",
     *        description = "杂志名称",
     *        required = false,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "job",
     *        description = "职务",
     *        required = false,
     *        type = "string",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "start_time",
     *        description = "任聘时间",
     *        required = false,
     *        type = "string",
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "返回兼职列表"
     *     ),
     *     @SWG\Response(
     *         response = 403,
     *         description = "查询失败",
     *     )
     * )
     *
     **/
    public function actionJournalList(){
        $data = yii::$app->request->post();
        if($data['emp_number'] == ''){
            $data['emp_number'] = $this->empNumber;
        }
        $journal = new Journal();
        $model = $journal->journallist($data);
        return $model;
    }




    /**
     * @SWG\Post(path="/journal/journal-add",
     *     tags={"云平台-Journal-杂志编委"},
     *     summary="添加杂志",
     *     description="添加杂志",
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
     *        name = "journal_name",
     *        description = "杂志名称",
     *        required = true,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "job",
     *        description = "职务",
     *        required = true,
     *        type = "string",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "start_time",
     *        description = "任聘时间",
     *        required = true,
     *        type = "string",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "remark",
     *        description = "备注",
     *        required = false,
     *        type = "string",
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "返回杂志列表"
     *     ),
     *     @SWG\Response(
     *         response = 403,
     *         description = "查询失败",
     *     )
     * )
     *
     **/
    public function actionJournalAdd(){
        $data = yii::$app->request->post();
        if($data['emp_number'] == ''){
            $data['emp_number'] = $this->empNumber;
        }
        $journal = new Journal();
        $model = $journal->journaladd($data);
        return $model;
    }

    /**
     * @SWG\Post(path="/journal/journal-sel",
     *     tags={"云平台-Journal-杂志编委"},
     *     summary="查询详情",
     *     description="查询详情",
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
     *        description = "杂志表id",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "返回杂志列表"
     *     ),
     *     @SWG\Response(
     *         response = 403,
     *         description = "查询失败",
     *     )
     * )
     *
     **/
    public function actionJournalSel(){
        $id = yii::$app->request->post();
        $journal = new Journal();
        $model = $journal->journalsel($id);
        return $model;
    }


    /**
     * @SWG\Post(path="/journal/journal-update",
     *     tags={"云平台-Journal-杂志编委"},
     *     summary="修改杂志",
     *     description="修改杂志",
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
     *        description = "杂志表id",
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
     *        name = "journal_name",
     *        description = "杂志名称",
     *        required = true,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "job",
     *        description = "职务",
     *        required = true,
     *        type = "string",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "start_time",
     *        description = "任聘时间",
     *        required = true,
     *        type = "string",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "remark",
     *        description = "备注",
     *        required = false,
     *        type = "string",
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "返回杂志列表"
     *     ),
     *     @SWG\Response(
     *         response = 403,
     *         description = "查询失败",
     *     )
     * )
     *
     **/
    public function actionJournalUpdate(){
        $data = yii::$app->request->post();
        $journal = new Journal();
        $model = $journal->holdupdate($data);
        return $model;
    }




    /**
     * @SWG\Post(path="/journal/journal-del",
     *     tags={"云平台-Journal-杂志编委"},
     *     summary="删除杂志",
     *     description="删除杂志",
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
     *        description = "杂志表id",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "返回兼职列表"
     *     ),
     *     @SWG\Response(
     *         response = 403,
     *         description = "查询失败",
     *     )
     * )
     *
     **/
    public function actionJournalDel(){
        $id = yii::$app->request->post('id');
        $journal = new Journal();
        $model = $journal->journaldel($id);
        return $model;
    }




}

