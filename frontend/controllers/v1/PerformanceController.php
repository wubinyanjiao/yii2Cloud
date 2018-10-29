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
use common\models\subunit\Subunit;
use common\models\employee\Employee;
use common\models\performance\PerformanceParam;
use common\models\performance\BonusCalculationManageConfig;
use common\models\performance\BonusCalculationConfig;

use common\models\performance\BonusCalculationManage;
use common\models\performance\BonusCalculationManageList;
use common\models\performance\BonusCalculation;
use common\models\performance\BonusCalculationList;

use common\models\leave\LeaveEntitlement;



use cheatsheet\Time;


class PerformanceController extends \common\rest\Controller
{
    public $stat_date = null;
    public $end_date = null;

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
     * @SWG\Post(path="/performance/get-search-list",
     *     tags={"云平台-Performance-绩效"},
     *     summary="获取奖金管理-查询条件",
     *     description="用户登录：成功返回用户信息；失败返回具体原因",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "Token",
     *        description = "token",
     *        required = false,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "status",
     *        description = "状态 1有默认值 0无默认值",
     *        required = false,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "status_one",
     *        description = "是否显示年份栏 1是",
     *        required = false,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "status_two",
     *        description = "是否显示月份栏 1是",
     *        required = false,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "status_three",
     *        description = "是否显示状态栏 1是",
     *        required = false,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "status_four",
     *        description = "是否显示类型 1是",
     *        required = false,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "status_five",
     *        description = "是否显示组 1是",
     *        required = false,
     *        type = "string"
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "登陆成功，返回用户信息"
     *     ),
     *     @SWG\Response(
     *         response = 422,
     *         description = "Data Validation Failed ",
     *     )
     * )
     *
     */
    public function actionGetSearchList(){

        $status = Yii::$app->request->post('status'); 
        $status_one = Yii::$app->request->post('status_one'); 
        $status_two = Yii::$app->request->post('status_two'); 
        $status_three = Yii::$app->request->post('status_three'); 
        $status_four = Yii::$app->request->post('status_four'); 
        $status_five = Yii::$app->request->post('status_five'); 

        $customerId = $this->customerId;

        $BonusCalculationManage = new BonusCalculationManage();

        $maxBonusDate =$BonusCalculationManage->getBonusCalculationManageByMaxDate($customerId);

        if($maxBonusDate['bonusDate']){
            $nowDate = $maxBonusDate['bonusDate'];
        }else{
            $nowDate = date('Y-m-d');
        }

        $list = $BonusCalculationManage->getBonusCalculationManageByDescDate($customerId);
        $only_che = false;
        
        if($status&&$status_one&&$status_two&&empty($status_three)&&empty($status_four)&&empty($status_five)){  //
            if($list){
                $nowDate = $list->bonusDate;

                if(date('m',strtotime($nowDate))==12){
                    $lastY = date('Y',strtotime('+1 year',strtotime($nowDate)));

                    $lastM = date('m',strtotime($lastY.'-'.'01-01'));
                }else{
                    $lastY =date('Y',strtotime($nowDate));
                    $lastM = date('m',strtotime('+1 month',strtotime($nowDate)));
                }

                $lastY = array($lastY);
                $lastM = array($lastM);
                $only_che = true;
            }

        }
        //var_dump(array($lastM),array($lastY));die;
        $params = Yii::$app->params;
        $date = getDiffYearAndMonth($nowDate);
        $PerformanceParam = new PerformanceParam();

        $userId = $this->userId;
        

        $check_year = array();
        $check_month = array();
        $check_type = array();
        $check_status = array();
        $check_subunit = array();

        if($status){
            $data = $PerformanceParam->getPerformanceByUserid($userId,1);
            if($data){
                $params_year = $data->year;
                $params_month = $data->month;
                $params_type = $data->type;
                $params_status = $data->status;
                $params_subunit = $data->subunit;

                if($params_year){
                    $check_year = explode(',',trim($params_year,','));
                }
                if($params_month){
                    $check_month = explode(',',trim($params_month,','));
                }
                if($params_type){
                    $check_type = explode(',',trim($params_type,','));
                }
                if($params_status){
                    $check_status = explode(',',trim($params_status,','));
                }
                if($params_subunit){
                    $check_subunit = explode(',',trim($params_subunit,','));
                }

            }
        }
        
        $backArr = array();
        if($status_one){
            if($status){
                $arr['checked'] = true;   
            }
            foreach($date['year'] as $k=>$v){
                $arr = array();
                $arr['label'] = $v.'年';
                $arr['id'] = $v; 

                if($only_che){
                    if(in_array($v, $lastY)){
                        $arr['checked'] = true;  
                    }else{
                        $arr['checked'] = false;
                    }    
                }else{
                    if(in_array($v, $check_year)){
                        $arr['checked'] = true;  
                    }else{
                        $arr['checked'] = false;
                    }
                }

                

                $backArr['year'][] = $arr;
            }
        }
        if($status_two){

            foreach($date['month'] as $k=>$v){
                $arr = array();
                $arr['label'] = $v.'月';
                $arr['id'] = $v; 
                if($only_che){
                    if(in_array($v, $lastM)){
                        $arr['checked'] = true;  
                    }else{
                        $arr['checked'] = false;
                    }
                }else{
                    if(in_array($v, $check_month)){
                        $arr['checked'] = true;  
                    }else{
                        $arr['checked'] = false;
                    }
                }
                
                $backArr['month'][] = $arr;
            }
        }
        if($status_three){
            $list = $params['performanceStatus'];

            foreach($list as $k=>$v){
                $arr = array();
                $arr['label'] = $v;
                $arr['id'] = $k; 
                if(in_array($k, $check_status)){
                    $arr['checked'] = true;  
                }else{
                    $arr['checked'] = false;
                }
                $backArr['status'][] = $arr;
            }
        }
        if($status_four){
            $list = $params['performanceBath'];

            foreach($list as $k=>$v){
                $arr = array();
                $arr['label'] = $v;
                $arr['id'] = $k; 
                if(in_array($k, $check_type)){
                    $arr['checked'] = true;  
                }else{
                    $arr['checked'] = false;
                }
                $backArr['type'][] = $arr;
            }
        }
        if($status_five){
            $Subunit = new Subunit();
            $list = $Subunit->getAllWorkStation();
            foreach($list as $k=>$v){
                $arr = array();
                $arr['label'] = $v->name;
                $arr['value'] = $v->id; 
                if(in_array($v->id, $check_subunit)){
                    $arr['checked'] = true;  
                }else{
                    $arr['checked'] = false;
                }
                $backArr['subunit'][] = $arr;
            }
        }
        $this->serializer['status'] = true;
        $this->serializer['errno'] = 0;
        $this->serializer['message'] = '查询成功'; 
        return $backArr;
    }
    /**
     * @SWG\Post(path="/performance/save-bonus-config",
     *     tags={"云平台-Performance-绩效"},
     *     summary="保存奖金发放配置",
     *     description="用户登录：成功返回用户信息；失败返回具体原因",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "Token",
     *        description = "60f5d74b625b79aafe22808f8bbddec4907f8204",
     *        required = false,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "userField",
     *        description = "工资号对应字段",
     *        required = false,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "checkingField",
     *        description = "需要校验的字段, 多个以逗号隔开",
     *        required = false,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "userGroup",
     *        description = "人员所属组配置,多个以逗号隔开 801:15,803:15",
     *        required = false,
     *        type = "string"
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "登陆成功，返回用户信息"
     *     ),
     *     @SWG\Response(
     *         response = 422,
     *         description = "Data Validation Failed ",
     *     )
     * )
     *
     */
    
    public function actionSaveBonusConfig(){

        $userField = Yii::$app->request->post('userField'); 
        $checkingField = Yii::$app->request->post('checkingField'); 
        $userGroup = Yii::$app->request->post('userGroup'); 

        $userGroup = json_decode($userGroup);
        $checkingField = json_decode($checkingField);

        $customerId = $this->customerId;
        $Subunit = new Subunit();
        $BonusCalculationManageConfig = new BonusCalculationManageConfig();
        $sub = $Subunit->getSubunitByCustomerId($customerId,1);
        $groupId = $sub->id;

        $data = $BonusCalculationManageConfig->getBonusByCustId($groupId,$customerId);

        if($data){
            $BonusCalculationManageConfig = $data;
        }

        if($userField){
            $arr['sheetFields'] = 'emp_number';
            $arr['sheetName'] = $userField;
            $arr['sheetLocationY']  = '';
            $arr['sheetLocationX']  = '';
            $userFieldJson = json_encode($arr);
            $BonusCalculationManageConfig->userField = $userFieldJson;
        }
        if($checkingField){
            $arr = array();
            $i=0;
            foreach ($checkingField as $key => $value) {
                if($value->val){
                    $arr[$i]['sheetFields'] = '';
                    $arr[$i]['sheetName'] = trim($value->val);
                    $arr[$i]['sheetLocationY'] = '';
                    $arr[$i]['sheetLocationX'] = '';
                }
                $i++;
            }
            // $arr = array();
            // $checking = explode(',', $checkingField);

            // foreach ($checking as $key => $value) {
            //     if(empty($value)){
            //         continue;
            //     }
            //     $arr[$key]['sheetFields'] = '';
            //     $arr[$key]['sheetName'] = trim($value);
            //     $arr[$key]['sheetLocationY'] = '';
            //     $arr[$key]['sheetLocationX'] = '';
            // }

            $checkingJosn = json_encode($arr);
            $BonusCalculationManageConfig->checkingField = $checkingJosn;
        }

        if($userGroup){
            $i = 0 ;
            $arr = array();
            $User = new User();
            foreach ($userGroup as $key => $value) {
                foreach ($value->emp_number as $k => $v) {
                    if(empty($v)){
                        continue;
                    }
                    $arr[$i]['emp_number'] = $v;
                    $arr[$i]['groupId'] = $value->id;
                    $user = $User->getSystemUsersByEmpNumber($v);
                    if($user){
                        $arr[$key]['userName'] = $user->user_name;
                    }else{
                        $arr[$key]['userName'] = '';
                    }
                     
                    $i++;
                }
            }



            // $arr = array();
            // $userList = explode(',', $userGroup);
            // $User = new User();
            // foreach ($userList as $key => $value) {
            //     if(empty($value)){
            //         continue;
            //     }
            //     $userval = explode(':', $value);

            //     $arr[$key]['emp_number'] = $userval[0];
            //     $arr[$key]['groupId'] = $userval[1];
                
                
                
            // }

            $userJson = json_encode($arr);
            $BonusCalculationManageConfig->userGroup = $userJson;
        }
        $BonusCalculationManageConfig->customerId = $customerId;
        $BonusCalculationManageConfig->groupId = $groupId;

        $istrue=$BonusCalculationManageConfig->save();

        if($istrue){
            $this->serializer['status'] = true;
            $this->serializer['errno'] = 0;
            $this->serializer['message'] = '添加成功';
            return false;
        }else{
            $this->serializer['status'] = false;
            $this->serializer['errno'] = 2;
            $this->serializer['message'] = '添加失败';
            return false;
        }
        
        
    }
    /**
     * @SWG\Post(path="/performance/get-bonus-config",
     *     tags={"云平台-Performance-绩效"},
     *     summary="获取奖金发放配置",
     *     description="用户登录：成功返回用户信息；失败返回具体原因",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "Token",
     *        description = "60f5d74b625b79aafe22808f8bbddec4907f8204",
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
     *         description = "Data Validation Failed ",
     *     )
     * )
     *
     */
    
    public function actionGetBonusConfig(){

        $customerId = $this->customerId;
        $Subunit = new Subunit();
        $Employee = new Employee();
        $BonusCalculationManageConfig = new BonusCalculationManageConfig();
        $sub = $Subunit->getSubunitByCustomerId($customerId,1);
        $groupId = $sub->id;

        $data = $BonusCalculationManageConfig->getBonusByCustId($groupId,$customerId);

        $backArr = array();
        if($data){
            $BonusCalculationManageConfig = $data;

            $userField = $BonusCalculationManageConfig->userField;
            $checkingField = $BonusCalculationManageConfig->checkingField;
            $userGroup = $BonusCalculationManageConfig->userGroup;

            if($userField){
                $userFieldArr = json_decode($userField);
                
                $backArr['userField'] = $userFieldArr->sheetName;
            }else{
                $backArr['userField'] = '';
            }

            if($checkingField){
                $checkingFieldArr = json_decode($checkingField);

                $arr = array();
                foreach($checkingFieldArr as $k=>$v){
                    
                    if($v->sheetName){
                        $arr[]=array('val'=>$v->sheetName,'hint'=>'请输入Excel中需要校验的列名：例如：'.$v->sheetName);
                    }
                }
                $backArr['checkingField'] = $arr;
            }else{
                $backArr['checkingField'] = array(
                            array('val'=>'','hint'=>'请输入Excel中需要校验的列名：例如：基本绩效工资'),
                            array('val'=>'','hint'=>'请输入Excel中需要校验的列名：例如：除20%外合计'),
                            //array('val'=>'','hint'=>'请输入Excel中需要校验的列名：例如：组别')
                        );
            }
            if($userGroup){
                $userGroupArr = json_decode($userGroup);
                $arr = array();


                foreach($userGroupArr as $k=>$v){ 


                    if($v->groupId&&$v->emp_number){
                        $arr[$v->groupId][] = $v->emp_number;

                        // $employee = $Employee->getEmpByNumNber($v->emp_number);
                        // $firstName = $employee->emp_firstname;

                        // $sub = $Subunit->getDepartmentName($v->groupId);
                        // $arr[]=array('label'=>$firstName,'emp_number'=>$v->emp_number,'val'=>$sub,'id'=>$v->groupId);
                    }
                }
                $bacArr = array();
                foreach ($arr as $key => $value) {
                    $label = array();
                    foreach ($value as $k => $v) {
                        $label[] =(string)$v;
                    }

                    $bacArr[] = array('id'=>(string)$key,'emp_number'=>$label);
                }
                
                $backArr['userGroup'] = $bacArr;
            }else{
                $backArr['userGroup'] = array(array('id'=>'','emp_number'=>array()));;
            }
        }else{
            $backArr['userField'] = '';
            $backArr['checkingField'] = array(
                            array('val'=>'','hint'=>'请输入Excel中需要校验的列名：例如：基本绩效工资'),
                            array('val'=>'','hint'=>'请输入Excel中需要校验的列名：例如：除20%外合计'),
                            //array('val'=>'','hint'=>'请输入Excel中需要校验的列名：例如：组别')
                        );
            $backArr['userGroup'] = array(array('id'=>'','emp_number'=>array()));

        }
        $this->serializer['status'] = true;
        $this->serializer['errno'] = 0;
        $this->serializer['message'] = '获取成功';
        return $backArr;
            

        // }else{
        //     $this->serializer['status'] = false;
        //     $this->serializer['errno'] = 2;
        //     $this->serializer['message'] = '获取失败';
        //     return false;
        // }

        
        
    }


    /**
     * @SWG\Post(path="/performance/get-bonus-manage",
     *     tags={"云平台-Performance-绩效"},
     *     summary="获取奖金管理列表",
     *     description="用户登录：成功返回用户信息；失败返回具体原因",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "Token",
     *        description = "60f5d74b625b79aafe22808f8bbddec4907f8204",
     *        required = false,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "year",
     *        description = "年份 2018,2018",
     *        required = false,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "month",
     *        description = "月份 1,3",
     *        required = false,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "status",
     *        description = "状态 0,1",
     *        required = false,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "subunit",
     *        description = "组id 1,2",
     *        required = false,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "page",
     *        description = "分页",
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
     *         description = "Data Validation Failed ",
     *     )
     * )
     *
     */
    
    public function actionGetBonusManage(){
        if($this->userName!='3204'){
            $this->serializer['status'] = false;
            $this->serializer['errno'] = 0;
            $this->serializer['message'] = '你没有权限操作绩效管理'; 
            return false;
        }
        $year = Yii::$app->request->post('year'); 
        $month = Yii::$app->request->post('month'); 
        $status = Yii::$app->request->post('status'); 
        $subunit = Yii::$app->request->post('subunit'); 
        $page = Yii::$app->request->post('page'); 
        #####查询条件存入数据库start#####
        $PerformanceParam = new PerformanceParam();
        $userId = $this->userId;
        $customerId = $this->customerId;
        $Pparm =  $PerformanceParam->getPerformanceByUserid($userId,1);

        if($Pparm){
            $PerformanceParam= $Pparm;
        }else{
            $PerformanceParam->user_id = $userId;
            $PerformanceParam->state = 1;
        }
        $PerformanceParam->year = $year;
        $PerformanceParam->month = $month;
        $PerformanceParam->status = $status;
        $PerformanceParam->subunit = $subunit;
        $PerformanceParam->save();
        #####查询条件存入数据库end#####
        
        
        $Subunit = new Subunit();
        $Employee = new Employee();

        if(empty($page)){
            $page  = 1; 
        }

        $pageSize = Yii::$app->params['pageSize']['default'];
        $BonStatus = Yii::$app->params['performanceStatus'];
        $search['limit'] = $pageSize;   //每页数 20
        $offset = ($page >= 1) ? (($page - 1) * $pageSize) : 0;
        $search['offset'] = $offset;
        $search['customerId'] = $customerId;

        if($year){
            $date = array();
            $yearArr = explode(',', trim($year,','));
            if($month){
                $monthArr = explode(',', trim($month,','));
            }else{
                $monthArr = array();
            }

            foreach($yearArr as $k=>$v){
                if($monthArr){
                    foreach($monthArr as $ks=>$vs){
                        $dat = $v.'-'.$vs.'-'.'1';
                        $date[] = date('Y-m-d',strtotime($dat));
                    }
                    
                }else{
                    for($i=1;$i<=12;$i++){
                        $dat = $v.'-'.$i.'-'.'1';
                        $date[] = date('Y-m-d',strtotime($dat));
                    }
                }
            }
            $search['year'] = $date;
        }else{
            $search['year'] = null;
        }

        if($status){
            $statusArr = explode(',', trim($status,','));
            $search['status'] = $statusArr;
        }else{
            $search['status'] = null;
        }

        if($subunit){
            $subunitArr = explode(',', trim($subunit,','));
            $search['subunit'] = $subunitArr;
        }else{
            $search['subunit'] = null;
        }

       
        $BonusCalculationManage = new BonusCalculationManage();
        $data = $BonusCalculationManage->getBonusCalculationManageSearch($search);
        $count = $data['count'];
        $backArr = array();
        $i = 1;
        foreach ($data['list'] as $key => $value) {
            $arr = array();

            $arr['serialNumber'] = $i+$offset;
            $arr['id'] = $value->id;
            $arr['date'] = date('Y年m月',strtotime($value->bonusDate));
            $arr['subunitName'] = $value->subunit->name;
            $arr['status'] = $BonStatus[$value->status];
            $arr['statusId'] = $value->status;

            $backArr[] = $arr;
            $i++;
        }

        if($backArr){
            $this->serializer['status'] = true;
            $this->serializer['errno'] = 0;
            $this->serializer['message'] = '获取成功';
            return array('data'=>$backArr,'totalCount'=>(int)$count,'current_page'=>(int)$page,'pageSize'=>(int)$pageSize);
            
        }else{

            $this->serializer['status'] = false;
            $this->serializer['errno'] = 2;
            $this->serializer['message'] = '暂无数据';
            return false;
        }

        
        
    }


    /**
     * @SWG\Post(path="/performance/add-bonus-manage",
     *     tags={"云平台-Performance-绩效"},
     *     summary="新增主任奖金计算",
     *     description="用户登录：成功返回用户信息；失败返回具体原因",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "Token",
     *        description = "60f5d74b625b79aafe22808f8bbddec4907f8204",
     *        required = false,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "year",
     *        description = "年份2018",
     *        required = false,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "month",
     *        description = "月份 3",
     *        required = false,
     *        type = "string"
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "登陆成功，返回用户信息"
     *     ),
     *     @SWG\Response(
     *         response = 422,
     *         description = "Data Validation Failed ",
     *     )
     * )
     *
     */
    public function actionAddBonusManage(){
        
        $year = Yii::$app->request->post('year'); 
        $month = Yii::$app->request->post('month'); 

        $SheeetHost =  env('SHEET_HOST_INFO');
        if(empty($year)||empty($month)){
            $this->serializer['status'] = false;
            $this->serializer['errno'] = 2;
            $this->serializer['message'] = '请选择日期';
            return false;
        }

        $time = strtotime($year.'-'.$month);
        if(!$time){
            $this->serializer['status'] = false;
            $this->serializer['errno'] = 2;
            $this->serializer['message'] = '日期格式错误';
            return false;
        }

        $bonusDate = date('Y-m-d',$time);
        $bonusDateM = date('Ym',$time);
        $customerId = $this->customerId;
        if(!$customerId){
            $this->serializer['status'] = false;
            $this->serializer['errno'] = 2;
            $this->serializer['message'] = '客户id错误';
            return false;
        }
        $isBase = 1;
        $status = 0;
        $Subunit = new Subunit();
        $sub = $Subunit->getSubunitByCustomerId($customerId,1);
        $groupId = $sub->id;
        $sheetName = 'bonus.'.$customerId.'.'.$groupId.'.'.$bonusDateM.'.manage';
        $BonusCalculationManage = new BonusCalculationManage();

        $bon = $BonusCalculationManage->getBonusCalculationManage($customerId,$bonusDate,$groupId);
        if(!$bon){
            $BonusCalculationManage->customerId = $customerId;
            $BonusCalculationManage->bonusDate = $bonusDate;
            $BonusCalculationManage->groupId = $groupId;
            $BonusCalculationManage->isBase = $isBase;
            $BonusCalculationManage->status = $status;
            $BonusCalculationManage->sheetName = $sheetName;
            $BonusCalculationManage->create_time = date('Y-m-d H:i:s');
            $true = $BonusCalculationManage->save();

            if($true){
                $id = $BonusCalculationManage->id;
                $backArr = array('sheetName'=>$sheetName,'url'=>$SheeetHost.$sheetName,'id'=>$id);
                $this->serializer['status'] = true;
                $this->serializer['errno'] = 0;
                $this->serializer['message'] = '添加成功';
                return $backArr;
            }else{
                $this->serializer['status'] = false;
                $this->serializer['errno'] = 2;
                $this->serializer['message'] = '添加失败';
                return false;
            }
        }else{
            $this->serializer['status'] = false;
            $this->serializer['errno'] = 2;
            $this->serializer['message'] = '你已经添加过了';
            return false;
        }
    }

    /**
     * @SWG\Post(path="/performance/see-bonus-manage",
     *     tags={"云平台-Performance-绩效"},
     *     summary="下发前预览",
     *     description="用户登录：成功返回用户信息；失败返回具体原因",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "Token",
     *        description = "60f5d74b625b79aafe22808f8bbddec4907f8204",
     *        required = false,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "sheetName",
     *        description = "excel名称",
     *        required = false,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "id",
     *        description = "manage表id",
     *        required = false,
     *        type = "string"
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "登陆成功，返回用户信息"
     *     ),
     *     @SWG\Response(
     *         response = 422,
     *         description = "Data Validation Failed ",
     *     )
     * )
     *
     */
    public function actionSeeBonusManage(){

        $sheetName = Yii::$app->request->post('sheetName');
        $id = Yii::$app->request->post('id');
        $SheeetHost =  env('SHEET_HOST_INFO');
        //$url = $SheeetHost.$sheetName.'.csv.json';
        $url = $SheeetHost.$sheetName.'.csv.json';

        $response = getCURLByExcel($url,1,'PUT');
        $response = json_decode($response);
        $customerId = $this->customerId;


        if($response){
            if(count($response)<2){
                $this->serializer['status'] = false;
                $this->serializer['errno'] = 2;
                $this->serializer['message'] = '你还没上传excel文件';
                return false;
            }


            $Subunit = new Subunit();
            $Employee = new Employee();
            $User  = new User();
            $BonusCalculationManage = new BonusCalculationManage();

            $result = $BonusCalculationManage->getBonusCalculationManageById($id);

            if(!$result){
                $this->serializer['status'] = false;
                $this->serializer['errno'] = 2;
                $this->serializer['message'] = '你还没上传excel文件';
                return false;
            }

            $bonusDate = $result->bonusDate;

            $subArr = $Subunit->getWorkStationNameById($customerId);
            $BonusCalculationManageConfig = new BonusCalculationManageConfig();
            $sub = $Subunit->getSubunitByCustomerId($customerId,1);
            $groupId = $sub->id;
            $data = $BonusCalculationManageConfig->getBonusByCustId($groupId,$customerId);

            if($data){
                $BonusCalculationManageConfig = $data;

                $userField = $BonusCalculationManageConfig->userField;
                $checkingField = $BonusCalculationManageConfig->checkingField;
                $userGroup = $BonusCalculationManageConfig->userGroup;

                if($userField){
                    $userFieldArr = json_decode($userField);    
                    $gongzihao = $userFieldArr->sheetName;
                }else{
                    $gongzihao = '工资号';
                }

                if($checkingField){
                    $checkingFieldArr = json_decode($checkingField);
                    $arr = array();
                    $percent20 = $checkingFieldArr[0]->sheetName;
                    $percent80 = $checkingFieldArr[1]->sheetName;
                    $zu = '组别';
                    //$zu = $checkingFieldArr[2]->sheetName;
                }else{
                    $percent20 = '基本绩效工资';
                    $percent80 = '除20%外合计';
                    $zu = '组别';
                }


                if($userGroup){
                    $userGroupArr = json_decode($userGroup);
                    
                   
                    foreach($userGroupArr as $k=>$v){ 
                        if($v->groupId&&$v->emp_number){
                            $employee = $Employee->getEmpByNumNber($v->emp_number);
                            $firstName = $employee->emp_firstname;
                            $sub = $Subunit->getDepartmentName($v->groupId);
                            //$GroupArr[$v->userName]=$sub;

                            if(!empty($v->userName)){
                                $userName = $v->userName;
                            }else{
                               $user = $User->getSystemUsersByEmpNumber($v->emp_number);

                                $userName = $user->user_name;
                            }
                            
                            $GroupArr[$userName]=$sub;
                        }
                    }
                    
                }else{
                   $GroupArr = array();
                }

            }else{
                $gongzihao = '工资号';
                $percent20 = '基本绩效工资';
                $percent80 = '除20%外合计';
                $zu = '组别';
                $GroupArr = array();
            }
            
            $arr = array();
            $header = $response['0'];

            if(!in_array($gongzihao,$header)){
                $this->serializer['status'] = false;
                $this->serializer['errno'] = 2;
                $this->serializer['message'] = '奖金配置工资号列验证不成功';
                return false;
            }
            if(!in_array($percent20,$header)){
                $this->serializer['status'] = false;
                $this->serializer['errno'] = 2;
                $this->serializer['message'] = '奖金配置基本工资列验证不成功';
                return false;
            }
            if(!in_array($percent80,$header)){
                $this->serializer['status'] = false;
                $this->serializer['errno'] = 2;
                $this->serializer['message'] = '奖金配置除20%外合计列验证不成功';
                return false;
            }
            unset($response[0]);
            
            $list = array();
            // foreach ($header as $key => $value) {
     
            //     foreach ($response as $k => $v) {
            //         if($value==$zu){
            //             $arr[]  = $v[$key];
            //         }
                    
            //     }
            // }

            foreach ($response as $key => $value) {
                $ar = array();
                foreach ($header as $ks => $vs) {
                    
                    $ar[trim($vs)] = trim($value[$ks]);
                }

                $user = $User->getSystemUsersByUserName(trim($ar[$gongzihao]));

                if($user){

                    $old_station = $User->getEmployeeWorkstation($user->emp_number,$bonusDate);

                    $work_station = $old_station['id'];

                    $arr[] = $subArr[$work_station];
                    $ar[$zu] = $subArr[$work_station];
                    $list[] = $ar;
                }else{
                    //找不到的员工
                }
                
            }
            
            
            $arr = array_unique($arr);

            $empArr = array();
            foreach ($arr as $key => $value) {
                $empArr[$value] = array();
            }

            foreach ($list as $k => $v) {

                 // if($k<1){
                 //    continue;
                 // }  
                $lc  = $v[$gongzihao];

                if(!empty($GroupArr[$lc])){
                    if(empty($empArr[$GroupArr[$lc]]['人数'])){
                        $empArr[$GroupArr[$lc]]['人数'] = 0;
                    }
                    if(empty($empArr[$GroupArr[$lc]][$percent20])){
                        $empArr[$GroupArr[$lc]][$percent20] = 0;
                    }
                    if(empty($empArr[$GroupArr[$lc]][$percent80])){
                        $empArr[$GroupArr[$lc]][$percent80] = 0;
                    }
                    $empArr[$GroupArr[$lc]]['人数'] += 1;
                    $empArr[$GroupArr[$lc]][$percent20] +=(float) $v[$percent20];
                    $empArr[$GroupArr[$lc]][$percent80] +=(float) $v[$percent80];
                    $empArr[$GroupArr[$lc]]['组名'] = $GroupArr[$lc];
                }else{

                    if(empty($empArr[$v[$zu]]['人数'])){
                        $empArr[$v[$zu]]['人数'] = 0;
                    }
                    if(empty($empArr[$v[$zu]][$percent20])){
                        $empArr[$v[$zu]][$percent20] = 0;
                    }
                    if(empty($empArr[$v[$zu]][$percent80])){
                        $empArr[$v[$zu]][$percent80] = 0;
                    }

                    $abd = 0 ;
                    $percentCount = 0 ;
                    foreach ($v as $ks => $vs) {
                        if($abd==1){
                            $percentCount +=(float) $vs;
                        }
                        if(trim($ks)==trim($percent20)){
                            $abd=1;
                        }
                    }


                    $empArr[$v[$zu]]['人数'] += 1;
                    $empArr[$v[$zu]][$percent20] +=(float) $v[$percent20];
                    $empArr[$v[$zu]][$percent80] +=(float) $percentCount;
                    $empArr[$v[$zu]]['组名'] = $v[$zu];
                }
                
            }

            $backHeader[] = array('title'=>'组名','key'=>'subName','align'=>'center');
            $backHeader[] = array('title'=>'人数','key'=>'peopleConut','align'=>'center');
            $backHeader[] = array('title'=>'基本工资','key'=>'percent20','align'=>'center');
            $backHeader[] = array('title'=>'除20%外合计','key'=>'percent80','align'=>'center');
            $backEnd = array();
            $backArr = array();
            $peopleConut = 0;
            $per20 =  0 ;
            $per80  = 0 ;
            $i = 0 ;

           
            foreach ($empArr as $key => $value) {
                $arr = array();
     
                $arr['subName'] = $value['组名'];
                $arr['peopleConut'] = $value['人数'];
                $arr['percent20'] = $value[$percent20];
                $arr['percent80'] = $value[$percent80];

                $peopleConut += $value['人数'];
                $per20 += $value[$percent20];
                $per80 += $value[$percent80];

                $backArr[] = $arr;

            }

            $backEnd['subName'] = '合计';
            $backEnd['peopleConut'] = $peopleConut;
            $backEnd['percent20'] = $per20;
            $backEnd['percent80'] = $per80;
            // array_unshift($backArr,$backHeader);
            array_push($backArr,$backEnd);

            $this->serializer['status'] = true;
            $this->serializer['errno'] = 0;
            $this->serializer['message'] = '';
            return array('header'=>$backHeader,'content'=>$backArr);
            
        }else{
            $this->serializer['status'] = false;
            $this->serializer['errno'] = 2;
            $this->serializer['message'] = '获取数据失败';
            return false;
        }
       

    }

    /**
     * @SWG\Post(path="/performance/confirm-distribute",
     *     tags={"云平台-Performance-绩效"},
     *     summary="确认分发",
     *     description="用户登录：成功返回用户信息；失败返回具体原因",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "Token",
     *        description = "60f5d74b625b79aafe22808f8bbddec4907f8204",
     *        required = false,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "sheetName",
     *        description = "excel名称",
     *        required = false,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "id",
     *        description = "manage表id",
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
     *         description = "Data Validation Failed ",
     *     )
     * )
     *
     */
    public function actionConfirmDistribute(){

        $sheetName = Yii::$app->request->post('sheetName');
        $id = Yii::$app->request->post('id');
        $SheeetHost =  env('SHEET_HOST_INFO');
       
        $url = $SheeetHost.$sheetName.'.csv.json';

        $response = getCURLByExcel($url,1,'PUT');
        $response = json_decode($response);
        $customerId = $this->customerId;
       
        if($response){
            if(count($response)<2){
                $this->serializer['status'] = false;
                $this->serializer['errno'] = 2;
                $this->serializer['message'] = '你还没上传excel文件';
                return false;
            }
            $Subunit = new Subunit();
            $Employee = new Employee();
            $User = new User();
            $BonusCalculationManage = new BonusCalculationManage();
            $BonusCalculationManageConfig = new BonusCalculationManageConfig();

            $manage = $BonusCalculationManage->getBonusCalculationManageById($id);

            if(!empty($manage)){
                if($manage->status!=0){
                    $this->serializer['status'] = false;
                    $this->serializer['errno'] = 2;
                    $this->serializer['message'] = '此次下发的excel状态不对,已经下发过了';
                    return false;
                }
            }else{
                $this->serializer['status'] = false;
                $this->serializer['errno'] = 2;
                $this->serializer['message'] = '参数错误';
                return false;
            }

            $sub = $Subunit->getSubunitByCustomerId($customerId,1);
            $allSubArr = $Subunit->getWorkStationNameById($customerId);
            $groupId = $sub->id;
            $data = $BonusCalculationManageConfig->getBonusByCustId($groupId,$customerId);

            if($data){
                $BonusCalculationManageConfig = $data;

                $userField = $BonusCalculationManageConfig->userField;
                $checkingField = $BonusCalculationManageConfig->checkingField;
                $userGroup = $BonusCalculationManageConfig->userGroup;

                if($userField){
                    $userFieldArr = json_decode($userField);    
                    $gongzihao = $userFieldArr->sheetName;
                }else{
                    $gongzihao = '工资号';
                }

                if($checkingField){
                    $checkingFieldArr = json_decode($checkingField);
                    $arr = array();
                    $percent20 = $checkingFieldArr[0]->sheetName;
                    $percent80 = $checkingFieldArr[1]->sheetName;
                    //$zu = $checkingFieldArr[2]->sheetName;
                    $zu = '组别';
                }else{
                    $percent20 = '基本绩效工资';
                    $percent80 = '除20%外合计';
                    $zu = '组别';
                }

                if($userGroup){
                    $userGroupArr = json_decode($userGroup);
                    $arr = array();              
                    foreach($userGroupArr as $k=>$v){ 
                        if($v->groupId&&$v->emp_number){
                            $employee = $Employee->getEmpByNumNber($v->emp_number);
                            $firstName = $employee->emp_firstname;
                            $sub = $Subunit->getDepartmentName($v->groupId);

                            if(!empty($v->userName)){
                                $userName = $v->userName;
                            }else{
                               $user = $User->getSystemUsersByEmpNumber($v->emp_number);

                                $userName = $user->user_name;
                            }

                            //$userName = ltrim($v->userName,'0');
                            $GroupArr[$userName]=$sub;
                        }
                    }
                    
                }else{
                   $GroupArr = array();
                }

            }else{
                $gongzihao = '工资号';
                $percent20 = '基本绩效工资';
                $percent80 = '除20%外合计';
                $zu = '组别';
                $GroupArr = array();
            }
            $header = $response['0'];

            if(!in_array($gongzihao,$header)){
                $this->serializer['status'] = false;
                $this->serializer['errno'] = 2;
                $this->serializer['message'] = '奖金配置工资号列验证不成功';
                return false;
            }
            if(!in_array($percent20,$header)){
                $this->serializer['status'] = false;
                $this->serializer['errno'] = 2;
                $this->serializer['message'] = '奖金配置基本工资列验证不成功';
                return false;
            }
            if(!in_array($percent80,$header)){
                $this->serializer['status'] = false;
                $this->serializer['errno'] = 2;
                $this->serializer['message'] = '奖金配置除20%外合计列验证不成功';
                return false;
            }
            unset($response[0]);
            //unset($response[1]);
            
            $list = array();
            $arr = array();

      
            // foreach ($header as $key => $value) {
     
            //     foreach ($response as $k => $v) {
            //         if(trim($value)==trim($zu)){
            //             $arr[]  = $v[$key];
            //         }
                    
            //     }
            // }

            $headerCon = array();
            $s = 1;
            foreach ($response as $key => $value) {
                $ar = array();
                $d = 1;
                foreach ($header as $ks => $vs) {
                    
                    $ar[trim($vs)] = trim($value[$ks]);
                    if($s==1){
                        $headerCon[$d] = trim($vs);
                        $d++;
                    }

                    // $user = $User->getSystemUsersByUserName(trim($ar[$gongzihao]));

                    // if($user){
                    //     $work_station = $user->employee->work_station;

                    
                    //     $arr[] = $subArr[$work_station];
                    //     $ar[$zu] = $subArr[$work_station];
                    //     $list[] = $ar;
                    // }
                    
                }




                $s++;
                $list[] = $ar;


            }
 
            $arr = array_unique($arr);
            $empArr = array();
            // foreach ($arr as $key => $value) {

            //     $subunit = $Subunit->getSubunitByName(trim($value));

            //     if(!$subunit){
    
            //         $this->serializer['status'] = false;
            //         $this->serializer['errno'] = 2;
            //         $this->serializer['message'] = '上传的文件组别错误';
            //         return false;
            //     }
            //     $subArr[$value] = $subunit['id'];
            // }
    
            $manage->status=10;
            $mangeId = $manage->id;
            $bonusDate = $manage->bonusDate;
            if(empty($list)){
                $this->serializer['status'] = false;
                $this->serializer['errno'] = 2;
                $this->serializer['message'] = '获取数据失败';
                return false;
            }
            $subArr = array();
            $calculation_id = null;
            $manageId = null;
            $bonArr = array();
            foreach ($list as $k => $v) {
                $lc  = ltrim($v[$gongzihao],'0');
                $lc  = $v[$gongzihao];
                $user = $User->getSystemUsersByUserName($v[$gongzihao]);


                if($user){
                    $BonusCalculationManageList = new BonusCalculationManageList();
                    $emp_number = $user->emp_number;
                    $las = $BonusCalculationManageList->getBonusCalculationManageListByDate($bonusDate,$emp_number);
                    if($las){
                        $BonusCalculationManageList = $las;
                        
                    }
                    $BonusCalculationManageList->bonus_id = $mangeId;
                    $BonusCalculationManageList->customerId = $customerId;
                    $BonusCalculationManageList->bonusDate = $bonusDate;
                    // if(!empty($GroupArr[$lc])){
                    //     $empSub = $Subunit->getSubunitByName($GroupArr[$lc]);
                    // }else{

                    //     $empSub = $Subunit->getSubunitByName($v[$zu]);
                    // }
                    $old_station = $User->getEmployeeWorkstation($user->emp_number,$bonusDate);

                    $work_station = $old_station['id'];
                    $workStationName = $old_station['name'];
                    //$work_station = $user->employee->work_station;
                    //$workStationName  = $allSubArr[$work_station];
                    $BonusCalculationManageList->groupId = $work_station;
                    $BonusCalculationManageList->status = 10;
                    $BonusCalculationManageList->emp_number = $user->emp_number;
                    $BonusCalculationManageList->percent20 = $v[$percent20];

                    $abd = 0 ;
                    $percentCount = 0 ;
                    foreach ($v as $ks => $vs) {
                        if($abd==1){
                            $percentCount +=(float) $vs;
                        }
                        if(trim($ks)==trim($percent20)){
                            $abd=1;
                        }
                    }


                    $BonusCalculationManageList->percent80 = $percentCount;
                    //$v[$percent80] = $percentCount;
                    $v[$zu] = $workStationName;
                    $temp = $v;

                    $BonusCalculationManageList->sheetInfo = json_encode($temp);
                    $BonusCalculationManageList->create_time = date('Y-m-d H:i:s');
                    $BonusCalculationManageList->save();
                    
                    if(!in_array($BonusCalculationManageList->groupId,$subArr)){

                        $BonusCalculationManage = new BonusCalculationManage();
                        $man = $BonusCalculationManage->getBonusCalculationManageBySub($bonusDate,$BonusCalculationManageList->groupId);

                        if(!$man){
                            $BonusCalculationManage->customerId = $customerId;
                            $BonusCalculationManage->bonusDate = $bonusDate;
                            $BonusCalculationManage->isBase = 0;
                            $BonusCalculationManage->groupId = $BonusCalculationManageList->groupId;

                            
                            $bonusDateM = date('Ym',strtotime($bonusDate));
                            $BonusCalculationManage->status = 10;
                            $sheetName = 'bonus.'.$customerId.'.'.$BonusCalculationManageList->groupId.'.'.$bonusDateM.'.manage';
                            $BonusCalculationManage->sheetName = $sheetName;
                            $BonusCalculationManage->sendTime = date('Y-m-d H:i:s');
                            $BonusCalculationManage->create_time = date('Y-m-d H:i:s');
                            $BonusCalculationManage->save();
                            $manageId= $BonusCalculationManage->id;
                        }else{
                            $manageId= $man->id;
                            $BonusCalculationManage = $man;
                        }


                        //创建 组长表
                        $BonusCalculation = new BonusCalculation();
                        $bonusCalculation = $BonusCalculation->getBonusCalculationBySub($bonusDate,$BonusCalculationManageList->groupId);

                        if(!$bonusCalculation){
                            array_push($subArr, $BonusCalculationManageList->groupId);
                            $BonusCalculation->bonus_id = $manageId;
                            $BonusCalculation->customerId = $customerId;
                            $BonusCalculation->bonusDate = $bonusDate;
                            $BonusCalculation->groupId = $BonusCalculationManageList->groupId;
                            $BonusCalculation->status = 10;
                            // $bonusDateM = date('Ymd',strtotime($bonusDate));
                            // $sheetName = $customerId.'.'.$bonusDateM.'.'.$BonusCalculationManageList->groupId;
                            $BonusCalculation->sheetName = $BonusCalculationManage->sheetName;
                            $BonusCalculation->receiveTime = date('Y-m-d H:i:s');
                            $BonusCalculation->save();
                            $calculation_id = $BonusCalculation->id;
                            if($headerCon){
                                $BonusCalculationConfig = new BonusCalculationConfig();

                                $bs = $BonusCalculationConfig->getBonusCalculationConBySub($BonusCalculationManageList->groupId);

                                if($bs){
                                    $BonusCalculationConfig = $bs;
                                }
                                $BonusCalculationConfig->groupId = $BonusCalculationManageList->groupId;
                                $BonusCalculationConfig->customerId = $customerId;
                                $BonusCalculationConfig->salarySheetField = null;
                                $BonusCalculationConfig->allSheetField = json_encode($headerCon) ;
                                $BonusCalculationConfig->save();

                            }


                        }else{
                            $calculation_id = $bonusCalculation->id;
                        }
                        $bonArr[$BonusCalculationManageList->groupId] = array('bonus_id'=>$manageId,'calculation_id'=>$calculation_id);

                    }

                    if($calculation_id){
                        $BonusCalculationList = new BonusCalculationList();
                        $calst = $BonusCalculationList->getBonusCalculationListByDate($bonusDate,$emp_number,$BonusCalculationManageList->groupId);
                        if($calst){
                            $BonusCalculationList = $calst;
                        }
                        $BonusCalculationList->bonus_id = $bonArr[$BonusCalculationManageList->groupId]['bonus_id'];
                        $BonusCalculationList->calculation_id = $bonArr[$BonusCalculationManageList->groupId]['calculation_id'];; 
                        $BonusCalculationList->customerId = $customerId;
                        $BonusCalculationList->bonusDate = $bonusDate;
                        $BonusCalculationList->groupId = $BonusCalculationManageList->groupId;
                        $BonusCalculationList->status = 10;
                        $BonusCalculationList->emp_number = $emp_number;
                        $BonusCalculationList->percent20 = $v[$percent20];
                        //$BonusCalculationList->percent80 = $percentCount;
                        $v[$zu] = $workStationName;
                        $temp = $v;
                        $BonusCalculationList->sheetInfo = json_encode($temp);
                        $BonusCalculationList->create_time = date('Y-m-d H:i:s');
                        $BonusCalculationList->save();

                    }
                }
   
                
            }

            $manage->save();
            $BonusCalculationManage->saveExcelByManange($bonusDate,$customerId,$header);

            $this->serializer['status'] = true;
            $this->serializer['errno'] = 0;
            $this->serializer['message'] = '下发成功';
            return true;
            
        }else{
            $this->serializer['status'] = false;
            $this->serializer['errno'] = 2;
            $this->serializer['message'] = '分发失败!';
            return false;
        }
       

    }


    /**
     * @SWG\Post(path="/performance/get-bonus-calculation",
     *     tags={"云平台-Performance-绩效"},
     *     summary="组长获取奖金列表",
     *     description="用户登录：成功返回用户信息；失败返回具体原因",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "Token",
     *        description = "60f5d74b625b79aafe22808f8bbddec4907f8204",
     *        required = false,
     *        type = "string"
     *     ),
     *     
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "status",
     *        description = "状态 0待上报,1已上报",
     *        required = false,
     *        type = "string"
     *     ),

     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "page",
     *        description = "分页",
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
     *         description = "Data Validation Failed ",
     *     )
     * )
     *
     */
    
    public function actionGetBonusCalculation(){
        $status = Yii::$app->request->post('status'); 
        $subunit = $this->workStation; 
        $page = Yii::$app->request->post('page'); 
        
        
        
        $Subunit = new Subunit();
        $Employee = new Employee();

        if(empty($page)){
            $page  = 1; 
        }

        $pageSize = Yii::$app->params['pageSize']['default'];

        $search['limit'] = $pageSize;   //每页数 20
        $offset = ($page >= 1) ? (($page - 1) * $pageSize) : 0;
        $search['offset'] = $offset;

        if($status){
            $search['status'] = $status;
        }else{
            $search['status'] = null;
        }

        if($subunit){
            $search['subunit'] = $subunit;
        }else{
            $search['subunit'] = null;
        }

       
        $BonusCalculation = new BonusCalculation();
        $data = $BonusCalculation->getBonusCalculationSearch($search);

        $count = $data['count'];
        $backArr = array();
        $i = 1;
        foreach ($data['list'] as $key => $value) {
            $arr = array();

            $arr['serialNumber'] = $i+$offset;
            $arr['id'] = $value->id;
            $arr['date'] = date('Y年m月',strtotime($value->bonusDate));
            $arr['subunitName'] = $value->subunit->name;
            if($value->status==11){
                $arr['status'] = '已上报';
            }else if($value->status==1){
                $arr['status'] = '确认归档';
            }else{
                $arr['status'] = '未上报';
            }

            if($value->receiveTime){
                $arr['receiveTime'] = date('Y-m-d',strtotime($value->receiveTime));
            }else{
                $arr['receiveTime'] = '';
            }
            if($value->submitTime){
                $arr['submitTime'] = date('Y-m-d',strtotime($value->submitTime));
            }else{
                $arr['submitTime'] = '';
            }
            
            $arr['statusId'] = $value->status;

            $backArr[] = $arr;
            $i++;
        }

        if($backArr){
            $this->serializer['status'] = true;
            $this->serializer['errno'] = 0;
            $this->serializer['message'] = '获取成功';
            return array('data'=>$backArr,'totalCount'=>(int)$count,'current_page'=>(int)$page,'pageSize'=>(int)$pageSize);
            
        }else{

            $this->serializer['status'] = false;
            $this->serializer['errno'] = 2;
            $this->serializer['message'] = '暂无数据';
            return false;
        }

        
        
    }


    /**
     * @SWG\Post(path="/performance/get-bonus-calculation-manage-detail",
     *     tags={"云平台-Performance-绩效"},
     *     summary="获取奖金分配详情页",
     *     description="用户登录：成功返回用户信息；失败返回具体原因",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "Token",
     *        description = "60f5d74b625b79aafe22808f8bbddec4907f8204",
     *        required = false,
     *        type = "string"
     *     ),
     *     
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "status",
     *        description = "状态 0默认10已下发未上报11已下发已上报1确认归档",
     *        required = false,
     *        type = "string"
     *     ),

     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "id",
     *        description = "ID",
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
     *         description = "Data Validation Failed ",
     *     )
     * )
     *
     */
    
    public function actionGetBonusCalculationManageDetail(){
        if($this->userName!='3204'){
            $this->serializer['status'] = false;
            $this->serializer['errno'] = 0;
            $this->serializer['message'] = '你没有权限操作绩效管理'; 
           // return false;
        }
        $status = Yii::$app->request->post('status'); 
        $id = Yii::$app->request->post('id'); 
        
        $customerId = $this->customerId;
        $BonusCalculationManage = new BonusCalculationManage();

        $data = $BonusCalculationManage->getBonusCalculationManageById($id);

        if($data){
            $sheetName = $data->sheetName;
            $Subunit = new Subunit();
            $topSub = $Subunit->getSubunitByCustomerId($customerId,1);   //顶级组

            $topGroupId = $topSub['id'];
            $groupId = $data->groupId;

            

            $backArr['showPro'] = array();   //status = 0时返回的内容
            $backArr['jiaoDui'] = array();   //status = 11时   
            $backArr['showHui'] = array();   //status = 1;
            if($data->status==0){
                $status  = '未下发';
                $backArr['showPro'][] = array('title'=>'奖金日期','val'=>date('Y年m月',strtotime($data->bonusDate))) ;
                $backArr['showPro'][] = array('title'=>'状态','val'=>$status) ;
            }else if($data->status==1){
                $status  = '确认归档';
                $back = $BonusCalculationManage->getContentByDate($data->id,$groupId,$topGroupId,$data->bonusDate,$status);
                $backArr['showPro'] = $back;

                if($groupId==$topGroupId){
                    $backHui = $BonusCalculationManage->huiContentByDate($data->id,$groupId,$topGroupId,$data->bonusDate);
                    $backArr['showHui'] = $backHui;

                    $newSheetName = $BonusCalculationManage->getBonusCalculationManageNewSheetName($data->bonusDate,$data->customerId);

                    if($newSheetName){
                        $sheetName = $newSheetName;
                    }
                }

            }else if($data->status==10){
                $status  = '已下发未上报';

                $back = $BonusCalculationManage->getContentByDate($data->id,$groupId,$topGroupId,$data->bonusDate,$status);
                $backArr['showPro'] = $back;
            }else if($data->status==11){
                $status  = '已下发已上报';
                $back = $BonusCalculationManage->getContentByDate($data->id,$groupId,$topGroupId,$data->bonusDate,$status);
                $backArr['showPro'] = $back;

                $xiaodui = $BonusCalculationManage->xiaoduiContentByDate($data->id,$groupId,$topGroupId,$data->bonusDate);
                $backArr['jiaoDui'] = $xiaodui;
            }


            $backArr['statusId'] = $data->status;
            $backArr['id'] = $data->id;
            $backArr['bonusId'] = $data->id;
            $backArr['sheetName'] = $sheetName;
            $backArr['bonusDate'] = $data->bonusDate;
            $backArr['sheetNameUrl'] = env('SHEET_HOST_INFO').$sheetName;


            $this->serializer['status'] = true;
            $this->serializer['errno'] = 0;
            $this->serializer['message'] = '';
            return $backArr;
        }else{
            $this->serializer['status'] = false;
            $this->serializer['errno'] = 2;
            $this->serializer['message'] = '获取失败';
            return false;
        }
        
        
    }

    /**
     * @SWG\Post(path="/performance/upload-excel",
     *     tags={"云平台-Performance-绩效"},
     *     summary="详情页上传excel文件",
     *     description="用户登录：成功返回用户信息；失败返回具体原因",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "Token",
     *        description = "60f5d74b625b79aafe22808f8bbddec4907f8204",
     *        required = false,
     *        type = "string"
     *     ),
     *     
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "bonusManageExcel",
     *        description = "上传文件字段",
     *        required = false,
     *        type = "file"
     *     ),

     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "id",
     *        description = "ID(22号新改成用 bonusId)",
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
     *         description = "Data Validation Failed ",
     *     )
     * )
     *
     */
    
    public function actionUploadExcel(){
        $bonusManageExcel = $_FILES;


        $url = $bonusManageExcel['bonusManageExcel']['name'];

        $houzi = substr(strrchr($url, '.'), 1);

        if($houzi!='xls'&&$houzi!='xlsx'){
            $this->serializer['status'] = false;
            $this->serializer['errno'] = 0;
            $this->serializer['message'] = '文件类型不对';
            return true;
        }
    
        $id = Yii::$app->request->post('id'); 

        $u = uplaode_excel_files($bonusManageExcel,'excel','bonusManageExcel');
        $BonusCalculationManage = new BonusCalculationManage();
        $User = new User();
        $upList = array();
        $customerId = $this->customerId;
        if($u){
            $upList = uplaode_files_by_excel($u,0);
            $list = array();
            if(count($upList)>=2){  //验证列名是否正确
                $header = $upList[1];

                $istrue = $BonusCalculationManage->verificationExcelList($header,$customerId);

                
                if($istrue['status']){
                    $backArr = $istrue['back'];   //返回的列名

                    $gongzihao = $backArr['gongzihao'];
                    $percent20 = $backArr['percent20'];
                    $percent80 = $backArr['percent80'];

                    unset($upList[1]);
                    $s = 1;
                    foreach ($upList as $key => $value) {
                        $ar = array();
                        $d = 1;
                        foreach ($header as $ks => $vs) {
                            
                            $ar[trim($vs)] = trim($value[$ks]);
                            if($s==1){
                                $headerCon[$d] = trim($vs);
                                $d++;
                            }
                            
                        }
                        $s++;
                        $list[] = $ar;
                    }
                }else{
                    $this->serializer['status'] = false;
                    $this->serializer['errno'] = 0;
                    $this->serializer['message'] = $istrue['message'];
                    return true;
                }

            }else{
                $this->serializer['status'] = false;
                $this->serializer['errno'] = 0;
                $this->serializer['message'] = '你上传excel文件缺少内容';
                return true;
            }

            

            $data = $BonusCalculationManage->getBonusCalculationManageById($id);

            if($data){
                $upList = array();
                $groupId = $data->groupId;
                $bonusDate = $data->bonusDate;
                $manageId = $data->id;
                $isBase = $data->isBase;
                
                $s= OverwriteWithExcel($u,$data->sheetName);

                if($s){
                    if(!$isBase&&$list){   // 组长上传的重新写入数据库
                        //写配置
                        $BonusCalculationConfig = new BonusCalculationConfig();

                        $haveCon = $BonusCalculationConfig->getBonusCalculationConBySub($groupId);

                        if($haveCon){
                            $haveCon->allSheetField = json_encode($headerCon);
                            $haveCon->save();
                        }else{
                            $BonusCalculationConfig->groupId = $groupId;
                            $BonusCalculationConfig->customerId = $customerId;
                            
                            $BonusCalculationConfig->allSheetField = json_encode($headerCon);
                            $BonusCalculationConfig->save();
                        }

                        //删除原来的 再写入新上传的 
                        $BonusCalculationManage->deleteBonusCalculationListBySub($groupId,$bonusDate);

                        $BonusCalculation = new BonusCalculation();
                        $parBon = $BonusCalculation-> getBonusCalculationBySub($bonusDate,$groupId) ;
                        if($parBon){
                            $status = $parBon->status;
                            $calculation_id = $parBon->id;
                            foreach ($list as $k => $v) {
                                $lc  = $v[$gongzihao];
                                $user = $User->getSystemUsersByUserName($v[$gongzihao]);
                                if($user){
                                   
                                    $emp_number = $user->emp_number;

                                    $BonusCalculationList = new BonusCalculationList();
                                    $calst = $BonusCalculationList->getBonusCalculationListByDate($bonusDate,$emp_number,$groupId);
                                    if($calst){
                                        $BonusCalculationList = $calst;
                                    }
                                    $BonusCalculationList->bonus_id = $manageId;
                                    $BonusCalculationList->calculation_id = $calculation_id;
                                    $BonusCalculationList->customerId = $customerId;
                                    $BonusCalculationList->bonusDate = $bonusDate;
                                    $BonusCalculationList->groupId = $groupId;
                                    $BonusCalculationList->status = $status;
                                    $BonusCalculationList->emp_number = $emp_number;
                                    $BonusCalculationList->percent20 = $v[$percent20];

                                    $abd = 0 ;
                                    $percentCount = 0 ;
                                    foreach ($v as $ks => $vs) {
                                        if($abd==1){
                                            $percentCount +=(float) $vs;
                                        }
                                        if(trim($ks)==trim($percent20)){
                                            $abd=1;
                                        }
                                    }

                                    if(empty($v[$percent80])){
                                        $BonusCalculationList->percent80 = $percentCount;
                                    }else{
                                        $BonusCalculationList->percent80 = $v[$percent80];
                                    }

                                    
                                    $temp = $v;
                                    $BonusCalculationList->sheetInfo = json_encode($temp);
                                    $BonusCalculationList->create_time = date('Y-m-d H:i:s');
                                    $BonusCalculationList->save();

                                    
                                }
                   
                                
                            }
                        }else{
                            $this->serializer['status'] = false;
                            $this->serializer['errno'] = 0;
                            $this->serializer['message'] = '上传失败';
                            return true;
                        }

                    }
                    $this->serializer['status'] = true;
                    $this->serializer['errno'] = 0;
                    $this->serializer['message'] = '上传成功';
                    return true;
                }
                
            }
            
        }

        $this->serializer['status'] = false;
        $this->serializer['errno'] = 2;
        $this->serializer['message'] = '上传失败';
        return false;
        
        
    }

    /**
     * @SWG\Post(path="/performance/get-manage-subunit-detail",
     *     tags={"云平台-Performance-绩效"},
     *     summary="点击组名查看详情 (已确认归档页)",
     *     description="用户登录：成功返回用户信息；失败返回具体原因",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "Token",
     *        description = "60f5d74b625b79aafe22808f8bbddec4907f8204",
     *        required = false,
     *        type = "string"
     *     ),
     *     
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "bonusDate",
     *        description = "奖金日期",
     *        required = false,
     *        type = "string"
     *     ),

     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "id",
     *        description = "组id",
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
     *         description = "Data Validation Failed ",
     *     )
     * )
     *
     */
    
    public function actionGetManageSubunitDetail(){
        //$status = Yii::$app->request->post('status'); 
        $id = Yii::$app->request->post('id'); 
        $bonusDate = Yii::$app->request->post('bonusDate');
        $customerId = $this->customerId;
        $Subunit = new Subunit();

        //$bonusDate = '2018-03-01';
        if(empty($id)){
            $id = 0;
            $topSub = $Subunit->getSubunitByCustomerId($customerId,1);   //顶级组
        }
        
        
        $BonusCalculationManage = new BonusCalculationManage();

        $data = $BonusCalculationManage->getBonusCalculationManageListBySub($id,$bonusDate,$customerId);

        if($data){


            $this->serializer['status'] = true;
            $this->serializer['errno'] = 0;
            $this->serializer['message'] = '';
            return $data;
        }else{
            $this->serializer['status'] = false;
            $this->serializer['errno'] = 2;
            $this->serializer['message'] = '获取失败';
            return false;
        }
        
        
    }

    /**
     * @SWG\Post(path="/performance/confirm-filing",
     *     tags={"云平台-Performance-绩效"},
     *     summary="确认归档提交",
     *     description="用户登录：成功返回用户信息；失败返回具体原因",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "Token",
     *        description = "60f5d74b625b79aafe22808f8bbddec4907f8204",
     *        required = false,
     *        type = "string"
     *     ),
     *     
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "bonusDate",
     *        description = "奖金日期",
     *        required = false,
     *        type = "string"
     *     ),

     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "id",
     *        description = "id",
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
     *         description = "Data Validation Failed ",
     *     )
     * )
     *
     */
    
    public function actionConfirmFiling(){
        //$status = Yii::$app->request->post('status'); 
        $id = Yii::$app->request->post('id'); 
        $bonusDate = Yii::$app->request->post('bonusDate');
        $customerId = $this->customerId;
        $Subunit = new Subunit();

        $BonusCalculationManage = new BonusCalculationManage();

        $manage = $BonusCalculationManage->getBonusCalculationManageById($id);

        if($manage){
            if($manage->status!=11){
                $this->serializer['status'] = false;
                $this->serializer['errno'] = 2;
                $this->serializer['message'] = '待确认归档的文件状态不对';
                return false;
            }

            $sub = $Subunit->getSubunitByCustomerId($customerId,1);
            $topGroupId = $sub->id;

            $SheeetHost =  env('SHEET_HOST_INFO');
            $sheetName = $manage->sheetName;
            $bonusDate = $manage->bonusDate;
            $groupId = $manage->groupId;
            $manageId = $manage->id;

            $BonusCalculation = new BonusCalculation();
            $User = new User();
            $bonCal = $BonusCalculation->getBonusCalculationBySub($bonusDate,$groupId);
            $calId = $bonCal->id;
            $manageId = $manage->id;
            $manage->status=1;
            $bonCal->status = 1;
            $url = $SheeetHost.$sheetName.'.csv.json';

            $response = getCURLByExcel($url,1,'PUT');
            $response = json_decode($response);

            if($response){   //读取excel 插入数据库
                if(count($response)<2){
                    $this->serializer['status'] = false;
                    $this->serializer['errno'] = 2;
                    $this->serializer['message'] = '你还没上传excel文件';
                    return false;
                }

                $header = $response['0'];

                $gongzihao = '工资号';
                $percent20 = '基本绩效工资';
                $percent80 = '除20%外合计';
                $zu = '组别';

                unset($response[0]);
                //unset($response[1]);
                
                $list = array();
                $arr = array();
                foreach ($header as $key => $value) {
     
                    foreach ($response as $k => $v) {
                        if(trim($value)==trim($zu)){
                            $arr[]  = $v[$key];
                        }
                        
                    }
                }
                

                foreach ($response as $key => $value) {
                    $ar = array();
                    foreach ($header as $ks => $vs) {
                        
                        $ar[trim($vs)] = trim($value[$ks]);
                    }
                    $list[] = $ar;
                }
                if(empty($list)){
                    $this->serializer['status'] = false;
                    $this->serializer['errno'] = 2;
                    $this->serializer['message'] = '待确认归档的文件状态不对';
                    return false;
                }
                $arr = array_unique($arr);
                
                $transaction = Yii::$app->db->beginTransaction();
                try{
                    //先删除数据
                    //$BonusCalculationManage->deleteBonusCalculationManageListBySub($groupId,$bonusDate);
                    $BonusCalculationManage->deleteBonusCalculationListBySub($groupId,$bonusDate);
                   
                    foreach ($list as $key => $v) {

                        $lc  = $v[$gongzihao];

                        $user = $User->getSystemUsersByUserName($lc);


                        if($user){


                            //$BonusCalculationManageList = new BonusCalculationManageList();
                            $emp_number = $user->emp_number;
                            // $las = $BonusCalculationManageList->getBonusCalculationManageListByDate($bonusDate,$emp_number);
                            // if($las){
                            //     $BonusCalculationManageList = $las;
                                
                            // }
                            // $BonusCalculationManageList->bonus_id = $manageId;
                            // $BonusCalculationManageList->customerId = $customerId;
                            // $BonusCalculationManageList->bonusDate = $bonusDate;
                            
                            // $BonusCalculationManageList->groupId = $groupId;
                            // $BonusCalculationManageList->status = 1;
                            // $BonusCalculationManageList->emp_number = $user->emp_number;
                            // $BonusCalculationManageList->percent20 = $v[$percent20];
 
                            // $abd = 0 ;
                            // $percentCount = 0 ;
                            // foreach ($v as $ks => $vs) {
                            //     if($abd==1){
                            //         $percentCount +=(float) $vs;
                            //     }
                            //     if(trim($ks)==trim($percent20)){
                            //         $abd=1;
                            //     }
                            // }
                            // if(!empty($v[$percent80])){  //有值用原来的 没值重新计算
                            //     $BonusCalculationManageList->percent80 = $v[$percent80];
                            // }else{
                            //     $abd = 0 ;
                            //     $percentCount = 0 ;
                            //     foreach ($v as $ks => $vs) {
                            //         if($abd==1){
                            //             $percentCount +=(float) $vs;
                            //         }
                            //         if(trim($ks)==trim($percent20)){
                            //             $abd=1;
                            //         }
                            //     }
                            //     $BonusCalculationManageList->percent80 = $percentCount;
                            //     $v[$percent80] = $percentCount;
                            // }

                            // //$BonusCalculationManageList->percent80 = $v[$percent80];;
                            // //$v[$percent80] = $percentCount;
                            // $temp = $v;

                            // $BonusCalculationManageList->sheetInfo = json_encode($temp);
                            // $BonusCalculationManageList->create_time = date('Y-m-d H:i:s');
                            // $BonusCalculationManageList->save();

                            //
                            if($calId){
                                $BonusCalculationList = new BonusCalculationList();
                                $calst = $BonusCalculationList->getBonusCalculationListByDate($bonusDate,$emp_number,$groupId);
                                if($calst){
                                    $BonusCalculationList = $calst;
                                }
                                $BonusCalculationList->bonus_id = $manageId;
                                $BonusCalculationList->calculation_id = $calId; 
                                $BonusCalculationList->customerId = $customerId;
                                $BonusCalculationList->bonusDate = $bonusDate;
                                $BonusCalculationList->groupId = $groupId;
                                $BonusCalculationList->status = 1;
                                $BonusCalculationList->emp_number = $emp_number;
                                $BonusCalculationList->percent20 = $v[$percent20];

                                if(!empty($v[$percent80])){  //有值用原来的 没值重新计算
                                    $BonusCalculationList->percent80 = $v[$percent80];
                                }else{
                                    $abd = 0 ;
                                    $percentCount = 0 ;
                                    foreach ($v as $ks => $vs) {
                                        if($abd==1){
                                            $percentCount +=(float) $vs;
                                        }
                                        if(trim($ks)==trim($percent20)){
                                            $abd=1;
                                        }
                                    }
                                    $BonusCalculationList->percent80 = $percentCount;
                                    $v[$percent80] = $percentCount;
                                }

                                //$BonusCalculationList->percent80 = $v[$percent80];
                                $temp = $v;
                                $BonusCalculationList->sheetInfo = json_encode($temp);
                                $BonusCalculationList->create_time = date('Y-m-d H:i:s');
                                $BonusCalculationList->save();

                            }

                        }

                    }

                    $bonCal->save();
                    $manage->save();

                    $topLs = $BonusCalculationManage->getManageByStatus($bonusDate,1,$topGroupId,$customerId);

                    if (!$topLs) {
                        $ma = $BonusCalculationManage->getBonusCalculationManage($customerId,$bonusDate,$topGroupId);
                        if($ma){
                            $ma->status=1;
                            $ma->save();

                            $BonusCalculationManage->updateParentNewExcel($customerId,$bonusDate);
                        }
                    }
                    $BonusCalculationManage->updateBonusCalculationManageById($manageId,$groupId,$bonusDate,$manage->status);
                    //上报成功后把更新的数据写入主excel中
                    

                    $transaction->commit();
                    $this->serializer['status'] = true;
                    $this->serializer['errno'] = 0;
                    $this->serializer['message'] = '归档成功';
                    return true;

                }catch(\Exception $e) {
                    $transaction->rollback();
                    $this->serializer['status'] = false;
                    $this->serializer['errno'] = 2;
                    $this->serializer['message'] = '参数错误!!!';
                    return false;
                    
                }
            }else{
                $this->serializer['status'] = false;
                $this->serializer['errno'] = 2;
                $this->serializer['message'] = '参数错误!!';
                return false;
            }

          
        }else{

            $this->serializer['status'] = false;
            $this->serializer['errno'] = 2;
            $this->serializer['message'] = '参数错误!';
            return false;
        }

        

        
        
    }


     /**
     * @SWG\Post(path="/performance/get-bonus-calculation-detail",
     *     tags={"云平台-Performance-绩效"},
     *     summary="获取组长视角详情页(未上报/已上报)",
     *     description="用户登录：成功返回用户信息；失败返回具体原因",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "Token",
     *        description = "60f5d74b625b79aafe22808f8bbddec4907f8204",
     *        required = false,
     *        type = "string"
     *     ),
     *     
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "status",
     *        description = "状态 0默认未上报 1已上报",
     *        required = false,
     *        type = "string"
     *     ),

     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "id",
     *        description = "ID",
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
     *         description = "Data Validation Failed ",
     *     )
     * )
     *
     */
    
    public function actionGetBonusCalculationDetail(){
        $status = Yii::$app->request->post('status'); 
        $id = Yii::$app->request->post('id'); 
        
        $customerId = $this->customerId;
        $BonusCalculation = new BonusCalculation();
        $BonusCalculationConfig = new BonusCalculationConfig();

        $data = $BonusCalculation->getBonusCalculationById($id);

        if($data){
            $Subunit = new Subunit();
            $topSub = $Subunit->getSubunitByCustomerId($customerId,1);   //顶级组

            $topGroupId = $topSub['id'];
            $groupId = $data->groupId;
            $bonusDate = $data->bonusDate;

            

            $backArr['showPro'] = array();   //status = 0时返回的内容
            $backArr['jiaoDui'] = array();   //status = 11时   
            $backArr['showCon'] = array();   //员工可看奖金列配置
            if($data->status==10){
                $status  = '待上报';
                $back = $BonusCalculation->getCalContentByDate($data->id,$groupId,$data->bonusDate,$status);
                $backArr['showPro'] = $back;

                $xiaoDui = $BonusCalculation->xiaoduiCalContentByDate($id,$groupId,$bonusDate);
                
                $backArr['jiaoDui'] = $xiaoDui;
                $showCon = $BonusCalculationConfig->getBonusCalculationConfigBySub($groupId);


                $backArr['showCon'] = $showCon;

            }else if($data->status==11){
                $status  = '已上报 未确认';
                $back = $BonusCalculation->getCalContentByDate($data->id,$groupId,$data->bonusDate,$status);
                $backArr['showPro'] = $back;

            }else if($data->status==1){
                $status  = '已上报 已确认';
                $back = $BonusCalculation->getCalContentByDate($data->id,$groupId,$data->bonusDate,$status);
                $backArr['showPro'] = $back;

            }else{
                $this->serializer['status'] = false;
                $this->serializer['errno'] = 2;
                $this->serializer['message'] = '获取失败';
                return false;
            }


            $backArr['statusId'] = $data->status;
            $backArr['id'] = $data->id;
            $backArr['bonusId'] = $data->bonus_id;
            $backArr['groupId'] = $data->groupId;
            $backArr['sheetName'] = $data->sheetName;
            $backArr['bonusDate'] = $data->bonusDate;
            $backArr['sheetNameUrl'] = env('SHEET_HOST_INFO').$data->sheetName;


            $this->serializer['status'] = true;
            $this->serializer['errno'] = 0;
            $this->serializer['message'] = '';
            return $backArr;
        }else{
            $this->serializer['status'] = false;
            $this->serializer['errno'] = 2;
            $this->serializer['message'] = '获取失败';
            return false;
        }
        
        
    }

    /**
     * @SWG\Post(path="/performance/save-bonus-calculation-con",
     *     tags={"云平台-Performance-绩效"},
     *     summary="修改工资条显示列配置",
     *     description="用户登录：成功返回用户信息；失败返回具体原因",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "Token",
     *        description = "60f5d74b625b79aafe22808f8bbddec4907f8204",
     *        required = false,
     *        type = "string"
     *     ),
     *     
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "groupId",
     *        description = "组id",
     *        required = false,
     *        type = "string"
     *     ),

     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "showList",
     *        description = "显示的列",
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
     *         description = "Data Validation Failed ",
     *     )
     * )
     *
     */
    
    public function actionSaveBonusCalculationCon(){
        $groupId = Yii::$app->request->post('groupId'); 
        $showList = Yii::$app->request->post('showList'); 
        
        $customerId = $this->customerId;
        $BonusCalculation = new BonusCalculation();
        $BonusCalculationConfig = new BonusCalculationConfig();

        $data = $BonusCalculationConfig->getBonusCalculationConBySub($groupId);

        if($data){
            
            //$showArr = explode(',', $showList);
            $showArr = json_decode($showList);
            
            //$data->salarySheetField = json_encode($showArr);
            $allSheetField = json_decode($data->allSheetField);
            $show = array();
            foreach ($allSheetField as $key => $value) {
                if(in_array($key, $showArr)){
                    $show[$key] = $value;

                    //$show[] = $arr;
                }
            }
            $data->salarySheetField = json_encode($show);
            $data->save();


            $this->serializer['status'] = true;
            $this->serializer['errno'] = 0;
            $this->serializer['message'] = '修改成功';
            return true;
        }else{
            $this->serializer['status'] = false;
            $this->serializer['errno'] = 2;
            $this->serializer['message'] = '修改失败';
            return false;
        }
        
        
    }

    /**
     * @SWG\Post(path="/performance/confirm-report",
     *     tags={"云平台-Performance-绩效"},
     *     summary="组长确认上报",
     *     description="用户登录：成功返回用户信息；失败返回具体原因",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "Token",
     *        description = "60f5d74b625b79aafe22808f8bbddec4907f8204",
     *        required = false,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "id",
     *        description = "ID",
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
     *         description = "Data Validation Failed ",
     *     )
     * )
     *
     */
    
    public function actionConfirmReport(){
        $id = Yii::$app->request->post('id'); 
        $showList = Yii::$app->request->post('showList'); 
        
        $customerId = $this->customerId;
        $BonusCalculation = new BonusCalculation();
        $BonusCalculationManage = new BonusCalculationManage();
        $BonusCalculationConfig = new BonusCalculationConfig();
//$BonusCalculationManage->updateParentExcel($customerId,'2018-05-01');die;
        $data = $BonusCalculation->getBonusCalculationById($id);

        if($data){
            $SheeetHost =  env('SHEET_HOST_INFO');
            $sheetName = $data->sheetName;
            $groupId = $data->groupId;
            $bonusDate = $data->bonusDate;
            $bonus_id = $data->bonus_id;

            $url = $SheeetHost.$sheetName.'.csv.json';

            $response = getCURLByExcel($url,1,'PUT');
            $response = json_decode($response);
            
            //var_dump($response);die;

            if($response){
                if(count($response)<2){
                    $this->serializer['status'] = false;
                    $this->serializer['errno'] = 2;
                    $this->serializer['message'] = '你还没上传excel文件';
                    return false;
                }
                $header = $response['0'];

                $gongzihao = '工资号';
                $percent20 = '基本绩效工资';
                $percent80 = '除20%外合计';
                $zu = '组别';

                unset($response[0]);
                //unset($response[1]);
                $list = array();
                $arr = array();
                foreach ($header as $key => $value) {
     
                    foreach ($response as $k => $v) {
                        if(trim($value)==trim($zu)){
                            $arr[]  = $v[$key];
                        }
                        
                    }
                }
                

                foreach ($response as $key => $value) {
                    $ar = array();
                    foreach ($header as $ks => $vs) {
                        
                        $ar[trim($vs)] = trim($value[$ks]);
                    }
                    $list[] = $ar;
                }
                if(empty($list)){
                    $this->serializer['status'] = false;
                    $this->serializer['errno'] = 2;
                    $this->serializer['message'] = '待确认归档的文件状态不对';
                    return false;
                }
                $arr = array_unique($arr);
                $User = new User();
                $transaction = Yii::$app->db->beginTransaction();
                try{
                    //先删除数据
                    $BonusCalculationManage->deleteBonusCalculationListBySub($groupId,$bonusDate);
                   //var_dump('expression');die;
                    foreach ($list as $key => $v) {

                        $lc  = $v[$gongzihao];

                        $user = $User->getSystemUsersByUserName($lc);
                        if($user){
                            $BonusCalculationList = new BonusCalculationList();
                            $emp_number = $user->emp_number;
                            $las = $BonusCalculationList->getBonusCalculationListByDate($bonusDate,$emp_number,$groupId);
                            if($las){
                                $BonusCalculationList = $las;
                                
                            }
                            $BonusCalculationList->bonus_id = $bonus_id;
                            $BonusCalculationList->customerId = $customerId;
                            $BonusCalculationList->bonusDate = $bonusDate;
                            $BonusCalculationList->calculation_id = $data->id;
                            $BonusCalculationList->groupId = $groupId;
                            $BonusCalculationList->status = 11;
                            $BonusCalculationList->emp_number = $user->emp_number;
                            $BonusCalculationList->percent20 = $v[$percent20];
 
                            

                            if(!empty($v[$percent80])){  //有值用原来的 没值重新计算
                                $BonusCalculationList->percent80 = $v[$percent80];
                            }else{
                                $abd = 0 ;
                                $percentCount = 0 ;
                                foreach ($v as $ks => $vs) {
                                    if($abd==1){
                                        $percentCount +=(float) $vs;
                                    }
                                    if(trim($ks)==trim($percent20)){
                                        $abd=1;
                                    }
                                }
                                $BonusCalculationList->percent80 = $percentCount;
                                $v[$percent80] = $percentCount;
                            }

                            $temp = $v;

                            $BonusCalculationList->sheetInfo = json_encode($temp);
                            $BonusCalculationList->create_time = date('Y-m-d H:i:s');
                            $BonusCalculationList->save();

                        }

                    }
                    $data->status = 11;
                    $data->save();

                    $BonusCalculationManage->updateBonusCalculationManageById($bonus_id,$groupId,$bonusDate,$data->status);
                
                    //上报成功后把更新的数据写入主excel中 不用了 等所有的都确认归档了 在新生成excel 主任的excel 不动
                    //$BonusCalculationManage->updateParentExcel($customerId,$bonusDate);
                    $transaction->commit();
                    $this->serializer['status'] = true;
                    $this->serializer['errno'] = 0;
                    $this->serializer['message'] = '确认上报成功';
                    return true;

                }catch(\Exception $e) {
                    $transaction->rollback();
                    $this->serializer['status'] = false;
                    $this->serializer['errno'] = 2;
                    $this->serializer['message'] = '参数错误!!!';
                    return false;
                    
                }


            }else{
                $this->serializer['status'] = false;
                $this->serializer['errno'] = 2;
                $this->serializer['message'] = '参数错误!';
                return false;
            }


        }else{
            $this->serializer['status'] = false;
            $this->serializer['errno'] = 2;
            $this->serializer['message'] = '参数错误';
            return false;
        }
        
        
    }

 
      /**
     * @SWG\Post(path="/performance/emp-payrolln",
     *     tags={"云平台-Performance-绩效"},
     *     summary="组员获取工资条",
     *     description="用户登录：成功返回用户信息；失败返回具体原因",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "Token",
     *        description = "60f5d74b625b79aafe22808f8bbddec4907f8204",
     *        required = false,
     *        type = "string"
     *     ),
     *     
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "year",
     *        description = "年份",
     *        required = false,
     *        type = "string"
     *     ),

     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "month",
     *        description = "月份",
     *        required = false,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "isWX",
     *        description = "微信端查询工资条传 1",
     *        required = false,
     *        type = "string"
     *     ),
          @SWG\Parameter(
     *        in = "formData",
     *        name = "bonusDate",
     *        description = "微信端 日期",
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
     *         description = "Data Validation Failed ",
     *     )
     * )
     *
     */
    
    public function actionEmpPayrolln(){
        $year = Yii::$app->request->post('year'); 
        $month = Yii::$app->request->post('month'); 
        $bonusDate = Yii::$app->request->post('bonusDate');
        $isWX = Yii::$app->request->post('isWX');

        if(!$isWX){
            if(empty($year)||empty($month)){
                $this->serializer['status'] = false;
                $this->serializer['errno'] = 0;
                $this->serializer['message'] = '请选择年份和月份';
                return false;
            }
        }
        $customerId = $this->customerId;
        $empNumber = $this->empNumber;
        $workStation = $this->workStation;

        //$empNumber = 770;
        $BonusCalculation = new BonusCalculation();
        $BonusCalculationList = new BonusCalculationList();
        $BonusCalculationConfig = new BonusCalculationConfig();

        if($isWX){
            if(empty($bonusDate)){
                $this->serializer['status'] = false;
                $this->serializer['errno'] = 0;
                $this->serializer['message'] = '请选择日期';
                return false;
            }
        }else{
            $bonusDate = date('Y-m-d',strtotime($year.'-'.$month.'-01'));
        }
        

        $data = $BonusCalculationList->getBonusCalculationListByDate($bonusDate,$empNumber,$workStation);

        if($data){
            if($data->status!=1){
                $this->serializer['status'] = false;
                $this->serializer['errno'] = 0;
                $this->serializer['message'] = '你的工资条还没有确认归档';
                return false;
            }

            $groupId = $data->groupId;
            $con = $BonusCalculationConfig->getBonusCalculationConBySub($groupId);

            if($con){
                $showArr = json_decode($con->salarySheetField);
            }
            if(empty($showArr)){
                $showArr = array('工资号','姓名','基本绩效工资','业务岗位效益奖励','除20%外合计');
            }
            $showArr = (array)$showArr;

            $payrolln = json_decode($data->sheetInfo);
            $showList = array();
            foreach ($payrolln as $key => $value) {
                if(in_array(trim($key), $showArr)){
                    $arr = array();
                    $arr['title'] =  $key; 
                    $arr['val'] =   $value;

                    $showList[] = $arr;
                }

                  
            }

            $this->serializer['status'] = true;
            $this->serializer['errno'] = 0;
            $this->serializer['message'] = '查询成功';
            return $showList;
        }else{
            $this->serializer['status'] = true;
            $this->serializer['errno'] = 2;
            $this->serializer['message'] = '没有此月记录';
            return array();
        }
        
        
    }   


    /**
     * @SWG\Post(path="/performance/leader-proofreading",
     *     tags={"云平台-Performance-绩效"},
     *     summary="组长上报前校对",
     *     description="用户登录：成功返回用户信息；失败返回具体原因",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "Token",
     *        description = "60f5d74b625b79aafe22808f8bbddec4907f8204",
     *        required = false,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "sheetName",
     *        description = "excel名称",
     *        required = false,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "id",
     *        description = "manage表id",
     *        required = false,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "isLeader",
     *        description = "是否是组长视角 1 是 0管理员视角",
     *        required = false,
     *        type = "string"
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "登陆成功，返回用户信息"
     *     ),
     *     @SWG\Response(
     *         response = 422,
     *         description = "Data Validation Failed ",
     *     )
     * )
     *
     */
    public function actionLeaderProofreading(){
        //$sheetName = Yii::$app->request->post('sheetName');
        $id = Yii::$app->request->post('id');
        $isLeader = Yii::$app->request->post('isLeader');

        if(empty($id)){
            $this->serializer['status'] = false;
            $this->serializer['errno'] = 2;
            $this->serializer['message'] = '参数错误';
            return false;
        }

        $bonusCalculation = new BonusCalculation();
        $BonusCalculationManage = new BonusCalculationManage();

        if($isLeader){
            $calculation = $bonusCalculation->getBonusCalculationById($id);
        }else{
            $calculation = $bonusCalculation->getBonusCalculationByBonusId($id);
        }
        

        if($calculation){
            if($isLeader){
                if($calculation->status!=10){
                    $this->serializer['status'] = false;
                    $this->serializer['errno'] = 2;
                    $this->serializer['message'] = '此文件不是未上报状态';
                    return false;
                }
            }else{
                if($calculation->status!=11){
                    $this->serializer['status'] = false;
                    $this->serializer['errno'] = 2;
                    $this->serializer['message'] = '此文件不是已上报状态';
                    return false;
                }
            }

            

            $sheetName = $calculation->sheetName;
            $bonusDate = $calculation->bonusDate;
            $groupId = $calculation->groupId;

            $Subunit = new Subunit();
            $subName = $Subunit->getDepartmentName($groupId);


            $SheeetHost =  env('SHEET_HOST_INFO');
            $url = $SheeetHost.$sheetName.'.csv.json';
            $customerId = $this->customerId;

            $back = $BonusCalculationManage->verificationExcel($url,$customerId);

            if($back['status']){
                $response  = $back['response'];

                $gongzihao = $back['back']['gongzihao'];
                $percent20 = $back['back']['percent20'];
                $percent80 = $back['back']['percent80'];
                $zu = $back['back']['zu'];

                $arr = array();
                $header = $response['0'];

            
                unset($response[0]);
               // unset($response[1]);
                $list = array();
                foreach ($header as $key => $value) {
         
                    foreach ($response as $k => $v) {
                        if($value==$zu){
                            $arr[]  = $v[$key];
                        }
                        
                    }
                }

                foreach ($response as $key => $value) {
                    $ar = array();
                    foreach ($header as $ks => $vs) {
                        
                        $ar[trim($vs)] = trim($value[$ks]);
                    }
                    $list[] = $ar;
                }
                
                $arr = array_unique($arr);
                //var_dump($response);die;

                $empArr = array();
                foreach ($arr as $key => $value) {
                    $empArr[$value] = array();
                }

                $SHcount = 0;
                $SHpercent20 = 0;
                $SHpercent80 = 0;

                foreach ($list as $k => $v) {     


                    $abd = 0 ;
                    $dsc = 1;
                    $percentCount = 0 ;
                    foreach ($v as $ks => $vs) {
                        if(trim($ks)==trim($percent80)){
                            $dsc = 0;
                        }

                        if($abd==1){
                            if($dsc){
                                $percentCount +=(float) $vs;
                            }
                            
                        }
                        if(trim($ks)==trim($percent20)){
                            $abd=1;
                        }

                        
                    }


                    $SHcount += 1;
                    $SHpercent20 +=(float) $v[$percent20];
                    $SHpercent80 +=(float) $percentCount;

                }


#########
                $backHeader[] = array('title'=>'组名','key'=>'subName','align'=>'center');
                $backHeader[] = array('title'=>'人数(下发)','key'=>'peopleConut','align'=>'center');
                $backHeader[] = array('title'=>'基本工资(下发)','key'=>'percent20','align'=>'center');
                $backHeader[] = array('title'=>'除20%外合计(下发)','key'=>'percent80','align'=>'center');
                
                $backHeader[] = array('title'=>'人数(上报)','key'=>'peopleConutSh','align'=>'center');
                $backHeader[] = array('title'=>'基本工资(上报)','key'=>'percent20Sh','align'=>'center');
                $backHeader[] = array('title'=>'除20%外合计(上报)','key'=>'percent80Sh','align'=>'center');
                $backHeader[] = array('title'=>'校验结果','key'=>'checkout','align'=>'center');
#####
#   

                if($isLeader){
                    $bonusId = $calculation->bonus_id;
                    //$data = $bonusCalculation->getCalContentCountByDate($id,$groupId,$bonusDate);
                }else{
                    //$data = $bonusCalculation->getCalContentCountManageByDate($id,$groupId,$bonusDate);
                    $bonusId = $calculation->id ;
                }      

                $data = $bonusCalculation->getCalContentCountManageByDate('',$groupId,$bonusDate);

                $jiao = true ;
                if($data['count']!=$SHcount){
                    $jiao = false;
                }
                if($data['percent20']!=$SHpercent20){
                    $jiao = false;
                }
                if($data['percent80']!=$SHpercent80){
                    $jiao = false;
                }

                $backArr['subName'] = $subName;
                $backArr['peopleConut'] = $data['count'];
                $backArr['percent20'] =(float) $data['percent20'];
                $backArr['percent80'] =(float) $data['percent80'];
                $backArr['peopleConutSh'] = $SHcount;
                $backArr['percent20Sh'] = $SHpercent20;
                $backArr['percent80Sh'] = $SHpercent80;
                if($jiao){
                    $backArr['checkout'] = $jiao;
                }else{
                    $backArr['checkout'] = $jiao;
                }
                

                

                $this->serializer['status'] = true;
                $this->serializer['errno'] = 0;
                $this->serializer['message'] = '';
                return array('header'=>$backHeader,'content'=>array($backArr),'checkoutStatus'=>$jiao);

            }else{
                $this->serializer['status'] = false;
                $this->serializer['errno'] = 2;
                $this->serializer['message'] = $back['message'];
                return false;
            }



            $response = getCURLByExcel($url,1,'PUT');
            $response = json_decode($response);
            $customerId = $this->customerId;

        }else{
            $this->serializer['status'] = false;
            $this->serializer['errno'] = 2;
            $this->serializer['message'] = '参数错误';
            return false;
        }

    }
    /**
     * @SWG\Post(path="/performance/get-payrolln-config",
     *     tags={"云平台-Performance-绩效"},
     *     summary="组长获取工资条配置列表",
     *     description="用户登录：成功返回用户信息；失败返回具体原因",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "Token",
     *        description = "60f5d74b625b79aafe22808f8bbddec4907f8204",
     *        required = false,
     *        type = "string"
     *     ),
     *     
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "id",
     *        description = "bonus_id",
     *        required = false,
     *        type = "string"
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "登陆成功，返回用户信息"
     *     ),
     *     @SWG\Response(
     *         response = 422,
     *         description = "Data Validation Failed ",
     *     )
     * )
     *
     */
    
    public function actionGetPayrollnConfig(){
        $id = Yii::$app->request->post('id'); 
        

        if(empty($id)){
           
            $this->serializer['status'] = false;
            $this->serializer['errno'] = 0;
            $this->serializer['message'] = '参数错误';
            return false;
               
        }
        $customerId = $this->customerId;
        $empNumber = $this->empNumber;
        $workStation = $this->workStation;

        //$empNumber = 770;
        $BonusCalculationManage = new BonusCalculationManage();
        $BonusCalculationList = new BonusCalculationList();
        $BonusCalculationConfig = new BonusCalculationConfig();

        $list = $BonusCalculationManage->getBonusCalculationManageById($id);

        if($list){
            $groupId = $list->groupId;
            $data = $BonusCalculationConfig->getBonusCalculationConfigBySub($groupId); 
            $this->serializer['status'] = true;
            $this->serializer['errno'] = 0;
            $this->serializer['message'] = '';
            return $data;
        }else{
            $this->serializer['status'] = false;
            $this->serializer['errno'] = 0;
            $this->serializer['message'] = '你还没有上传过工资文件';
            return false;
        }


    } 
    
}
