<?php

namespace frontend\controllers\v1;

/**
 *  直接访问的接口不用token验证
* 用户管理 用户列表
*/
use yii;
use yii\web\Response;

use yii\captcha\CaptchaAction;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\rest\OptionsAction;

//use frontend\models\gedu\resources\LoginForm;
//use frontend\models\gedu\resources\UserForm;
// use frontend\models\gedu\resources\User;
// use frontend\models\gedu\resources\UsersToUsers;
// use frontend\modules\user\models\SignupSmsForm;
use common\models\employee\Employee;
use common\models\pim\EmpPicture;
use common\models\system\SystemUsers;
use common\models\system\MemberToken;
use common\models\subunit\Subunit;
use common\models\user\User;

use common\models\attendance\AttendanceRecord;
use common\models\attendance\AttendanceRecordDetail;
use common\models\leave\LeaveEntitlement;
use common\models\system\UniqueId;
use common\models\attendance\ApproverTab;
use common\models\leave\LeaveRequest;
use common\models\overtime\Overtime;
use common\models\shift\ShiftChangeApply;
use common\models\system\LatitudeLongitude;
use common\models\attendance\WorkLoad;

use common\models\shift\RotationResultTemp;
use common\models\system\UserLoginLog;



//use common\components\Qiniu\Auth;
//use common\components\Qiniu\Storage\BucketManager;

use cheatsheet\Time;

class DirectVisitController extends \common\rest\SysController
{
    //public $modelClass = 'common\models\SystemUsers';

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
     * @SWG\Post(path="/direct-visit/shaky-punch",
     *     tags={"云平台-DirectVisit-直接访问接口"},
     *     summary="微信摇摇签到打卡",
     *     description="微信摇摇签到打卡",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "customer_id",
     *        description = "用户标识",
     *        required = false,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "open_id",
     *        description = "用户标识",
     *        required = false,
     *        type = "string"
     *     ),
     *     
     *     @SWG\Response(
     *         response = 200,
     *         description = ""
     *     ),
     *     @SWG\Response(
     *         response = 403,
     *         description = "",
     *     )
     * )
     *
     */
    public function actionShakyPunch(){
        //微信摇摇验证customerid 和 openID 值返回true 和false 不 进行打卡操作
        // errno = 8 已绑定 没班次  errno=9 没绑定  此状态 不能随便改动
        $params = Yii::$app->params;
        //测试
        $post=Yii::$app->request->post(); 

       
        $customer_id=$post['customer_id'];
        $open_id=$post['open_id']; 
        
        $templateId = @$params['templateId'][$customer_id];

        if(empty($customer_id)||empty($open_id)){
            $this->serializer['status'] = false;
            $this->serializer['errno'] = 2;
            $this->serializer['message'] = '参数错误';
            return false;
        }

        

        $templateId = $templateId['default'];
        $search['customer_id'] = $customer_id;
        $search['open_id'] = $open_id;

        $User = new User();
        $user = $User->getSystemUsersBySearch($search);

        if($user){
            if($user['id']==1){
                $this->serializer['status'] = false;
                $this->serializer['errno'] = 8;
                $this->serializer['message'] = '管理员不用打卡';
                return false;
            }

            $empNumber = $user['emp_number'];
            $date = date('Y-m-d');
            $Atte = new AttendanceRecord(); 
            $workTime = $Atte->getWorkShiftByDate($empNumber,$date);

            if($workTime){
                return true;
            }else{
                $this->serializer['status'] = false;
                $this->serializer['errno'] = 8;
                $this->serializer['message'] = '今天没有班次';
                return false;
            }


            

        }else{
            $this->serializer['status'] = false;
            $this->serializer['errno'] = 9;
            $this->serializer['message'] = '找不到此用户';
            return false;
        }
       
        return false;
    }

    /**
     * @SWG\Post(path="/direct-visit/get-image",
     *     tags={"云平台-DirectVisit-直接访问接口"},
     *     summary="获取首页登录页图片",
     *     description="获取首页登录页图片",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "customer_id",
     *        description = "用户标识",
     *        required = false,
     *        type = "string"
     *     ),
     *     
     *     @SWG\Response(
     *         response = 200,
     *         description = ""
     *     ),
     *     @SWG\Response(
     *         response = 403,
     *         description = "",
     *     )
     * )
     *
     */
    public function actionGetImage(){
        $params = Yii::$app->params;

        $post=Yii::$app->request->post(); 
        $customer_id= @$post['customer_id'];


        if($customer_id){
            $img = @$params['kehuImage'][$customer_id];
            if(empty($img)){
                $img = @$params['kehuImage']['default'];
            }
        }else{
            $img = @$params['kehuImage']['default'];
        }

        return $img;

    }
    

    /**
     * @SWG\Post(path="/direct-visit/punch-detai",
     *     tags={"云平台-DirectVisit-直接访问接口"},
     *     summary="获取打卡详情",
     *     description="获取打卡详情",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "customer_id",
     *        description = "用户标识",
     *        required = false,
     *        type = "string"
     *     ),
     *      @SWG\Parameter(
     *        in = "formData",
     *        name = "openId",
     *        description = "用户标识",
     *        required = false,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "isWe",
     *        description = "0摇一摇打卡进 ,1微信端进 ",
     *        required = false,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "Token",
     *        description = "token 当isWe=1是传",
     *        required = false,
     *        type = "string"
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = ""
     *     ),
     *     @SWG\Response(
     *         response = 403,
     *         description = "",
     *     )
     * )
     *
     */
    public function actionPunchDetai(){

        $params = Yii::$app->params;
        $isWe=Yii::$app->request->post('isWe'); 
        $openId=Yii::$app->request->post('openId'); 

        if($isWe){
            $Tokens=Yii::$app->request->post('Token'); 
            if(null== $Tokens){
                $Tokens=Yii::$app->request->get('Token');
            }
            $Token = @$_SERVER['HTTP_AUTHORIZATION'];   
            if(empty($Token)){
                if(empty($Tokens)){
                    $this->serializer['status'] = false;
                    $this->serializer['errno'] = 401;
                    $this->serializer['message'] = 'Token不能为空';
                    return false;

                }else{
                    $Token = $Tokens;
                }
                
            }else{
                $Tstring = explode('token', $Token);
                if(is_base64($Tstring[1])){
                    $Token_arr = base64_decode(base64_decode($Tstring[1]));
                    $TokenArray = explode(':', $Token_arr);
                    $Token  = trim($TokenArray[2]);
                }else{
                    $Token = trim($Tstring[1]);
                }  
            }
            $MemberToken = new MemberToken();
            $detail= $MemberToken->getTokenByToken($Token);
            if(empty($detail)){
                $this->serializer['status'] = false;
                $this->serializer['errno'] =401;
                $this->serializer['message'] = 'Token不正确';
                return false;

            }else{  
                 $userId = $detail['userid'];
                 $SystemUser = new SystemUsers();
                 $user = $SystemUser->searchSystemUsersById($userId);
            }
        }else{
            if(empty($openId)){
                $this->serializer['status'] = false;
                $this->serializer['errno'] = 0;
                $this->serializer['message'] = '微信标识不能为空';
                return false;
            }
             $customer_id=Yii::$app->request->post('customer_id');

            if(empty($customer_id)){
                $this->serializer['status'] = false;
                $this->serializer['errno'] = 0;
                $this->serializer['message'] = 'customer_id不能为空';
                return false;
            }


            $User = new User();
            $search['open_id'] = $openId;
            $search['customer_id'] = $customer_id;
            $user = $User->getSystemUsersBySearch($search);
        }
        if(!$user){
            $this->serializer['status'] = false;
            $this->serializer['errno'] = 2;
            $this->serializer['message'] = '找不到此用户';
            return false;
            return false;
        }
        $userId = $user['id'];
        if($userId==1){
            $this->serializer['status'] = false;
            $this->serializer['errno'] = 2;
            $this->serializer['message'] = '管理员不用打卡';
            return false;
        }

        $empNumber = $user['emp_number'];

        $workStation = $user['employee']['work_station'];

        $date = date('Y-m-d');
        $params = Yii::$app->params;
        $work_time_late = $params['work_time_late']*60;  //准许迟到早退的时间
        $Atte = new AttendanceRecord(); 
        $record  = $Atte->getAttendanceRecord($empNumber,$date);

        $dakaArr = array();
        if($record){   //已有了打卡 记录
            $RecordId = $record['id'];

            $punchInUserTime = $record['punch_in_user_time'];
            $punchInUserTimeAfter = $record['end_time_afternoon'];
            $punchOutUserTimeAfter = $record['start_time_afternoon'];
            $punchOutUserTime = $record['punch_out_user_time'];

            $punchThirdStart = $record['start_time_third'];
            $punchThirdEnd = $record['end_time_third'];

            if($punchInUserTime){
                $punchInUserTime = date('m-d H:i',strtotime($punchInUserTime));
            }
            if($punchInUserTimeAfter){
                $punchInUserTimeAfter = date('m-d H:i',strtotime($punchInUserTimeAfter));
            }
            if($punchOutUserTimeAfter){
                $punchOutUserTimeAfter = date('m-d H:i',strtotime($punchOutUserTimeAfter));
            }
            if($punchOutUserTime){
                $punchOutUserTime = date('m-d H:i',strtotime($punchOutUserTime));
            }

            if($punchThirdStart){
                $punchThirdStart = date('m-d H:i',strtotime($punchThirdStart));
            }
            if($punchThirdEnd){
                $punchThirdEnd = date('m-d H:i',strtotime($punchThirdEnd));
            }

        }else{
            $RecordId = 0;
            $punchInUserTime = '';
            $punchOutUserTimeAfter = '';
            $punchInUserTimeAfter = '';
            $punchOutUserTime = '';
            $punchThirdStart = '';
            $punchThirdEnd = '';
        }

        $statShowDaka =$punchInUserTime?false:true;    
        $middEndShowDaka =$punchInUserTimeAfter?false:true;
        $middStatShowDaka =$punchOutUserTimeAfter?false:true;
        $endShowDaka =$punchOutUserTime?false:true;

        $thirdStatShowDaka =$punchThirdStart?false:true;
        $thirdEndShowDaka =$punchThirdEnd?false:true;

        $workTime = $Atte->getWorkShiftByDate($empNumber,$date);


        if($workTime){
            if($workTime['isWork']){
                $firstWorkTime = $workTime['work_date'];
                $workName = $workTime['work_name'];
                $workShiftId = $workTime['shiftId'];
                $is_daka_half = $workTime['is_daka_half'];
                $is_amont_work = $workTime['is_amont_work'];
                $remark = $workTime['remark'];

                $start_time = null;

                if(!empty($workTime['work_start_time'])&&$workTime['work_start_time']!='00:00'){

                    $dakaArr[] = array('sign'=>'上班','workName'=>$workName,'WorkTime'=>$workTime['work_start_time'],'showDaka'=>$statShowDaka,'dakaTime'=>$punchInUserTime,'workShiftId'=>$workShiftId,'RecordId'=>$RecordId,'istrue'=>1,'isz'=>true);

                    // if($workTime['work_end_time']&&!empty($workTime['is_daka_half'])){
                    //     $dakaArr[] = array('sign'=>'下班','workName'=>$workName,'WorkTime'=>$workTime['work_middend_time'],'showDaka'=>$middEndShowDaka,'dakaTime'=>$punchInUserTimeAfter,'workShiftId'=>$workShiftId,'RecordId'=>$RecordId,'istrue'=>2,'isz'=>true);
                    // }
                    if(empty($workTime['work_end_time'])){
                        $dakaArr[] = array('sign'=>'下班','workName'=>$workName,'WorkTime'=>$workTime['work_middend_time'],'showDaka'=>$middEndShowDaka,'dakaTime'=>$punchInUserTimeAfter,'workShiftId'=>$workShiftId,'RecordId'=>$RecordId,'istrue'=>2,'isz'=>true);
                    }else{
                        if(!empty($workTime['is_daka_half'])){
                            $dakaArr[] = array('sign'=>'下班','workName'=>$workName,'WorkTime'=>$workTime['work_middend_time'],'showDaka'=>$middEndShowDaka,'dakaTime'=>$punchInUserTimeAfter,'workShiftId'=>$workShiftId,'RecordId'=>$RecordId,'istrue'=>2,'isz'=>true);
                        }
                    }
                    

                    $start_time = $workTime['work_start_time'];
                }

                if(!empty($workTime['work_middstart_time'])&&$workTime['work_middstart_time']!='00:00'){

                    if($workTime['work_end_time']&&!empty($workTime['is_daka_half'])){
                        $dakaArr[] = array('sign'=>'上班','workName'=>$workName,'WorkTime'=>$workTime['work_middstart_time'],'showDaka'=>$middStatShowDaka,'dakaTime'=>$punchOutUserTimeAfter,'workShiftId'=>$workShiftId,'RecordId'=>$RecordId,'istrue'=>3,'isz'=>true);
                    }

                    
                    $dakaArr[] = array('sign'=>'下班','workName'=>$workName,'WorkTime'=>$workTime['work_end_time'],'showDaka'=>$endShowDaka,'dakaTime'=>$punchOutUserTime,'workShiftId'=>$workShiftId,'RecordId'=>$RecordId,'istrue'=>4,'isz'=>true);
                    if(empty($start_time)){
                        $start_time = $workTime['work_middstart_time'];
                    }
                }

                if(!empty($workTime['work_thirdstart_time'])&&$workTime['work_thirdstart_time']!='00:00'){

                    $dakaArr[] = array('sign'=>'上班','workName'=>$workName,'WorkTime'=>$workTime['work_thirdstart_time'],'showDaka'=>$thirdStatShowDaka,'dakaTime'=>$punchOutUserTimeAfter,'workShiftId'=>$workShiftId,'RecordId'=>$RecordId,'istrue'=>5,'isz'=>true);
                    $dakaArr[] = array('sign'=>'下班','workName'=>$workName,'WorkTime'=>$workTime['work_thirdend_time'],'showDaka'=>$thirdEndShowDaka,'dakaTime'=>$punchThirdEnd,'workShiftId'=>$workShiftId,'RecordId'=>$RecordId,'istrue'=>6,'isz'=>true);

                    if(empty($start_time)){
                        $start_time = $workTime['work_thirdstart_time'];
                    }
                }



                // $start_time = date('H:i',strtotime($workTime['work_date'].' '.$workTime['work_start_time']));

                // $end_time_afternoon = date('H:i',strtotime($workTime['work_date'].' '.$workTime['work_middend_time']));
                // $start_time_afternoon = date('H:i',strtotime($workTime['work_date'].' '.$workTime['work_middstart_time']));
                // $end_time = date('H:i',strtotime($workTime['work_date'].' '.$workTime['work_end_time']));

                // if($end_time=='00:00'||$end_time=='00:00:00'){
                //     $end_time = $end_time_afternoon;

                //     $end_time_afternoon = '00:00';
                // }

                // $dakaArr[] = array('sign'=>'上班','workName'=>$workName,'WorkTime'=>$start_time,'showDaka'=>$statShowDaka,'dakaTime'=>$punchInUserTime,'workShiftId'=>$workShiftId,'RecordId'=>$RecordId,'istrue'=>1,'isz'=>true);
                // if(($end_time_afternoon!='00:00:00'&&$end_time_afternoon!='00:00')&&$is_daka_half){
                //     $dakaArr[] = array('sign'=>'下班','workName'=>$workName,'WorkTime'=>$end_time_afternoon,'showDaka'=>$middEndShowDaka,'dakaTime'=>$punchInUserTimeAfter,'workShiftId'=>$workShiftId,'RecordId'=>$RecordId,'istrue'=>2,'isz'=>true);
                // }

                // if(($start_time_afternoon!='00:00:00'&&$start_time_afternoon!='00:00')&&$is_daka_half){
                //     $dakaArr[] = array('sign'=>'上班','workName'=>$workName,'WorkTime'=>$start_time_afternoon,'showDaka'=>$middStatShowDaka,'dakaTime'=>$punchOutUserTimeAfter,'workShiftId'=>$workShiftId,'RecordId'=>$RecordId,'istrue'=>3,'isz'=>true);
                // }


                // $dakaArr[] = array('sign'=>'下班','workName'=>$workName,'WorkTime'=>$end_time,'showDaka'=>$endShowDaka,'dakaTime'=>$punchOutUserTime,'workShiftId'=>$workShiftId,'RecordId'=>$RecordId,'istrue'=>4,'isz'=>true);
            }
            if($workTime['isJia']){
                $overAttend =$Atte->getAttendanceRecordByOver($empNumber,$date);
                if($overAttend){
                    $overRecordId = $overAttend['id'];

                    $overPunchInUserTime = $overAttend['punch_in_user_time'];

                    $overPunchOutUserTime = $overAttend['punch_out_user_time'];

                    if($overPunchInUserTime){
                        $overPunchInUserTime = date('m-d H:i',strtotime($overPunchInUserTime));
                    }
 
                    if($overPunchOutUserTime){
                        $overPunchOutUserTime = date('m-d H:i',strtotime($overPunchOutUserTime));
                    }
                }else{
                    $overRecordId = 0;
                    $overPunchInUserTime = '';
                    $overPunchOutUserTime = '';
                } 

                $overStatShowDaka =$overPunchInUserTime?false:true;    
                $overEndShowDaka =$overPunchOutUserTime?false:true;

                $overFirstWorkTime = $workTime['over_date'];
                $overName = $workTime['overName'];
                $overWorkShiftId = 0;

                $overStart_time = date('H:i',strtotime($workTime['over_date'].' '.$workTime['overStat_time']));

                $overEnd_time = date('H:i',strtotime($workTime['over_date'].' '.$workTime['overEnd_time']));


                $dakaArr[] = array('sign'=>'上班','workName'=>$overName,'WorkTime'=>$overStart_time,'showDaka'=>$overStatShowDaka,'dakaTime'=>$overPunchInUserTime,'workShiftId'=>$overWorkShiftId,'RecordId'=>$overRecordId,'istrue'=>7,'isz'=>true);
                $dakaArr[] = array('sign'=>'下班','workName'=>$overName,'WorkTime'=>$overEnd_time,'showDaka'=>$overEndShowDaka,'dakaTime'=>$overPunchOutUserTime,'workShiftId'=>$overWorkShiftId,'RecordId'=>$overRecordId,'istrue'=>8,'isz'=>true);
   
            }

        }else{
            $this->serializer['status'] = false;
            $this->serializer['errno'] = 2;
            $this->serializer['message'] = '今天没有班次';
            return false;
        }

        $EmpPicture = new EmpPicture();
        $picture = $EmpPicture->getEmpPictureByEmpNumber($empNumber);
        if($picture&&!empty($picture->epic_picture_url)){
            //$url='http://'.$_SERVER['HTTP_HOST'];
            $url= env('STORAGE_HOST_INFO');
            $LatitudeAndLongitude['empPicture'] = $url.trim($picture->epic_picture_url,'/');
        }else{
            $params = Yii::$app->params;
            $defaultHead = $params['defaultHead'];
            $LatitudeAndLongitude['empPicture'] = $defaultHead;
        }
        $LatitudeAndLongitude['empNumber'] = $empNumber;
        $LatitudeAndLongitude['late_time'] = $work_time_late;
        $LatitudeAndLongitude['date'] = date("Y年m月d日",time());

        $weekarray=array("星期日","星期一","星期二","星期三","星期四","星期五","星期六"); 
        $LatitudeAndLongitude['week'] = $weekarray[date("w")] ;
        $LatitudeAndLongitude['RecordId'] = $RecordId;
        $LatitudeAndLongitude['isAmontWork'] = $is_amont_work;
        $LatitudeAndLongitude['remark'] = $remark?$remark:'';
        $LatitudeAndLongitude['work_name'] = $workName;
        $LatitudeAndLongitude['firstWorkTime'] = $firstWorkTime.' '.$start_time;
        $dakaArr = arraySequence($dakaArr,'istrue','SORT_ASC');

       
        $LatitudeLongitude = new LatitudeLongitude();

        $lati =$LatitudeLongitude->getLatitudeLongitudeByWorkStation($workStation);
        if($lati){
            $LatitudeAndLongitude['latitude'] = $lati->latitude?$lati->latitude:0;
            $LatitudeAndLongitude['longitude'] = $lati->longitude?$lati->longitude:0;
            $LatitudeAndLongitude['punching_range'] = $lati->punching_range?$lati->punching_range:0;
        }else{
            $LatitudeAndLongitude['latitude'] = 0;
            $LatitudeAndLongitude['longitude'] = 0;
            $LatitudeAndLongitude['punching_range'] = 0;
        }

        return array('dakaArr'=>$dakaArr,'latitudeAndLongitude'=>$LatitudeAndLongitude);


    }

    /**
     * @SWG\Post(path="/direct-visit/punch-in",
     *     tags={"云平台-DirectVisit-直接访问接口"},
     *     summary="提交打卡信息",
     *     description="提交打卡信息",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "customer_id",
     *        description = "用户标识",
     *        required = false,
     *        type = "string"
     *     ),
     *      @SWG\Parameter(
     *        in = "formData",
     *        name = "openId",
     *        description = "用户标识",
     *        required = false,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "istrue",
     *        description = "打卡按钮标识",
     *        required = false,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "firstWorkTime",
     *        description = "firstWorkTime",
     *        required = false,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "recordId",
     *        description = "ID",
     *        required = false,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "workShiftId",
     *        description = "班次ID",
     *        required = false,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "isWe",
     *        description = "0摇一摇打卡进 ,1微信端进 ",
     *        required = false,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "Token",
     *        description = "token 当isWe=1是传",
     *        required = false,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "lat",
     *        description = "token 当isWe=1是传",
     *        required = false,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "lng",
     *        description = "token 当isWe=1是传",
     *        required = false,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "distance",
     *        description = "token 当isWe=1是传",
     *        required = false,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "strLocation",
     *        description = "token 当isWe=1是传",
     *        required = false,
     *        type = "string"
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = ""
     *     ),
     *     @SWG\Response(
     *         response = 403,
     *         description = "",
     *     )
     * )
     *
     */
    public function actionPunchIn(){


        $openId=Yii::$app->request->post('openId'); 

        $recordId = Yii::$app->request->post('recordId'); 
        $workShiftId = Yii::$app->request->post('workShiftId'); 
        $firstWorkTime = Yii::$app->request->post('firstWorkTime'); 
        $istrue = Yii::$app->request->post('istrue'); 
        ###########
        $isWe=Yii::$app->request->post('isWe'); 

        $lat=Yii::$app->request->post('lat');
        $lng=Yii::$app->request->post('lng');
        $strLocation=Yii::$app->request->post('strLocation');
        $distance=Yii::$app->request->post('distance');
        $workload=Yii::$app->request->post('workload');



        if($isWe){
            $Tokens=Yii::$app->request->post('Token'); 
            if(null== $Tokens){
                $Tokens=Yii::$app->request->get('Token');
            }
            $Token = @$_SERVER['HTTP_AUTHORIZATION'];   
            if(empty($Token)){
                if(empty($Tokens)){
                    $this->serializer['status'] = false;
                    $this->serializer['errno'] =401;
                    $this->serializer['message'] = 'Token不能为空';
                    return false;

                }else{
                    $Token = $Tokens;
                }
                
            }else{
                $Tstring = explode('token', $Token);
                if(is_base64($Tstring[1])){
                    $Token_arr = base64_decode(base64_decode($Tstring[1]));
                    $TokenArray = explode(':', $Token_arr);
                    $Token  = trim($TokenArray[2]);
                }else{
                    $Token = trim($Tstring[1]);
                }  
            }
            $MemberToken = new MemberToken();
            $detail= $MemberToken->getTokenByToken($Token);
            if(empty($detail)){
                $this->serializer['status'] = false;
                $this->serializer['errno'] =401;
                $this->serializer['message'] = 'Token不正确';
                return false;

            }else{  
                 $userId = $detail['userid'];
                 $SystemUser = new SystemUsers();
                 $user = $SystemUser->searchSystemUsersById($userId);
            }
        }else{
            if(empty($openId)){
                $this->serializer['status'] = false;
                $this->serializer['errno'] = 0;
                $this->serializer['message'] = '微信标识不能为空';
                return false;
            }
             $customer_id=Yii::$app->request->post('customer_id');

            // if(empty($customer_id)){
            //     $this->serializer['status'] = false;
            //     $this->serializer['errno'] = 0;
            //     $this->serializer['message'] = 'customer_id不能为空';
            //     return false;
            // }


            $User = new User();
            $search['open_id'] = $openId;
           // $search['customer_id'] = $customer_id;
            $user = $User->getSystemUsersBySearch($search);
        }


#############

        $Atte = new AttendanceRecord(); 
        if(!$user){
            $this->serializer['status'] = false;
            $this->serializer['errno'] = 2;
            $this->serializer['message'] = '找不到此用户';
            return false;
        }
        $params = Yii::$app->params;
        $work_time_late = $params['work_time_late']*60;
        $empNumber = $user['emp_number'];
        $userId = $user['id'];
        if($userId==1){
            $this->serializer['status'] = false;
            $this->serializer['errno'] = 2;
            $this->serializer['message'] = '管理员不用打卡';
            return false;
        }

        if($istrue>=7){
            $date = date('Y-m-d');
        }else{
            $date = date('Y-m-d',strtotime($firstWorkTime));
        }
        
        $nowDate = date('Y-m-d H:i:s');
        $nowTime = time();

        $workTime = $Atte->getWorkShiftByDate($empNumber,$date);

        if(empty($workTime)){
            $this->serializer['status'] = false;
            $this->serializer['errno'] = 2;
            $this->serializer['message'] = '今天没有班次';
            return false;
        }

        $work_start_time = $workTime['work_start_time'];
        $work_middend_time = $workTime['work_middend_time'];
        $work_middstart_time =$workTime['work_middstart_time'];
        $work_end_time = $workTime['work_end_time'];

        $work_thirdstart_time = $workTime['work_thirdstart_time'];
        $work_thirdend_time = $workTime['work_thirdend_time'];

        $wname = $workTime['work_name'];
        $workDate = $workTime['work_date'];
        $clock_in = $workTime['clock_in'];
        $work_shift_id = $workTime['shiftId'];
        $overName = $workTime['overName'];
        $overStat_time = $workTime['overStat_time'];
        $overEnd_time = $workTime['overEnd_time'];
        $is_daka_half = $workTime['is_daka_half'];


        if($recordId){
            $record = $Atte->getAttendanceRecordById($recordId);
            $work_start_time = $record['work_start_time'];
            $work_middend_time = $record['work_middend_time'];
            $work_middstart_time =$record['work_middstart_time'];
            $work_end_time = $record['work_end_time'];
            $work_thirdstart_time = $record['work_start_third'];
            $work_thirdend_time = $record['work_end_third'];

            $wname = $record['work_name'];
            $workDate = date('Y-m-d',strtotime($record['first_daka_time']));
            $clock_in = $record['clock_in'];
            $is_daka_half = $record['is_daka_half'];
        }else{
            $arr = array('empNumber'=>$empNumber,'date'=>$date);

            $record = new AttendanceRecord();
            $UniqueId = new UniqueId();

            $uniqId = $UniqueId->getTableIdByName('ohrm_attendance_record');
            $uid = $uniqId['last_id']+1;
            $uniqId->last_id = $uid;
            $uniqId->save();
            $record->id = $uid;
            $record->employee_id = $empNumber;
            $record->first_daka_time = $nowDate;
            $record->create_time = date('Y-m-d H:i:s');
            $record->state = 'PUNCHED IN';
            $record->work_shift_id = $work_shift_id;

            $record->work_start_time = $work_start_time;
            $record->work_middend_time = $work_middend_time;
            $record->work_middstart_time = $work_middstart_time;
            $record->work_end_time = $work_end_time;

            $record->work_start_third = $work_thirdstart_time;
            $record->work_end_third = $work_thirdend_time;

            $record->work_name = $wname;
            $record->clock_in = $clock_in;
            if($is_daka_half){
                $record->is_daka_half = $is_daka_half;
            }else{
                $record->is_daka_half = 0;
            }
            
            
            if($istrue>=7){
                $record->work_start_time = $overStat_time;
                $record->work_end_time = $overEnd_time;
                $record->work_middend_time = '';
                $record->work_middstart_time = '';
                $record->work_start_third = '';
                $record->work_end_third = '';
                $record->work_shift_id = 0;
                $record->work_name = $overName;
            }

            
        }

        $record->punch_count = $record->punch_count+1;
        $first_daka_time = $record->first_daka_time;
        if(empty($first_daka_time)){
            $record->first_daka_time = $nowDate;
        }
        
        if(empty($work_end_time)||$work_end_time=='00:00:00'||$work_end_time=='00:00'){
            $work_end_time = $work_middend_time;
        }

        if($istrue==1){
            if(empty($recordId)){
                $chaDate  = date('Y-m-d',strtotime($nowDate));
                $res = $Atte->getPunchRecordByPunchedIn($empNumber,$chaDate);
                if($res&&!empty($res->punch_in_user_time)){
                    $this->serializer['status'] = false;
                    $this->serializer['errno'] = 2;
                    $this->serializer['message'] = '您已经打过卡了';
                    return false;
                }
                $record->state = 'PUNCHED IN';
            }else{

                if(empty($workTime['work_middstart_time'])&&empty($workTime['work_thirdend_time'])){
                    $nextState = 'PUNCHED OUT';
                    $record->state  = $nextState;
                }

            }
            
            $record->punch_in_user_time = $nowDate;
            $record->punch_in_utc_time = date('Y-m-d H:i:s',strtotime('-8 hour',$nowTime));
            $record->punch_in_time_offset = '8';
            $record->punch_in_actual_time = $nowDate;
            //$record->state = 'PUNCHED IN';

            if(($nowTime - $work_time_late )> strtotime($workDate.' '.$work_start_time)){
                $record->late_count = $record->late_count + 1;
            }
            $record->save();
        }else if($istrue==2){
            if($recordId){
                if(empty($workTime['work_middstart_time'])&&empty($workTime['work_thirdend_time'])){
                    $nextState = 'PUNCHED OUT';
                    $record->state  = $nextState;
                }
            }

            $record->end_time_afternoon = $nowDate;
            if($workTime['is_daka_half']){
                if($workTime['clock_in']){
                    $banTime = strtotime($workTime['work_date'].' '.$record->work_middend_time);
                   
                    if(($nowTime + $work_time_late) < strtotime($workDate.' '.$work_start_time)){
                        $record->retreat_count =$record->retreat_count + 1;
                    }
                }

            }
            $record->save();
        }else if($istrue==3){
            if($recordId){
                if(!empty($record->punch_out_user_time)&&empty($workTime['work_start_time'])&&empty($workTime['work_thirdend_time'])){
                    $nextState = 'PUNCHED OUT';
                    $record->state  = $nextState;
                }
            }
            $record->work_middstart_time = $work_middstart_time;
            $record->work_end_time = $work_end_time;


            $record->start_time_afternoon= $nowDate;

            if(($nowTime -$work_time_late ) > strtotime($workDate.' '.$work_middstart_time)){
                $record->late_count = $record->late_count + 1;
            }
            $record->save();
        }else if($istrue==4){
            
            if($recordId){
                if(empty($workTime['work_start_time'])&&empty($workTime['work_thirdend_time'])){
                    $nextState = 'PUNCHED OUT';
                    $record->state  = $nextState;
                }
                if(empty($workTime['work_thirdend_time'])){
                    $nextState = 'PUNCHED OUT';
                    $record->state  = $nextState;
                }
                
            }
            

            $record->punch_out_user_time = $nowDate;
            $record->punch_out_utc_time = date('Y-m-d H:i:s',strtotime('-8 hour',$nowTime));
            $record->punch_out_time_offset = '8';
            $record->punch_out_actual_time = $nowDate;

            $record->daka_status = 2;
            if(!$clock_in){
                $record->daka_status = 0;
            }


            // if($workTime['is_daka_half']){
                if($workTime['clock_in']){                
                    if(($nowTime + $work_time_late ) < strtotime($workDate.' '.$work_end_time)){
                        $record->retreat_count =$record->retreat_count + 1;
                    }
                }

           // }
            $record->save();

        }else if($istrue==5){
            if($recordId){
                if(!empty($record->start_time_third)){
                    $nextState = 'PUNCHED OUT';
                    $record->state  = $nextState;
                }
                if(empty($workTime['work_start_time'])&&empty($workTime['work_middstart_time'])){
                    $nextState = 'PUNCHED OUT';
                    $record->state  = $nextState;
                }    
            }

            $record->start_time_third = $nowDate;
            
            
           
            if($workTime['clock_in']){                
                if(($nowTime + $work_time_late ) < strtotime($workDate.' '.$work_thirdstart_time)){
                    $record->late_count =$record->late_count + 1;
                }
            }

            
            $record->save();

        }else if($istrue==6){      
            if($recordId){
                if(empty($workTime['work_start_time'])&&empty($workTime['work_middstart_time'])){
                    $nextState = 'PUNCHED OUT';
                    $record->state  = $nextState;
                }
                if(!empty($record->start_time_third)){
                    $nextState = 'PUNCHED OUT';
                    $record->state  = $nextState;
                }
                
                
                
            }


            $record->end_time_third = $nowDate;
            
            if(!$clock_in){
                $record->daka_status = 0;
            }
            
            if($workTime['clock_in']){                
                if(($nowTime + $work_time_late ) < strtotime($workDate.' '.$work_thirdend_time)){
                    $record->retreat_count =$record->retreat_count +1 ;
                }
            }

            
            $record->save();

        }else if($istrue==7){
            
            $record->state = 'PUNCHED IN';
            $record->punch_in_user_time = $nowDate;
            $record->punch_in_utc_time = date('Y-m-d H:i:s',strtotime('-8 hour',$nowTime));
            $record->punch_in_time_offset = '8';
            $record->punch_in_actual_time = $nowDate;
            $record->state = 'PUNCHED IN';

            // if(($nowTime - $work_time_late )> strtotime($workDate.' '.$work_start_time)){
            //     $record->daka_status = 1;
            // }else{
            //     if($clock_in){
            //         $record->daka_status = 3;
            //     }else{
            //         $record->daka_status = 0;
            //     }
            // }
            $record->daka_status = 0;
            $record->is_in_status = -1;
            $record->save();
        }else if($istrue==8){
            $nextState = 'PUNCHED OUT';
            $record->state  = $nextState;

            $record->punch_out_user_time = $nowDate;
            $record->punch_out_utc_time = date('Y-m-d H:i:s',strtotime('-8 hour',$nowTime));
            $record->punch_out_time_offset = '8';
            $record->punch_out_actual_time = $nowDate;

            $record->daka_status = 0;
            
            $record->save();

        }else{
            $this->serializer['status'] = false;
            $this->serializer['errno'] = 2;
            $this->serializer['message'] = '参数错误';
            return false;
        }
        if($record->id){

            if(!empty($workload)){  //插入工作量
                foreach($workload as $k=>$v){
                    $WorkLoad = new WorkLoad();
                    $WorkLoad->employee_id = $empNumber;
                    $WorkLoad->workload = $v['work'];

                    //$WorkLoad->workcontent_id = $v['workcontent'];
                    $WorkLoad->work_weight = $v['work'];
                    $WorkLoad->workshift_id = $workShiftId;
                    $WorkLoad->work_date = date('Y-m-d');
                    $WorkLoad->create_time =  date('Y-m-d H:i:s');
                    $WorkLoad->save();
                }
            }

            if($isWe){
                //成功记录打卡位置
                if($lat){
                    $AttendanceDetail = new AttendanceRecordDetail();
                    $AttendanceDetail->record_id = $record->id;
                    $AttendanceDetail->emp_number = $empNumber;
                    $AttendanceDetail->lat = $lat;
                    $AttendanceDetail->lng = $lng;
                    $AttendanceDetail->distance = $distance;
                    $AttendanceDetail->address = $strLocation;
                    $AttendanceDetail->create_time = date('Y-m-d H:i:s');
                    $AttendanceDetail->status = $istrue;
                    $AttendanceDetail->save();
                }
            }
            $lastdate ='' ;
            $lastid = 0 ;
            $message = '打卡成功';

            if($isWe){   //计算漏打卡
                $w = date('w',strtotime($firstWorkTime));
                if($w==0){
                     $date=date('Y-m-d',strtotime($firstWorkTime.' -2 days'));
                }else if($w==1){
                     $date=date('Y-m-d',strtotime($firstWorkTime.' -3 days'));
                }else{
                    $date=date('Y-m-d',strtotime($firstWorkTime.' -1 days')); 
                }

                $workTime = $Atte->getWorkShiftByDate($empNumber,$date);

                if($workTime&&$workTime['isWork']){
                    $lastWorkDate = $workTime['work_date'];
                    $nextState = 'PUNCHED IN';

                    $res = $Atte->getPunchRecordByPunchedIn($empNumber,$date);
                    if($res){
                        if($res->state=='PUNCHED IN'){
                            $lastdate = date('Y-m-d',strtotime($res->first_daka_time));
                            $lastid = $res->id;
                            $message ='打卡成功,你在'.$lastdate.'有未完成的打卡记录是否跳到漏打卡页面?';
                        }
                    }else{
                        $isK = $Atte->getAttendanceRecordByWB($empNumber, $date,2);
                        if(!$isK){
                            $lastdate = $date;
                            $message ='打卡成功,你在'.$lastdate.'有未完成的打卡记录是否跳到漏打卡页面?';
                        }
                        
                    }
                }
            }


            $this->serializer['status'] = true;
            $this->serializer['errno'] = 0;
            $this->serializer['message'] = $message;
            return array('lastDate'=>$lastdate,'lastid'=>$lastid);
        }
        
    }
    /**
     * 获取远程图片放到本服务器中 
     * @return [type] [description]
     */
    public function actionGetHeadPic()
    {

        $params = Yii::$app->params;
        
        //测试
        $post = Yii::$app->request->post(); 


        //var_dump($post);die;

        $url = $post['img'];
        $newName = $post['newName'];

        //$url = "http://www.baidu.com/img/baidu_jgylogo3.gif";
        $save_dir = "/data/wwwroot/uploadfile/public/head_pic";
        $filename = $newName;
        $res = getFileDown($url, $save_dir, $filename, 1);
        return $res;
        var_dump($res);       

    }


    /**
     * @SWG\Get(path="/direct-visit/unbind-token",
     *     tags={"云平台-DirectVisit-直接访问接口"},
     *     summary="解除用户token和openID",
     *     description="提交打卡信息",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "query",
     *        name = "user_name",
     *        description = "工资号",
     *        required = false,
     *        type = "string"
     *     ), 
     *     @SWG\Parameter(
     *        in = "query",
     *        name = "work_station",
     *        description = "组id",
     *        required = false,
     *        type = "string"
     *     ), 
     *     @SWG\Response(
     *         response = 200,
     *         description = ""
     *     ),
     *     @SWG\Response(
     *         response = 403,
     *         description = "",
     *     )
     * )
     *
     */
    public function actionUnbindToken(){
        $userName=Yii::$app->request->get('user_name');

        $workStation=Yii::$app->request->get('work_station');

        if($workStation){
            $search['subunit'] = $workStation;  
        }else{
            $search = array();
        }

        if($userName){
            $SystemUsers = new User();
            $recod = $SystemUsers->getSystemUsersByUserName($userName);
            if($recod){
                $recod->open_id = null;
                $recod->bind_time = null;
                $res = $recod->save();
                $SystemUsers->deleteOpenUnbind($recod->id);
                $MemberToken = new MemberToken();
                $cos = $MemberToken->deleteTokenById($recod->id);
                return true;

            }else{
                $this->serializer['status'] = false;
                $this->serializer['errno'] = 2;
                $this->serializer['message'] = '找不到此用户';
                return false;
            }
        }else{
            $this->serializer['status'] = false;
            $this->serializer['errno'] = 2;
            $this->serializer['message'] = '请输入工资号';
            return false;
            $Employee = new Employee();
            $empList = $Employee->getSubunitBySearch($search);
            foreach ($empList as $key => $value) {
                $SystemUsers = new User();
                $recod = $SystemUsers->getSystemUsersByEmpNumber($value['emp_number']);
                if($recod){
                    $recod->open_id = null;
                    $recod->bind_time = null;
                    $res = $recod->save();
                    $SystemUsers->deleteOpenUnbind($recod->id);
                    $MemberToken = new MemberToken();
                    $cos = $MemberToken->deleteTokenById($recod->id);
                }
            }
        }
        
        return true;

    }

    /**
     * @SWG\Get(path="/direct-visit/update-atab",
     *     tags={"云平台-DirectVisit-直接访问接口"},
     *     summary="查询所有申请",
     *     description="查询所有申请",
     *     produces={"application/json"},

     *     @SWG\Response(
     *         response = 200,
     *         description = ""
     *     ),
     *     @SWG\Response(
     *         response = 403,
     *         description = "",
     *     )
     * )
     *
     */
    public function actionUpdateAtab(){
        $ApproverTab = new ApproverTab();

        $list = $ApproverTab->getAllList();
        $i=1;
        foreach ($list as $key => $value) {
            if($value->create_time){
                continue;
            }

            
            $type = $value->app_type;
            if($value->app_type==1){
                $Leave = new LeaveRequest();   
                $id = $value->leave_id;
                $data = $Leave->getLeaveRequestById($id);
                if($data){
                    
                    $value->create_time = $data->create_time;
                    $value->save();
                }
               

            }else if($value->app_type==2){

                $Overtime = new Overtime();
                $id = $value->overtime_id;

                $data = $Overtime->getOvertimeById($id);
                if($data){
          
                    $value->create_time = $data->creat_time;
                    $value->save();
                }


            }else if($value->app_type==3){
  
                $AttendanceRecord = new AttendanceRecord();
                $id = $value->attend_id;
                $data = $AttendanceRecord->getAttendanceRecordById($id);
                if($data){
     
                    $value->create_time = $data->create_time;
                    $value->save();
                }



            }else if($value->app_type==4){
  
                $ShiftChangeApply = new ShiftChangeApply();
                $id = $value->shift_apply_id;
                $data = $ShiftChangeApply->getShiftChangeApplyById($id);
                if($data){

                    $value->create_time = $data->create_at;
                    $value->save();
                }
            }

            if(empty($data)){
                $ApproverTab->deleteById($id,$type);
            }


        }
        $this->serializer['status'] = true;
        $this->serializer['errno'] = 0;
        $this->serializer['message'] = '';
        return true;

    }
    
    public function actionGetAtab(){
        $ApproverTab = new ApproverTab();

        $list = $ApproverTab->getAllListCa();
        $i=1;

        $this->serializer['status'] = true;
        $this->serializer['errno'] = 0;
        $this->serializer['message'] = '';
        return $list;

    }

    public function actionGetUserLog(){
        $page = Yii::$app->request->post('page'); 
        $pageSize = 100;
        $offset = ($page >= 1) ? (($page - 1) * $pageSize) : 0;
        

        $id =Yii::$app->request->post('id');
        $UserLog = new UserLoginLog();
        $log = $UserLog->getLogOrderBy($pageSize,$offset);
        
        
        $this->serializer['status'] = true;
        $this->serializer['errno'] = 0;
        $this->serializer['message'] = '';
        return $log;
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
}
