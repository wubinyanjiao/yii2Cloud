<?php
namespace frontend\controllers\v1;

use common\base\PasswordHash;
use common\models\attendance\ApproverTab;
use common\models\overtime\Overtime;
use common\models\user\User;
use yii\web\Response;
use yii;

class WechatEmployeeController extends \common\rest\Controller
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
     * @SWG\Post(path="/wechat-employee/password",
     *     tags={"云平台-WechatEmployee-微信员工详情"},
     *     summary="修改密码",
     *     description="修改密码",
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
     *        name = "old_password",
     *        description = "老密码",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "new_password",
     *        description = "新密码",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "详情列表"
     *     ),
     *     @SWG\Response(
     *         response = 403,
     *         description = "查询失败",
     *     )
     * )
     *
     **/
    public function actionPassword(){
        $data = yii::$app->request->post();
        $emp_number = $this->empNumber;
        $pass = new PasswordHash();
        $user = User::find()->where(['emp_number'=>$emp_number])->one();
        $info = $pass->verify($data['old_password'],$user['user_password']);
        if($info){
            $user->user_password = $pass->hash($data['new_password']);
            $query = $user->save();
            return $query;
        }else{
            $this->serializer['message'] = '原密码错误';
            $this->serializer['status'] = false;
        }



    }


    /**
     * @SWG\Post(path="/wechat-employee/details",
     *     tags={"云平台-WechatEmployee-微信员工详情"},
     *     summary="微信员工详情",
     *     description="微信员工详情",
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
     *         description = "详情列表"
     *     ),
     *     @SWG\Response(
     *         response = 403,
     *         description = "查询失败",
     *     )
     * )
     *
     **/
    public function actionDetails(){
        $emp_number = $this->empNumber;
        $user = new User();
        $model = $user->WeChatEmployee($emp_number);
        return $model;
    }


    /**
     * @SWG\Post(path="/wechat-employee/update-wechat-employee",
     *     tags={"云平台-WechatEmployee-微信员工详情"},
     *     summary="修改微信员工详情",
     *     description="修改微信员工详情",
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
     *        name = "emp_firstname",
     *        description = "员工姓名",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "emp_mobile",
     *        description = "员工手机号",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "emp_work_telephone",
     *        description = "员工电话",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "eec_name",
     *        description = "紧急联系人",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "eec_mobile_no",
     *        description = "紧急联系人电话",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "详情列表"
     *     ),
     *     @SWG\Response(
     *         response = 403,
     *         description = "查询失败",
     *     )
     * )
     *
     **/
    public function actionUpdateWechatEmployee(){
        $emp_number = $this->empNumber;
        $data = yii::$app->request->post();
        $user = new User();
        $model = $user->UpdateWeChatEmployee($emp_number,$data);
        return $model;
    }


    /**
     * @SWG\Post(path="/wechat-employee/my-message",
     *     tags={"云平台-WechatEmployee-微信员工详情"},
     *     summary="我的消息列表",
     *     description="我的消息列表",
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
     *        name = "is_read",
     *        description = "0未读 1已读",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "targetType",
     *        description = "消息的类型,1休假，2调班，3加班，4培训",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "page",
     *        description = "页数",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "消息列表"
     *     ),
     *     @SWG\Response(
     *         response = 403,
     *         description = "操作失败",
     *     )
     * )
     *
     **/
    public function actionMyMessage(){
        $emp_number = $this->empNumber;
        $is_read = Yii::$app->request->post('is_read');
        $targetType = Yii::$app->request->post('targetType');
        $page = Yii::$app->request->post('page');
        $approverTab=new ApproverTab();
        $table = $approverTab->getMessageTable('user_message',$emp_number);
        $content_table = $approverTab->getMessageTable('user_message_detail',$emp_number);
        $user = new User();
        $model = $user->getMessageTable($table,$content_table,$emp_number,$is_read,$targetType,$page);
        if(empty($model['data'])){
            $this->serializer['status'] = false;
            $this->serializer['message'] = '暂无数据';
            return false;
        }
        return $model;
    }
}

