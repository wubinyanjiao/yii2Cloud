<?php

namespace frontend\controllers\v1;

/**
* 注册,登陆,登出,密码找回
*/
use yii;
use yii\web\Response;

use yii\captcha\CaptchaAction;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\rest\OptionsAction;

use common\models\system\SystemUsers;
use common\base\PasswordHash;
use common\models\subunit\Subunit;
use common\models\user\User;
use common\models\pim\EmpPicture;
use common\models\system\MemberToken;
use cheatsheet\Time;
use common\models\system\UserLoginLog;

class SignInController extends \common\rest\Controller
{
    //public $modelClass = 'frontend\models\gedu\resources\User';

    /**
     * @var array
     */
    public $serializer = [
        'class'              => 'common\rest\Serializer',
        'collectionEnvelope' => 'result',
        // 'errno'              => 0,
        'message'            => 'OK',
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

        // 移除access行为，参数为空全部移除
        // Yii::$app->controller->detachBehavior('access');
        return $action;
    }
    /**
    * @inheritdoc
    */
    public function behaviors()
    {
        return ArrayHelper::merge(
            parent::behaviors(),
            [
                'access' => [
                    'class' => AccessControl::className(),
                    'rules' => [[
                        'allow' => true,
                        'matchCallback' => function ($rule, $action) {
                            // return true;
                            // var_dump($this->module->id . '_' . $this->id . '_' . $action->id); exit();
                            return \Yii::$app->user->can(
                                $this->module->id . '_' . $this->id . '_' . $action->id, 
                                ['route' => true]
                            );
                        },
                    ]]
                ]
            ]
        );
    }


    /**
     * @SWG\Post(path="/sign-in/login",
     *     tags={"GEDU-SignIn-用户接口"},
     *     summary="用户登录[已经自测]",
     *     description="用户登录：成功返回用户信息；失败返回具体原因",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "userName",
     *        description = "工资号",
     *        required = true,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "passWord",
     *        description = "密码",
     *        required = true,
     *        type = "string"
     *     ),
     *     
     *     @SWG\Response(
     *         response = 200,
     *         description = "登陆成功，返回用户信息"
     *     ),
     *     @SWG\Response(
     *         response = 422,
     *         description = "Data Validation Failed 账号或密码错误",
     *     )
     * )
     *
     */
    public function actionLogin()
    {

        $post=Yii::$app->request->post();
        $params = Yii::$app->params;
        $defaultHead = $params['defaultHead'];
        $UserLoginLog  = new UserLoginLog();


        if(empty($post)){
            // $result['status'] = false;
            // $result['message'] = '请输入用户名或密码';
            // echo json_encode($result);die;

            $this->serializer['status'] = false;
            $this->serializer['errno'] = 0;
            $this->serializer['message'] = '请输入用户名或密码';

           

            return ;
        }

        $userName = !empty($post['userName'])?trim($post['userName']):''; 
        $passWord = !empty($post['passWord'])?$post['passWord']:'';
        $openId = !empty($post['openId'])?$post['openId']:'';

        if(empty($userName)||empty($passWord)){
            // $result['status'] = false;
            // $result['message'] = '请输入用户名或密码';
            // echo json_encode($result);die;

            $this->serializer['status'] = false;
            $this->serializer['errno'] = 0;
            $this->serializer['message'] = '请输入用户名或密码';
            return ;
        }
        try {
            $SystemUser = new SystemUsers();
            $list = $SystemUser->searchSystemUsersByName($userName);

            if($list){
                $PasswordHash = new PasswordHash();
                $passTure = $PasswordHash->verify($passWord,$list['user_password']);
                if($passTure){
                    $userDetails['customerId'] = $list['customer_id'];
                    $userDetails['userName'] = $list['user_name'];
                    $userDetails['userRoleId'] = $list['user_role_id'];
                    $userDetails['userId'] = $list['id'];

                    $userDetails['workStation'] = $list['employee']['work_station'];
                    $userDetails['workStationName'] = '';
                    $userDetails['empPicture']  =$defaultHead;
                    if($list['id']==1){    //管理员账号没有员工信息
                        $userDetails['empNumber'] = null;
                        $userDetails['firstName'] = '';
                    }else{
                        $userDetails['empNumber'] = $list['emp_number'];
                        $userDetails['firstName'] = $list['employee']['emp_firstname'];

                        if($list['employee']['work_station']){
                            $userDetails['workStation'] = $list['employee']['work_station'];
                            $Subunit = new Subunit();
                            $station = $Subunit->getWorkStationById($list['employee']['work_station']);
                            $userDetails['workStationName']  = $station->name;
                        
                        }

                        if($list['employee']['is_leader']){
                            $userDetails['isLeader'] = true;
                        }else{
                            $userDetails['isLeader'] = false;
                        }
                    }

                    if($list['emp_number']){
                        $EmpPicture = new EmpPicture();
                        $picture = $EmpPicture->getEmpPictureByEmpNumber($list['emp_number']);
                        if($picture&&!empty($picture->epic_picture_url)){
                            //$url='http://'.$_SERVER['HTTP_HOST'];
                            $url= env('STORAGE_HOST_INFO');
                            $userDetails['empPicture'] = $url.trim($picture->epic_picture_url,'/');
                        }
                    }

                    
                    
                    $MemberToken = new MemberToken();

                    $uToken = $MemberToken->getTokenById($list['id']);
                    if(!empty($uToken['token'])){
                        $userDetails['token'] = $uToken['token'];
                    }else{
                        $token = settoken();
                        $userDetails['token'] = $token;
                        $isupdate = $MemberToken->updateTokenById($list['id'],$token);
                    }

      
                    
                   
                        // if($openId){
                        //     $SystemUsers = new User();
                        //     $recod = $SystemUsers->getSystemUsersById($userDetails['userId']);
                        //     $recod->open_id = base64_decode(base64_decode($openId));
                        //     $recod->bind_time = date('Y-m-d H:i:s');
                        //     $res = $recod->save();
                        // }
                        
                        // $result['status'] = true;
                        // $result['message'] = '登录成功';
                        // $result['data'] = $userDetails;
                        // echo json_encode($result);die;

                        $this->serializer['status'] = true;
                        $this->serializer['errno'] = 0;
                        $this->serializer['message'] = '登录成功';
                        $result['data'] = $userDetails;
                        return $result;


                    
                    
                }else{
                    // $result['status'] = false;
                    // $result['message'] = '工资号错误';
                    // echo json_encode($result);die;

                    $this->serializer['status'] = false;
                    $this->serializer['errno'] = 0;
                    $this->serializer['message'] = '密码错误';
                    $content = $userName.' 密码错误';
                    $UserLoginLog->CreateUserLoginLog($userName,$content);
                    return ;
                }
            }else{
                // $result['status'] = false;
                // $result['message'] = '工资号错误';
                // echo json_encode($result);die;

                $this->serializer['status'] = false;
                $this->serializer['errno'] = 0;
                $this->serializer['message'] = '工资号错误';
                $content = $userName.' 工资号错误';

                $UserLoginLog->CreateUserLoginLog($userName,$content);

                return ;
            }

        } catch (\Exception $e) {
            // $result['status'] = false;
            // $result['message'] = '登录失败';
            // echo json_encode($result);die;
            $this->serializer['status'] = false;
            $this->serializer['errno'] = 0;
            $this->serializer['message'] = '登录失败';

            return ;
        }
    }


    /**
     * @SWG\Post(path="/sign-in/logout",
     *     tags={"GEDU-SignIn-用户接口"},
     *     summary="退出用户账户",
     *     description="退出用户账户接口",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "Token",
     *        description = "token",
     *        required = true,
     *        type = "string"
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "成功返回[]，失败返回提示信息"
     *     )
     * )
     *
     */
    public function actionLogout()
    {
        $Tokens=Yii::$app->request->post('Token'); 
        if(null== $Tokens){
            $Tokens=Yii::$app->request->get('Token');
        }
        $Token = @$_SERVER['HTTP_AUTHORIZATION'];
        //$Token = '1212'; 
        $UserLoginLog = new UserLoginLog;
        if(empty($Token)){
            if(empty($Tokens)){
                // $result['status'] = false;
                // $result['message'] ='Token不能为空';
                // echo json_encode($result);die;

                $this->serializer['status'] = false;
                $this->serializer['errno'] = 0;
                $this->serializer['message'] = 'Token不能为空';
                return ;
            }else{
                $Token = $Tokens;
            }
            
        }else{
            $Tstring = explode('token', $Token);

            $Token_arr = base64_decode(base64_decode($Tstring[1]));

 
            $TokenArray = explode(':', $Token_arr);

            $Token  = trim($TokenArray[2]); 
        }

        $MemberToken = new MemberToken();
        $userid =$MemberToken->getTokenByToken(trim($Token));

        $recod = $MemberToken->deleteTokenByToken(trim($Token));

            

        //$recod = true;
        if($recod){

            // $SystemUsers = new User();
            // $recod = $SystemUsers->getSystemUsersById($userid['userid']);
            // $recod->open_id = null;
            // $recod->bind_time = null;
            // $res = $recod->save();
            $this->serializer['status'] = true;
            $this->serializer['errno'] = 0;
            $this->serializer['message'] = '退出成功';
            return true;
        }else{
            
            $this->serializer['status'] = false;
            $this->serializer['errno'] = 401;
            $this->serializer['message'] = '退出失败';
            return false;
        }

        // $result['status'] = true;
        // $result['message'] = '退出成功';
        // echo json_encode($result);die;

        
    
    }

     /**
     * @SWG\Get(path="/sign-in/open-unbind",
     *     tags={"GEDU-SignIn-用户接口"},
     *     summary="退出用户账户",
     *     description="退出用户账户接口",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "query",
     *        name = "Token",
     *        description = "token",
     *        required = true,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "empNumber",
     *        description = "员工ID",
     *        required = true,
     *        type = "string"
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "成功返回[]，失败返回提示信息"
     *     )
     * )
     *
     */
    public function actionOpenUnbind()
    {
        $empNumber=Yii::$app->request->get('empNumber'); 
        $params = Yii::$app->params;
        $UserLoginLog = new UserLoginLog;
        if(empty($empNumber)){
            $this->serializer['status'] = false;
            $this->serializer['errno'] = 2;
            $this->serializer['message'] = '解绑失败';

            $content = $this->userName.'解绑用户时传了空id';
            $UserLoginLog->CreateUserLoginLog($this->empNumber,$content);
            return ;
        }

        $SystemUsers = new User();
        $recod = $SystemUsers->getSystemUsersByEmpNumber($empNumber);

        $openId = $recod->open_id;
        $customerId = $recod->customer_id;

        $recod->open_id = null;
        $recod->bind_time = null;
        $res = $recod->save();
        $SystemUsers->deleteOpenUnbind($recod->id);

        $MemberToken = new MemberToken();
        $cos = $MemberToken->deleteTokenById($recod->id);

        if($openId&&$customerId){
            // $param['customer_id'] = $customerId;
            // $param['open_id'] = $openId;
            // $url = $params['WEIXINUBINDLABEL'];
            // httpPostByYii($param,$url);
        }
        

        $this->serializer['status'] = true;
        $this->serializer['errno'] = 0;
        $this->serializer['message'] = '解绑成功';
        return ;
        

        // $result['status'] = true;
        // $result['message'] = '退出成功';
        // echo json_encode($result);die;

        
    
    }





}
