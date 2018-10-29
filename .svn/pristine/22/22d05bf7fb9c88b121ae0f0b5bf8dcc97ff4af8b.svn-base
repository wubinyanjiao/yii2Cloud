<?php
namespace frontend\controllers\v1;
use common\models\reward\Reward;
use yii\web\Response;
use yii;

class RewardController extends \common\rest\Controller
{


    /**
     * @var string
     */
    public $modelClass = 'common\models\Reward';

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
     * @SWG\Post(path="/reward/reward-add",
     *     tags={"云平台-Reward-惩奖统计"},
     *     summary="添加惩奖",
     *     description="添加惩奖",
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
     *        name = "is_reward",
     *        description = "类别 1：奖励    2：惩罚",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "emp_name",
     *        description = "员工",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "info",
     *        description = "说明",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "result",
     *        description = "结果",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "惩奖列表"
     *     ),
     *     @SWG\Response(
     *         response = 403,
     *         description = "添加失败",
     *     )
     * )
     *
     **/
    public function actionRewardAdd(){
        $data = yii::$app->request->post();
        $reward = new Reward();
        $model = $reward->rewardadd($data);
        if($model === 2){
            $this->serializer['message'] = '没有此员工';
            $this->serializer['status'] = false;
            return false;
        }
        return $model;
    }



    /**
     * @SWG\Post(path="/reward/reward-list",
     *     tags={"云平台-Reward-惩奖统计"},
     *     summary="惩奖列表",
     *     description="惩奖列表",
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
     *        name = "page",
     *        description = "页码",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "惩奖列表"
     *     ),
     *     @SWG\Response(
     *         response = 403,
     *         description = "添加失败",
     *     )
     * )
     *
     **/
    public function actionRewardList(){
        $user_name = $this->userName;
        $work_station = $this->workStation;
        $page = yii::$app->request->post('page');
        $reward = new Reward();
        $model = $reward->rewardlist($user_name,$work_station,$page);
        return $model;
    }



    /**
     * @SWG\Post(path="/reward/reward-del",
     *     tags={"云平台-Reward-惩奖统计"},
     *     summary="惩奖列表删除",
     *     description="惩奖列表删除",
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
     *        description = "奖罚表id",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "惩奖列表"
     *     ),
     *     @SWG\Response(
     *         response = 403,
     *         description = "删除失败",
     *     )
     * )
     *
     **/
    public function actionRewardDel(){
        $id = yii::$app->request->post('id');
        $info = Reward::deleteAll(['id'=>$id]);
        return $info;
    }



    /**
     * @SWG\Post(path="/reward/reward-sel",
     *     tags={"云平台-Reward-惩奖统计"},
     *     summary="惩奖详情",
     *     description="惩奖详情",
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
     *        description = "奖罚表id",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "惩奖列表"
     *     ),
     *     @SWG\Response(
     *         response = 403,
     *         description = "删除失败",
     *     )
     * )
     *
     **/
    public function actionRewardSel(){
        $id = yii::$app->request->post('id');
        $reward = new Reward();
        $model = $reward->rewardsel($id);
        return $model;
    }


    /**
     * @SWG\Post(path="/reward/reward-update",
     *     tags={"云平台-Reward-惩奖统计"},
     *     summary="修改惩奖",
     *     description="修改惩奖",
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
     *        description = "惩奖表id",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "is_reward",
     *        description = "类别 1：奖励    2：惩罚",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "emp_name",
     *        description = "员工",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "info",
     *        description = "说明",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "result",
     *        description = "结果",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "惩奖列表"
     *     ),
     *     @SWG\Response(
     *         response = 403,
     *         description = "修改失败",
     *     )
     * )
     *
     **/
    public function actionRewardUpdate(){
        $data = yii::$app->request->post();
        $reward = new Reward();
        $model = $reward->rewardupdate($data);
        return $model;
    }



    /**
     * @SWG\Post(path="/reward/reward-sum",
     *     tags={"云平台-Reward-惩奖统计"},
     *     summary="惩奖统计",
     *     description="惩奖统计",
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
     *     @SWG\Response(
     *         response = 200,
     *         description = "惩奖统计"
     *     ),
     *     @SWG\Response(
     *         response = 403,
     *         description = "查找失败",
     *     )
     * )
     *
     **/
    public function actionRewardSum(){
        $start_time = yii::$app->request->post('start_time');
        $end_time = yii::$app->request->post('end_time');
        $customer_id = $this->customerId;
        $workStation = $this->workStation;
        $reward = new Reward();
        $model = $reward->rewardsum($workStation,$customer_id,$start_time,$end_time);
        return $model;
    }



    /**
     * @SWG\Post(path="/reward/reward-sumsel",
     *     tags={"云平台-Reward-惩奖统计"},
     *     summary="惩奖统计详情",
     *     description="惩奖统计详情",
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
     *        name = "subunit_id",
     *        description = "组id",
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
     *     @SWG\Response(
     *         response = 200,
     *         description = "惩奖统计"
     *     ),
     *     @SWG\Response(
     *         response = 403,
     *         description = "查找失败",
     *     )
     * )
     *
     **/
    public function actionRewardSumsel(){
        $start_time = yii::$app->request->post('start_time');
        $end_time = yii::$app->request->post('end_time');
        $workStation = yii::$app->request->post('subunit_id');
        $reward = new Reward();
        $model = $reward->rewardsumsel($workStation,$start_time,$end_time);
        return $model;
    }

}

