<?php
namespace frontend\controllers\v1;
use common\models\reward\Reward;
use common\models\user\User;
use yii\web\Response;
use yii;

class LunzhuanController extends \common\rest\SysController
{


    /**
     * @var string
     */
    public $modelClass = 'common\models\User';

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
     * @SWG\Post(path="/lunzhuan/rotation",
     *     tags={"云平台-User-用户"},
     *     summary="轮转数据更新",
     *     description="轮转数据更新",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "Token",
     *        description = "token",
     *        required = false,
     *        type = "string"
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "员工管理列表"
     *     ),
     *     @SWG\Response(
     *         response = 403,
     *         description = "查询失败",
     *     )
     * )
     *
     **/
    public function actionRotation(){
        $user = new User();
        $ip = $user->getIp();
        $IP_WHITELIST = env('IP_WHITELIST');
        $IP_WHITELIST = explode("|",$IP_WHITELIST);
        $info = in_array($ip,$IP_WHITELIST);
        if($info === false){
            $this->serializer['status'] = false;
            $this->serializer['message'] = '没有权限操作';
            return false;
        }

        $model = $user->RotationUpdate();
        return $model;
    }


}

