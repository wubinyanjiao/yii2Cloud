<?php
namespace frontend\controllers\v1;

use common\models\Article;
use common\models\employee\Employee;
use common\models\teach\Teach;
use common\models\thesis\Publication;
use common\models\thesis\Thesis;
use common\models\thesis\ThesisType;
use common\models\user\User;
use GeckoPackages\PHPUnit\Asserts\AssertHelper;
use opw\react\ReactAsset;
use yii\rest\ActiveController;
use yii\web\Response;
use yii;

class ThesisController extends \common\rest\Controller
{


    /**
     * @var string
     */
    public $modelClass = 'common\models\Thesis';

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
     * @SWG\Post(path="/thesis/type",
     *     tags={"云平台-Thesis-论文"},
     *     summary="论文类型",
     *     description="论文类型",
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
        $query = ThesisType::find()->all();
        return $query;
    }


    /**
     * @SWG\Post(path="/thesis/article",
     *     tags={"云平台-Thesis-论文"},
     *     summary="文章类型",
     *     description="文章类型",
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
    public function actionArticle(){
        $query = \common\models\thesis\Article::find()->all();
        return $query;
    }


    /**
     * @SWG\Post(path="/thesis/publication",
     *     tags={"云平台-Thesis-论文"},
     *     summary="刊物类型",
     *     description="刊物类型",
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
    public function actionPublication(){
        $query = Publication::find()->all();
        return $query;
    }


    /**
     * @SWG\Post(path="/thesis/thesis-add",
     *     tags={"云平台-Thesis-论文"},
     *     summary="添加论文",
     *     description="添加论文",
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
     *        name = "thesis_name",
     *        description = "论文题目",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "first_author_type",
     *        description = "第一作者类型",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "first_author",
     *        description = "第一作者",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "first_author_unit",
     *        description = "第一作者单位",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "corresponding_author_type",
     *        description = "通讯作者类型",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "corresponding_author",
     *        description = "通讯作者",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "corresponding_author_unit",
     *        description = "通讯作者单位",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "publication",
     *        description = "发表的刊物，论文集",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "volume",
     *        description = "年，卷，期，页码",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "ISSN",
     *        description = "issn号",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "influence",
     *        description = "影响",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "url",
     *        description = "网络连接",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "is_include",
     *        description = "是否收录   0：否   1：是",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *        enum = {0,1}
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "thesis_type_id",
     *        description = "论文类别id",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "article_type_id",
     *        description = "文章类型id",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "publication_type_id",
     *        description = "刊物类型id",
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
     *         description = "论文列表"
     *     ),
     *     @SWG\Response(
     *         response = 403,
     *         description = "添加失败",
     *     )
     * )
     *
     **/
    public function actionThesisAdd(){
        $data = yii::$app->request->post();
        if($data['emp_number'] == ''){
            $data['emp_number'] = $this->empNumber;
        }

        $thesis = new Thesis();
        $model = $thesis->thesisadd($data);
        return $model;
    }



    /**
     * @SWG\Post(path="/thesis/thesis-update",
     *     tags={"云平台-Thesis-论文"},
     *     summary="修改论文",
     *     description="修改论文",
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
     *        description = "论文表id",
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
     *        name = "thesis_name",
     *        description = "论文题目",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "first_author_type",
     *        description = "第一作者类型",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "first_author",
     *        description = "第一作者",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "first_author_unit",
     *        description = "第一作者单位",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "corresponding_author_type",
     *        description = "通讯作者类型",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "corresponding_author",
     *        description = "通讯作者",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "corresponding_author_unit",
     *        description = "通讯作者单位",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "publication",
     *        description = "发表的刊物，论文集",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "volume",
     *        description = "年，卷，期，页码",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "ISSN",
     *        description = "issn号",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "influence",
     *        description = "影响",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "url",
     *        description = "网络连接",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "is_include",
     *        description = "是否收录   0：否   1：是",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *        enum = {0,1}
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "thesis_type_id",
     *        description = "论文类别id",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "article_type_id",
     *        description = "文章类型id",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "publication_type_id",
     *        description = "刊物类型id",
     *        required = true,
     *        type = "integer"
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
     *         description = "论文列表"
     *     ),
     *     @SWG\Response(
     *         response = 403,
     *         description = "修改失败",
     *     )
     * )
     *
     **/
    public function actionThesisUpdate(){
        $data = yii::$app->request->post();
        $thesis = new Thesis();
        $model = $thesis->thesisupdate($data);
        return $model;
    }



    /**
     * @SWG\Post(path="/thesis/list",
     *     tags={"云平台-Thesis-论文"},
     *     summary="论文列表",
     *     description="论文列表",
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
     *        name = "thesis_name",
     *        description = "论文题目",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "first_author_type",
     *        description = "第一作者类型",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "first_author",
     *        description = "第一作者",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "first_author_unit",
     *        description = "第一作者单位",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "corresponding_author_type",
     *        description = "通讯作者类型",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "corresponding_author",
     *        description = "通讯作者",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "corresponding_author_unit",
     *        description = "通讯作者单位",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "publication",
     *        description = "发表的刊物，论文集",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "volume",
     *        description = "年，卷，期，页码",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "ISSN",
     *        description = "issn号",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "influence",
     *        description = "影响",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "url",
     *        description = "网络连接",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "is_include",
     *        description = "是否收录   0：否   1：是",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *        enum = {0,1}
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "thesis_type_id",
     *        description = "论文类别id",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "article_type_id",
     *        description = "文章类型id",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "publication_type_id",
     *        description = "刊物类型id",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "论文列表"
     *     ),
     *     @SWG\Response(
     *         response = 403,
     *         description = "查找失败",
     *     )
     * )
     *
     **/
    public function actionList(){
        $data = yii::$app->request->post();
        if($data['emp_number'] == ''){
            $data['emp_number'] = $this->empNumber;
        }
        $thesis = new Thesis();
        $model = $thesis->thesislist($data);
        return $model;
    }



    /**
     * @SWG\Post(path="/thesis/thesis-sel",
     *     tags={"云平台-Thesis-论文"},
     *     summary="详情",
     *     description="详情",
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
     *        description = "论文表id",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "论文列表"
     *     ),
     *     @SWG\Response(
     *         response = 403,
     *         description = "删除失败",
     *     )
     * )
     *
     **/
    public function actionThesisSel(){
        $id = yii::$app->request->post('id');
        $thesis = new Thesis();
        $model = $thesis->sel($id);
        return $model;
    }


    /**
     * @SWG\Post(path="/thesis/thesis-del",
     *     tags={"云平台-Thesis-论文"},
     *     summary="删除论文",
     *     description="删除论文",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "id",
     *        description = "论文表id",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "论文列表"
     *     ),
     *     @SWG\Response(
     *         response = 403,
     *         description = "删除失败",
     *     )
     * )
     *
     **/
    public function actionThesisDel(){
        $id = yii::$app->request->post('id');
        $thesis = new Thesis();
        $model = $thesis->thesisdel($id);
        return $model;
    }



    /**
     * @SWG\Post(path="/thesis/atta-del",
     *     tags={"云平台-Thesis-论文"},
     *     summary="删除附件",
     *     description="删除附件",
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
     *         description = "论文列表"
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
        $thesis = new Thesis();
        $model = $thesis->attadel($eattach_id,$emp_number);
        return $model;
    }



}

