<?php

namespace frontend\controllers\v1;

/**
* 权限模块
*/
use yii;
use yii\web\Response;

use yii\captcha\CaptchaAction;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\rest\OptionsAction;

use common\models\user\User;
use common\models\user\UserRole;
use common\models\system\MemberToken;
use common\models\system\WeixinMember;
use common\models\system\UserLoginLog;
use common\models\system\SystemUsers;
use common\models\system\LatitudeLongitude;

use common\base\PasswordHash;
use common\models\leave\Leave;
use common\models\leave\LeaveRequest;
use common\models\leave\LeaveEntitlement;
use common\models\leave\LeaveType;
use common\models\leave\LeaveEntitlementLog;

use common\models\pim\EmpPicture;
use common\models\subunit\Subunit;
use common\models\system\AppSys;
use common\models\employee\Employee;
use common\models\attendance\ApproverTab;
use common\models\shift\Schedule;
use common\models\shift\ShiftType;
use common\models\shift\ShiftChangeApply;
use common\models\overtime\Overtime;
use common\models\attendance\AttendanceRecord;
use \common\models\shift\ShiftResult;
use \common\models\system\UniqueId;
use \common\models\workload\WorkLoad;
use \common\models\workload\WorkContent;
use common\models\performance\BonusCalculationList;
use common\models\performance\PerformanceParam;
use common\models\performance\BonusCalculationManageConfig;
use common\models\performance\BonusCalculationConfig;
use common\models\performance\BonusCalculation;

use common\models\overtime\OvertimeComment;


use cheatsheet\Time;


class WeixinController extends \common\rest\Controller
{

    /**
     * @var array
     */
    public $serializer = [
        'class'              => 'common\rest\Serializer',
        'collectionEnvelope' => 'result',
        'errno'              => 0,
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
     * @SWG\Post(path="/weixin/open-verification",
     *     tags={"云平台-WEIXIN-微信接口"},
     *     summary="微信验证openID登录",
     *     description="用户登录：成功返回用户信息；失败返回具体原因",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "customerId",
     *        description = "customerId",
     *        required = true,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "openId",
     *        description = "openId",
     *        required = false,
     *        type = "string"
     *     ),
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
    public function actionOpenVerification()
    {   
        //$post=Yii::$app->request->post();
        $params = Yii::$app->params;
        $customerId = Yii::$app->request->post('customerId'); 
        $openId = Yii::$app->request->post('openId');

        $customerId = base64_decode(base64_decode($customerId));
        $openId = base64_decode(base64_decode($openId));

        if(empty($customerId)||empty($openId)){
            $this->serializer['status'] = false;
            $this->serializer['errno'] = 403;
            $this->serializer['message'] = '验证失败'; 
            return false;
        }
        
        $WeixinMember = new WeixinMember();
        $MemberToken = new MemberToken();

        $ros = $WeixinMember->searchWeixinMemberBy($customerId,$openId);

            if(empty($ros)){
                $this->serializer['status'] = false;
                $this->serializer['errno'] = 403;
                $this->serializer['message'] = '验证失败'; 
                return false;
            }else{
                $userId = $ros['userid'];

                $user_token = $MemberToken->getTokenById($userId);
    
                //如果没有该用户的token记录，则新插入一条数据
                if(empty($user_token)){
                    $newtoken= settoken();
                    $MemberToken->token = $newtoken;
                    $MemberToken->userid = $userId;
                    $MemberToken->save();
                    $token=$newtoken;
                }else{
                    if(empty($user_token['token'])){
                        $newtoken=settoken();
                        $user_token->token = $newtoken;
                        $user_token->save();
                        $token=$newtoken;
                    }else{
                        $token=$user_token['token'];
                    }
                }
            }


            $User = new User();

            $list = $User->searchSystemUsersById($userId);

            if($list){
                $userDetails = array();
                $userDetails['userName'] = $list['user_name'];
                $userDetails['userRoleId'] = $list['user_role_id'];
                $userDetails['userRole'] = $list['user_role_id'];

                if($list['user_role_id']){
                    $userRole = new UserRole();
                    $role = $userRole->getUserRoleById($list['user_role_id']);

                    $userDetails['userRoleId'] = $list['user_role_id'];
                    $userDetails['userRole'] = $role['name'];
                }else{
                    $userDetails['userRoleId'] = 0;
                    $userDetails['userRole'] = '';

                }

                

                $userDetails['status'] = $list['status'];

                $userDetails['employeeName'] = '';
                $userDetails['employeeId'] = '';
                $userDetails['empLeader'] = 0;
                $userDetails['customerId'] = $customerId;

                $userDetails['userId'] = $list['id'];
                $userDetails['isLeader'] = false;

                $userDetails['subunitName'] = '';
                $userDetails['empPicture'] = '';
                $userDetails['token'] = $token;

            
                if(!empty($list['emp_number'])){
                    $userDetails['employeeId'] = '';

                    if($list['employee']['work_station']){
                        $userDetails['workStation'] = $list['employee']['work_station'];
                        $Subunit = new Subunit();
                        $station = $Subunit->getWorkStationById($list['employee']['work_station']);
                        $userDetails['subunitName']  = $station->name;
                    
                    }

                    $userDetails['employeeName'] = $list['employee']['emp_firstname'];
                    $userDetails['employeeId'] = $list['emp_number'];
                    $userDetails['empLeader'] = $list['employee']['is_leader'];
                    if($list['employee']['is_leader']){
                        $userDetails['isLeader'] = true;
                    }else{
                        $userDetails['isLeader'] = false;
                    }

                    $EmpPicture = new EmpPicture();
                    $picture = $EmpPicture->getEmpPictureByEmpNumber($list['emp_number']);
                    if($picture&&!empty($picture->epic_picture_url)){
                        //$url='http://'.$_SERVER['HTTP_HOST'];
                        $url= env('STORAGE_HOST_INFO');
                        $userDetails['empPicture'] = $url.trim($picture->epic_picture_url,'/');
                    }

                }

                $user = $User->getSystemUsersById($list['id']);
                    
                $user->open_id = $openId;
                $user->bind_time =date('Y-m-d H:i:s');
                $user->save();

                $param['customer_id'] = $customerId;
                $param['open_id'] = $openId;
                $url = $params['WEIXINBINDLABEL'];
                httpPostByYii($param,$url);


                $this->serializer['status'] = true;
                $this->serializer['errno'] = 0;
                $this->serializer['message'] = '登录成功'; 
                return  $userDetails;

            }else{
                $this->serializer['status'] = false;
                $this->serializer['errno'] = 403;
                $this->serializer['message'] = '验证失败'; 
                return false;
            }   




    }

    /**
     * @SWG\Post(path="/weixin/user-login",
     *     tags={"云平台-WEIXIN-微信接口"},
     *     summary="账号登录",
     *     description="用户登录：成功返回用户信息；失败返回具体原因",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "username",
     *        description = "工资号",
     *        required = true,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "password",
     *        description = "密码",
     *        required = true,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "customerId",
     *        description = "customerId",
     *        required = true,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "openId",
     *        description = "openId",
     *        required = false,
     *        type = "string"
     *     ),
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
    public function actionUserLogin(){
        $params = Yii::$app->params;

        $userName = Yii::$app->request->post('username'); 
        $passWord = Yii::$app->request->post('password'); 

        $customerId = Yii::$app->request->post('customerId'); 
        $openId = Yii::$app->request->post('openId');

        $userName = base64_decode(base64_decode($userName));
        $passWord = base64_decode(base64_decode($passWord));
        $customerId = base64_decode(base64_decode($customerId));
        $openId = base64_decode(base64_decode($openId));

        if($customerId){            
            $customerArr = $params['customerArr'];
            if(!in_array($customerId,$customerArr)){
                $loginLog = new UserLoginLog();
                $loginLog->user_name = $userName;
                $loginLog->create_date = date('Y-m-d H:i:s');
                $content = $userName.' 使用了错误的客户ID :'.$customerId;
                $loginLog->content = $content;
                $loginLog->save();
                $this->serializer['status'] = false;
                $this->serializer['errno'] = 2;
                $this->serializer['message'] = '客户ID错误'; 
                return false;
            }
        }
        // if(empty($openId)){
        //     $this->serializer['status'] = false;
        //     $this->serializer['errno'] = 2;
        //     $this->serializer['message'] = '获取用户信息失败!!'; 
        //     return false;
        // }

            $SystemUser = new SystemUsers();

            $User = new User();
            $list = $SystemUser->searchSystemUsersByName($userName);

            if($list){
                $PasswordHash = new PasswordHash();
                $passTure = $PasswordHash->verify($passWord,$list['user_password']);
                $defaultHead = $params['defaultHead'];
                if($passTure){
                    $userCustomer = $list['customer_id'];

                    if(!empty($customerId)&&$userCustomer!=$customerId){
                        $loginLog = new UserLoginLog();
                        $loginLog->emp_number = $list['emp_number'];
                        $loginLog->user_name = $userName;
                        $loginLog->create_date = date('Y-m-d H:i:s');
                        $content = $userName.' 使用了错误的客户ID :'.$customerId.' ,客户ID:'.$userCustomer;
                        $loginLog->content = $content;
                        $loginLog->save();

                        $this->serializer['status'] = false;
                        $this->serializer['errno'] = 2;
                        $this->serializer['message'] = '客户ID错误'; 
                        return false;
                    }

                    if($openId){
                        if($list['open_id']&&$list['open_id']!=$openId){
                        
                            $loginLog = new UserLoginLog();
                            $loginLog->emp_number = $list['emp_number'];
                            $loginLog->user_name = $userName;
                            $loginLog->create_date = date('Y-m-d H:i:s');
                            $content = $userName.' 使用了错误的openID:'.$openId.' ,openID:'.$list['open_id'];
                            $loginLog->content = $content;
                            $loginLog->save();
                            $this->serializer['status'] = false;
                            $this->serializer['errno'] = 2;
                            $this->serializer['message'] = '此账号已经绑定过openId'; 
                            return false;
                        }
                        
                    }



                    $userDetails = array();
                    $userDetails['userName'] = $list['user_name'];
                    $userDetails['userRoleId'] = $list['user_role_id'];
                    $userDetails['userRole'] = $list['user_role_id'];

                    if($list['user_role_id']){
                        $userRole = new UserRole();
                        $role = $userRole->getUserRoleById($list['user_role_id']);

                        $userDetails['userRoleId'] = $list['user_role_id'];
                        $userDetails['userRole'] = $role['name'];
                    }else{
                        $userDetails['userRoleId'] = 0;
                        $userDetails['userRole'] = '';

                    }

                

                    $userDetails['status'] = $list['status'];

                    $userDetails['employeeName'] = '';
                    $userDetails['employeeId'] = '';
                    $userDetails['empLeader'] = 0;
                    $userDetails['customerId'] = $customerId;

                    $userDetails['userId'] = $list['id'];
                    $userDetails['isLeader'] = false;

                    $userDetails['subunitName'] = '';
                    $userDetails['empPicture'] = '';
                    //$userDetails['token'] = $token;

                    $MemberToken = new MemberToken();
                    $WeixinMember = new WeixinMember();

                    $uToken = $MemberToken->getTokenById($list['id']);
                    if(!empty($uToken['token'])){
                        $userDetails['token'] = $uToken['token'];
                    }else{
                        $token = settoken();
                        $userDetails['token'] = $token;
                        $isupdate = $MemberToken->updateTokenById($list['id'],$token);
                    }
                    if($customerId){
                        

                        if($openId){
                            $ros = $WeixinMember->searchWeixinMemberBy($customerId,$openId);
                            if(empty($ros)){
                                $WeixinMember->deleteWeiXinTokenById($list['id']);

                                $WeixinMember->customer_id = $customerId;
                                $WeixinMember->openid = $openId;
                                $WeixinMember->userid = $list['id'];
                                $WeixinMember->save();
                            }else{
                                $WeixinMember->updateWeinXinTokenById($list['id'],$openId,$customerId);
                            }
                            
                        }
                    }
                
                    if(!empty($list['emp_number'])){
                        $userDetails['employeeId'] = '';

                        if($list['employee']['work_station']){
                            $userDetails['workStation'] = $list['employee']['work_station'];
                            $Subunit = new Subunit();
                            $station = $Subunit->getWorkStationById($list['employee']['work_station']);
                            $userDetails['subunitName']  = $station->name;
                        
                        }

                        $userDetails['employeeName'] = $list['employee']['emp_firstname'];
                        $userDetails['employeeId'] = $list['emp_number'];
                        $userDetails['empLeader'] = $list['employee']['is_leader'];
                        if($list['employee']['is_leader']){
                            $userDetails['isLeader'] = true;
                        }else{
                            $userDetails['isLeader'] = false;
                        }

                        $EmpPicture = new EmpPicture();
                        $picture = $EmpPicture->getEmpPictureByEmpNumber($list['emp_number']);
                        if($picture&&!empty($picture->epic_picture_url)){
                            //$url='http://'.$_SERVER['HTTP_HOST'];
                            $url= env('STORAGE_HOST_INFO');
                            $userDetails['empPicture'] = $url.trim($picture->epic_picture_url,'/');
                        }

                    }

                    $user = $User->getSystemUsersById($list['id']);
                        
                    $user->open_id = $openId;
                    $user->bind_time =date('Y-m-d H:i:s');
                    $user->save();

                    $param['customer_id'] = $customerId;
                    $param['open_id'] = $openId;
                    $url = $params['WEIXINBINDLABEL'];
                    httpPostByYii($param,$url);


                    $this->serializer['status'] = true;
                    $this->serializer['errno'] = 0;
                    $this->serializer['message'] = '登录成功'; 
                    return  $userDetails;  
                    
                }else{

                    $this->serializer['status'] = false;
                    $this->serializer['errno'] = 0;
                    $this->serializer['message'] = '密码错误';
                    return ;
                }
            }else{

                $this->serializer['status'] = false;
                $this->serializer['errno'] = 0;
                $this->serializer['message'] = '工资号错误';
                return ;
            }

    }

    /**
     * @SWG\Post(path="/weixin/homepage-detail",
     *     tags={"云平台-WEIXIN-微信接口"},
     *     summary="首页获取定位及默认审批人信息",
     *     description="用户登录：成功返回用户信息；失败返回具体原因",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "Token",
     *        description = "d9ffcc1da74103ee274802ff85045e3da3a8f77f",
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
    public function actionHomepageDetail(){
        $params = Yii::$app->params;
        $work_time_late  = $params['work_time_late'];

        $date = date('Y-m-d');

        $LatitudeLongitude = new LatitudeLongitude();
        $Employee = new Employee();
        $backDetail = array();

        $backDetail['empNumber'] = $this->empNumber?$this->empNumber:0;
        $backDetail['late_time'] = $work_time_late;
        $backDetail['date'] = date("Y年m月d日",time());
        $lati =$LatitudeLongitude->getLatitudeLongitudeByWorkStation($this->workStation);
        if($lati){
            $backDetail['latitude'] = $lati['latitude'];
            $backDetail['longitude'] = $lati['longitude'];
            $backDetail['punching_range'] = $lati['punching_range'];
        }else{
            $backDetail['latitude'] = 0;
            $backDetail['longitude'] = 0;
            $backDetail['punching_range'] = 0;
        }

        if($this->workStation){
            $empList = $Employee->getEmpByWorkStation($this->workStation);

            if($empList){
                $empArr = array();
                foreach($empList as $emp){
                    if($emp['is_leader']<1){
                        continue;
                    }
                    $arr = array('key'=>$emp['emp_number'],'val'=>$emp['emp_firstname']);
                    $empArr[] = $arr; 
                }
                $backDetail['defaultApplyMen'] = $empArr;
            }else{
                $backDetail['defaultApplyMen'] = array(array('key'=>'','val'=>''));
            }
        }else{
            $backDetail['defaultApplyMen'] = array(array('key'=>'','val'=>''));
        }

        $backDetail['work_name'] = '';
        $backDetail['isAmontWork'] = 0;
        $backDetail['workShiftId'] = 0;
        return $backDetail;
    }

    /**
     * @SWG\Post(path="/weixin/latitude-and-longitude",
     *     tags={"云平台-WEIXIN-微信接口"},
     *     summary="提交经纬度设置",
     *     description="用户登录：成功返回用户信息；失败返回具体原因",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "Token",
     *        description = "d9ffcc1da74103ee274802ff85045e3da3a8f77f",
     *        required = true,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "latitude",
     *        description = "经度",
     *        required = true,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "longitude",
     *        description = "纬度",
     *        required = true,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "punchingRange",
     *        description = "范围",
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
    public function actionLatitudeAndLongitude(){
        $params = Yii::$app->params;
        $work_time_late  = $params['work_time_late'];

        $date = date('Y-m-d');

        $LatitudeLongitude = new LatitudeLongitude();
        $Employee = new Employee();
        $empNumber = Yii::$app->request->post('UserID'); 
        $latitude = Yii::$app->request->post('latitude'); 
        $longitude = Yii::$app->request->post('longitude'); 
        $punching_range = Yii::$app->request->post('punchingRange'); 



        if(empty($latitude)) {
            $this->serializer['status'] = false;
            $this->serializer['errno'] = 0;
            $this->serializer['message'] = '经度不能为空';
            return ;
        }
        if(empty($longitude)) {
            $this->serializer['status'] = false;
            $this->serializer['errno'] = 0;
            $this->serializer['message'] = '纬度不能为空';
            return ;
        }
        if(empty($this->empNumber)){
            $this->serializer['status'] = false;
            $this->serializer['errno'] = 0;
            $this->serializer['message'] = '管理员不能定位';
            return ;
        }
        $employee = $Employee->getEmpByNumNber($this->empNumber); 

        if(!$this->isLeader){
            $this->serializer['status'] = false;
            $this->serializer['errno'] = 0;
            $this->serializer['message'] = '不是组长不能定位';
            return ;

        }

        $Latitude = $LatitudeLongitude->getLatitudeLongitudeByWorkStation($this->workStation);

        if (is_null($Latitude['id'])) {           
                $LatitudeLongitude = new LatitudeLongitude();
                $LatitudeLongitude->latitude = $latitude ;
                $LatitudeLongitude->longitude = $longitude ;
                $LatitudeLongitude->punching_range = $punching_range ;
                $LatitudeLongitude->work_station = $this->workStation ;
                $LatitudeLongitude->save();

                if($LatitudeLongitude->id){
                    $this->serializer['status'] = true;
                    $this->serializer['errno'] = 0;
                    $this->serializer['message'] = '保存成功';
                    return ;
                }else{
                    $this->serializer['status'] = false;
                    $this->serializer['errno'] = 0;
                    $this->serializer['message'] = '保存失败';
                    return ;
                }
        } else {
            try {
                $Latitude->latitude = $latitude ;
                $Latitude->longitude = $longitude ;
                $Latitude->punching_range = $punching_range ;
                $Latitude->save();

                $this->serializer['status'] = true;
                $this->serializer['errno'] = 0;
                $this->serializer['message'] = '保存成功';
            }catch (\Exception $e) {
                $this->serializer['status'] = false;
                $this->serializer['errno'] = 0;
                $this->serializer['message'] = '保存失败';
                return ;
            }
        }
    }

    /**
     * @SWG\Post(path="/weixin/leave-myentitlement",
     *     tags={"云平台-WEIXIN-微信接口"},
     *     summary="获取我的休假",
     *     description="用户登录：成功返回用户信息；失败返回具体原因",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "Token",
     *        description = "d9ffcc1da74103ee274802ff85045e3da3a8f77f",
     *        required = true,
     *        type = "string"
     *     ),
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
    public function actionLeaveMyentitlement(){
        if(empty($this->empNumber)&&$this->userRoleId==1){
            $this->serializer['status'] = false;
            $this->serializer['errno'] = 0;
            $this->serializer['message'] = '管理员没有假期';
            return false;
        }

        $Entitlement = new LeaveEntitlement();
        $list = $Entitlement->getEmpLeaveEntitlement($this->empNumber,null,1);
        $backArr = array();
        foreach ($list as $key => $value) {
            $arr = array();
            $arr['days'] = $value->no_of_days - $value->days_used;
            $arr['entitlementId'] = $value->id;
            $arr['LeaveType'] = $value->leaveType->name;
            $arr['leaveTypeId'] = $value->leave_type_id;
            $arr['from_date'] = $value->from_date;
            $arr['to_date'] = $value->to_date;

            $backArr[] = $arr;
        }
        $this->serializer['status'] = true;
        $this->serializer['errno'] = 0;
        $this->serializer['message'] = '';
        return array('data'=>$backArr);
        
    }

    /**
     * @SWG\Post(path="/weixin/entitlement-log",
     *     tags={"云平台-WEIXIN-微信接口"},
     *     summary="获取假期变动明细",
     *     description="用户登录：成功返回用户信息；失败返回具体原因",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "Token",
     *        description = "60f5d74b625b79aafe22808f8bbddec4907f8204",
     *        required = true,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "status",
     *        description = "状态 0 所有 1增加 2减少",
     *        required = false,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "leaveTypeId",
     *        description = "假期类型ID",
     *        required = true,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "entitlementId",
     *        description = "休假ID",
     *        required = true,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "page",
     *        description = "当前页",
     *        required = false,
     *        type = "string"
     *     ),
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
    public function actionEntitlementLog(){
        $leaveTypeId = Yii::$app->request->post('leaveTypeId'); 
        $entitlementId = Yii::$app->request->post('entitlementId'); 
        $status = Yii::$app->request->post('status'); 
        $page = Yii::$app->request->post('page'); 

        if(empty($leaveTypeId)||empty($entitlementId)){
            $this->serializer['status'] = false;
            $this->serializer['errno'] = 0;
            $this->serializer['message'] = '';
            return false;
        }

        if(empty($page)){
            $page = 1 ;
        }
        $pageSize = Yii::$app->params['pageSize']['default'];
        
        $offset = ($page >= 1) ? (($page - 1) * $pageSize) : 0;


        $LeaveEntitlementLog = new LeaveEntitlementLog();
        $list = $LeaveEntitlementLog->getEntitlementLogById($leaveTypeId,$entitlementId,$status,$pageSize,$offset);
        $count = $list['count'];
        $backArr = array();
        foreach ($list['list'] as $key => $value) {
            $arr = array();
            $arr['createById']= $value->create_by_id;
            $arr['createByName']= $value->create_by_name;
            $arr['date']= $value->create_time;
            if($value->status==1){
                $arr['days']= '+'.floatval($value->days);
            }else{
                $arr['days']= '-'.abs(floatval($value->days));
            }
            $arr['entitlementId']= $value->entitlement_id;
            $arr['id']= $value->id;
            $arr['leaveTypeId']= $value->entitlement_type;
            $arr['leaveTypeName']= $value->leaveType->name;
            $arr['noOfDays']= $value->no_of_days;
            $arr['note']= $value->note;


            $backArr[] = $arr;
        }

 
        $this->serializer['status'] = true;
        $this->serializer['errno'] = 0;
        $this->serializer['message'] = '';
        return array('data'=>$backArr,'totalCount'=>(int)$count,'current_page'=>(int)$page,'pageSize'=>(int)$pageSize);
        
        
    }

    /**
     * @SWG\Post(path="/weixin/my-application",
     *     tags={"云平台-WEIXIN-微信接口"},
     *     summary="我的申请",
     *     description="用户登录：成功返回用户信息；失败返回具体原因",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "Token",
     *        description = "60f5d74b625b79aafe22808f8bbddec4907f8204",
     *        required = true,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "queryType",
     *        description = "1 申请 2同意 0取消 -1 拒绝",
     *        required = false,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "type",
     *        description = "类型 0所有 1请假 2加班 3漏打卡 4调班",
     *        required = true,
     *        type = "string"
     *     ),
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
    public function actionMyApplication(){

        if($this->userRoleId==1&&empty($this->empNumber)){
            $this->serializer['status'] = false;
            $this->serializer['errno'] = 0;
            $this->serializer['message'] = '管理员没有申请的选项';
            return false;
        }
        $AppSys = new AppSys();

        $queryType = Yii::$app->request->post('queryType'); 
        $type = Yii::$app->request->post('type'); 
        $list =array();



        if($type ==1){   //休假
            $list = $AppSys->getLeaveList($this->empNumber,$queryType);
        }else if($type ==2){ //加班
            $list = $AppSys->getOverList($this->empNumber,$queryType);
        }else if($type ==3){  //打卡
            $list = $AppSys->getAtteList($this->empNumber,$queryType);
        }else if($type ==4){  //调班
            $list = $AppSys->getShiftApplyList($this->empNumber,$queryType);
        }else {
            $tep1 = $AppSys->getLeaveList($this->empNumber,$queryType);
            
            if(!empty($tep1)){
               $list = array_merge($list, $tep1);
            }

            $tep2 = $AppSys->getOverList($this->empNumber,$queryType);
            
            if(!empty($tep2)){
                $list = array_merge($list, $tep2);
            }
            $tep3= $AppSys->getAtteList($this->empNumber,$queryType);
            
            if(!empty($tep3)){
                $list = array_merge($list, $tep3);
            }
            $tep4 = $AppSys->getShiftApplyList($this->empNumber,$queryType);
            
            //  $list = array_merge($tep2,$tep3,$tep4);
            if(!empty($tep4)){
                $list = array_merge($list, $tep4);
            }

        }

        if(!empty($list)){
            $list = arraySequence($list,'time','SORT_DESC');

            $this->serializer['status'] = true;
            $this->serializer['errno'] = 0;
            $this->serializer['message'] = '请求成功';
            return array('data'=>$list);
        }else{
            $this->serializer['status'] = false;
            $this->serializer['errno'] = 0;
            $this->serializer['message'] = '暂无数据';
            return false;  
        }
        
    }

    /**
     * @SWG\Post(path="/weixin/application-list",
     *     tags={"云平台-WEIXIN-微信接口"},
     *     summary="我的申请列表",
     *     description="用户登录：成功返回用户信息；失败返回具体原因",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "Token",
     *        description = "60f5d74b625b79aafe22808f8bbddec4907f8204",
     *        required = true,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "queryType",
     *        description = "1 申请 2同意 0取消 -1 拒绝",
     *        required = true,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "type",
     *        description = "类型 0所有 1请假 2加班 3漏打卡 4调班",
     *        required = true,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "isSub",
     *        description = "1 我的申请  2我的审核",
     *        required = true,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "page",
     *        description = "分页id",
     *        required = true,
     *        type = "string"
     *     ),
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
    public function actionApplicationList(){

        if($this->userRoleId==1&&empty($this->empNumber)){
            $this->serializer['status'] = false;
            $this->serializer['errno'] = 0;
            $this->serializer['message'] = '管理员没有申请的选项';
            return false;
        }
        $AppSys = new AppSys();

        $queryType = Yii::$app->request->post('queryType'); 
        $type = Yii::$app->request->post('type'); 
        $page = Yii::$app->request->post('page');
        $isSub = Yii::$app->request->post('isSub');

        if($isSub!=1){
            $isSub  = 2;
        }
        
        $pageSize = $limit = Yii::$app->params['pageSize']['default'];

        $offset = ($page >= 1) ? (($page - 1) * $pageSize) : 0;

        if(!$page){
            $page = 1;
        }
        $list =array();
        
    
        $list = $AppSys->getApplicationList($this->empNumber,$queryType,$type,$isSub,$offset,$limit);

        if(!empty($list['data'])){
            $list['current_page'] = (int)$page;
            $list['pageSize'] = (int)$pageSize;
            $list['totalCount'] =(int)$list['totalCount'];
            $this->serializer['status'] = true;
            $this->serializer['errno'] = 0;
            $this->serializer['message'] = '请求成功';
            return $list;
        }else{
            $this->serializer['status'] = false;
            $this->serializer['errno'] = 2;
            $this->serializer['message'] = '暂无数据';
            return false;  
        }
        
    }


    /**
     * @SWG\Post(path="/weixin/update-apply-status",
     *     tags={"云平台-WEIXIN-微信接口"},
     *     summary="审批全部同意",
     *     description="用户登录：成功返回用户信息；失败返回具体原因",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "Token",
     *        description = "60f5d74b625b79aafe22808f8bbddec4907f8204",
     *        required = true,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "type",
     *        description = "类型 0所有 1请假 2加班 3漏打卡 4调班",
     *        required = true,
     *        type = "string"
     *     ),
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
    public function actionUpdateApplyStatus(){

        if($this->userRoleId==1&&empty($this->empNumber)){
            $this->serializer['status'] = false;
            $this->serializer['errno'] = 0;
            $this->serializer['message'] = '管理员没有申请的选项';
            return false;
        }
        $AppSys = new AppSys();
        $ApproverTab = new ApproverTab();

        $queryType = Yii::$app->request->post('queryType'); 
        $type = Yii::$app->request->post('type'); 

        $queryType = 1;

        $list =array();

        if($this->userId==1){
            $empNumber = 0;
        }else{
            $empNumber = $this->empNumber;
        }
        
    
        $list = $AppSys->saveApplicationListAll($this->empNumber,$queryType,$type);

        if(!empty($list)){
            $this->serializer['status'] = true;
            $this->serializer['errno'] = 0;
            $this->serializer['message'] = '修改成功';
            return $list;
        }else{
            $this->serializer['status'] = false;
            $this->serializer['errno'] = 0;
            $this->serializer['message'] = '修改失败';
            return false;  
        }
        
    }

    /**
     * @SWG\Post(path="/weixin/application-detail",
     *     tags={"云平台-WEIXIN-微信接口"},
     *     summary="查看申请/审批详情",
     *     description="用户登录：成功返回用户信息；失败返回具体原因",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "Token",
     *        description = "60f5d74b625b79aafe22808f8bbddec4907f8204",
     *        required = true,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "queryType",
     *        description = "1 申请中 2同意 -1拒绝",
     *        required = true,
     *        type = "string"
     *     ),
    *      @SWG\Parameter(
     *        in = "formData",
     *        name = "type",
     *        description = "类型 1请假 2加班 3漏打卡 4调班",
     *        required = true,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "id",
     *        description = "要查询的id",
     *        required = true,
     *        type = "string"
     *     ),
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
    public function actionApplicationDetail(){
        $queryType = Yii::$app->request->post('queryType'); 
        $type = Yii::$app->request->post('type'); 
        $id = Yii::$app->request->post('id');

        $backArr = array();
        $nowtime = date('Y-m-d H:i:s');
        if(empty($id||empty($type))){
            $this->serializer['status'] = false;
            $this->serializer['errno'] = 0;
            $this->serializer['message'] = '参数错误';
            return false;
        }
        $ApproverTab =new ApproverTab();
        if($type==1){
            $LeaveRequest = new LeaveRequest();
            $data = $LeaveRequest->getLeaveRequestById($id);

            if($data){
                if(is_null($queryType)){
                    $queryType = (int) $data->is_pro;
                }
            }else{
                $this->serializer['status'] = false;
                $this->serializer['errno'] = 0;
                $this->serializer['message'] = '找不到此数据';
                return false;
            }
            $sup_list = $ApproverTab->getApplicantBySubEmployee($data->emp_number,$id,$type);

            $backArr['id'] = $data->id ;
            $backArr['type'] = 1;
            $backArr['queryType'] = $queryType;
            $backArr['queryType'] = $data->is_pro;
            $backArr['name'] = $data->leaveType->name;
            $backArr['note'] = $data->comments;
            $backArr['time'] = $data->create_time;
            $backArr['beforeTime'] = getTimeConversion(floor((strtotime($nowtime)-strtotime($data->create_time))/60)) . '之前';


            $backArr['waitFortime'] = '已等待'.getTimeConversion(floor((strtotime($nowtime)-strtotime($data->create_time))/60)) ;

            $backArr['formName'] = $sup_list['sub'].' 发起申请';

            $stat_time = '';
            $end_time  = '';
            $statDay = '';
            $endDay = '';
            $lengthDay = 0; 
            foreach ($data->leave as $k => $v) {
                if(!empty($v['start_time'])&&$v['start_time']!='00:00:00'){
                    $stat_time =$v['start_time'] ;
                    $end_time  = '';
                }
                if(!empty($v['end_time'])&&$v['end_time']!='00:00:00'){
                    $end_time =$v['end_time'] ;
                }
                if($k==0){
                    $statDay = $v['date'] ;
                }
                $endDay = $v['date'];
                $lengthDay+=$v['length_days'];
            }

            $backArr['head_note'] =array('first'=> '请假'.$lengthDay.'天 ','second'=>$statDay.' '.$stat_time.'--'.$endDay.' '.$end_time);  
            if($queryType==1){

                $backArr['operation'] =array(
                        array(
                                'formName'=>$sup_list['sub'],
                                'formdet' => '发起申请' ,
                                'formtime' => $data->create_time,
                            ),
                        array(
                                'formName'=>$sup_list['sup'],
                                'formdet' => '审批中' ,
                                'formtime' => '已等待'.getTimeConversion(floor((strtotime($nowtime)-strtotime($data->create_time))/60)) ,

                            )
                        );
                
            }else if($queryType==2){
                if(!empty($sup_list['gre'])){
                    $backArr['operation'] =array(
                            array(
                                    'formName'=>$sup_list['sub'],
                                    'formdet' => '发起申请' ,
                                    'formtime' => $data->create_time,
                                ),
                            array(
                                    'formName'=>$sup_list['gre'],
                                    'formdet' => '已审核' ,
                                    'formtime' =>'',
                                )
                            );
                }else{
                    $backArr['operation'] =array(
                            array(
                                    'formName'=>$sup_list['sub'],
                                    'formdet' => '发起申请' ,
                                    'formtime' => $data->create_time,
                                ),
                            array(
                                    'formName'=>'组长',
                                    'formdet' => '审核同意' ,
                                    'formtime' =>'',
                                )
                            );
                }
            }else if($queryType==-1){
                if(!empty($sup_list['gre'])){
                    $backArr['operation'] =array(
                            array(
                                    'formName'=>$sup_list['sub'],
                                    'formdet' => '发起申请' ,
                                    'formtime' => $data->create_time,
                                ),
                            array(
                                    'formName'=>$sup_list['gre'],
                                    'formdet' => '审核拒绝' ,
                                    'formtime' =>'',
                                )
                            );
                }else{
                    $backArr['operation'] =array(
                            array(
                                    'formName'=>$sup_list['sub'],
                                    'formdet' => '发起申请' ,
                                    'formtime' => $data->create_time,
                                ),
                            array(
                                    'formName'=>'组长',
                                    'formdet' => '审核拒绝' ,
                                    'formtime' =>'',
                                )
                            );

                }
            }else if($queryType===0){
                if(!empty($sup_list['gre'])){
                    $leave_arr['operation'] =array(
                            array(
                                    'formName'=>$sup_list['sub'],
                                    'formdet' => '发起申请' ,
                                    'formtime' => $data->create_time,
                                ),
                            array(
                                    'formName'=>$sup_list['gre'],
                                    'formdet' => '撤销' ,
                                    'formtime' =>'',
                                )
                            );
                }else{
                    $leave_arr['operation'] =array(
                            array(
                                    'formName'=>$sup_list['sub'],
                                    'formdet' => '发起申请' ,
                                    'formtime' => $data->create_time,
                                ),
                            array(
                                    'formName'=>'组长',
                                    'formdet' => '撤销' ,
                                    'formtime' =>'',
                                )
                            );

                }
            }else{
                $this->serializer['status'] = false;
                $this->serializer['errno'] = 0;
                $this->serializer['message'] = '错误的请求';
                return false;
            }
            if(!empty($sup_list['wit'])){
                $backArr['toProveBy'] =$sup_list['wit'];
            }else{
                $backArr['toProveBy'] = '';
            }
            if(!empty($sup_list['chao'])){
                $backArr['chaoName'] = $sup_list['chao'];
            }else{
                $backArr['chaoName'] = '';
            }
            
            $backArr['Copyinformation'] ='';

        }else if($type==2){
            $OverTime = new Overtime();
            $data = $OverTime->getOvertimeById($id);

            if($data){
                if(is_null($queryType)){
                    $queryType = (int) $data->is_pro;
                }
            }else{
                $this->serializer['status'] = false;
                $this->serializer['errno'] = 0;
                $this->serializer['message'] = '找不到此数据';
                return false;
            }

            $sup_list = $ApproverTab->getApplicantBySubEmployee($data->emp_number,$id,$type);
            $backArr['id'] = $data->id ;
            $backArr['type'] = 2;
            $backArr['queryType'] = $queryType;
            $backArr['name'] = '加班';
            $backArr['note'] = $data->content;
            $backArr['time'] = $data->creat_time;
            $backArr['beforeTime'] = getTimeConversion(floor((strtotime($nowtime)-strtotime($data->creat_time))/60)) .'之前';
            $backArr['waitFortime'] = '已等待'.getTimeConversion(floor((strtotime($nowtime)-strtotime($data->creat_time))/60)) ;

            if(!empty($data->end_day)){
                $end_day = $data->end_day;
            }else{
                $end_day = $data->current_day;
            }

            $backArr['formName'] = $sup_list['sub'].' 发起申请';
            $backArr['head_note'] =array('first'=>'加班'.$data->hour_differ.'小时 ','second'=>$data->current_day.' '.$data->stat_time.'--'.$end_day.' '.$data->end_time); 
            if($queryType==1){
                if(!empty($sup_list['sup'])){
                    $backArr['operation'] =array(
                            array(
                                    'formName'=>$sup_list['sub'],
                                    'formdet' => '发起申请' ,
                                    'formtime' => $data->creat_time,
                                ),
                            array(
                                    'formName'=>$sup_list['sup'],
                                    'formdet' => '审批中' ,
                                    'formtime' => '已等待'.getTimeConversion(floor((strtotime($nowtime)-strtotime($data->creat_time))/60)) ,
                                )
                            );
                }else{
                    $backArr['operation'] =array(
                            array(
                                    'formName'=>$sup_list['sub'],
                                    'formdet' => '发起申请' ,
                                    'formtime' => $data->creat_time,
                                ),
                            array(
                                    'formName'=>'组长',
                                    'formdet' => '审批中' ,
                                    'formtime' => '已等待'.getTimeConversion(floor((strtotime($nowtime)-strtotime($data->creat_time))/60)) ,
                                )
                            );

                }
            }else if($queryType==2){
                if(!empty($sup_list['gre'])){
                    $backArr['operation'] =array(
                            array(
                                    'formName'=>$sup_list['sub'],
                                    'formdet' => '发起申请' ,
                                    'formtime' => $data->creat_time,
                                ),
                            array(
                                    'formName'=>$sup_list['gre'],
                                    'formdet' => '已审核' ,
                                    'formtime' =>'',
                                )
                            );
                }else{
                    $backArr['operation'] =array(
                            array(
                                    'formName'=>$sup_list['sub'],
                                    'formdet' => '发起申请' ,
                                    'formtime' => $data->creat_time,
                                ),
                            array(
                                    'formName'=>'组长',
                                    'formdet' => '审核同意' ,
                                    'formtime' =>'',
                                )
                            );
                }
            }else if($queryType==-1){
                if(!empty($sup_list['gre'])){
                    $backArr['operation'] =array(
                            array(
                                    'formName'=>$sup_list['sub'],
                                    'formdet' => '发起申请' ,
                                    'formtime' => $over_list->creat_time,
                                ),
                            array(
                                    'formName'=>$sup_list['gre'],
                                    'formdet' => '审核拒绝' ,
                                    'formtime' =>'',
                                )
                            );
                }else{
                    $back['operation'] =array(
                            array(
                                    'formName'=>$sup_list['sub'],
                                    'formdet' => '发起申请' ,
                                    'formtime' => $data->creat_time,
                                ),
                            array(
                                    'formName'=>'组长',
                                    'formdet' => '审核拒绝' ,
                                    'formtime' =>'',
                                )
                            );

                }
            }else if($queryType===0){
                if(!empty($sup_list['gre'])){
                    $backArr['operation'] =array(
                            array(
                                    'formName'=>$sup_list['sub'],
                                    'formdet' => '发起申请' ,
                                    'formtime' => $data->creat_time,
                                ),
                            array(
                                    'formName'=>$sup_list['gre'],
                                    'formdet' => '撤销' ,
                                    'formtime' =>'',
                                )
                            );
                }else{
                    $backArr['operation'] =array(
                            array(
                                    'formName'=>$sup_list['sub'],
                                    'formdet' => '发起申请' ,
                                    'formtime' => $data->creat_time,
                                ),
                            array(
                                    'formName'=>'组长',
                                    'formdet' => '撤销' ,
                                    'formtime' =>'',
                                )
                            );

                }
            }else{
                $this->serializer['status'] = false;
                $this->serializer['errno'] = 0;
                $this->serializer['message'] = '错误的请求';
                return false;
            }
            if(!empty($sup_list['wit'])){
                $backArr['toProveBy'] = $sup_list['wit'];
            }else{
                $backArr['toProveBy'] = '';
            }
            if(!empty($sup_list['chao'])){
                $backArr['chaoName'] = $sup_list['chao'];
            }else{
                $backArr['chaoName'] = '';
            }
            $backArr['Copyinformation'] ='';

        }else if($type==3){
            $AttendanceRecord = new AttendanceRecord();

            $data = $AttendanceRecord->getAttendanceRecordById($id);
            if($data){
                if(is_null($queryType)){
                    $queryType = (int) $data->is_pro;
                }
            }else{
                $this->serializer['status'] = false;
                $this->serializer['errno'] = 0;
                $this->serializer['message'] = '找不到此数据';
                return false;
            }
            $sup_list = $ApproverTab->getApplicantBySubEmployee($data->employee_id,$id,$type);
            $backArr['id'] = $data->id ;
            $backArr['type'] = 3;
            $backArr['queryType'] = $queryType;

            if($data->is_in_status==1){
                $backArr['name'] = '上班漏打卡';
                $backArr['head_note'] = array('first'=>'漏打卡时间','second'=>date('Y-m-d H:i',strtotime($data->punch_in_user_time)));
                $backArr['note'] = $data->punch_in_note;
            }else if($data->is_in_status==2){
                $backArr['name'] = '下班漏打卡';
                $backArr['head_note'] =  array('first'=>'漏打卡时间','second'=>date('Y-m-d H:i',strtotime($data->punch_in_user_time)).'--'.date('Y-m-d H:i',strtotime($data->punch_out_user_time)));
                $backArr['note'] =$data->punch_in_note .'--'. $data->punch_out_note;
            }
            $backArr['formName'] = $sup_list['sub'].' 发起申请';



            $backArr['time'] = $data->create_time;
            $backArr['beforeTime'] = getTimeConversion(floor((strtotime($nowtime)-strtotime($data->create_time))/60)) .'之前';

            if($queryType==1){
                if(!empty($sup_list['sup'])){
                    $backArr['operation'] =array(
                            array(
                                    'formName'=>$sup_list['sub'],
                                    'formdet' => '发起申请' ,
                                    'formtime' => $data->create_time,
                                ),
                            array(
                                    'formName'=>$sup_list['sup'],
                                    'formdet' => '审批中' ,
                                    'formtime' => '已等待'. getTimeConversion(floor((strtotime($nowtime)-strtotime($data->create_time))/60)),
                                )
                            );
                }
            }else if($queryType==2){
                if(!empty($sup_list['gre'])){
                    $backArr['operation'] =array(
                            array(
                                    'formName'=>$sup_list['sup'],
                                    'formdet' => '发起申请' ,
                                    'formtime' => $data->create_time,
                                ),
                            array(
                                    'formName'=>$sup_list['gre'],
                                    'formdet' => '已审核' ,
                                    'formtime' =>'',
                                )
                            );
                }else{
                    $backArr['operation'] =array(
                            array(
                                    'formName'=>$sup_list['sup'],
                                    'formdet' => '发起申请' ,
                                    'formtime' => $data->create_time,
                                ),
                            array(
                                    'formName'=>'组长',
                                    'formdet' => '审核同意' ,
                                    'formtime' =>'',
                                )
                            );
                }
            }else if($queryType==-1){
                if(!empty($sup_list['gre'])){
                    $backArr['operation'] =array(
                            array(
                                    'formName'=>$sup_list['sup'],
                                    'formdet' => '发起申请' ,
                                    'formtime' => $data->create_time,
                                ),
                            array(
                                    'formName'=>$sup_list['gre'],
                                    'formdet' => '审核拒绝' ,
                                    'formtime' =>'',
                                )
                            );
                }else{
                    $backArr['operation'] =array(
                            array(
                                    'formName'=>$sup_list['sup'],
                                    'formdet' => '发起申请' ,
                                    'formtime' => $data->create_time,
                                ),
                            array(
                                    'formName'=>'组长',
                                    'formdet' => '审核拒绝' ,
                                    'formtime' =>'',
                                )
                            );

                }
            }else if($queryType===0){
                if(!empty($sup_list['gre'])){
                    $backArr['operation'] =array(
                            array(
                                    'formName'=>$sup_list['sup'],
                                    'formdet' => '发起申请' ,
                                    'formtime' => $data->create_time,
                                ),
                            array(
                                    'formName'=>$sup_list['gre'],
                                    'formdet' => '撤销' ,
                                    'formtime' =>'',
                                )
                            );
                }else{
                    $backArr['operation'] =array(
                            array(
                                    'formName'=>$sup_list['sup'],
                                    'formdet' => '发起申请' ,
                                    'formtime' => $data->create_time,
                                ),
                            array(
                                    'formName'=>'组长',
                                    'formdet' => '撤销' ,
                                    'formtime' =>'',
                                )
                            );

                }
            }else{
                $this->serializer['status'] = false;
                $this->serializer['errno'] = 0;
                $this->serializer['message'] = '错误的请求';
                return false;
            }
            if(!empty($sup_list['wit'])){
                $backArr['toProveBy'] = $sup_list['wit'];
            }else{
                $backArr['toProveBy'] = '';
            }
            if(!empty($sup_list['chao'])){
                $backArr['chaoName'] = $sup_list['chao'];
            }else{
                $backArr['chaoName'] = '';
            }

            $backArr['Copyinformation'] ='';

        }else if($type==4){
            $ShiftChangeApply = new ShiftChangeApply();
            $Employee = new Employee();
            $Schedule = new Schedule();
            $ShiftType = new ShiftType();
            $data = $ShiftChangeApply->getShiftChangeApplyById($id);
            if($data){
                if(is_null($queryType)){
                    $queryType = (int) $data->status;
                }
            }else{
                $this->serializer['status'] = false;
                $this->serializer['errno'] = 0;
                $this->serializer['message'] = '找不到此数据';
                return false;
            }
            $sup_list = $ApproverTab->getApplicantBySubEmployee($data->orange_emp,$id,$type);

            $backArr['id'] = $data->id ;
            $backArr['type'] = 4;
            $backArr['queryType'] = $queryType;
            $backArr['name'] = '调班 '.$data->shift_date;
            $backArr['note'] =$data->reason_shift;
            $backArr['time'] = $data->create_at;
            $time_mark = $data->time_mark;

            $backArr['beforeTime'] = getTimeConversion(floor((strtotime($nowtime)-strtotime($data->create_at))/60)) .'之前';

            $backArr['waitFortime'] = '已等待'.getTimeConversion(floor((strtotime($nowtime)-strtotime($data->create_at))/60)) ;

            $orange_emp =  $Employee->getEmpByNumNber($data->orange_emp);
            $confirm_emp =  $Employee->getEmpByNumNber($data->confirm_emp);
            $schedule =  $Schedule->getScheduleById($data->schedule_id);


            $shift_arr['formName'] = $orange_emp->emp_firstname.' 发起申请';

            if($schedule){
                $scheduleName = $schedule->name;
            }else{
                $scheduleName = '';
            }

               
            $orangeShiftType = $ShiftType->getShifTypeById($data->orange_type);
            $confirmShiftType = $ShiftType->getShifTypeById($data->confirm_type);


                if($time_mark==0){
                    if($orangeShiftType){
                        $orange_start_time= getStrSubstr($orangeShiftType['start_time']);
                        if(!empty($orangeShiftType['end_time'])&&$orangeShiftType['end_time']!='00:00:00'){
                            $orange_end_time = getStrSubstr($orangeShiftType['end_time']);
                        }else{
                            $orange_end_time = getStrSubstr($orangeShiftType['end_time_afternoon']);
                        }
                    }
                    if($confirmShiftType){
                        $confirm_start_time= getStrSubstr($confirmShiftType['start_time']);
                        if(!empty($confirmShiftType['end_time'])&&$confirmShiftType['end_time']!='00:00:00'){
                            $confirm_end_time = getStrSubstr($confirmShiftType['end_time']);
                        }else{
                            $confirm_end_time = getStrSubstr($confirmShiftType['end_time_afternoon']);
                        }
                    }
                }else if($time_mark==1){
                    if($orangeShiftType){
                        $orange_start_time= getStrSubstr($orangeShiftType['start_time']);
                        $orange_end_time = getStrSubstr($orangeShiftType['end_time_afternoon']);
                    }
                    if($confirmShiftType){
                        $confirm_start_time= getStrSubstr($confirmShiftType['start_time']);
                        $confirm_end_time = getStrSubstr($confirmShiftType['end_time_afternoon']);
                    }

                    
                }else if($time_mark==2){   
                    if($orangeShiftType){
                        $orange_start_time = getStrSubstr($orangeShiftType['start_time_afternoon']);
                        $orange_end_time = getStrSubstr($orangeShiftType['end_time']);
                    }
                    if($confirmShiftType){
                        $confirm_start_time= getStrSubstr($confirmShiftType['start_time_afternoon']);
                        $confirm_end_time = getStrSubstr($confirmShiftType['end_time']);
                    }
                    

                    

                }

                if($orangeShiftType){
                    $showfirst = $orangeShiftType['name'].' '.$orange_start_time.'-'.$orange_end_time.' '.$orange_emp->emp_firstname; 
                }else{
                    $showfirst = ' 休假 '.$orange_emp->emp_firstname; 
                }
                if($confirmShiftType){
                    $showsecond = $confirmShiftType['name'].' '.$confirm_start_time.'-'.$confirm_end_time.' '.$confirm_emp->emp_firstname; 
                }else{
                    $showsecond = '休假 '.$confirm_emp->emp_firstname; 
                }

                $backArr['head_note'] =array('first'=>$showfirst,'second'=>$showsecond);
                if($queryType==1){
                    if(!empty($sup_list['sup'])){
                        $backArr['operation'] =array(
                                array(
                                        'formName'=>$orange_emp->emp_firstname,
                                        'formdet' => '发起申请' ,
                                        'formtime' => $data->create_at,
                                    ),
                                array(
                                        'formName'=>$sup_list['sup'],
                                        'formdet' => '审批中' ,
                                        'formtime' => '已等待'.getTimeConversion(floor((strtotime($nowtime)-strtotime($data->create_at))/60)) ,
                                    )
                                );
                    }else{
                        $backArr['operation'] =array(
                                array(
                                        'formName'=>$orange_emp->emp_firstname,
                                        'formdet' => '发起申请' ,
                                        'formtime' => $data->create_at,
                                    ),
                                array(
                                        'formName'=>'组长',
                                        'formdet' => '审批中' ,
                                        'formtime' => '已等待'.getTimeConversion(floor((strtotime($nowtime)-strtotime($data->create_at))/60)) ,
                                    )
                                );

                    }
                }else if($queryType==2){
                    if(!empty($sup_list['gre'])){
                        $backArr['operation'] =array(
                                array(
                                        'formName'=>$orange_emp->emp_firstname,
                                        'formdet' => '发起申请' ,
                                        'formtime' => $data->create_at,
                                    ),
                                array(
                                        'formName'=>$sup_list['gre'],
                                        'formdet' => '已审核' ,
                                        'formtime' =>'',
                                    )
                                );
                    }else{
                        $backArr['operation'] =array(
                                array(
                                        'formName'=>$orange_emp->emp_firstname,
                                        'formdet' => '发起申请' ,
                                        'formtime' => $data->create_at,
                                    ),
                                array(
                                        'formName'=>'组长',
                                        'formdet' => '审核同意' ,
                                        'formtime' =>'',
                                    )
                                );
                    }
                }else if($queryType==-1){
                    if(!empty($sup_list['gre'])){
                        $backArr['operation'] =array(
                                array(
                                        'formName'=>$orange_emp->emp_firstname,
                                        'formdet' => '发起申请' ,
                                        'formtime' =>$data->create_at,
                                    ),
                                array(
                                        'formName'=>$sup_list['gre'],
                                        'formdet' => '审核拒绝' ,
                                        'formtime' =>'',
                                    )
                                );
                    }else{
                        $backArr['operation'] =array(
                                array(
                                        'formName'=>$orange_emp->emp_firstname,
                                        'formdet' => '发起申请' ,
                                        'formtime' => $data->create_at,
                                    ),
                                array(
                                        'formName'=>'组长',
                                        'formdet' => '审核拒绝' ,
                                        'formtime' =>'',
                                    )
                                );

                    }
                }else if($queryType===0){
                    if(!empty($sup_list['gre'])){
                        $shift_arr['operation'] =array(
                                array(
                                        'formName'=>$orange_emp->emp_firstname,
                                        'formdet' => '发起申请' ,
                                        'formtime' =>$shift_list->create_at,
                                    ),
                                array(
                                        'formName'=>$sup_list['gre'],
                                        'formdet' => '撤销' ,
                                        'formtime' =>'',
                                    )
                                );
                    }else{
                        $shift_arr['operation'] =array(
                                array(
                                        'formName'=>$orange_emp->emp_firstname,
                                        'formdet' => '发起申请' ,
                                        'formtime' => $data->create_at,
                                    ),
                                array(
                                        'formName'=>'组长',
                                        'formdet' => '撤销' ,
                                        'formtime' =>'',
                                    )
                                );

                    }
                }else{
                    $this->serializer['status'] = false;
                    $this->serializer['errno'] = 0;
                    $this->serializer['message'] = '错误的请求';
                    return false;
                }
                if(!empty($sup_list['wit'])){
                    $backArr['toProveBy'] = $sup_list['wit'];
                }else{
                    $backArr['toProveBy'] = '';
                }
                if(!empty($sup_list['chao'])){
                    $backArr['chaoName'] = $sup_list['chao'];
                }else{
                    $backArr['chaoName'] = '';
                }
                $backArr['Copyinformation'] ='';


        }else{
            $this->serializer['status'] = false;
            $this->serializer['errno'] = 0;
            $this->serializer['message'] = '错误的请求';
            return false;
        }

        if($backArr){
            $this->serializer['status'] = true;
            $this->serializer['errno'] = 0;
            $this->serializer['message'] = '';
            return array('data'=>$backArr);
        }else{
            $this->serializer['status'] = false;
            $this->serializer['errno'] = 0;
            $this->serializer['message'] = '暂无数据';
            return false;
        }
        
    }

    /**
     * @SWG\Post(path="/weixin/update-status",
     *     tags={"云平台-WEIXIN-微信接口"},
     *     summary="修改申请状态",
     *     description="用户登录：成功返回用户信息；失败返回具体原因",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "Token",
     *        description = "60f5d74b625b79aafe22808f8bbddec4907f8204",
     *        required = true,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "queryType",
     *        description = "1 申请中 2同意 -1拒绝",
     *        required = true,
     *        type = "string"
     *     ),
    *      @SWG\Parameter(
     *        in = "formData",
     *        name = "type",
     *        description = "类型 1请假 2加班 3漏打卡 4调班",
     *        required = true,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "id",
     *        description = "要查询的id",
     *        required = true,
     *        type = "string"
     *     ),
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
    public function actionUpdateStatus(){
        $queryType = Yii::$app->request->post('queryType'); 
        $type = Yii::$app->request->post('type'); 
        $id = Yii::$app->request->post('id');
        $note = Yii::$app->request->post('note');

        $statusArr = array(-1,0,1,2,3);
        $is_true = false;
        if(!in_array($queryType,$statusArr)){
            $this->serializer['status'] = false;
            $this->serializer['errno'] = 0;
            $this->serializer['message'] = '修改的状态不正确';
            return false;
        }else{
            $status  = (int) $queryType;
        }



        $ApproverTab = new ApproverTab();
        if($type==1){
            $LeaveRequest = new LeaveRequest();
            $data = $LeaveRequest->getLeaveRequestById($id);
            if($data){
                $is_pro = (int) $data->is_pro;
                if($is_pro!=1){
                    $this->serializer['status'] = false;
                    $this->serializer['errno'] = 0;
                    $this->serializer['message'] = '你已经修改过此状态了';
                    return false; 
                }    
            }else{
                $this->serializer['status'] = false;
                $this->serializer['errno'] = 0;
                $this->serializer['message'] = '参数错误';
                return false;
            }
            $empNumber = $data->emp_number;

            $LeaveEntitlement = new LeaveEntitlement();

            $LeaveRequest = new LeaveRequest();
            $list = $LeaveRequest->getViemLeaveRequestList($id);
            foreach($list as $v){
                $ids[] =$v['id'];
            }
            if($status==2){
                if($data->is_pro==1){
                    $data->is_pro = $status ;
                    $is_true = $LeaveEntitlement->updateLeaveStatus($empNumber,$ids,$status,null,0,$note,$this->empNumber);
                }
            }else{
                if($data->is_pro==1||$data->is_pro==2||$data->is_pro==3){
                    $is_true = $LeaveEntitlement->updateLeaveStatus($empNumber,$ids,$status,null,0,$note,$this->empNumber);
                }     
            }  

        }else if($type==2){
            $OverTime = new Overtime();
            $data = $OverTime->getOvertimeById($id);
            if($data){
                $is_pro = (int) $data->is_pro;
                if($is_pro!=1){
                    $this->serializer['status'] = false;
                    $this->serializer['errno'] = 0;
                    $this->serializer['message'] = '你已经修改过此状态了';
                    return false; 
                }    
            }else{
                $this->serializer['status'] = false;
                $this->serializer['errno'] = 0;
                $this->serializer['message'] = '参数错误';
                return false;
            }

            if($status==2){
                if($data->is_pro==1){
                    $is_true = $OverTime->updateOvertimeStatus($id,$status,$this->empNumber,$note);
                }
            }else{
                if($data->is_pro==1||$data->is_pro==2||$data->is_pro==3){
                    $is_true = $OverTime->updateOvertimeStatus($id,$status,$this->empNumber,$note);
                }     
            }  


        }else if($type==3){
            $AttendanceRecord = new AttendanceRecord();
            $data = $AttendanceRecord->getAttendanceRecordById($id);
            if($data){
                $is_pro = (int) $data->is_pro;
                if($is_pro!=1){
                    $this->serializer['status'] = false;
                    $this->serializer['errno'] = 0;
                    $this->serializer['message'] = '你已经修改过此状态了';
                    return false; 
                }    
            }else{
                $this->serializer['status'] = false;
                $this->serializer['errno'] = 0;
                $this->serializer['message'] = '参数错误';
                return false;
            }
            $data->is_pro = $status ;
            if($status!=2){
                if($status==0){
                    $data->is_out_status = 1;
                }else if($queryType==-1){
                    $data->is_out_status = -1;
                }else{
                    $data->is_out_status = 1;
                }
                $data->save();
            }else{
                $data->save();
                $punchDate = date('Y-m-d',strtotime($data->first_daka_time));
                $attrs = $AttendanceRecord->getAttendanceRecord($data->employee_id,$punchDate);
                    $nextState = 'PUNCHED OUT';
                    if($attrs){
  

                        $attrs->state = $nextState;
                        $attrs->daka_status = 0;
                        $attrs->save();
                    }
            }

            $ApproverTab->updateStatusById($id,3,$status,$this->empNumber);
            $is_true = true;
        }else if($type==4){
            $ShiftChangeApply = new ShiftChangeApply();
            $Employee = new Employee();
            $Schedule = new Schedule();
            $ShiftType = new ShiftType();
            $ShiftResult = new ShiftResult();
            $data = $ShiftChangeApply->getShiftChangeApplyById($id);
            if($data){
                $is_pro = (int) $data->status;
                if($is_pro!=1){
                    $this->serializer['status'] = false;
                    $this->serializer['errno'] = 0;
                    $this->serializer['message'] = '你已经修改过此状态了';
                    return false; 
                }    
            }else{
                $this->serializer['status'] = false;
                $this->serializer['errno'] = 0;
                $this->serializer['message'] = '参数错误';
                return false;
            }

            if($status==2){
                $orangeType = $data->orange_type;
                $orangeTypEntity=$ShiftType->getShifTypeById($orangeType);

                $confirmType = $data->confirm_type;
                $newShiftType =$ShiftType->getShifTypeById($confirmType);

                $isUp =$ShiftResult->confirmShiftNoLeave($data->schedule_id,$data->shift_date,$data->orange_emp,$data->confirm_emp,$data->orange_type,$data->confirm_type,$data->time_mark,null,$orangeTypEntity,$newShiftType);

                if($isUp['status']){
                    $data->status = $status;
                    $data->save();
                    $ApproverTab->updateStatusById($id,4,$status,$this->empNumber);
                    $is_true =true;
                }else{
                    $is_true =false;
                }

            }else{
                $data->status = $status;
                $data->save();
            }


        }
        if($is_true){
            $this->serializer['status'] = true;
            $this->serializer['errno'] = 0;
            $this->serializer['message'] = '修改成功';
            return true;
        }else{
            $this->serializer['status'] = false;
            $this->serializer['errno'] = 0;
            $this->serializer['message'] = '修改失败';
            return false;
        }

    }

    /**
     * @SWG\Post(path="/weixin/get-employee-list",
     *     tags={"云平台-WEIXIN-微信接口"},
     *     summary="获取抄送人审批人证明人",
     *     description="用户登录：成功返回用户信息；失败返回具体原因",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "Token",
     *        description = "60f5d74b625b79aafe22808f8bbddec4907f8204",
     *        required = true,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "upWard",
     *        description = "类型 0证明人 1审批人 2抄送人",
     *        required = true,
     *        type = "string"
     *     ),
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
    public function actionGetEmployeeList(){


        $upWard = Yii::$app->request->post('upWard'); 

        $list =array();

        $User = new User();

        if($upWard==1){
            $userRole = array(1,4,5);
            $workStation = $this->workStation;
        }else if($upWard==2){
            $userRole = array(1,4,5,9);
            $workStation = $this->workStation;
        }else{
            $userRole = array();
            $workStation = null;
        }


        $emplist = $User->getEmployeeByRoleId($userRole);
        $emplist1 = $User->getEmployeeByRoleId(null,$workStation);

        $list = array();

        foreach ($emplist1 as $key => $value) {
            if(empty($value->emp_number)){
                continue;
            }
            if(empty($value->employee->emp_firstname)){
                continue;
            }
            if($value->employee->is_leader){
                $list[$value->emp_number] = $value->employee->emp_firstname;
            }
            
        }

        foreach ($emplist as $key => $value) {
            if(empty($value->emp_number)){
                continue;
            }
            if(empty($value->employee->emp_firstname)){
                continue;
            }
            $list[$value->emp_number] = $value->employee->emp_firstname;
        }
        $backArr = array();
        foreach ($list as $key => $value) {
            $arr['key'] = $key;
            $arr['val'] = $value;
            $backArr[]  = $arr;
        }

        if(!empty($backArr)){
            $this->serializer['status'] = true;
            $this->serializer['errno'] = 0;
            $this->serializer['message'] = '';
            return array('data'=>$backArr);
        }else{
            $this->serializer['status'] = false;
            $this->serializer['errno'] = 0;
            $this->serializer['message'] = '获取失败';
            return false;  
        }
        
    }

    /**
     * @SWG\Post(path="/weixin/get-leave-type",
     *     tags={"云平台-WEIXIN-微信接口"},
     *     summary="获取休假类型",
     *     description="用户登录：成功返回用户信息；失败返回具体原因",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "Token",
     *        description = "60f5d74b625b79aafe22808f8bbddec4907f8204",
     *        required = true,
     *        type = "string"
     *     ),
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
    public function actionGetLeaveType(){

        $LeaveType = new LeaveType();

        $leaveTypeList = $LeaveType->getLeaveTypeList(0);
        $date = date('Y-m-d');
        $backArr = array();
        $LeaveEntitlement =  new LeaveEntitlement();
        if(count($leaveTypeList)){
            $responseArray = array();
            foreach ($leaveTypeList as $type) {
                $detail = $LeaveEntitlement->getEmpLeaveEntitlementByType($this->empNumber, $type->id);

                $balance= 0;
                if($detail){
                    $balance = $detail->no_of_days - $detail->days_used;
                }
                

                if($balance>0){
                    $balance = floatval($balance);
                    if($type->islimit){
                        $arr = array('key'=>$type->id,'val'=>$type->name.'('.$balance.')');
                    }else{
                        $arr = array('key'=>$type->id,'val'=>$type->name);
                    } 
                }else{
                    if(!$type->islimit){
                        $arr = array('key'=>$type->id,'val'=>$type->name);
                    }else{
                        continue;
                    } 
                }
                $backArr[] = $arr;
                
            }
        }else{
            $this->serializer['status'] = false;
            $this->serializer['errno'] = 0;
            $this->serializer['message'] = '管理员还没有创建休假类型';
            return false;  
        }

        if(!empty($backArr)){
            $this->serializer['status'] = true;
            $this->serializer['errno'] = 0;
            $this->serializer['message'] = '';
            return array('data'=>$backArr);
        }else{
            $this->serializer['status'] = false;
            $this->serializer['errno'] = 0;
            $this->serializer['message'] = '没有可用假期';
            return false;  
        }
        
    }


    /**
     * @SWG\Post(path="/weixin/my-leave-request",
     *     tags={"云平台-WEIXIN-微信接口"},
     *     summary="提交休假申请",
     *     description="用户登录：成功返回用户信息；失败返回具体原因",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "Token",
     *        description = "60f5d74b625b79aafe22808f8bbddec4907f8204",
     *        required = true,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "startTime",
     *        description = "开始日期",
     *        required = true,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "endTime",
     *        description = "结束日期",
     *        required = true,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "note",
     *        description = "理由",
     *        required = true,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "leaveType",
     *        description = "休假类型",
     *        required = true,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "duration_type",
     *        description = "全天 0  上午1 下午2 ",
     *        required = true,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "proId",
     *        description = "审批人id",
     *        required = true,
     *        type = "string"
     *     ),
     *      @SWG\Parameter(
     *        in = "formData",
     *        name = "approver",
     *        description = "审批人姓名",
     *        required = true,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "chaoId",
     *        description = "抄送人id",
     *        required = false,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "chaoName",
     *        description = "抄送人姓名",
     *        required = false,
     *        type = "string"
     *     ),

     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "exaId",
     *        description = "证明人id",
     *        required = false,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "witness",
     *        description = "证明人姓名",
     *        required = false,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "unWeekend",
     *        description = "是否剔除周末 1剔除 0不剔除",
     *        required = false,
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
    public function actionMyLeaveRequest(){  
        $empNumber = $this->empNumber;
        $leaveTypeId = Yii::$app->request->post('leaveType');
        $startTime = Yii::$app->request->post('startTime');
        $endTime = Yii::$app->request->post('endTime');
        $duration = Yii::$app->request->post('duration_type');
        $note = Yii::$app->request->post('note');
        $unWeekend = Yii::$app->request->post('unWeekend');

        $exaId = Yii::$app->request->post('exaId');
        $proId = Yii::$app->request->post('proId');
        $chaoId = Yii::$app->request->post('chaoId');
        $chaoName = Yii::$app->request->post('chaoName');
        $approver = Yii::$app->request->post('approver');
        $witness = Yii::$app->request->post('witness');

        $pro_arr = explode(',',$proId);
 
        $chao_arr = explode(',',$chaoId);
        $AppSys = new AppSys();

        

        if(empty($pro_arr)){
            $this->serializer['status'] = false;
            $this->serializer['errno'] = 2;
            $this->serializer['message'] = '请选择审批人';   
            return false;
        }

        if(!$leaveTypeId||!$empNumber){
            $this->serializer['status'] = false;
            $this->serializer['errno'] = 2;
            $this->serializer['message'] = '参数错误';   
            return false;
        }

        if($startTime>$endTime){
            $this->serializer['status'] = false;
            $this->serializer['errno'] = 2;
            $this->serializer['message'] = '开始日期不能大于结束日期';   
            return false;
        }


        $Employee = new Employee();

        $days = array();
        if($startTime==$endTime){  //一天的
            $days = array($startTime);
        }else{
            if($unWeekend){   //剔除周末
                $arrDay = getendday1($startTime,$endTime);

                if(empty($arrDay['useDay'])){
                    $this->serializer['status'] = false;
                    $this->serializer['errno'] = 2;
                    $this->serializer['message'] = '日期选择错误';   
                    return false;
                }
                $days = $arrDay['useDay'];
            }else{
                $days =prDates($startTime,$endTime);
            }
        }

        $days = array_unique($days);
        sort($days);
        
        $countDay = count($days);
        if($countDay==1){
            if($duration){
                $lengthDay = 0.5;
            }else{
                $lengthDay = 1;
            }
        }else if($countDay>1){
            $lengthDay = $countDay;
        }else{
            $this->serializer['status'] = false;
            $this->serializer['errno'] = 2;
            $this->serializer['message'] = '请选择日期';   
            return false;
        }

        $LeaveEntitlement = new LeaveEntitlement();

        $LeaveType = new LeaveType();
        $Leave = new Leave();
        $leavetype = $LeaveType->getLeaveTypeById($leaveTypeId);
        $leaveTypeName = $leavetype['name'];
        if($leavetype['islimit']){   //有限假判断余假数是否够用
            $etitlementv = $LeaveEntitlement->getEmpLeaveEntitlementByType($empNumber,$leaveTypeId);
            if($etitlementv){
                $surplus = $etitlementv->no_of_days - $etitlementv->days_used;
                if($surplus<=0){
                    $surplus = 0; 
                }else{ 
                    $surplus = floatval($surplus);
                }
            }else{
                $surplus = 0;
            }
            if($surplus<$lengthDay){
                $this->serializer['status'] = false;
                $this->serializer['errno'] = 2;
                $this->serializer['message'] = '你的余假数小于你需要请的天数';   
                return false;
            }
        }

        $userDay = $Leave->verificationLeave($empNumber,$leaveTypeId,$days);

        if($userDay){   //有请假
            if($lengthDay==0.5){
                if($userDay[0]['duration_type']&&$userDay['0']['duration_type']!=$duration){

                }else{
                    $this->serializer['status'] = false;
                    $this->serializer['errno'] = 2;
                    $this->serializer['message'] = '你选中的日期已经请过假了';   
                    return false;
                }
            }else{
                $this->serializer['status'] = false;
                $this->serializer['errno'] = 2;
                $this->serializer['message'] = '你选中的日期已经请过假了';   
                return false;
            }
        }else{

        }
        $first = reset($days);
        $end = end($days);


        if($lengthDay==0.5){
            $isHa = $LeaveEntitlement->appointEmployeeLeave($empNumber,$leaveTypeId,$first,$end,$duration,1,$note,$this->firstName,$this->userId,$days);
        }else if($lengthDay==1){
            $isHa =$LeaveEntitlement->appointEmployeeLeave($empNumber,$leaveTypeId,$first,$end,0,1,$note,$this->firstName,$this->userId,$days);
        }else{
            // $first = reset($days);
            // $end = end($days);
            $isHa =$LeaveEntitlement->appointEmployeeLeave($empNumber,$leaveTypeId,$first,$end,0,1,$note,$this->firstName,$this->userId,$days);
        }

        if($isHa['status']&&!empty($isHa['result'])){
            $ApproverTab = new ApproverTab();
            $TabArr = $ApproverTab->saveApproverTabRecod($pro_arr,$this->empNumber,$isHa['result'],$exaId,1,1,$chaoName,$chaoId);
            $a = array();
            foreach($TabArr as $v){
                // $title = '您收到一条请假申请'; //array('tabId'=>$ApproverTab->id,'supId'=>$val);
                // $targetId = $v['tabId'] ;
                // $targetType = 1;
                // $sendId = $this->empNumber;
                // $receiverId = $v['supId'];
                // $content = '申请请假,请求批准';
                //SendMessageByAPI($title,$targetId,$targetType,$sendId,$receiverId,$content);
                $param = array();
                $param['type'] = 1;
                $param['approver'] = $v['supId'];
                $param['sendId'] = $this->empNumber;
                $param['firsteHead'] = '您好，您有一条新审核提醒';
                $param['keyword2'] = date('Y-m-d H:i:s');
                $param['keyword3'] = $leaveTypeName;
                //$param['footer'] = '点击查看';
                $param['footer'] = '';
                $param['url'] = 'my-approval';
                $a[] =$AppSys->sendWeiXinNotice($param);
            }

            foreach($chao_arr as $v){
                // $title = '您收到一条请假申请'; //array('tabId'=>$ApproverTab->id,'supId'=>$val);
                // $targetId = $targetId ;
                // $targetType = 1;
                // $sendId = $this->empNumber;
                // $receiverId = $v;
                // $content = $this->empFirstName.'申请请假,请求查看';
                // SendMessageByAPI($title,$targetId,$targetType,$sendId,$receiverId,$content);

                $param = array();
                $param['type'] = 1;
                $param['approver'] = $v;
                $param['sendId'] = $this->empNumber;
                $param['firsteHead'] = '您好，您收到一条请假申请抄送';
                $param['keyword2'] = date('Y-m-d H:i:s');
                $param['keyword3'] = $leaveTypeName;
                //$param['footer'] = '点击查看';
                $param['footer'] = '';
                $param['url'] = 'news';
                $a[] =$AppSys->sendWeiXinNotice($param);
            }

            $this->serializer['status'] = true;
            $this->serializer['errno'] = 0;
            $this->serializer['message'] = '请假成功';   
            return $a;
        }else{
            $this->serializer['status'] = false;
            $this->serializer['errno'] = 0;
            $this->serializer['message'] = '请假失败';   
            return false;
        }
        
        
    }
    
    /**
     * @SWG\Post(path="/weixin/punch-supplement",
     *     tags={"云平台-WEIXIN-微信接口"},
     *     summary="漏打卡提交",
     *     description="用户登录：成功返回用户信息；失败返回具体原因",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "Token",
     *        description = "60f5d74b625b79aafe22808f8bbddec4907f8204",
     *        required = true,
     *        type = "string"
     *     ),
     *     
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "note",
     *        description = "理由",
     *        required = false,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "date",
     *        description = "日期",
     *        required = false,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "punchDate_1",
     *        description = "上班时间",
     *        required = false,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "punchDate_2",
     *        description = "下班时间",
     *        required = false,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "proId",
     *        description = "审批人id",
     *        required = true,
     *        type = "string"
     *     ),
     *      @SWG\Parameter(
     *        in = "formData",
     *        name = "approver",
     *        description = "审批人姓名",
     *        required = true,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "chaoId",
     *        description = "抄送人id",
     *        required = false,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "chaoName",
     *        description = "抄送人姓名",
     *        required = false,
     *        type = "string"
     *     ),

     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "exaId",
     *        description = "证明人id",
     *        required = false,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "witness",
     *        description = "证明人姓名",
     *        required = false,
     *        type = "string"
     *     ),
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
    public function actionPunchSupplement(){

        // $RecordId = Yii::$app->request->post('RecordId');    //打卡表id
        // $punchStatus = Yii::$app->request->post('punchStatus'); // 1 上班打卡 2下班打卡
        // $punchStatus_1 = Yii::$app->request->post('punchStatus_1'); // 1 上
        // $punchStatus_2 = Yii::$app->request->post('punchStatus_2'); // 1 下

        $note =Yii::$app->request->post('note');  //备注理由
        $punchDate =Yii::$app->request->post('date'); //漏打卡日期
        $punchDate_1 =Yii::$app->request->post('punchDate_1'); //漏打卡上班时间时间
        $punchDate_2 =Yii::$app->request->post('punchDate_2'); //漏打卡下班时间
        
        $proId =Yii::$app->request->post('proId');  //审批人id人id
        $chaoId =Yii::$app->request->post('chaoId');  //抄送id人id
        $chaoName = Yii::$app->request->post('chaoName');  //抄送人姓名
        $exaId =Yii::$app->request->post('exaId');  //证明人人id
        $witness = Yii::$app->request->post('witness');  //证明人姓名
        $chaoName = Yii::$app->request->post('chaoName');  //抄送人id人id
        
        $chao_arr = explode(',', $chaoId);
        $pro_arr = explode(',',$proId);

        $Ndate = date('Y-m-d'); 
        $dateTime = date('Y-m-d H:i:s');  //打卡时间
        if(empty($punchDate)){
            $this->serializer['status'] = false;
            $this->serializer['errno'] = 0;
            $this->serializer['message'] = '补打卡日期不能为空';
            return false; 
        }
        if($Ndate<date('Y-m-d',strtotime($punchDate))){
            $this->serializer['status'] = false;
            $this->serializer['errno'] = 0;
            $this->serializer['message'] = '不能补签未来的打卡信息';
            return false;
        }
        $empNumber = $this->empNumber; //用户id
        
        if(empty($empNumber)){
            $this->serializer['status'] = false;
            $this->serializer['errno'] = 0;
            $this->serializer['message'] = '系统账号不用打卡';
            return false;
        }
        $params = Yii::$app->params;
        $work_time_late = $params['work_time_late']*60;

        $Atte = new AttendanceRecord();
        $AppSys = new AppSys();

        $workTime = $Atte->getWorkShiftByDate($empNumber,$punchDate);
        //var_dump($workTime);die;
        $work_start_time = null;
        $work_middend_time = null;
        $work_middstart_time = null;
        $work_end_time = null;
        $work_date = null;
        $work_thirdstart_time = null;
        $work_thirdend_time= null;
        $wname = null;
        $clock_in = null; 
        $is_daka_half = 0;

        if($workTime){
            if($workTime['isWork']){
                $work_start_time = $workTime['work_start_time'];
                $work_middend_time = $workTime['work_middend_time'];
                $work_middstart_time = $workTime['work_middstart_time'];
                $work_end_time = $workTime['work_end_time'];

                $work_thirdstart_time = $workTime['work_thirdstart_time'];
                $work_thirdend_time = $workTime['work_thirdend_time'];
                $work_date = $workTime['work_date'];
                $wname = $workTime['work_name'];
                $clock_in = $workTime['clock_in'];
                $is_daka_half = $workTime['is_daka_half'];
            }
        }

        $atte = $Atte->getAttendanceRecord($empNumber,$punchDate,2);

        if($atte){

        }else{
            $atte = new AttendanceRecord();
            $UniqueId = new UniqueId();

            $uniqId = $UniqueId->getTableIdByName('ohrm_attendance_record');
            $uid = $uniqId['last_id']+1;
            $uniqId->last_id = $uid;
            $uniqId->save();
            $atte->id = $uid;
            $atte->employee_id = $empNumber;
            //$atte->first_daka_time = $dateTime;
            $atte->create_time = date('Y-m-d H:i:s');

            if(!empty($punchDate_1)&&$punchDate_1!='00:00'){
                $date1 = $punchDate.' '.$punchDate_1;
                $atte->first_daka_time = $date1;
            }else{
                if(!empty($punchDate_2)){
                    $date2 = $punchDate.' '.$punchDate_2;
                    $atte->first_daka_time = $date2;
                }
            }

            $atte->work_start_time = $work_start_time;
            $atte->work_middend_time = $work_middend_time;
            $atte->work_middstart_time = $work_middstart_time;
            $atte->work_end_time = $work_end_time;

            $atte->start_time_third = $work_thirdstart_time;
            $atte->end_time_third = $work_thirdend_time;
            $atte->work_name = $wname;
            $atte->clock_in = $clock_in;
            $atte->is_daka_half = $is_daka_half;
        }

        if(!empty($punchDate_1)){
                    
            $nextState = 'PUNCHED IN';
            $date1 = $punchDate.' '.$punchDate_1;
            $utc_date1 = date('Y-m-d H:i:s',strtotime($date1)-(3600*8));
            $atte->punch_in_utc_time = $utc_date1;
            $atte->punch_in_user_time = $date1;
            $atte->punch_in_actual_time = $dateTime;
            $atte->punch_in_time_offset = '8';

            $banTime = strtotime($work_date.' '.$work_start_time);
            $daTime = strtotime($date1);

            if($work_date){
                if(($daTime - $work_time_late ) > $banTime){
                    $atte->daka_status = 1;
                }else{
                    $atte->daka_status = 0;
                }
            }else{
                $atte->daka_status = 0;
            }
        }

        if(!empty($punchDate_2)){
            if(empty($punchDate_1)){
                // $date1 = $punchDate.' '.$punchDate_2;
                // $utc_date1 = date('Y-m-d H:i:s',strtotime($date1)-(3600*8));
                // $atte->setPunchInUtcTime($utc_date1);
                // $atte->setPunchInUserTime($date1);
                // $atte->setPunchInActualTime($dateTime);
                // $atte->setPunchInTimeOffset(8);
            }
            $nextState = 'PUNCHED OUT';
            $date2 = $punchDate.' '.$punchDate_2;
            $utc_date2 = date('Y-m-d H:i:s',strtotime($date2)-(3600*8));
            $atte->punch_out_utc_time = $utc_date2;
            $atte->punch_out_user_time = $date2;
            $atte->punch_out_actual_time = $dateTime;
            $atte->punch_out_time_offset = '8';


            if($work_end_time&&$work_end_time!='00:00:00'){
                $banTime = strtotime($work_date.' '.$work_end_time);
            }else{
                if($work_middend_time&&$work_middend_time!='00:00:00'){
                    $banTime = strtotime($work_date.' '.$work_middend_time);
                }else{
                    $banTime = 0;
                }
                    
            }
            $daTime = strtotime($date2);
            if(($daTime + $work_time_late ) < $banTime){
                $atte->daka_status = 2;
            }else{
                $atte->daka_status = 0;
            }
        }

        $atte->punch_in_note = $note;   
        $atte->is_in_status = 1;

        $atte->state = $nextState;
        $atte->is_pro = 1;
        $istrue = $atte->save();

        if($istrue){
            $id = $atte->id;
            if(!empty($punchDate_2)){
                //把正常的上班打卡记录修改为  
                $attr = $Atte->getAttendanceRecord($empNumber,$punchDate);
                $nextState = 'PUNCHED OUT';;
                if($attr){
                    $attr->state = $nextState;

                    $attr->late_count = 0;
                    $attr->retreat_count = 0;
                    $attr->save();
                }
            
            }
            $ApproverTab = new ApproverTab();
            $TabArr = $ApproverTab->saveApproverTabRecod($pro_arr,$empNumber,$id,$exaId,3,1,$chaoName,$chaoId);
            $a = array();
            foreach($TabArr as $v){
                // $title = '您收到一条请假申请'; //array('tabId'=>$ApproverTab->id,'supId'=>$val);
                // $targetId = $v['tabId'] ;
                // $targetType = 1;
                // $sendId = $this->empNumber;
                // $receiverId = $v['supId'];
                // $content = '申请请假,请求批准';
                //SendMessageByAPI($title,$targetId,$targetType,$sendId,$receiverId,$content);
                $param = array();
                $param['type'] = 1;
                $param['approver'] = $v['supId'];
                $param['sendId'] = $empNumber;
                $param['firsteHead'] = '您好，您有一条新审核提醒';
                $param['keyword2'] = date('Y-m-d H:i:s');
                $param['keyword3'] = '漏打卡申请';
                //$param['footer'] = '点击查看';
                $param['footer'] = '';
                $param['url'] = 'my-approval';
                $a[] =$AppSys->sendWeiXinNotice($param);
            }

            foreach($chao_arr as $v){
                // $title = '您收到一条请假申请'; //array('tabId'=>$ApproverTab->id,'supId'=>$val);
                // $targetId = $targetId ;
                // $targetType = 1;
                // $sendId = $this->empNumber;
                // $receiverId = $v;
                // $content = $this->empFirstName.'申请请假,请求查看';
                // SendMessageByAPI($title,$targetId,$targetType,$sendId,$receiverId,$content);

                $param = array();
                $param['type'] = 1;
                $param['approver'] = $v;
                $param['sendId'] = $empNumber;
                $param['firsteHead'] = '您好，您收到一条漏打卡申请抄送';
                $param['keyword2'] = date('Y-m-d H:i:s');
                $param['keyword3'] = '漏打卡申请';
                //$param['footer'] = '点击查看';
                $param['footer'] = '';
                $param['url'] = 'news';
                $a[] =$AppSys->sendWeiXinNotice($param);
            }
            $this->serializer['status'] = true;
            $this->serializer['errno'] = 0;
            $this->serializer['message'] = '打卡成功';
            return $a;

        }else{
            $this->serializer['status'] = false;
            $this->serializer['errno'] = 0;
            $this->serializer['message'] = '打卡失败';
            return false;  
        }
        
    }




    /**
     * @SWG\Post(path="/weixin/punch-bydate",
     *     tags={"云平台-WEIXIN-微信接口"},
     *     summary="根据日期获取漏打卡时间",
     *     description="用户登录：成功返回用户信息；失败返回具体原因",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "Token",
     *        description = "60f5d74b625b79aafe22808f8bbddec4907f8204",
     *        required = true,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "date",
     *        description = "要查询的漏打卡日期",
     *        required = true,
     *        type = "string"
     *     ),
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
    public function actionPunchBydate(){

        $date =Yii::$app->request->post('date');  //备注理由
        
        if(empty($date)){
            $date = date('Y-m-d');
        }
        $empNumber = $this->empNumber;
        
        if(empty($this->empNumber)){
            $this->serializer['status'] = false;
            $this->serializer['errno'] = 0;
            $this->serializer['message'] = '管理员没有班次';
            return false;  
            
        }

        $AttendanceService = new AttendanceRecord();
        $Employee = new Employee();


        $dakaArr = array();
        $is_amont_work = 0;
        $shiftDetail = array();
        $workshift = array();
        $headerWorkName = '';
 
        
            $arr = array('empNumber'=>$this->empNumber,'date'=>$date);
            $workshift = $AttendanceService->getEmployeeWorkDetail($arr);
  
            if($workshift){
                $start_time = null;
                $end_time = null;
                $shiftTypeId = 0;
                $name = '';
                if($workshift['frist_type_id']||$workshift['second_type_id']||$workshift['third_type_id']){
                    $workShiftId = $workshift['id'] ;

                    

                    if($workshift['frist_type_id']){
                        $shiftTypeId = $workshift['frist_type_id'];
                        $shiftType = $AttendanceService->getShiftTypeById($workshift['frist_type_id']);
                        $start_time =$shiftType['start_time'];
                        $end_time =$shiftType['end_time_afternoon'];
                        $name = $shiftType['name'];


                    }

                    if($workshift['second_type_id']){
                        $shiftType = $AttendanceService->getShiftTypeById($workshift['second_type_id']);
                        if(empty($start_time)){
                            $start_time =$shiftType['start_time_afternoon'];
                        }
                        
                        $end_time =$shiftType['end_time'];

                        if($shiftTypeId!=$workshift['second_type_id']){
                            $name .= '/'.$shiftType['name'];
                        }
                        
                        $shiftTypeId = $workshift['second_type_id'];
                        
                    }
                    if($workshift['third_type_id']){
                        $shiftType = $AttendanceService->getShiftTypeById($workshift['third_type_id']);

                        if(empty($start_time)){
                            $start_time =$shiftType['time_start_third'];
                        }
                        
                        $end_time =$shiftType['time_end_third'];
                        if($shiftTypeId!=$workshift['third_type_id']){
                            $name .= '/'.$shiftType['name'];
                        }
                        
                        $shiftTypeId = $workshift['second_type_id'];
                    }


                }else{
                    $workShiftId = 0 ;
                    $name = '';
                    $start_time = '';
                    $end_time = '';
                }
                
            }else{
                $workShiftId = 0 ;
                $name = '';
                $start_time = '';
                $end_time = '';
                
            }

        $dakaArr = array('date'=>$date,'start_time'=>$start_time,'end_time'=>$end_time);
        $this->serializer['status'] = true;
        $this->serializer['errno'] = 0;
        $this->serializer['message'] = '';
        return array('dakaArr'=>$dakaArr);;




        
    }

    /**
     * @SWG\Post(path="/weixin/get-workload-detals",
     *     tags={"云平台-WEIXIN-微信接口"},
     *     summary="获取下班打卡添加工作量界面信息接口",
     *     description="用户登录：成功返回用户信息；失败返回具体原因",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "Token",
     *        description = "60f5d74b625b79aafe22808f8bbddec4907f8204",
     *        required = true,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "workShiftId",
     *        description = "班次id",
     *        required = false,
     *        type = "string"
     *     ),
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
    public function actionGetWorkloadDetals(){

        $workShiftId =Yii::$app->request->post('workShiftId');  //备注理由
        
        $empNumber = $this->empNumber; //用户id
        $workStationName = $this->workStationName;
        $date = date('Y-m-d');

        if(empty($empNumber)){
            $this->serializer['status'] = false;
            $this->serializer['errno'] = 0;
            $this->serializer['message'] = '系统账号不用添加工作量';
            return false;

        }
        $Employee = new Employee();
        $employee = $Employee->getEmpByNumNber($empNumber);

        

        $EmpPicture = new EmpPicture();
        $picture = $EmpPicture->getEmpPictureByEmpNumber($empNumber);
        if($picture&&!empty($picture->epic_picture_url)){
            //$url='http://'.$_SERVER['HTTP_HOST'];
            $url= env('STORAGE_HOST_INFO');
            $imgurl = $url.trim($picture->epic_picture_url,'/');
        }else{
            $imgurl = '';
        }

        if($workStationName){
            $subunit_name = $workStationName;
        }else{
            $subunit_name = '';
        }
       

        $AttendanceService = new AttendanceRecord();
        if($workShiftId){
            $arr = array('empNumber'=>$empNumber,'id'=>$workShiftId);
        }else{
            $arr = array('empNumber'=>$empNumber,'date'=>$date);
        }
        
        $res = $AttendanceService->getEmployeeWorkDetail($arr);
        if(!empty($res)&&(!empty($res['frist_type_id'])||!empty($res['second_type_id'])||!empty($res['third_type_id']))){
            $name = '';
            $shiftTypeId = 0;
            $duty_factor = 0;

            if($res['frist_type_id']){
                $shiftTypeId = $res['frist_type_id'];
                $shiftType = $AttendanceService->getShiftTypeById($res['frist_type_id']);
                $start_time =$shiftType['start_time'];
                $end_time =$shiftType['end_time_afternoon'];
                $name = $shiftType['name'];
                $duty_factor = $shiftType['duty_factor'];


            }

            if($res['second_type_id']){
                $shiftType = $AttendanceService->getShiftTypeById($res['second_type_id']);
                if(empty($start_time)){
                    $start_time =$shiftType['start_time_afternoon'];
                }
                
                $end_time =$shiftType['end_time'];

                if($shiftTypeId!=$res['second_type_id']){
                    $name .= '/'.$shiftType['name'];
                }
                
                $shiftTypeId = $res['second_type_id'];
                if(empty($duty_factor)){
                    $duty_factor = $shiftType['duty_factor'];
                }
                
            }
            if($res['third_type_id']){
                $shiftType = $AttendanceService->getShiftTypeById($res['third_type_id']);

                if(empty($start_time)){
                    $start_time =$shiftType['time_start_third'];
                }
                
                $end_time =$shiftType['time_end_third'];
                if($shiftTypeId!=$res['third_type_id']){
                    $name .= '/'.$shiftType['name'];
                }
                
                $shiftTypeId = $res['second_type_id'];
                if(empty($duty_factor)){
                    $duty_factor = $shiftType['duty_factor'];
                }
                
            }


            $list['workName'] = trim($name,'/');
            $list['duty_factor'] = $duty_factor?$duty_factor:1;
            $list['empSubunit']  = $subunit_name?$subunit_name:'';
            $list['empPicture']  =$imgurl?$imgurl:'';
            $workShiftId = $res['id'];
            $list['workShiftId'] = $workShiftId;
            $this->serializer['status'] = true;
            $this->serializer['errno'] = 0;
            $this->serializer['message'] = '';
            return $list;


        }else{
            $this->serializer['status'] = false;
            $this->serializer['errno'] = 0;
            $this->serializer['message'] = '今天没有班次';
            return false;

        }

    }

    /**
     * @SWG\Post(path="/weixin/get-work-content",
     *     tags={"云平台-WEIXIN-微信接口"},
     *     summary="获取工作名称列表接口",
     *     description="用户登录：成功返回用户信息；失败返回具体原因",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "Token",
     *        description = "60f5d74b625b79aafe22808f8bbddec4907f8204",
     *        required = true,
     *        type = "string"
     *     ),

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
    public function actionGetWorkContent(){

        $empNumber = $this->empNumber; //用户id
        $workStation = $this->workStation;
        $work_content_list = array();
        $WorkLoadService = new WorkContent();
        if(!empty($workStation)){

            $work_content_list = $WorkLoadService->getWorkContentList($workStation);  //工作名称
        
        }
        
        
        
            $list = array();
            $list[] = array('key'=>0,'val'=>'其他');

            foreach($work_content_list as $k=>$v){
                $arr =array();
                $arr=array('key'=>$k,'val'=>$v);
                $list[] = $arr;
            }

        

            $this->serializer['status'] = true;
            $this->serializer['errno'] = 0;
            $this->serializer['message'] = '';
            return array('data'=>$list);

        

    }

    /**
     * @SWG\Post(path="/weixin/get-work-weight",
     *     tags={"云平台-WEIXIN-微信接口"},
     *     summary="获取工作量界面工作系数",
     *     description="用户登录：成功返回用户信息；失败返回具体原因",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "Token",
     *        description = "60f5d74b625b79aafe22808f8bbddec4907f8204",
     *        required = true,
     *        type = "string"
     *     ),

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
    public function actionGetWorkWeight(){

        $empNumber = $this->empNumber; //用户id
        $workStation = $this->workStation;
        $work_content_list = array();
        $WorkLoadService = new WorkContent();
        $empNumber = $this->empNumber; //用户id
        
        $list[0]['key'] = 1;
        $list[0]['val'] = 0.9;

        $list[1]['key'] = 2;
        $list[1]['val'] = 1;

        $list[2]['key'] = 3;
        $list[2]['val'] = 1.3;

        $this->serializer['status'] = true;
        $this->serializer['errno'] = 0;
        $this->serializer['message'] = '';
        return array('data'=>$list);

        

    }

    /**
     * @SWG\Post(path="/weixin/get-payrolln-date",
     *     tags={"云平台-WEIXIN-微信接口"},
     *     summary="获取工资条查询日期",
     *     description="用户登录：成功返回用户信息；失败返回具体原因",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "Token",
     *        description = "60f5d74b625b79aafe22808f8bbddec4907f8204",
     *        required = true,
     *        type = "string"
     *     ),

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
    public function actionGetPayrollnDate(){

        $empNumber = $this->empNumber; //用户id
        $workStation = $this->workStation;
        $customerId = $this->customerId;

        $BonusCalculationList = new BonusCalculationList();

        $list = $BonusCalculationList->getBonusCalculationListDateByEmp($empNumber,$customerId,1);
        $backArr = array();
        if($list){
            foreach ($list as $key => $value) {
                $arr = array('title'=>date('Y年m月',strtotime($value['bonusDate'])),'label'=>$value['bonusDate']);

                $backArr[] = $arr;
            }
        }else{

        }

        
        $this->serializer['status'] = true;
        $this->serializer['errno'] = 0;
        $this->serializer['message'] = '';
        return $backArr;

        

    }

    public function actionDeleteAtteById(){

        $id =Yii::$app->request->post('id');
        if(empty($id)){
            $this->serializer['status'] = false;
            return false;
        }
        $AttendanceRecord = new AttendanceRecord();
        $AttendanceRecord->deleteAtteById($id);
        $this->serializer['status'] = true;
        $this->serializer['errno'] = 0;
        $this->serializer['message'] = '';
        return true;

        

    }

    /**
     * @SWG\Post(path="/weixin/apply-overtime",
     *     tags={"云平台-WEIXIN-微信接口"},
     *     summary="加班申请",
     *     description="用户登录：成功返回用户信息；失败返回具体原因",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "Token",
     *        description = "60f5d74b625b79aafe22808f8bbddec4907f8204",
     *        required = true,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "startTime",
     *        description = "加班开始时间 2018-10-27 09:00",
     *        required = true,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "endTime",
     *        description = "结束时间 2018-10-27 12:00",
     *        required = true,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "note",
     *        description = "加班备注",
     *        required = true,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "is_holiday",
     *        description = "是否转调休 1是",
     *        required = false,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "exaId",
     *        description = "证明人id 多个以逗号隔开",
     *        required = false,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "witness",
     *        description = "证明人姓名 多个以逗号隔开",
     *        required = false,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "proId",
     *        description = "审批人id 多个以逗号隔开",
     *        required = true,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "chaoId",
     *        description = "抄送人id 多个以逗号隔开",
     *        required = false,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "chaoName",
     *        description = "抄送人姓名 多个以逗号隔开",
     *        required = false,
     *        type = "string"
     *     ),

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
    public function actionApplyOvertime(){

        $startTime = Yii::$app->request->post('startTime');   //加班日期
        $endTime = Yii::$app->request->post('endTime');   //加班日期
        $exaId = Yii::$app->request->post('exaId');   //证明人ID
        $proId = Yii::$app->request->post('proId');   //审批热ID
        $chaoId = Yii::$app->request->post('chaoId');   //抄送人ID
        $chaoName = Yii::$app->request->post('chaoName');   //抄送人名
        $note = Yii::$app->request->post('note');   //加班备注
        $is_holiday = Yii::$app->request->post('is_holiday');   //转倒休
        $witness = Yii::$app->request->post('witness');   //转倒休

        $pro_arr = explode(',',$proId);
 
        $chao_arr = explode(',',$chaoId);

        $empNumber = $this->empNumber; //用户id

        if(empty($empNumber)){
            $this->serializer['status'] = false;
            $this->serializer['errno'] = 0;
            $this->serializer['message'] = '管理员不用申请加班';
            return false;
        }

        $statmin = date('H:i',strtotime($startTime));
        $endmin  = date('H:i',strtotime($endTime));

        $statday = date('Y-m-d',strtotime($startTime));
        $endday = date('Y-m-d',strtotime($endTime));

        $date=floor((strtotime($endTime)-strtotime($startTime))/86400);
        $hour=floor((strtotime($endTime)-strtotime($startTime))/3600);

        if($statday==$endday){
            if($statmin>=$endmin){
                $this->serializer['status'] = false;
                $this->serializer['errno'] = 0;
                $this->serializer['message'] = '开始时间大于结束时间';
                return false;
            }
            

            if($hour<8){
                $time_differ = sprintf("%.2f",$hour/8);
                $hour_differ =$hour;
            }else{
                $time_differ = sprintf("%.2f",$hour/24);
                $hour_differ =$hour;
            }


        }else if($statday<$endday){    //加班多余1天的  
            if($date==1){      //隔天
                if($statmin<$endmin){
                    if($hour<8){
                        $time_differ = sprintf("%.2f",$hour/8);
                        $hour_differ =$hour;
                    }else{
                        $time_differ = sprintf("%.2f",$hour/24);
                        $hour_differ =$hour;
                    }
                }else if($statmin==$endmin){
                    $hour_differ = 1;
                    $time_differ = 1;
                }else{
                    $hurs1 = $hour-24;
                    if($hurs1<8){
                        $differ1 = sprintf("%.2f",$hour1/8);
                    }else{
                        $differ1 = sprintf("%.2f",$hour1/24);
                    }
                    $hour_differ =$hour;
                    $time_differ = 1+$differ1;
                }
            }else{    //大于一天的
                if($statmin<$endmin){
                    if(($hour%24)<8){
                        $time_differ = sprintf("%.2f",($hour%24)/8)-1;
                        $hour_differ =$hour;
                    }else{
                        $time_differ = sprintf("%.2f",($hour%24)/24)-1;
                        $hour_differ =$hour;
                    }

                }else if($statmin==$endmin){
                    $hour_differ = $hour;
                    $time_differ = $date;
                }else{
                    $hurs1 = $hour-24;
                    if(($hour%24)<8){
                        $differ1 = sprintf("%.2f",($hour%24)/8);
                    }else{
                        $differ1 = sprintf("%.2f",($hour%24)/24);
                    }
                    $hour_differ =$hour;
                    $time_differ = $date+$differ1;
                }
            }

        }else{
            $this->serializer['status'] = false;
            $this->serializer['errno'] = 0;
            $this->serializer['message'] = '开始时间大于结束时间';
            return false;

        }

        $applyOvertime = new Overtime();

        $applyOvertime->emp_number = $this->empNumber ;  //员工id
        $applyOvertime->creat_time = date('Y-m-d H:i:s');
        $applyOvertime->current_day = $statday;
        $applyOvertime->end_day = $endday;
        if($is_holiday==1){
            $applyOvertime->is_holiday = $is_holiday;
        }

        $applyOvertime->stat_time = $statmin;
        $applyOvertime->end_time = $endmin;
        $applyOvertime->content = $note;
        $applyOvertime->hour_differ = $hour_differ;
        $applyOvertime->time_differ = $time_differ;   
        $applyOvertime->status = 1;    //1等待批准
        $applyOvertime->is_pro = 1;    //1等待批准
        $applyOvertime->operation_name = $this->firstName;
        $applyOvertime->save();
        
        $overId = $applyOvertime->id;

        if($overId){
            if($note){
                $overtime_comment = new OvertimeComment();
                $overtime_comment->overtime_id = $overId;  
                $overtime_comment->created = date('Y-m-d H:i:s');

                if($this->empNumber){
 
                    $overtime_comment->created_by_name=$this->firstName;
                }else{
                    $overtime_comment->created_by_name='管理员';
                }
                
                $overtime_comment->created_by_id =  $this->userId;
                $overtime_comment->created_by_emp_number = $this->empNumber;
                $overtime_comment->comments = $note;

                $a = $overtime_comment->save();
            }

            $ApproverTab = new ApproverTab();
            $AppSys = new AppSys();
            $TabArr = $ApproverTab->saveApproverTabRecod($pro_arr,$this->empNumber,$overId,$exaId,2,1,$chaoName,$chaoId);
            $a = array();
            foreach($TabArr as $v){
                $param = array();
                $param['type'] = 1;
                $param['approver'] = $v['supId'];
                $param['sendId'] = $this->empNumber;
                $param['firsteHead'] = '您好，您有一条新审核提醒';
                $param['keyword2'] = date('Y-m-d H:i:s');
                $param['keyword3'] = '加班申请';
                //$param['footer'] = '点击查看';
                $param['footer'] = '';
                $param['url'] = 'my-approval';
                $a[] =$AppSys->sendWeiXinNotice($param);
            }

            foreach($chao_arr as $v){

                $param = array();
                $param['type'] = 1;
                $param['approver'] = $v;
                $param['sendId'] = $this->empNumber;
                $param['firsteHead'] = '您好，您收到一条加班申请抄送';
                $param['keyword2'] = date('Y-m-d H:i:s');
                $param['keyword3'] = '加班申请';
                //$param['footer'] = '点击查看';
                $param['footer'] = '';
                $param['url'] = 'news';
                $a[] =$AppSys->sendWeiXinNotice($param);
            }

            $this->serializer['status'] = true;
            $this->serializer['errno'] = 0;
            $this->serializer['message'] = '申请成功';   
            return $a;
        }else{
            $this->serializer['status'] = false;
            $this->serializer['errno'] = 0;
            $this->serializer['message'] = '申请失败';   
            return false;
        }


        

    }
    


    
}

