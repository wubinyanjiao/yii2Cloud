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

use common\models\system\SystemUsers;
use common\models\leave\Leave;
use common\models\leave\LeaveRequest;
use common\models\leave\LeaveEntitlement;
use common\models\leave\LeaveType;
use common\models\leave\LeaveEntitlementLog;
use common\models\leave\LeaveRequestComment;
use common\models\leave\LeaveComment;

use common\models\employee\Employee;
use common\models\user\User;
use common\models\subunit\Subunit;

use cheatsheet\Time;


class LeaveController extends \common\rest\Controller
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
     * @SWG\Post(path="/leave/get-leave-list",
     *     tags={"云平台-Leave-休假"},
     *     summary="获取休假列表",
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
     *        name = "statDate",
     *        description = "开始时间",
     *        required = false,
     *        type = "string"
     *     ),
     *      @SWG\Parameter(
     *        in = "formData",
     *        name = "endDate",
     *        description = "结束时间",
     *        required = false,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "status",
     *        description = "状态",
     *        required = false,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "empNumber",
     *        description = "员工ID",
     *        required = false,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "workStation",
     *        description = "所在小组 int",
     *        required = false,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "page",
     *        description = "当前页数",
     *        required = false,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "lastEmp",
     *        description = "是否过期员工 1是 0 否",
     *        required = false,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "ismine",
     *        description = "是否是我的列表 1是 0 否",
     *        required = false,
     *        type = "string"
     *     ),
     *
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
    public function actionGetLeaveList()
    {
        $LeaveEntitlement = new LeaveEntitlement();
        //$s =$LeaveEntitlement->appointEmployeeLeave(800,1,'2018-10-16','2018-10-16',0);



        $post=Yii::$app->request->post();
        $defaultDate = getDefaultDate();

        // $statDate = !empty($post['statDate'])?$post['statDate']:$defaultDate['statDate'];
        // $endDate = !empty($post['endDate'])?$post['endDate']:$defaultDate['endDate'];
        $status = Yii::$app->request->post('status');

        $statDate = !empty($post['statDate'])?$post['statDate']:null;
        $endDate = !empty($post['endDate'])?$post['endDate']:null;
        // $statDate = date('Y-m-d',strtotime($statDate));
        // $endDate = date('Y-m-d',strtotime($endDate));

        $ismine = !empty($post['ismine'])?$post['ismine']:0;
        if(isset($status)){
            $staArr = explode(',', trim($status,','));
            foreach ($staArr as $key => $value) {
                if($value==2){
                    array_push($staArr, '3');
                }
            }
            $search['status'] = $staArr;
        }else{
            $search['status']  = null;
        }


        $search['workStation'] = !empty($post['workStation'])?$post['workStation'] : 0;
        //$search['empNumber'] = !empty($post['empNumber'])?$post['empNumber'] : 0;
        $search['lastEmp'] = !empty($post['lastEmp'])?$post['lastEmp'] : 0;
        $search['page'] = !empty($post['page'])?$post['page']:1;

        $firstName = trim(Yii::$app->request->post('empNumber'));  //前端传的是名字不是ID 要根据名字获取ID

        $Employee = new Employee();
        if($firstName){
            // if($this->userRoleId==2||$this->userRoleId==8){
            //     $arr = $Employee->getEmpNumberByFirstName($firstName,$this->workStation);
            // }else{
            //     $arr =$Employee->getEmpNumberByFirstName($firstName);
            // }
            //$search['empNumber'] = $arr;
            $search['empNumber'] = array($firstName);
        }

        if(empty($search['page'])){
            $page  = 1;
        }else{
            $page = $search['page'] ;
        }

        if($this->userId!=1){
            $search['workStation'] = $this->workStation;
        }

        if($ismine){
            $search['empNumber'] = array($this->empNumber);
        }

        $pageSize = Yii::$app->params['pageSize']['default'];
        //$search['limit'] = env('RECORDS_PER_PAGE');   //每页数 20
        $search['limit'] = $pageSize;   //每页数 20

        $offset = ($page >= 1) ? (($page - 1) * $pageSize) : 0;
        $search['offset'] = $offset;

        if(!strtotime($statDate)){
            $statDate =$defaultDate['statDate'] ;
            $statDate =null ;
        }
        if(!strtotime($endDate)){
            $endDate =$defaultDate['endDate'] ;
            $endDate =null ;
        }
        $search['statDate'] = $statDate;
        $search['endDate'] = $endDate;

        $Leave = new LeaveRequest();
        $list = $Leave->getViemLeaveList($search);

        $count = $list['count'];
        $list = $list['list'];
        $backArr = array();
        $SystemUsers = new SystemUsers();


        foreach($list as $k=>$v){
            $arr['id'] = $v['id'];
            $arr['empNumber'] = $v['emp_number'];
            $arr['firstName'] = $v['employee']['emp_firstname'];
            $user = $SystemUsers->searchSystemUsersById($v['emp_number'],true);
            $arr['userName'] = $user['user_name'];
            $arr['leaveTypeName'] = $v['leaveType']['name'];
            $arr['noOfDays'] = $v['no_of_days']?$v['no_of_days']:0;

            $com = '';


            if($v['leaveRequestComment']){
                $value = end($v['leaveRequestComment']);
                $access['time']  = $value['created'];
                $access['created_by_name']  = $value['created_by_name'];
                $access['comments']  = $value['comments'];
                $com = $value['comments'];
                // foreach ($v['leaveRequestComment'] as $key => $value) {
                //     $access['time']  = $value['created'];
                //     $access['created_by_name']  = $value['created_by_name'];
                //     $access['comments']  = $value['comments'];
                //     $com[] = $access;
                // }

            }
            $arr['comment'] = $com;

            $isSoam = false;   //所有假期是否状态一致  false 一致  true不一致
            $status = 0;
            $i = 1;
            $firstDate = '';
            $secondDate ='';
            $lengthDay = 0;
            $anpaiDay = 0;
            $quxiaoDay =0;
            $dengDay =0;
            $allD = 0;

            foreach($v['leave'] as $key=>$value){
                if($i==1){
                    if($value['duration_type']==0){
                        $lengthDay=1;
                        $firstDate = $value['date'];
                    }else if($value['duration_type']==1){
                        $lengthDay=0.5;
                        $firstDate = $value['date'].'(09:00-13:00)半天';
                    }else if($value['duration_type']==2){
                        $lengthDay=0.5;
                        $firstDate = $value['date'].'(13:00-17:00)半天';
                    }

                    $status = $value['status'];

                }else{
                    if($value['duration_type']==0){
                        $lengthDay=1;
                    }else if($value['duration_type']==1){
                        $lengthDay=0.5;
                    }else if($value['duration_type']==2){
                        $lengthDay=0.5;
                    }
                    $secondDate = '至'.$value['date'];

                    if($status != $value['status']){
                        $isSoam = true;
                    }
                }

                if($value['status'] < 1){
                    $quxiaoDay  += $lengthDay;
                }else if($value['status']==1){
                    $dengDay += $lengthDay;
                }else if($value['status']>1){
                    $anpaiDay += $lengthDay;
                }

                $allD +=$lengthDay;

                $i++;
            }

            $leaveStatus = '';
            if($quxiaoDay>0){
                $leaveStatus .='取消('.$quxiaoDay.')';
            }
            if($anpaiDay>0){
                $leaveStatus .='已安排('.$anpaiDay.')';
            }
            if($dengDay>0){
                $leaveStatus .='等待批准('.$dengDay.')';
            }
            $operation = array();
            if($isSoam){
                $operation[0] = array('name'=>'进入详情页面','status'=>-10);
            }else{
                if($status <= 0 ){

                }else if($status==1){
                    if($ismine){
                        $operation[2] = array('name'=>'取消','status'=>0);
                    }else{
                        $operation[0] = array('name'=>'批准','status'=>2);
                        $operation[1] = array('name'=>'拒绝','status'=>-1);
                        $operation[2] = array('name'=>'取消','status'=>0);
                    }

                }else if($status>1){
                    if(!$ismine){
                        $operation[] = array('name'=>'取消','status'=>0);
                    }

                }
            }

            $arr['leaveDate'] = $firstDate.$secondDate;
            $arr['lengthDay'] = $allD;
            $arr['leaveStatus'] = $leaveStatus;
            $arr['operation'] = $operation;
            $backArr[] = $arr;

        }


        $this->serializer['status'] = true;
        $this->serializer['errno'] = 0;
        $this->serializer['message'] = '成功';
        return array('data'=>$backArr,'totalCount'=>(int)$count,'current_page'=>(int)$page,'pageSize'=>(int)$pageSize);

    }
    /**
     * @SWG\Post(path="/leave/get-leave-request-list",
     *     tags={"云平台-Leave-休假"},
     *     summary="根据ID获取休假列表",
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
     *        name = "id",
     *        description = "休假id",
     *        required = false,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "page",
     *        description = "当前页数",
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
    public function actionGetLeaveRequestList()
    {

        $post=Yii::$app->request->post();

        $id = !empty($post['id'])?$post['id'] : 0;
        // $search['page'] = !empty($post['page'])?$post['page']:0;
        // $search['limit'] = env('RECORDS_PER_PAGE');   //每页数 20

        if(empty($id)){
            $this->serializer['status'] = false;
            $this->serializer['errno'] = 2;
            $this->serializer['message'] = '参数错误';
            return false;
        }


        $Leave = new LeaveRequest();
        $list = $Leave->getViemLeaveRequestList($id);

        $request = $Leave->getLeaveRequestById($id);
        $noOfDays = $request->no_of_days;
        $backArr = array();
        $SystemUsers = new SystemUsers();
        foreach($list as $k=>$v){
            $arr['id'] = $v['id'];
            $arr['requestId'] = $v['leave_request_id'];
            $arr['firstName'] = $v['employee']['emp_firstname'];
            $user = $SystemUsers->searchSystemUsersById($v['emp_number'],true);
            $arr['userName'] = $user['user_name'];
            $arr['leaveTypeName'] = $v['leaveType']['name'];
            $arr['noOfDays'] = $noOfDays?floatval($noOfDays):0;

            $com = '';


            if($v['leaveComment']){
                $value = end($v['leaveComment']);
                // $access['time']  = $value['created'];
                // $access['created_by_name']  = $value['created_by_name'];
                // $access['comments']  = $value['comments'];
                // $com[] = $access;
                $com = $value['comments'];
                // foreach ($v['leaveComment'] as $key => $value) {
                //     $access['time']  = $value['created'];
                //     $access['created_by_name']  = $value['created_by_name'];
                //     $access['comments']  = $value['comments'];
                //     $com[] = $access;
                // }
            }
            $arr['comment'] = $com;
            $lengthDay = 0;
            if($v['duration_type']==0){
                $lengthDay =1;
                $firstDate = $v['date'];
            }else if($v['duration_type']==1){
                $lengthDay =0.5;
                $firstDate = $v['date'].'(09:00-13:00)半天';
            }else if($v['duration_type']==2){
                $lengthDay =0.5;
                $firstDate = $v['date'].'(13:00-17:00)半天';
            }
            $arr['lengthDay'] = $lengthDay;
            $arr['leaveDate'] = $firstDate;


            if($v['status'] == -1){
                $arr['leaveStatus'] = '已拒绝';
                $operation = array();
            }else if($v['status']==0){
                $arr['leaveStatus'] = '已取消';
                $operation = array();
            }else if($v['status'] ==1){
                $arr['leaveStatus'] = '等待批准';
                $operation[0] = array('name'=>'批准','status'=>2);
                $operation[1] = array('name'=>'拒绝','status'=>-1);
                $operation[2] = array('name'=>'取消','status'=>0);
            }else if($v['status'] ==2){
                $arr['leaveStatus'] = '已同意';
                $operation[0] = array('name'=>'取消','status'=>0);
            }else if($v['status'] ==3){
                $arr['leaveStatus'] = '已使用';
                $operation[0] = array('name'=>'取消','status'=>0);
            }

            $arr['operation'] = $operation;
            $backArr[] = $arr;

        }
        $this->serializer['status'] = true;
        $this->serializer['errno'] = 0;
        $this->serializer['message'] = '成功';
        //return array('data'=>$backArr,'totalCount'=>(int)$count,'current_page'=>(int)$page,'pageSize'=>(int)$pageSize);
        return array('data'=>$backArr);

    }

    /**
     * @SWG\Post(path="/leave/update-leave-status",
     *     tags={"云平台-Leave-休假"},
     *     summary="修改休假状态",
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
     *        name = "id",
     *        description = "休假id",
     *        required = false,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "queryType",
     *        description = "要修改的状态",
     *        required = false,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "isRequest",
     *        description = "是否是requestID true 是 false 否",
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
    public function actionUpdateLeaveStatus()
    {
//         $LeaveEntitlement = new LeaveEntitlement();
//         $s=$LeaveEntitlement->returnSchedulingById(10);

//         var_dump($s);die;
// die;
        $post=Yii::$app->request->post();

        $id = !empty($post['id'])?$post['id'] : 0;
        $queryType = !empty($post['queryType'])?$post['queryType'] : 0;
        $isRequest = !empty($post['isRequest'])?$post['isRequest'] : 0 ;


        if(empty($id)){
            $this->serializer['status'] = false;
            $this->serializer['errno'] = 2;
            $this->serializer['message'] = '参数错误';
            return false;
        }


        $Leave = new LeaveRequest();
        if($isRequest){
            $ids = array();
            $list = $Leave->getViemLeaveRequestList($id);
            foreach($list as $v){
                $ids[] =$v['id'];
            }

        }else{

            $ids = array($id);
        }

        $LeaveEntitlement = new LeaveEntitlement();
        $istrue = $LeaveEntitlement->updateLeaveStatus($this->empNumber,$ids,$queryType);
        if($istrue){
            $this->serializer['status'] = true;
            $this->serializer['errno'] = 0;
            $this->serializer['message'] = '成功';
        }else{
            $this->serializer['status'] = false;
            $this->serializer['errno'] = 2;
            $this->serializer['message'] = '修改失败';
        }

        return false;

    }

    /**
     * @SWG\Post(path="/leave/get-leave-type",
     *     tags={"云平台-Leave-休假"},
     *     summary="获取休假类型",
     *     description="微信端 iview 通用",
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
     *        name = "islimit",
     *        description = "是否是有限假期 1是",
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
    public function actionGetLeaveType()
    {

        $post=Yii::$app->request->post();
        $islimit = !empty($post['islimit'])?$post['islimit'] : 0;

        $LeaveType = new LeaveType();
        $list = $LeaveType->getLeaveTypeList($islimit);
        $backArr = array();

        foreach ($list as $key => $value) {
            $arr['key'] = $value['id'];
            $arr['val'] = $value['name'];
            $arr['islimit'] = $value['islimit'];
            $arr['orderid'] = $value['orderid'];

            $backArr[] =$arr;
        }


        if($backArr){
            $this->serializer['status'] = true;
            $this->serializer['errno'] = 0;
            $this->serializer['message'] = '成功';
            return array('data'=>$backArr);
        }else{
            $this->serializer['status'] = false;
            $this->serializer['errno'] = 2;
            $this->serializer['message'] = '获取失败';
        }

        return false;

    }

    /**
     * @SWG\Post(path="/leave/add-leave-entitlement",
     *     tags={"云平台-Leave-休假"},
     *     summary="添加假期",
     *     description="",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "Token",
     *        description = "8a589f7029b0b24e09013e017b4be9cbd20414cc",
     *        required = true,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "empId",
     *        description = "员工ID 多个ID 以逗号隔开",
     *        required = false,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "empNumber",
     *        description = "员工姓名 单个人增加或减少是传这个",
     *        required = false,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "gender",
     *        description = "性别 0所有 ,1 男,2女",
     *        required = false,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "leaveTypeId",
     *        description = "休假类型",
     *        required = false,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "note",
     *        description = "理由",
     *        required = false,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "days",
     *        description = "天数",
     *        required = false,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "status",
     *        description = "1 增加  2减少",
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
    public function actionAddLeaveEntitlement(){
        $empId = Yii::$app->request->post('empId');
        $empNumber = Yii::$app->request->post('empNumber');
        $gender = Yii::$app->request->post('gender');
        $leaveTypeId = Yii::$app->request->post('leaveTypeId');
        $note = Yii::$app->request->post('note');
        $days = Yii::$app->request->post('days');
        $status = Yii::$app->request->post('status');

        if (empty($empId)&&empty($empNumber)) {
            $this->serializer['status'] = false;
            $this->serializer['errno'] = 2;
            $this->serializer['message'] = '参数错误!';
            return false;
        }
        if(!$leaveTypeId){
            $this->serializer['status'] = false;
            $this->serializer['errno'] = 2;
            $this->serializer['message'] = '参数错误!';
            return false;
        }
        if($empNumber){
            // $str = explode('(', $empNumber);
            // if(empty($str[1])){
            //     $this->serializer['status'] = false;
            //     $this->serializer['errno'] = 2;
            //     $this->serializer['message'] = '参数错误';   
            //     return false;
            // }  
            // $str1 = explode(')', $str[1]);
            // $subName = $str1[0];
            // $Subunit = new Subunit();
            // $sub = $Subunit->getSubunitByName($subName);

            $Employee = new Employee();

            $emp = $Employee->getEmpByNumNber($empNumber);

            if($emp){
                $empList = array($emp['emp_number']);
            }else{
                $this->serializer['status'] = false;
                $this->serializer['errno'] = 2;
                $this->serializer['message'] = '找不到此员工';
                return false;
            }
            //end
        }

        if($empId){
            $empList = explode(',',$empId);
        }

        $Employee = new Employee();
        $empArr = $Employee->getEmpIdBySubunit($this->workStation);



        $LeaveEntitlement = new LeaveEntitlement();
        $created_by_id = $this->userId;
        if($created_by_id==1){
            $created_by_name = $this->userName;
        }else{
            $created_by_name = $this->firstName;
        }

        if($status==1){
            $days = abs($days);
            $message = '添加';
        }else if($status==2){
            $days = -abs($days);
            $message = '销假';
        }
        $i = 1;
        $first = false;
        $istrue = false;
        foreach ($empList as $key => $value) {

            if(!in_array($value, $empArr)){
                continue;
            }

            $istrue = $LeaveEntitlement->saveLeaveEntitlementByAdmin($value,$leaveTypeId,$days,$note,$created_by_id,$created_by_name);
            if($i==1){
                $first = $istrue;
            }

            $i++;
        }

        if(count($empList)==1&&$first==false){
            $this->serializer['status'] = false;
            $this->serializer['errno'] = 2;
            $this->serializer['message'] = $message.'失败';
            return false;
        }

        if($istrue==false){
            $this->serializer['status'] = false;
            $this->serializer['errno'] = 2;
            $this->serializer['message'] = $message.'失败';
            return false;
        }
        $this->serializer['status'] = true;
        $this->serializer['errno'] = 0;
        $this->serializer['message'] = $message.'成功';
        return false;

    }

    /**
     * @SWG\Post(path="/leave/get-leave-surplus",
     *     tags={"云平台-Leave-休假"},
     *     summary="获取某类型假期的余假数",
     *     description="",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "Token",
     *        description = "8a589f7029b0b24e09013e017b4be9cbd20414cc",
     *        required = true,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "empNumber",
     *        description = "员工ID",
     *        required = false,
     *        type = "string"
     *     ),

     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "leaveTypeId",
     *        description = "休假类型",
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
    public function actionGetLeaveSurplus(){
        $empNumber = Yii::$app->request->post('empNumber');
        $leaveTypeId = Yii::$app->request->post('leaveTypeId');
        if(!$leaveTypeId||!$empNumber){
            $this->serializer['status'] = false;
            $this->serializer['errno'] = 2;
            $this->serializer['message'] = '参数错误';
            return false;
        }

        //前端查询员工只能传名字 

        // $str = explode('(', $empNumber);
        // if(empty($str[1])){
        //     $this->serializer['status'] = false;
        //     $this->serializer['errno'] = 2;
        //     $this->serializer['message'] = '参数错误';   
        //     return false;
        // }  
        // $str1 = explode(')', $str[1]);
        // $subName = $str1[0];
        // $Subunit = new Subunit();
        // $sub = $Subunit->getSubunitByName($subName);

        $Employee = new Employee();

        $emp = $Employee->getEmpByNumNber($empNumber);

        if($emp){
            $empNumber = $emp['emp_number'];
        }else{
            $this->serializer['status'] = false;
            $this->serializer['errno'] = 2;
            $this->serializer['message'] = '找不到此员工';
            return false;
        }
        //end

        $LeaveEntitlement = new LeaveEntitlement();

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
        $this->serializer['status'] = true;
        $this->serializer['errno'] = 0;
        $this->serializer['message'] = '';
        $data['surplus'] = $surplus;
        return array('data'=>$data);

    }

    /**
     * @SWG\Post(path="/leave/assign-leave",
     *     tags={"云平台-Leave-休假"},
     *     summary="登记休假",
     *     description="",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "Token",
     *        description = "8a589f7029b0b24e09013e017b4be9cbd20414cc",
     *        required = true,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "empNumber",
     *        description = "员工ID",
     *        required = false,
     *        type = "string"
     *     ),

     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "leaveTypeId",
     *        description = "休假类型",
     *        required = false,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "leaveDate",
     *        description = "休假日期 ,多天以逗号隔开",
     *        required = false,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "unWeekend",
     *        description = "是否排除周末 1排除",
     *        required = false,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "note",
     *        description = "理由",
     *        required = false,
     *        type = "string"
     *     ),
     *    @SWG\Parameter(
     *        in = "formData",
     *        name = "duration",
     *        description = "一天的情况 0 全天 1上午半天 2下午半天",
     *        required = false,
     *        type = "string"
     *     ),
     *
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
    public function actionAssignLeave(){
        $empNumber = Yii::$app->request->post('empNumber');
        $leaveTypeId = Yii::$app->request->post('leaveTypeId');
        $leaveDate = Yii::$app->request->post('leaveDate');
        $duration = Yii::$app->request->post('duration');
        $note = Yii::$app->request->post('note');
        $unWeekend = Yii::$app->request->post('unWeekend');

        if(!$leaveTypeId||!$empNumber){
            $this->serializer['status'] = false;
            $this->serializer['errno'] = 2;
            $this->serializer['message'] = '参数错误';
            return false;
        }


        //前端查询员工只能传名字 

        // $str = explode('(', $empNumber);
        // if(empty($str[1])){
        //     $this->serializer['status'] = false;
        //     $this->serializer['errno'] = 2;
        //     $this->serializer['message'] = '请检测员工姓名是否正确';   
        //     return false;
        // }  
        // $str1 = explode(')', $str[1]);
        // $subName = $str1[0];
        // $Subunit = new Subunit();
        // $sub = $Subunit->getSubunitByName($subName);

        $Employee = new Employee();

        $emp = $Employee->getEmpByNumNber($empNumber);

        if($emp){
            $empNumber = $emp['emp_number'];
        }else{
            $this->serializer['status'] = false;
            $this->serializer['errno'] = 2;
            $this->serializer['message'] = '找不到此员工';
            return false;
        }
        //end
        $leaveDays = explode(',',$leaveDate );

        // if($leaveDays[0] != date('Y-m-d',strtotime($leaveDays[0]))){
        //     $this->serializer['status'] = false;
        //     $this->serializer['errno'] = 2;
        //     $this->serializer['message'] = '日期错误';   
        //     return false;
        // }
        // if($leaveDays[1] != date('Y-m-d',strtotime($leaveDays[1]))){
        //     $this->serializer['status'] = false;
        //     $this->serializer['errno'] = 2;
        //     $this->serializer['message'] = '日期错误';   
        //     return false;
        // }
        $days = array();

        if(!count($leaveDays)){
            $this->serializer['status'] = false;
            $this->serializer['errno'] = 2;
            $this->serializer['message'] = '请选择日期';
            return false;
        }

        if(count($leaveDays)==1){  //一天的
            $days = array($leaveDays[0]);
        }else{
            if($unWeekend){   //剔除周末
                $arrDay = getendday1($leaveDays[0],$leaveDays[1]);

                if(empty($arrDay['useDay'])){
                    $this->serializer['status'] = false;
                    $this->serializer['errno'] = 2;
                    $this->serializer['message'] = '日期选择错误';
                    return false;
                }
                $days = $arrDay['useDay'];
            }else{
                $days =prDates($leaveDays[0],$leaveDays[1]);
            }
        }


        // $days = array();
        // foreach ($leaveDays as $key => $value) {
        //     if(date('Y-m-d',strtotime($value))==$value){
        //             array_push($days, $value);            
        //     }
        // }
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
            $isHa = $LeaveEntitlement->appointEmployeeLeave($empNumber,$leaveTypeId,$first,$end,$duration,2,$note,$this->firstName,$this->userId,$days);
        }else if($lengthDay==1){
            $isHa =$LeaveEntitlement->appointEmployeeLeave($empNumber,$leaveTypeId,$first,$end,0,2,$note,$this->firstName,$this->userId,$days);
        }else{
            // $first = reset($days);
            // $end = end($days);
            $isHa =$LeaveEntitlement->appointEmployeeLeave($empNumber,$leaveTypeId,$first,$end,0,2,$note,$this->firstName,$this->userId,$days);
        }

        if($isHa['status']&&!empty($isHa['result'])){
            $this->serializer['status'] = true;
            $this->serializer['errno'] = 0;
            $this->serializer['message'] = '请假成功';
            return true;
        }else{
            $this->serializer['status'] = false;
            $this->serializer['errno'] = 0;
            $this->serializer['message'] = '请假失败';
            return true;
        }



    }

    /**
     * @SWG\Post(path="/leave/leave-balance-report",
     *     tags={"云平台-Leave-休假"},
     *     summary="假期使用情况报告",
     *     description="",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "Token",
     *        description = "8a589f7029b0b24e09013e017b4be9cbd20414cc",
     *        required = true,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "empNumber",
     *        description = "员工ID",
     *        required = false,
     *        type = "string"
     *     ),

     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "leaveTypeId",
     *        description = "休假类型",
     *        required = false,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "startDate",
     *        description = "休假开始日期日期 ",
     *        required = false,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "endDate",
     *        description = "休假截止日期 ",
     *        required = false,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "workStation",
     *        description = "所在小组",
     *        required = false,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "ismine",
     *        description = "是否是我的列表 1是 0 否",
     *        required = false,
     *        type = "string"
     *     ),
     *      @SWG\Parameter(
     *        in = "formData",
     *        name = "page",
     *        description = "当前页数",
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
    public function actionLeaveBalanceReport(){
        // $LeaveEntitlement = new LeaveEntitlement();
        // $s=$LeaveEntitlement->returnSchedulingById(10);

        // var_dump($s);die;


        $empNumber = Yii::$app->request->post('empNumber');
        $leaveTypeId = Yii::$app->request->post('leaveTypeId');
        $startDate = Yii::$app->request->post('startDate');
        $endDate = Yii::$app->request->post('endDate');
        $workStation = Yii::$app->request->post('workStation');
        $page = Yii::$app->request->post('page');
        $ismine = Yii::$app->request->post('ismine');

        if($this->userRoleId==2||$this->userRoleId==8){
            $workStation = $this->workStation;
        }
        $Employee = new Employee();
        $Leave = new Leave();
        $empArr = $Employee->getEmpIdBySubunit($workStation);


        //$firstName = trim(Yii::$app->request->post('empNumber'));  //前端传的是名字不是ID 要根据名字获取ID
        $search = array();
        $search['empNumber'] = array();
        if($empNumber){
            // if($this->userRoleId==2||$this->userRoleId==8){
            //     $arr = $Employee->getEmpNumberByFirstName($firstName,$this->workStation);
            // }else{
            //     $arr =$Employee->getEmpNumberByFirstName($firstName);
            // }


            $search['empNumber'] = array($empNumber);
        }




        if($ismine){
            $search['empNumber'] =array($this->empNumber);
        }else{
            // if($empNumber){
            //     if(!in_array($empNumber, $empArr)){
            //         $this->serializer['status'] = false;
            //         $this->serializer['errno'] = 0;
            //         $this->serializer['message'] = '你没权限查找次用户';   
            //         return false;
            //     }
            //     $search['empNumber'] = $empNumber;
            // }else{
            //     $search['empArr'] = $empArr;
            // }
        }
        if(empty($page)){
            $page = 1;
        }

        if(empty($leaveTypeId)){
            //$leaveTypeId = $Leave->getLeaveTypeByLimit(1);
            $leaveTypeId = 1;
        }else{
            $leaveTypeId =  $leaveTypeId;
        }



        $search['leaveTypeId'] = $leaveTypeId;
        $search['page'] = !empty($page)?$page:0;
        $pageSize = Yii::$app->params['pageSize']['default'];
        //$search['limit'] = env('RECORDS_PER_PAGE');   //每页数 20
        $search['limit'] = $pageSize;   //每页数 20
        $offset = ($page >= 1) ? (($page - 1) * $pageSize) : 0;
        $search['offset'] = $offset;

        $search['startDate'] = $startDate;
        $search['endDate'] = $endDate;
        $search['workStation'] = $workStation;

        //$Leave = new Leave();
        $backArr = $Leave->getLeaveBalanceReport($search);
        $list = $backArr['list'];
        $count = $backArr['count'];
        $this->serializer['status'] = true;
        $this->serializer['errno'] = 0;
        $this->serializer['message'] = '';

        return array('data'=>$list,'totalCount'=>(int)$count,'current_page'=>(int)$page,'pageSize'=>(int)$pageSize);





    }

    /**
     * @SWG\Post(path="/leave/entitlement-report",
     *     tags={"云平台-Leave-休假"},
     *     summary="假期变动明细",
     *     description="",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "Token",
     *        description = "8a589f7029b0b24e09013e017b4be9cbd20414cc",
     *        required = true,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "empNumber",
     *        description = "员工ID",
     *        required = false,
     *        type = "string"
     *     ),

     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "leaveTypeId",
     *        description = "休假类型",
     *        required = false,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "startDate",
     *        description = "休假开始日期日期 ",
     *        required = false,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "endDate",
     *        description = "休假截止日期 ",
     *        required = false,
     *        type = "string"
     *     ),
     *      @SWG\Parameter(
     *        in = "formData",
     *        name = "page",
     *        description = "当前页数",
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
    public function actionEntitlementReport(){
        $empNumber = trim(Yii::$app->request->post('empNumber'));
        $leaveTypeId = Yii::$app->request->post('leaveTypeId');
        $startDate = Yii::$app->request->post('startDate');
        $endDate = Yii::$app->request->post('endDate');

        $page = Yii::$app->request->post('page');


        if($this->userRoleId==2||$this->userRoleId==8){
            $workStation = $this->workStation;
        }

        if(empty($workStation)){
            $workStation = 0;
        }

        $Employee = new Employee();
        $Leave = new Leave();
        $empArr = $Employee->getEmpIdBySubunit($workStation);


        $search = array();
        $search['empNumber'] = array();
        // if($empNumber){
        //     if($this->userRoleId==2||$this->userRoleId==8){
        //         $arr = $Employee->getEmpNumberByFirstName($empNumber,$this->workStation);
        //     }else{
        //         $arr =$Employee->getEmpNumberByFirstName($empNumber);
        //     }


        //     $search['empNumber'] = $arr;
        // }else{
        //     $this->serializer['status'] = false;
        //     $this->serializer['errno'] = 0;
        //     $this->serializer['message'] = '请输入要查询的员工';
        //     return false;
        // }

        if($empNumber){
            if(!in_array($empNumber, $empArr)){
                $this->serializer['status'] = false;
                $this->serializer['errno'] = 0;
                $this->serializer['message'] = '你没权限查找次用户';
                return false;
            }
            $search['empNumber'] = $empNumber;
        }else{
            $this->serializer['status'] = false;
            $this->serializer['errno'] = 0;
            $this->serializer['message'] = '请输入要查询的员工';
            return false;
        }

        if(empty($page)){
            $page = 1;
        }

        if(empty($leaveTypeId)){
            $leaveTypeId = $Leave->getLeaveTypeByLimit(1);
        }else{
            $leaveTypeId = $leaveTypeId;
        }

        $search['leaveTypeId'] = $leaveTypeId;
        $search['page'] = !empty($page)?$page:0;
        $pageSize = Yii::$app->params['pageSize']['default'];

        $offset = ($page >= 1) ? (($page - 1) * $pageSize) : 0;
        $search['offset'] = $offset;
        //$search['limit'] = env('RECORDS_PER_PAGE');   //每页数 20
        $search['limit'] = $pageSize;   //每页数 20

        $search['startDate'] = $startDate;
        $search['endDate'] = $endDate;


        $LeaveEntitlementLog = new LeaveEntitlementLog();
        $backArr = $LeaveEntitlementLog->getEntitlementLogReport($search);
        $list = $backArr['list'];

        $data = array();
        foreach ($list as $key => $value) {
            $arr = array();
            $arr['id'] = $value->id;
            $arr['userName'] = $value->user->user_name;
            $arr['firstName'] = $value->employee->emp_firstname;
            $arr['leaveTypeName'] = $value->leaveType->name;
            $arr['date'] = date('Y-m-d',strtotime($value->create_time));
            $arr['days'] = floatval($value->days);
            $arr['noOfDays'] = $value->no_of_days?$value->no_of_days:0;
            $arr['note'] = $value->note;
            $arr['createByName'] = $value->create_by_name;


            $data[] = $arr;
        }
        $count = $backArr['count'];
        $this->serializer['status'] = true;
        $this->serializer['errno'] = 0;
        $this->serializer['message'] = '';

        return array('data'=>$data,'totalCount'=>(int)$count,'current_page'=>(int)$page,'pageSize'=>(int)$pageSize);

    }

    /**
     * @SWG\Post(path="/leave/get-request-comment",
     *     tags={"云平台-Leave-休假"},
     *     summary="获取休假评论",
     *     description="",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "Token",
     *        description = "8a589f7029b0b24e09013e017b4be9cbd20414cc",
     *        required = true,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "id",
     *        description = "休假ID",
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
    public function actionGetRequestComment(){
        $id = Yii::$app->request->post('id');

        if(empty($id)){
            $this->serializer['status'] = false;
            $this->serializer['errno'] = 0;
            $this->serializer['message'] = '参数错误';
            return false;
        }
        $leaveRequestComment = new LeaveRequestComment();
        $list = $leaveRequestComment->getLeaveRequestComment($id);

        $backArr = array();
        if($list){
            foreach ($list as $key => $value) {
                $arr = array();

                $arr['createTime'] = $value['created'];
                $arr['createByName'] = $value['created_by_name'];
                $arr['note']  = $value['comments'];

                $backArr[] = $arr;
            }
        }

        $this->serializer['status'] = true;
        $this->serializer['errno'] = 0;
        $this->serializer['message'] = '';
        return array('data'=>$backArr);

    }

    /**
     * @SWG\Post(path="/leave/save-request-comment",
     *     tags={"云平台-Leave-休假"},
     *     summary="添加休假评论",
     *     description="",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "Token",
     *        description = "8a589f7029b0b24e09013e017b4be9cbd20414cc",
     *        required = true,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "id",
     *        description = "休假ID",
     *        required = false,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "note",
     *        description = "评论内容",
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
    public function actionSaveRequestComment(){
        $id = Yii::$app->request->post('id');
        $note = Yii::$app->request->post('note');
        if(empty($id)||empty($note)){
            $this->serializer['status'] = false;
            $this->serializer['errno'] = 0;
            $this->serializer['message'] = '参数错误';
            return false;
        }
        $leaveRequestComment = new leaveRequestComment();


        $leaveRequestComment->leave_request_id = $id;
        $leaveRequestComment->created = date('Y-m-d H:i:s');

        $leaveRequestComment->created_by_name = $this->firstName;
        $leaveRequestComment->created_by_id = $this->userId;
        $leaveRequestComment->comments = $note;
        $leaveRequestComment->save();

        if($leaveRequestComment->id){
            $this->serializer['status'] = true;
            $this->serializer['errno'] = 0;
            $this->serializer['message'] = '评论成功';
            return true;
        }else{
            $this->serializer['status'] = false;
            $this->serializer['errno'] = 0;
            $this->serializer['message'] = '评论失败';
            return false;
        }





    }

    /////////////////
    /**
     * @SWG\Post(path="/leave/get-leave-comment",
     *     tags={"云平台-Leave-休假"},
     *     summary="获取单条休假评论",
     *     description="",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "Token",
     *        description = "8a589f7029b0b24e09013e017b4be9cbd20414cc",
     *        required = true,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "id",
     *        description = "休假ID",
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
    public function actionGetLeaveComment(){
        $id = Yii::$app->request->post('id');

        if(empty($id)){
            $this->serializer['status'] = false;
            $this->serializer['errno'] = 0;
            $this->serializer['message'] = '参数错误';
            return false;
        }
        $LeaveComment = new LeaveComment();
        $list = $LeaveComment->getLeaveComment($id);

        $backArr = array();
        if($list){
            foreach ($list as $key => $value) {
                $arr = array();

                $arr['createTime'] = $value['created'];
                $arr['createByName'] = $value['created_by_name'];
                $arr['note']  = $value['comments'];

                $backArr[] = $arr;
            }
        }

        $this->serializer['status'] = true;
        $this->serializer['errno'] = 0;
        $this->serializer['message'] = '';
        return array('data'=>$backArr);

    }

    /**
     * @SWG\Post(path="/leave/save-leave-comment",
     *     tags={"云平台-Leave-休假"},
     *     summary="添加单条休假评论",
     *     description="",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "Token",
     *        description = "8a589f7029b0b24e09013e017b4be9cbd20414cc",
     *        required = true,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "id",
     *        description = "休假ID",
     *        required = false,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "note",
     *        description = "评论内容",
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
    public function actionSaveLeaveComment(){
        $id = Yii::$app->request->post('id');
        $note = Yii::$app->request->post('note');
        if(empty($id)||empty($note)){
            $this->serializer['status'] = false;
            $this->serializer['errno'] = 0;
            $this->serializer['message'] = '参数错误';
            return false;
        }
        $LeaveComment = new LeaveComment();


        $LeaveComment->leave_id = $id;
        $LeaveComment->created = date('Y-m-d H:i:s');
        $LeaveComment->created_by_name = $this->firstName;
        $LeaveComment->created_by_id = $this->userId;
        $LeaveComment->comments = $note;
        $LeaveComment->save();

        if($LeaveComment->id){
            $this->serializer['status'] = true;
            $this->serializer['errno'] = 0;
            $this->serializer['message'] = '评论成功';
            return true;
        }else{
            $this->serializer['status'] = false;
            $this->serializer['errno'] = 0;
            $this->serializer['message'] = '评论失败';
            return false;
        }





    }

    /**
     * @SWG\Post(path="/leave/get-emp-list",
     *     tags={"云平台-Leave-休假"},
     *     summary="根据性别或者小组id或者员工",
     *     description="",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "Token",
     *        description = "8a589f7029b0b24e09013e017b4be9cbd20414cc",
     *        required = true,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "gender",
     *        description = "性别",
     *        required = false,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "workStation",
     *        description = "小组id",
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
    public function actionGetEmpList(){
        $gender = Yii::$app->request->post('gender');
        $workStation = Yii::$app->request->post('workStation');

        if(empty($gender)){
            $gender = 0;
        }
        if(empty($workStation)){
            if($this->userId!=1&&$this->workStation){
                $workStation = $this->workStation;
            }
        }

        $Employee = new Employee();
        $list = $Employee->getEmpNumberByGenderSubunit($gender,$workStation);

        $backArr = array();

        foreach ($list as $key => $value) {
            $backArr[] = array('value'=>$value['emp_number'],'label'=>$value['emp_firstname']);

        }

        $this->serializer['status'] = true;
        $this->serializer['errno'] = 0;
        $this->serializer['message'] = '';
        return array('data'=>$backArr);

    }

    /**
     * @SWG\Get(path="/leave/leave-excel",
     *     tags={"云平台-Leave-休假"},
     *     summary="导出余假到excel",
     *     description="",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "query",
     *        name = "Token",
     *        description = "Token",
     *        required = true,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "query",
     *        name = "startDate",
     *        description = "开始时间",
     *        required = false,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "query",
     *        name = "endDate",
     *        description = "结束时间",
     *        required = false,
     *        type = "string"
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "成功，返回假期信息"
     *     ),
     *     @SWG\Response(
     *         response = 422,
     *         description = "查询失败",
     *     )
     * )
     *
     */
    public function actionLeaveExcel(){
        $workStation = $this->workStation;
        $customerId = $this->customerId;
        $startDate = Yii::$app->request->get('startDate');
        $endDate = Yii::$app->request->get('endDate');
        $isLeader = $this->isLeader;
        $emp_number = $this->empNumber;
        $leave = new Leave();
        $data = $leave->leaveExcel($workStation,$customerId,$startDate,$endDate,$isLeader,$emp_number);
        include_once '../../common/phpexcel/PHPExcel.php';
        $phpexcel = new \PHPExcel();
        //设置比标题
        $phpexcel->getActiveSheet()->setTitle('假期明细');
        $phpexcel->getActiveSheet()->mergeCells('A1:A2');
        $phpexcel->getActiveSheet()->mergeCells('B1:B2');
        $phpexcel->getActiveSheet()->mergeCells('C1:C2');
        $phpexcel->getActiveSheet()->mergeCells('D1:D2');
        $phpexcel->getActiveSheet()->mergeCells('S1:S2');
        $phpexcel->getActiveSheet()->mergeCells('E1:K1');
        $phpexcel->getActiveSheet()->mergeCells('L1:R1');

        $phpexcel->getDefaultStyle()->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $phpexcel->getDefaultStyle()->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);

//设置表头
        $phpexcel->getActiveSheet() ->setCellValue('A1','序号')
            ->setCellValue('B1','工资号')
            ->setCellValue('C1','姓名')
            ->setCellValue('D1','变动前余假')
            ->setCellValue('E2','增加调休')
            ->setCellValue('E1','增加假期')
            ->setCellValue('F2','增加法定')
            ->setCellValue('G2','增加寒假')
            ->setCellValue('H2','增加暑假')
            ->setCellValue('I2','增加婚假')
            ->setCellValue('J2','增加产假')
            ->setCellValue('K2','增加丧假')
            ->setCellValue('L2','减少调休')
            ->setCellValue('L1','减少假期')
            ->setCellValue('M2','减少法定')
            ->setCellValue('N2','减少寒假')
            ->setCellValue('O2','减少暑假')
            ->setCellValue('P2','减少婚假')
            ->setCellValue('Q2','减少产假')
            ->setCellValue('R2','减少丧假')
            ->setCellValue('S1','变更后余假');
//从数据库取得需要导出的数据
//用foreach从第二行开始写数据，因为第一行是表头

        $i=3;
        foreach($data as $val){
            $phpexcel->getActiveSheet() ->setCellValue('A'.$i,$val['sumkey'])
                ->setCellValue('B'.$i, $val['user_name'])
                ->setCellValue('C'.$i, $val['emp_firstname'])
                ->setCellValue('D'.$i, $val['levae_sum'])
                ->setCellValue('E'.$i, $val['jia1'])
                ->setCellValue('F'.$i, $val['jia2'])
                ->setCellValue('G'.$i, $val['jia3'])
                ->setCellValue('H'.$i, $val['jia4'])
                ->setCellValue('I'.$i, $val['jia5'])
                ->setCellValue('J'.$i, $val['jia6'])
                ->setCellValue('K'.$i, $val['jia7'])
                ->setCellValue('L'.$i, $val['jian1'])
                ->setCellValue('M'.$i, $val['jian2'])
                ->setCellValue('N'.$i, $val['jian3'])
                ->setCellValue('O'.$i, $val['jian4'])
                ->setCellValue('P'.$i, $val['jian5'])
                ->setCellValue('Q'.$i, $val['jian6'])
                ->setCellValue('R'.$i, $val['jian7'])
                ->setCellValue('S'.$i, $val['huo']);
            $i++;
        }





        $obj_Writer = \PHPExcel_IOFactory::createWriter($phpexcel,'Excel5');
        if($startDate != ''){
            $startDate=strtotime($startDate);
            $startDate=date('Y-m-d',$startDate);
        }
        if($endDate != ''){
            $endDate=strtotime($endDate);
            $endDate=date('Y-m-d',$endDate);
        }

            $filename ='假期明细'. date('Y-m-d').".xls";//文件名



//设置header
        ob_end_clean();
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header('Content-Disposition:inline;filename="'.$filename.'"');
        header("Content-Transfer-Encoding: binary");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Pragma: no-cache");
        $obj_Writer->save('php://output');//输出
        die();//种植执行
    }


}
