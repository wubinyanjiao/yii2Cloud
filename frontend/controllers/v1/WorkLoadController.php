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
use common\models\workload\WorkLoad;
use common\models\workload\WorkContent;



use cheatsheet\Time;


class WorkLoadController extends \common\rest\Controller
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
     * @SWG\Post(path="/work-load/upload-workload",
     *     tags={"云平台-WorkLoad-工作量"},
     *     summary="上传工作量",
     *     description="用户登录：成功返回用户信息；失败返回具体原因",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "Token",
     *        description = "Token",
     *        required = false,
     *        type = "string"
     *     ),
           @SWG\Parameter(
     *        in = "formData",
     *        name = "bonusManageExcel",
     *        description = "工作量名称",
     *        required = false,
     *        type = "file"
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
    public function actionUploadWorkload()
    {   
        $xmlSign = 0 ;
        $work_station = 23; 

        $bonusManageExcel = $_FILES;


        @$url = $bonusManageExcel['bonusManageExcel']['name'];

        $houzi = substr(strrchr($url, '.'), 1);

        if($houzi!='xls'&&$houzi!='xlsx'){
            $this->serializer['status'] = false;
            $this->serializer['errno'] = 0;
            $this->serializer['message'] = '文件类型不对';
            return true;
        }
    
        

        $url = uplaode_excel_files($bonusManageExcel,'excel','bonusManageExcel');
        
        // $file = $_FILES;

        // $url = $file['bonusDialong_1']['name'];

        // $houzi = substr(strrchr($url, '.'), 1);

        // if($houzi!='xls'&&$houzi!='xlsx'){
        //     $this->serializer['status'] = false;
        //     $this->serializer['errno'] = 0;
        //     $this->serializer['message'] = '文件类型不对';
        //     return true;
        // }

        // $url = uplaode_excel_files($file,'excel','bonusDialong_1');
        //$u = uplaode_excel_files($bonusManageExcel,'excel','bonusManageExcel');

        //$url = '../public/excel/15366467385681.xls';
        if($url){
            $uplist = uplaode_files_by_excel($url,1);
            $is_ye = false;
            if($uplist){
                unset($uplist[1]);
                $i=0;

                $arr_header =  array();


                foreach ($uplist[2] as $key => $value) {
                    $i++;
                    if($i<3){
                        continue;
                    }

                    if($i==3){
                        if(is_numeric($value)){   //是按天数导入的
                            
                        }else{ 
                            $is_ye = true;                   
                            continue;
                        }
                    }
                    $arr_header[$key] = $value;

                }
                    
                

                unset($uplist[2]);
                $list = $uplist;
 
                $WorkContent = new WorkContent();
                $User = new User();
                $Employee = new Employee();


                $work_content_list = $WorkContent->getWorkContentList();  //工作名称

                $d = 25569;
                $t = 24 * 60 * 60;
                $s = '';

                if($is_ye){
                    $i=0;
                    foreach($list as $k=>$v){
                        if(!empty($v['A'])){
                            $userde = $User->getSystemUsersByUserName($v['A']);
                            $employee = $Employee->getEmpByNumNber($userde->emp_number);
                            if(!empty($employee->work_station)){
                                foreach($arr_header as $key=>$val){
                                    if(!$val){
                                        continue;
                                    }
                                    $arr = array();
                                    $wName = $WorkContent->getWorkContentByName(trim($val),$employee->work_station);
                                    $workcontent_id = null ;
                                    if(empty($wName)){
                                        $WorkConten =  new WorkContent();
                                        $WorkConten->name = trim($val);
                                        $WorkConten->work_station = $employee->work_station;
                                        $WorkConten->save();
                                        $workcontent_id= $WorkConten->id;
                                    }else{
                                        $workcontent_id = $wName->id;
                                    }

                                    $uploaddate = gmdate('Y-m-d', ($v['C'] - $d) * $t);
                                    $arr['work_date'] = $uploaddate;
                                    $arr['employeeId'] = $userde->emp_number;
                                    $arr['workcontent_id'] = $workcontent_id;
                                    $workload = $WorkContent->getWorkLoadByArr($arr);


                                    if(!empty($workload)&&!empty($wName->id)){
                                    //if($workload->id&&$workload->workcontent_id==$wName->id){
                                        $b[] = $i;
                                         if(empty($v[$key])){
                                            continue;
                                         }
                                         $workload->workload =floatval($v[$key]);
                                         $workload->work_check = floatval($v[$key]);
                                         $workload->work_weight = $v[$key];
                                         $workload->check_time =  date('Y-m-d H:i:s');
                                         $workload->workcontent_id = $workcontent_id;
                                         
                                         $workload->save();
                                    }else{
                                        $Load = new WorkLoad();
                                        if(empty($v[$key])){
                                            continue;
                                         }
                                        $Load->workload = floatval($v[$key]);
                                        $Load->work_check = floatval($v[$key]);
                                        $Load->work_weight = $v[$key];
                                        $Load->workcontent_id = $workcontent_id;
                                        $Load->employee_id = $userde->emp_number;
                                        $Load->work_date = $uploaddate;
                                        $Load->create_time =  date('Y-m-d H:i:s');
                                        $Load->check_time =  date('Y-m-d H:i:s');
                                        $Load->save();
                                       // $s .= '--b--';

                                     }
                                    
                                    $i++;

                                }

                            }
                        }

                    }



                }else{    
                    $i = 0; 
                    foreach($list as $k=>$v){
                        if(!empty($v['A'])){   //查询是否已经添加过了
                            $userde = $User->getSystemUsersByUserName($v['A']);
                            if(!empty($userde->id)){   //查找上传的员工是否在数据库中
                                //查询日期是否已经填写了

                                foreach($arr_header as $key=>$val){   //循环日期
                                    if(!$val){
                                        continue;
                                    }
                                    $keycun = $key;
                                    //查询表中是否已经有数据
                                    $arr['employeeId'] = $userde->emp_number;
                                    $uploaddate = gmdate('Y-m-d', ($val - $d) * $t);
                                    $arr['work_date'] = $uploaddate;
                                    $workLoad = $WorkContent->getWorkLoadByArr($arr);
                                    if(!empty($workLoad->id)){
                                        if(empty($v[$key])){
                                            continue;
                                         }
                                         $workLoad->workload = floatval($v[$keycun]);
                                         $workLoad->work_check = floatval($v[$keycun]);
                                         $workload->check_time =  date('Y-m-d H:i:s');
                                         $workload->work_weight = $v[$keycun];
                                         $workLoad->save();
                                    }else{
                                        if(empty($v[$key])){
                                            continue;
                                         }
                                        $Load = new WorkLoad();
                                        $Load->work_check = floatval($v[$key]);
                                        $Load->work_weight = $v[$key];
                                        $Load->employee_id = $userde->emp_number;
                                        $Load->workload = $v[$key];
                                        $Load->employee_id = $userde->emp_number;
                                        $Load->work_date = $uploaddate;
                                        $Load->check_time =  date('Y-m-d H:i:s');
                                        $Load->create_time =  date('Y-m-d H:i:s');
                                        $Load->save();

                                   }

                                   $ss[] = array($uploaddate,$v[$key]);
                                   $i++;
                                }
                                $bb[] =$ss; 

                            }

                        }
                    }
                }
            }

            $this->serializer['status'] = true;
            $this->serializer['errno'] = 0;
            $this->serializer['message'] = '上传成功';
            return false;

            

        }
        $this->serializer['status'] = false;
        $this->serializer['errno'] = 0;
        $this->serializer['message'] = '上传失败';
        return false;
        


    }


    

  

    

   



   

    private function getCountWorkLoad($empNumber = null){
         // $user_name = 'pwj222';
         // $stat_date = $this->stat_date = '2017-12-1';
         // $end_date  = $this->end_date = '2018-12-1';
        $stat_date = $this->stat_date ;
        $end_date  = $this->end_date;
        if(empty($empNumber)){
            return false;
        }
        $Employee = new Employee();
        $count_workload = $Employee->getCountWorkLoad($stat_date,$end_date,$empNumber);

        return $count_workload;

    }

    


    
}
