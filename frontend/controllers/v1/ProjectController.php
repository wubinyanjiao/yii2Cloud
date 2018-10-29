<?php
namespace frontend\controllers\v1;

use common\models\consider\Consider;
use common\models\employee\Employee;
use common\models\Leave;
use common\models\project\Level;
use common\models\project\Project;
use common\models\project\Source;
use common\models\project\Type;
use common\models\teach\Teach;
use common\models\user\User;
use opw\react\ReactAsset;
use yii\rest\ActiveController;
use yii\web\Response;
use yii;

class ProjectController extends \common\rest\Controller
{


    /**
     * @var string
     */
    public $modelClass = 'common\models\Project';

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
     * @SWG\Post(path="/project/level",
     *     tags={"云平台-Project-科研项目"},
     *     summary="级别",
     *     description="级别",
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
     *         description = "级别列表"
     *     ),
     *     @SWG\Response(
     *         response = 403,
     *         description = "添加失败",
     *     )
     * )
     *
     **/
    public function actionLevel(){
        $level = Level::find()->asArray()->all();
        return $level;
    }


    /**
     * @SWG\Post(path="/project/source",
     *     tags={"云平台-Project-科研项目"},
     *     summary="来源",
     *     description="来源",
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
     *         description = "级别列表"
     *     ),
     *     @SWG\Response(
     *         response = 403,
     *         description = "添加失败",
     *     )
     * )
     *
     **/
    public function actionSource(){
        $source = Source::find()->asArray()->all();
        return $source;
    }


    /**
     * @SWG\Post(path="/project/type",
     *     tags={"云平台-Project-科研项目"},
     *     summary="类别",
     *     description="类别",
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
     *         description = "级别列表"
     *     ),
     *     @SWG\Response(
     *         response = 403,
     *         description = "添加失败",
     *     )
     * )
     *
     **/
    public function actionType(){
        $type = Type::find()->asArray()->all();
        return $type;
    }



    /**
     * @SWG\Post(path="/project/project-list",
     *     tags={"云平台-Project-科研项目"},
     *     summary="科研项目列表",
     *     description="科研项目列表",
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
     *        name = "project_name",
     *        description = "项目名称",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "status",
     *        description = "状态",
     *        required = true,
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
     *        name = "leading",
     *        description = "负责人",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "participant",
     *        description = "参与者",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "ranking",
     *        description = "本人排名",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "support_unit",
     *        description = "依托单位",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "level_id",
     *        description = "级别id",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "source_id",
     *        description = "来源id",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "type_id",
     *        description = "类别id",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "apply_time",
     *        description = "申请时间",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "start_time",
     *        description = "开始时间",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "end_time",
     *        description = "结束时间",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "money",
     *        description = "立项金额",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "科研列表"
     *     ),
     *     @SWG\Response(
     *         response = 403,
     *         description = "查询失败",
     *     )
     * )
     *
     **/
    public function actionProjectList(){
        $data = yii::$app->request->post();
        if($data['emp_number'] == ''){
            $data['emp_number'] = $this->empNumber;
        }
        $project = new Project();
        $model = $project->projectlist($data);
        return $model;
    }



    /**
     * @SWG\Post(path="/project/project-add",
     *     tags={"云平台-Project-科研项目"},
     *     summary="添加科研项目",
     *     description="添加科研项目",
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
     *        name = "project_name",
     *        description = "项目名称",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "status",
     *        description = "状态",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "project_number",
     *        description = "项目编号",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "leading",
     *        description = "负责人",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "participant",
     *        description = "参与者",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "ranking",
     *        description = "本人排名",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "support_unit",
     *        description = "依托单位",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "level_id",
     *        description = "级别id",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "source_id",
     *        description = "来源id",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "type_id",
     *        description = "类别id",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "apply_time",
     *        description = "申请时间",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "start_time",
     *        description = "开始时间",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "end_time",
     *        description = "结束时间",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "money",
     *        description = "立项金额",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "科研列表"
     *     ),
     *     @SWG\Response(
     *         response = 403,
     *         description = "添加失败",
     *     )
     * )
     *
     **/
    public function actionProjectAdd(){
        $data = yii::$app->request->post();
        if($data['emp_number'] == ''){
            $data['emp_number'] = $this->empNumber;
        }
        $project = new Project();
        $model = $project->projectadd($data);
        return $model;
    }


    /**
     * @SWG\Post(path="/project/project-sel",
     *     tags={"云平台-Project-科研项目"},
     *     summary="修改科研项目",
     *     description="修改科研项目",
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
     *        description = "科研表id",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "科研列表"
     *     ),
     *     @SWG\Response(
     *         response = 403,
     *         description = "添加失败",
     *     )
     * )
     *
     **/
    public function actionProjectSel(){
        $id = yii::$app->request->post('id');
        $project = new Project();
        $model = $project->projectsel($id);
        return $model;
    }



    /**
     * @SWG\Post(path="/project/project-update",
     *     tags={"云平台-Project-科研项目"},
     *     summary="修改科研项目",
     *     description="修改科研项目",
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
     *        description = "科研表id",
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
     *        name = "project_name",
     *        description = "项目名称",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "status",
     *        description = "状态",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "project_number",
     *        description = "项目编号",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "leading",
     *        description = "负责人",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "participant",
     *        description = "参与者",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "ranking",
     *        description = "本人排名",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "support_unit",
     *        description = "依托单位",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "level_id",
     *        description = "级别id",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "source_id",
     *        description = "来源id",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "type_id",
     *        description = "类别id",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "apply_time",
     *        description = "申请时间",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "start_time",
     *        description = "开始时间",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "end_time",
     *        description = "结束时间",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "money",
     *        description = "立项金额",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "科研列表"
     *     ),
     *     @SWG\Response(
     *         response = 403,
     *         description = "添加失败",
     *     )
     * )
     *
     **/
    public function actionProjectUpdate(){
        $data = yii::$app->request->post();
        $project = new Project();
        $model = $project->projectupdate($data);
        return $model;
    }


    /**
     * @SWG\Post(path="/project/atta-del",
     *     tags={"云平台-Project-科研项目"},
     *     summary="附件删除",
     *     description="附件删除",
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
     *         description = "科研项目列表"
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
        $project = new Project();
        $model = $project->attadel($eattach_id,$emp_number);
        return $model;
    }


    /**
     * @SWG\Post(path="/project/project-del",
     *     tags={"云平台-Project-科研项目"},
     *     summary="科研项目删除",
     *     description="科研项目删除",
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
     *        description = "科研表id",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "科研列表"
     *     ),
     *     @SWG\Response(
     *         response = 403,
     *         description = "删除失败",
     *     )
     * )
     *
     **/
    public function actionProjectDel(){
        $id = yii::$app->request->post('id');
        $project = new Project();
        $model = $project->projectdel($id);
        return $model;
    }


}

