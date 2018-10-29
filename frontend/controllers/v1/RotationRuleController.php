<?php
namespace frontend\controllers\v1;
use common\models\shift\RotationRule;
use Yii;
use yii\web\Response;
use yii\web\Controller;
//use yii\helpers\ArrayHelper;
class RotationRuleController extends \common\rest\Controller
{
    /**
     * @var string
     */
    public $modelClass = 'common\models\shift\RotationRule';

    /**
     *
     * @var array
     */
    public $serializer = [
        'class' => 'common\rest\Serializer',    // 返回格式数据化字段
        'collectionEnvelope' => 'result',       // 制定数据字段名称
        'message' => 'OK',                      // 文本提示
        'errno'   => 0,
        'status'  =>'',
    ];
    /**
     * @param  [action] yii\rest\IndexAction
     * @return [type]
     */
    public function beforeAction($action)
    {
        $format = \Yii::$app->getRequest()->getQueryParam('format', 'json');

        if($format == 'xml'){
            \Yii::$app->response->format = \yii\web\Response::FORMAT_XML;
        }else{
            \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        }

        return $action;
    }

    /**
     * @param  [type]
     * @param  [type]
     * @return [type]
     */
    public function afterAction($action, $result){
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
     * @SWG\Post(path="/rotation-rule/create",
     *     tags={"云平台-RotationRule-轮转规则表"},
     *     summary="保存规则",
     *     description="新建保存规则",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "Token",
     *        description = "Token",
     *        required = true,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "rotationId",
     *        description = "轮转表id",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "groupId",
     *        description = "组id",
     *        required = false,
     *        type = "string",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "ruleType",
     *        description = "规则类型：空默认out调出规则in调入规则",
     *        required = true,
     *        type = "string",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "rotationRuleWarehouseId",
     *        description = "轮转规则库种子",
     *        required = false,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "select1",
     *        description = "格式[[101, 102], [201, 202]]",
     *        required = false,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "select2",
     *        description = "格式[[101, 102], [201, 202]]",
     *        required = false,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "input1",
     *        description = "格式[101, 102]",
     *        required = false,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "input2",
     *        description = "格式[101, 102]",
     *        required = false,
     *        type = "integer",
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "返回班次类型列表"
     *     ),
     *     @SWG\Response(
     *         response = 422,
     *         description = "Data Validation Failed 创建失败",
     *     )
     * )
     *
     **/
    public function actionCreate()
    {
        /**
         * 获取post值进行保存
         * insert into rotationRule (rotationId(轮转表id),groupId(组id),ruleType(规则类型,调出或者调入),
         * rotationRuleWarehouseId(轮转规则库id)) value(值)
         */
    }


}