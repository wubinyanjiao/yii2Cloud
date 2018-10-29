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
use common\models\system\SystemUsers;
use common\models\rotaryshift\RotaryShift;
use common\models\rotaryconfrim\RotaryConfrim;
use common\models\rotaryemployee\RotaryEmployee;
use common\models\rotaryrecord\RotaryRecord;
use common\models\rotaryrecordtmp\RotaryRecordTmp;
use common\models\rotationrule\RotationRule;
use common\models\subunit\Subunit;
use common\models\user\User;
use common\models\attendance\AttendanceRecord;
use common\models\leave\LeaveEntitlement;
use common\models\system\UniqueId;

//use common\components\Qiniu\Auth;
//use common\components\Qiniu\Storage\BucketManager;

use cheatsheet\Time;

class RotationController extends \common\rest\SysController
{

    //public $modelClass = 'common\models\SystemUsers';

    /**
     * @var array
     */
    public $serializer = [
        'class' => 'common\rest\Serializer',
        'collectionEnvelope' => 'result',
        // 'errno'              => 0,
        'message' => 'OK',
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


    public function actionIndex()
    {
        return false;
    }

    /**
     * @SWG\Post(path="/rotation/rotary-export",
     *     tags={"云平台-Rotation-轮转接口"},
     *     summary=" 导出Excle数据",
     *     description="导出Excle数据",
     *     produces={"application/json"},
     *      @SWG\Parameter(
     *        in = "formData",
     *        name = "rotaryId",
     *        description = "轮转Id",
     *        required = true,
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
    public function actionRotaryExport()
    {

        $rotaryId = Yii::$app->request->post('rotaryId');
        $rotaryId = isset($rotaryId) ?$rotaryId : '';
        if(empty($rotaryId)){
            $this->serializer['status'] = false;
            $this->serializer['errno'] = 1;
            $this->serializer['message'] = '轮转时间不满一个月';
            return false;
        }

        //获取头信息
        $RotaryShift = new RotaryShift();       //查询部门分组
        $Subunit = new Subunit();
        $RotaryShiftList = $RotaryShift->getWorkSHiftList($rotaryId);

        $RotaryEmployee = new RotaryEmployee();
        $rotaryConfrim = $RotaryEmployee->getWorkEmployee($rotaryId);
        if(empty($rotaryConfrim)){
            $this->serializer['status'] = false;
            $this->serializer['errno'] = 1;
            $this->serializer['message'] = '没有生成轮转顺序';
            return false;
        }
        $header['emp_firstname'] = '姓名';
        $header['rotary_department_id'] = '部门';

        $time =  strtotime($RotaryShiftList['date_to']) - strtotime($RotaryShiftList['date_from']);
        $month = floor($time/2592000);
        for($i=1; $i<=$month;$i++) {
            $date_from =  date("Y-m-d", strtotime("+$i months", strtotime($RotaryShiftList['date_from'])));
            $header['data'.$i] =  $date_from;
        }
        foreach($rotaryConfrim as $key=>$val){

            $arr[$key]['emp_firstname'] = $val['emp_firstname'];
            $arr[$key]['rotary_department_id'] = $Subunit->getDepartmentName($val['w_station']);
            for($i=1; $i<=$month; $i++){
                $date_from =  date("Y-m-d", strtotime("+$i months", strtotime($RotaryShiftList['date_from'])));
                if($val['date_to'] == $date_from){
                    $arr[$key]['data'.$i] = $Subunit->getDepartmentName($val['rotary_department_id']);
                }else{
                    $arr[$key]['data'.$i] = '';
                }
            }
        }
        array_unshift($arr,$header);


        $objPHPExcel  = new \PHPExcel();
        $objWriter = new \PHPExcel_Writer_Excel5($objPHPExcel);
        $letter = array(
            '1'=>'C','2'=>'D','3'=>'E', '4'=>'F', '5'=>'G','6'=>'H',
            '7'=>'I','8'=>'J', '9'=>'K', '10'=>'L', '11'=>'M','12'=>'N',
            '13'=>'O', '14'=>'P','15'=>'Q'
        );
        foreach ($arr as $key => $value) {
            $i=$key+1;//表格是从1开始的
            $objPHPExcel->getActiveSheet()->setCellValue('A'.$i,  $value['emp_firstname']);//这里是设置A1单元格的内容
            $objPHPExcel->getActiveSheet()->setCellValue('B'.$i,  $value['rotary_department_id']);////这里是设置B1单元格的内容
            for($j=1; $j<=$month;$j++) {
                $objPHPExcel->getActiveSheet()->setCellValue($letter[$j].$i,  $value['data'.$j]);////这里是设置B1单元格的内容
            }
            //以此类推，可以设置C D E F G看你需要了。
        }
        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);

        // Redirect output to a client’s web browser (Excel5)
        ob_end_clean();//清除缓冲区,避免乱码
        $filename = $RotaryShiftList['date_from'].'至'.$RotaryShiftList['date_to'].'轮转计划';
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");
        header("Content-Disposition:attachment;filename=$filename.xls");
        header("Content-Transfer-Encoding:binary");
        $objWriter->save('php://output');
        exit;
    }



    /**
     * @SWG\Post(path="/rotation/rotary-employee",
     *     tags={"云平台-Rotation-轮转接口"},
     *     summary="将临时数据表转到正式-- 确认调转",
     *     description="临时数据表转到正式-- 确认调转",
     *     produces={"application/json"},
     *      @SWG\Parameter(
     *        in = "formData",
     *        name = "rotaryId",
     *        description = "轮转Id",
     *        required = true,
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
    public function actionRotaryEmployee()
    {

        $rotaryId = Yii::$app->request->post('rotaryId');

        $rotaryId = isset($rotaryId) ? $rotaryId : '';

        $RotaryConfrim = new RotaryConfrim();       //规则轮转
        $list = $RotaryConfrim->getWorkConfrimList($rotaryId);
        if(empty($list)){
            $this->serializer['status'] = false;
            $this->serializer['errno'] = 1;
            $this->serializer['message'] = '临时表数据为空';
            return false;
        }
        $RotaryEmployee = new RotaryEmployee();
        //保存之前，先清空之前的轮转规则
        $RotaryEmployee->deleteWorkConfrim($rotaryId);

        //查询临时表数据
        foreach($list as $key=>$val){

            $RotaryEmployee->isNewRecord = true;
            $RotaryEmployee->id = $val['id'];
            $RotaryEmployee->rotary_id = $val['rotary_id'];
            $RotaryEmployee->emp_number = $val['emp_number'];     //emp_number
            $RotaryEmployee->date_from =$val['date_from'];                //当前时间 开始轮转时间   date_from
            $RotaryEmployee->date_to =$val['date_to'];                  //结束轮转时间 +月份 date_to
            $RotaryEmployee->orange_department_id =  $val['orange_department_id'];             //orange_department_id 之前id
            $RotaryEmployee->rotary_department_id = $val['rotary_department_id'];    //rotary_department_id    修改后id
            $RotaryEmployee->save() && $RotaryConfrim->id=0;

        }
        //删除临时表
        $status  = $RotaryConfrim->deleteWorkConfrim($rotaryId);
        return $status;
    }


    /**
     * @SWG\Post(path="/rotation/rotation-update-rule",
     *     tags={"云平台-Rotation-轮转接口"},
     *     summary="生成轮转",
     *     description="生成轮转",
     *     produces={"application/json"},
     *      @SWG\Parameter(
     *        in = "formData",
     *        name = "rotaryId",
     *        description = "轮转Id",
     *        required = true,
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

    //通过规则将人录入关系表
    public function actionRotationUpdateRule()
    {
        $rotaryId = Yii::$app->request->post('rotaryId');
        $rotaryId = isset($rotaryId) ?$rotaryId : '';
        $RotaryShift = new RotaryShift();       //查询部门分组
        $RotaryShiftList = $RotaryShift->getWorkSHiftList($rotaryId);
        //计算时间   轮转月份
        $time =  strtotime($RotaryShiftList['date_to']) - strtotime($RotaryShiftList['date_from']);
        $month = floor($time/2592000);

        if($month == 0){
            $this->serializer['status'] = false;
            $this->serializer['errno'] = 1;
            $this->serializer['message'] = '轮转时间不满一个月';
            return false;
        }
        $Rotary = new RotationRule();
        $rulelist = $Rotary->RotationRuleList($rotaryId);       //规则数组

        $order['first_department_id'] = $RotaryShiftList['first_department_id'];
        $order['second_department_id'] = $RotaryShiftList['second_department_id'];
        $order['third_department_id'] = $RotaryShiftList['third_department_id'];

        $RotaryConfrim = new RotaryConfrim();       // 临时关系表
        $RotaryConfrim->deleteWorkConfrim($rotaryId);   //生成之前 先删除

        for($i=0; $i<$month;$i++){
            foreach($order as $order_key=>$order_value){

                // order_value  是 轮转大组的的 id
                $date_from  = date("Y-m-d", strtotime("+$i months", strtotime($RotaryShiftList['date_from'])));

                if($order_key == 'first_department_id'){
                    $where = array();
                    $where['work_station'] = $order_value;
                    $where['rotaryid'] = $rotaryId;
                    $where['date_from'] = $date_from; //当前月份

                    if($rulelist['midlevel_year_status'] ==1){
                        //  中级职称满多长时间轮转 ---------------------
                        if($rulelist['midlevel_year_count']){
                            $where['midlevel_year_count'] = $rulelist['midlevel_year_count'];
                        }
                    }
                    if($rulelist['min_age_rotary_status'] ==1){
                        //年龄满足至少多少不轮转到门诊
                        if($order['third_department_id'] == 11){
                            if($rulelist['min_age_rotary']){
                                $where['min_age_rotary'] = $rulelist['min_age_rotary'];
                            }
                        }
                    }
                    if($rulelist['rotary_limit_time_status'] ==1){
                        //门诊满几年轮转
                        if($rulelist['rotary_limit_year']){
                            $where['rotary_limit_year'] = $rulelist['rotary_limit_year'];
                        }
                    }

                    if($rulelist['leader_no_rotary_status'] == 1){
                        //组长不参与轮转
                        $where['leader_no_rotary_status'] = true;
                    }

                    $user =  $RotaryConfrim->getConfrimList($order_value,$where);

                    if($user){
                        //插入
                        $RotaryConfrim->isNewRecord = true;
                        $RotaryConfrim->rotary_id = $rotaryId;
                        $RotaryConfrim->emp_number = $user['emp_number'];     //emp_number
                        $RotaryConfrim->date_from =$date_from;                //当前时间 开始轮转时间   date_from
                        $RotaryConfrim->date_to =$date_from;                  //结束轮转时间 +月份 date_to
                        $RotaryConfrim->orange_department_id =  $user['work_station'];             //orange_department_id 之前id
                        $RotaryConfrim->rotary_department_id = $order['second_department_id'];    //rotary_department_id    修改后id
                        $RotaryConfrim->save() && $RotaryConfrim->id=0;

                    }else{
                        $this->serializer['status'] = false;
                        $this->serializer['errno'] = 1;
                        $this->serializer['message'] = '数据为空';
                        return false;
                    }
                }

                if($order_key == 'second_department_id'){

                    $where = array();
                    $where['work_station'] = $order_value;
                    $where['rotaryid'] = $rotaryId;
                    $where['date_from'] = $date_from; //当前月份

                    if($rulelist['rotary_limit_time_status'] ==1){
                        //门诊满几年轮转
                        if($rulelist['rotary_limit_year']){
                            $where['rotary_limit_year'] = $rulelist['rotary_limit_year'];
                        }
                    }
                    if($rulelist['midlevel_year_status'] ==1){
                        //  中级职称满多长时间轮转 ---------------------
                        if($rulelist['midlevel_year_count']){
                            $where['midlevel_year_count'] = $rulelist['midlevel_year_count'];
                        }
                    }
                    if($rulelist['min_age_rotary_status'] ==1){
                        //年龄满足至少多少不轮转到门诊
                        if($order['third_department_id'] == 11){
                            if($rulelist['min_age_rotary']){
                                $where['min_age_rotary'] = $rulelist['min_age_rotary'];
                            }
                        }
                    }
                    if($rulelist['leader_no_rotary_status'] == 1){
                        //组长不参与轮转
                        $where['leader_no_rotary_status'] = true;
                    }
                    if($rulelist['averge_mid_level_status'] == 1){
                        //中级职称平均分配 $val['job_title_code'];
                        if($user['job_grade'] === '中级' ){
                            $where['averge_mid_level_status'] = true;
                        }
                    }
                    if($rulelist['averge_man_status'] ==1){
                        //男士平均分配
                        if($user['emp_gender'] == 1){
                            //如果这次是男的  则下一组就为男的
                            $where['emp_gender'] = 1;
                        }
                    }
                    if($rulelist['averge_graduate_status'] ==1){
                        //研究生平均分配
                        if($user['education_id']){
                            $where['education_id'] = $user['education_id'];
                        }
                    }

                    $user =  $RotaryConfrim->getConfrimList($order_value,$where);
                    if($user){
                        //插入
                        $RotaryConfrim->isNewRecord = true;
                        $RotaryConfrim->rotary_id = $rotaryId;
                        $RotaryConfrim->emp_number = $user['emp_number'];     //emp_number
                        $RotaryConfrim->date_from =$date_from;                //当前时间 开始轮转时间   date_from
                        $RotaryConfrim->date_to =$date_from;                  //结束轮转时间 +月份 date_to
                        $RotaryConfrim->orange_department_id =  $user['work_station'];             //orange_department_id 之前id
                        $RotaryConfrim->rotary_department_id = $order['third_department_id'];    //rotary_department_id    修改后id
                        $RotaryConfrim->save() && $RotaryConfrim->id=0;

                    }else{
                        $this->serializer['status'] = false;
                        $this->serializer['errno'] = 1;
                        $this->serializer['message'] = '数据为空';
                        return false;
                    }
                }

                if($order_key == 'third_department_id'){
                    $where = array();
                    $where['work_station'] = $order_value;
                    $where['rotaryid'] = $rotaryId;
                    $where['date_from'] = $date_from; //当前月份

                    if($rulelist['rotary_limit_time_status'] ==1){
                        //门诊满几年轮转
                        if($rulelist['rotary_limit_year']){
                            $where['rotary_limit_year'] = $rulelist['rotary_limit_year'];
                        }
                    }

                    if($rulelist['midlevel_year_status'] ==1){
                        //  中级职称满多长时间轮转 ---------------------
                        if($rulelist['midlevel_year_count']){
                            $where['midlevel_year_count'] = $rulelist['midlevel_year_count'];
                        }
                    }

                    if($rulelist['min_age_rotary_status'] ==1){
                        //年龄满足至少多少不轮转到门诊
                        if($order['first_department_id'] == 11){
                            if($rulelist['min_age_rotary']){
                                $where['min_age_rotary'] = $rulelist['min_age_rotary'];
                            }
                        }
                    }

                    if($rulelist['leader_no_rotary_status'] == 1){
                        //组长不参与轮转
                        $where['leader_no_rotary_status'] = true;
                    }
                    if($rulelist['averge_mid_level_status'] == 1){
                        //中级职称平均分配 $val['job_title_code'];
                        if($user['job_grade'] === '中级' ){
                            $where['averge_mid_level_status'] = true;
                        }
                    }
                    if($rulelist['averge_man_status'] ==1){
                        //男士平均分配
                        if($user['emp_gender'] == 1){
                            //如果这次是男的  则下一组就为男的
                            $where['emp_gender'] = 1;
                        }
                    }
                    if($rulelist['averge_graduate_status'] ==1){
                        //研究生平均分配
                        if($user['education_id']){
                            $where['education_id'] = $user['education_id'];
                        }
                    }

                    $user =  $RotaryConfrim->getConfrimList($order_value,$where);
                    if($user){
                        //插入
                        $RotaryConfrim->isNewRecord = true;
                        $RotaryConfrim->rotary_id = $rotaryId;
                        $RotaryConfrim->emp_number = $user['emp_number'];     //emp_number
                        $RotaryConfrim->date_from =$date_from;                //当前时间 开始轮转时间   date_from
                        $RotaryConfrim->date_to =$date_from;                  //结束轮转时间 +月份 date_to
                        $RotaryConfrim->orange_department_id =  $user['work_station'];             //orange_department_id 之前id
                        $RotaryConfrim->rotary_department_id = $order['first_department_id'];    //rotary_department_id    修改后id
                        $RotaryConfrim->save() && $RotaryConfrim->id=0;
                    }else{
                        $this->serializer['status'] = false;
                        $this->serializer['errno'] = 1;
                        $this->serializer['message'] = '数据为空';
                        return false;
                    }
                }
            }
            unset($user);
        }
        return true;
    }

    /**
     * @SWG\Post(path="/rotation/rotary-result-confirm",
     *     tags={"云平台-Rotation-轮转接口"},
     *     summary="查看轮转详情(临时)",
     *     description="查看轮转详情(临时)",
     *     produces={"application/json"},
     *      @SWG\Parameter(
     *        in = "formData",
     *        name = "rotaryId",
     *        description = "轮转Id",
     *        required = true,
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
    public function actionRotaryResultConfirm()
    {
        $rotaryId = Yii::$app->request->post('rotaryId');
        $rotaryId = isset($rotaryId) ?$rotaryId : '';
        //获取头信息
        $RotaryShift = new RotaryShift();       //查询部门分组
        $Subunit = new Subunit();
        $RotaryShiftList = $RotaryShift->getWorkSHiftList($rotaryId);
        $RotaryConfrim = new RotaryConfrim();
        $rotaryConfrim = $RotaryConfrim->getWorkConfrim($rotaryId);
        if(empty($rotaryConfrim)){
            $this->serializer['message'] = '没有生成轮转结果';
            return true;        //没有生成轮转结果
        }
        $arr['title'][] = array('title'=>'姓名');
        $arr['title'][] = array('title'=>'所属部门');

        $time =  strtotime($RotaryShiftList['date_to']) - strtotime($RotaryShiftList['date_from']);
        $month = floor($time/2592000);
        for($i=0; $i<$month;$i++) {
            $date_from =  date("Y-m-d", strtotime("+$i months", strtotime($RotaryShiftList['date_from'])));
            $arr['title'][] = array('title' => $date_from);
        }
        foreach($rotaryConfrim as $key=>$val){
             $a1 =   array('shift'=>array(
                        array('shift'=>$val['emp_firstname']),
                        array('bumen'=>$Subunit->getDepartmentName($val['orange_department_id'])),  //原部门
                    )
                );
            for($i=0; $i<$month;$i++){
                $date_from =  date("Y-m-d", strtotime("+$i months", strtotime($RotaryShiftList['date_from'])));
                if($val['date_to'] == $date_from){
                    array_push($a1['shift'],array('data'=>$Subunit->getDepartmentName($val['rotary_department_id'])));
                }else{
                    array_push($a1['shift'],array('data'=>''));
                }
            }
            $arr['info'][] = $a1;
        }
        return $arr;
        //获取轮转信息
    }

    /**
     * @SWG\Post(path="/rotation/rotary-result-employee",
     *     tags={"云平台-Rotation-轮转接口"},
     *     summary="查看轮转详情(正式)",
     *     description="查看轮转详情(正式)",
     *     produces={"application/json"},
     *      @SWG\Parameter(
     *        in = "formData",
     *        name = "rotaryId",
     *        description = "轮转Id",
     *        required = true,
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
    public function actionRotaryResultEmployee()
    {
        $rotaryId = Yii::$app->request->post('rotaryId');
        $rotaryId = isset($rotaryId) ?$rotaryId : '';
        //获取头信息
        $RotaryShift = new RotaryShift();       //查询部门分组
        $Subunit = new Subunit();
        $RotaryShiftList = $RotaryShift->getWorkSHiftList($rotaryId);
        $RotaryEmployee = new RotaryEmployee();
        $rotaryConfrim = $RotaryEmployee->getWorkEmployee($rotaryId);
        if(empty($rotaryConfrim)){
            $this->serializer['message'] = '没有保存轮转结果';
            return true;        //没有生成轮转结果
        }
        $arr['title'][] = array('title'=>'姓名');
        $arr['title'][] = array('title'=>'所属部门');

        $time =  strtotime($RotaryShiftList['date_to']) - strtotime($RotaryShiftList['date_from']);
        $month = floor($time/2592000);
        for($i=0; $i<$month;$i++) {
            $date_from =  date("Y-m-d", strtotime("+$i months", strtotime($RotaryShiftList['date_from'])));
            $arr['title'][] = array('title' => $date_from);
        }
        foreach($rotaryConfrim as $key=>$val){
             $a1 =   array('shift'=>array(
                        array('shift'=>$val['emp_firstname']),
                        array('bumen'=>$Subunit->getDepartmentName($val['orange_department_id'])),  //原部门
                    )
                );
            for($i=0; $i<$month;$i++){
                $date_from =  date("Y-m-d", strtotime("+$i months", strtotime($RotaryShiftList['date_from'])));
                if($val['date_to'] == $date_from){
                    array_push($a1['shift'],array('data'=>$Subunit->getDepartmentName($val['rotary_department_id'])));
                }else{
                    array_push($a1['shift'],array('data'=>''));
                }
            }
            $arr['info'][] = $a1;
        }
        return $arr;
        //获取轮转信息
    }


    /**
     * @SWG\Post(path="/rotation/get-rotation-list",
     *     tags={"云平台-Rotation-轮转接口"},
     *     summary="查看轮转列表",
     *     description="查看轮转列表",
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
    public function actionGetRotationList()
    {
        $RotaryShift = new RotaryShift();
        $rotaryshift = $RotaryShift->getWorkShift();
        $Subunit = new Subunit();
        if(empty($rotaryshift)){
            $array = array();
            $this->serializer['status'] = true;
            $this->serializer['errno'] = 0;
            $this->serializer['message'] = '正常 数据源为空';
            return $array;
        }else{

            foreach($rotaryshift as $key=>$val){
                $val['first_department_id'] = $Subunit->getDepartmentName($val['first_department_id']);
                $val['second_department_id'] =  $Subunit->getDepartmentName($val['second_department_id']);
                $val['third_department_id'] =  $Subunit->getDepartmentName($val['third_department_id']);
                //判断是否有规则， 如果有规则  status = 0,  如果没有规则 status = 1;
                if($RotaryShift->getDepartmentStatus($val['id'])){
                    $val['status'] = 1;
                }else{
                    $val['status'] = 0;
                }
                $list[] = $val;
            }
            return $list;
        }
    }


    /**
     * @SWG\Post(path="/rotation/update-rotation-rule",
     *     tags={"云平台-Rotation-轮转接口"},
     *     summary="修改排班规则",
     *     description="修改排班规则",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "rotaryId",
     *        description = "标识",
     *        required = true,
     *        type = "string"
     *     ),
     *      @SWG\Parameter(
     *        in = "formData",
     *        name = "averge_mid_level_count",
     *        description = "中级职称平均分配平均值",
     *        required = true,
     *        type = "string"
     *     ),
     *      @SWG\Parameter(
     *        in = "formData",
     *        name = "averge_mid_level_weight",
     *        description = "中级职称平均分配权重",
     *        required = true,
     *        type = "string"
     *     ),
     *      @SWG\Parameter(
     *        in = "formData",
     *        name = "averge_mid_level_status",
     *        description = "中级职称平均分配状态 0/关闭  1/开启",
     *        required = true,
     *        type = "string"
     *     ),
     *      @SWG\Parameter(
     *        in = "formData",
     *        name = "averge_man_count",
     *        description = "男士平均分配平均值",
     *        required = true,
     *        type = "string"
     *     ),
     *      @SWG\Parameter(
     *        in = "formData",
     *        name = "averge_man_weight",
     *        description = "男士平均分配权重",
     *        required = true,
     *        type = "string"
     *     ),
     *      @SWG\Parameter(
     *        in = "formData",
     *        name = "averge_man_status",
     *        description = "男士平均分配状态 0/关闭  1/开启",
     *        required = true,
     *        type = "string"
     *     ),
     *      @SWG\Parameter(
     *        in = "formData",
     *        name = "averge_graduate_count",
     *        description = "研究生平均分配",
     *        required = true,
     *        type = "string"
     *     ),
     *      @SWG\Parameter(
     *        in = "formData",
     *        name = "averge_graduate_weight",
     *        description = "研究生平均分配权重",
     *        required = true,
     *        type = "string"
     *     ),
     *      @SWG\Parameter(
     *        in = "formData",
     *        name = "averge_graduate_status",
     *        description = "研究生平均分配状态 0/关闭  1/开启",
     *        required = true,
     *        type = "string"
     *     ),
     *      @SWG\Parameter(
     *        in = "formData",
     *        name = "midlevel_year_count",
     *        description = "中级职称满多长时间轮转",
     *        required = true,
     *        type = "string"
     *     ),
     *      @SWG\Parameter(
     *        in = "formData",
     *        name = "midlevel_year_weight",
     *        description = "中级职称满多长时间轮转权重",
     *        required = true,
     *        type = "string"
     *     ),
     *      @SWG\Parameter(
     *        in = "formData",
     *        name = "midlevel_year_status",
     *        description = "中级职称满多长时间轮转状态 0/关闭  1/开启",
     *        required = true,
     *        type = "string"
     *     ),
     *      @SWG\Parameter(
     *        in = "formData",
     *        name = "firt_rotary_document",
     *        description = "第一个部门",
     *        required = true,
     *        type = "string"
     *     ),
     *      @SWG\Parameter(
     *        in = "formData",
     *        name = "sec_rotary_document",
     *        description = "第二个部门",
     *        required = true,
     *        type = "string"
     *     ),
     *      @SWG\Parameter(
     *        in = "formData",
     *        name = "third_rotary_document",
     *        description = "第三个部门",
     *        required = true,
     *        type = "string"
     *     ),
     *      @SWG\Parameter(
     *        in = "formData",
     *        name = "document_rotary_Weight",
     *        description = "部门权重权重",
     *        required = true,
     *        type = "string"
     *     ),
     *      @SWG\Parameter(
     *        in = "formData",
     *        name = "document_rotary_status",
     *        description = "部门状态 0/关闭  1/开启",
     *        required = true,
     *        type = "string"
     *     ),
     *      @SWG\Parameter(
     *        in = "formData",
     *        name = "rotary_limit_year",
     *        description = "门诊满几年轮转",
     *        required = true,
     *        type = "string"
     *     ),
     *      @SWG\Parameter(
     *        in = "formData",
     *        name = "rotary_limit_time_weight",
     *        description = "门诊满几年轮转权重",
     *        required = true,
     *        type = "string"
     *     ),
     *      @SWG\Parameter(
     *        in = "formData",
     *        name = "rotary_limit_time_status",
     *        description = "门诊满几年轮转状态 0/关闭  1/开启",
     *        required = true,
     *        type = "string"
     *     ),
     *      @SWG\Parameter(
     *        in = "formData",
     *        name = "min_age_rotary",
     *        description = "年龄满足至少多少不轮转到门诊",
     *        required = true,
     *        type = "string"
     *     ),
     *      @SWG\Parameter(
     *        in = "formData",
     *        name = "min_age_rotary_weight",
     *        description = "年龄满足至少多少不轮转到门诊权重",
     *        required = true,
     *        type = "string"
     *     ),
     *      @SWG\Parameter(
     *        in = "formData",
     *        name = "min_age_rotary_status",
     *        description = "年龄满足至少多少不轮转到门诊状态 0/关闭  1/开启",
     *        required = true,
     *        type = "string"
     *     ),
     *      @SWG\Parameter(
     *        in = "formData",
     *        name = "leader_no_rotary_weight",
     *        description = "组长不参与轮转权重",
     *        required = true,
     *        type = "string"
     *     ),
     *      @SWG\Parameter(
     *        in = "formData",
     *        name = "leader_no_rotary_status",
     *        description = "组长不参与轮转状态 0/关闭  1/开启",
     *        required = true,
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
    public function actionUpdateRotationRule()
    {
        //修改规则  RotationRule
        $rotaryId = Yii::$app->request->post('rotaryId');
        $data = Yii::$app->request->post();
        if(empty($data)){
            $this->serializer['status'] = false;
            $this->serializer['errno'] = 1;
            $this->serializer['message'] = '数据为空';
            return false;
        }else{
            $Rotary = new RotationRule();
            $rotary = $Rotary->find()->where("rotaryid = :rotaryid",[':rotaryid'=>$rotaryId])->one();

            $rotary->rotaryid = $rotaryId;
            $rotary->firt_rotary_document = $data['firt_rotary_document'];
            $rotary->sec_rotary_document = $data['sec_rotary_document'];
            $rotary->third_rotary_document = $data['third_rotary_document'];

            $rotary->averge_mid_level_count  = $data['averge_mid_level_count'];
            $rotary->averge_mid_level_weight = $data['averge_mid_level_weight'];
            $rotary->averge_mid_level_status  = $data['averge_mid_level_status'];
            $rotary->averge_man_count  = $data['averge_man_count'];
            $rotary->averge_man_weight  = $data['averge_man_weight'];
            $rotary->averge_man_status  = $data['averge_man_status'];
            $rotary->averge_graduate_count  = $data['averge_graduate_count'];
            $rotary->averge_graduate_weight  = $data['averge_graduate_weight'];
            $rotary->averge_graduate_status  = $data['averge_graduate_status'];
            $rotary->midlevel_year_count  = $data['midlevel_year_count'];
            $rotary->midlevel_year_weight  = $data['midlevel_year_weight'];
            $rotary->midlevel_year_status  = $data['midlevel_year_status'];
            $rotary->document_rotary_Weight  = $data['document_rotary_Weight'];
            $rotary->document_rotary_status  = $data['document_rotary_status'];
            $rotary->rotary_limit_year  = $data['rotary_limit_year'];
            $rotary->rotary_limit_time_weight  = $data['rotary_limit_time_weight'];
            $rotary->rotary_limit_time_status  = $data['rotary_limit_time_status'];
            $rotary->min_age_rotary  = $data['min_age_rotary'];
            $rotary->min_age_rotary_weight  = $data['min_age_rotary_weight'];
            $rotary->min_age_rotary_status  = $data['min_age_rotary_status'];
            $rotary->leader_no_rotary_weight  = $data['leader_no_rotary_weight'];
            $rotary->leader_no_rotary_status  = $data['leader_no_rotary_status'];
            $query = $rotary->save();
            return $query;
        }
    }


    /**
     * @SWG\Post(path="/rotation/show-rotation-rule",
     *     tags={"云平台-Rotation-轮转接口"},
     *     summary="修改轮转规则",
     *     description="修改轮转规则",
     *     produces={"application/json"},
     *      @SWG\Parameter(
     *        in = "formData",
     *        name = "rotaryId",
     *        description = "轮转Id",
     *        required = true,
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
    public function actionShowRotationRule()
    {
        $rotaryId = Yii::$app->request->post('rotaryId');
        //模拟数据
        $rotaryId = isset($rotaryId) ? $rotaryId : '';
        if(empty($rotaryId)){
            $this->serializer['status'] = false;
            $this->serializer['errno'] = 1;
            $this->serializer['message'] = '数据为空';
            return false;
        }
        $Rotary = new RotationRule();
        $rulelist = $Rotary->RotationRuleList($rotaryId);
        //查询   添加轮转规则
        $RotaryShift = new RotaryShift();       //查询部门分组
        $RotaryShiftList = $RotaryShift->getWorkSHiftList($rotaryId);

        if(empty($rulelist)){
            //没有规则  默认添加
            $Rotary->rotaryid = $rotaryId;
            $Rotary->firt_rotary_document = (string)$RotaryShiftList['first_department_id'];
            $Rotary->sec_rotary_document = (string)$RotaryShiftList['second_department_id'];
            $Rotary->third_rotary_document = (string)$RotaryShiftList['third_department_id'];
            $Rotary->averge_mid_level_count  = 1;
            $Rotary->averge_mid_level_weight = 1;
            $Rotary->averge_mid_level_status  = (string)0;
            $Rotary->averge_man_count  = 1;
            $Rotary->averge_man_weight  = 1;
            $Rotary->averge_man_status  = (string)0;
            $Rotary->averge_graduate_count  = 1;
            $Rotary->averge_graduate_weight  = 1;
            $Rotary->averge_graduate_status  = (string)0;
            $Rotary->midlevel_year_count  = 1;
            $Rotary->midlevel_year_weight  = 1;
            $Rotary->midlevel_year_status  = 1;
            $Rotary->document_rotary_Weight  = 1;
            $Rotary->document_rotary_status  = (string)0;
            $Rotary->rotary_limit_year  = 1;
            $Rotary->rotary_limit_time_weight  = 1;
            $Rotary->rotary_limit_time_status  = (string)0;
            $Rotary->min_age_rotary  = 1;
            $Rotary->min_age_rotary_weight  = 1;
            $Rotary->min_age_rotary_status  = (string)0;
            $Rotary->leader_no_rotary_weight  = 1;
            $Rotary->leader_no_rotary_status  = (string)1;
            $query = $Rotary->save();
            if($query){
                $rulelist = $Rotary->RotationRuleList($rotaryId);
            }
            return $rulelist;
        }else{
            //有规则 则查询
            return $rulelist;
        }
    }

    /**
     * @SWG\Post(path="/rotation/create-rotation-list",
     *     tags={"云平台-Rotation-轮转接口"},
     *     summary="添加轮转列表",
     *     description="添加轮转列表",
     *     produces={"application/json"},
     *      @SWG\Parameter(
     *        in = "formData",
     *        name = "name",
     *        description = "轮转名称",
     *        required = true,
     *        type = "string"
     *     ),
     *      @SWG\Parameter(
     *        in = "formData",
     *        name = "calFromDate",
     *        description = "开始时间",
     *        required = true,
     *        type = "string"
     *     ),
     *      @SWG\Parameter(
     *        in = "formData",
     *        name = "calToDate",
     *        description = "结束时间",
     *        required = true,
     *        type = "string"
     *     ),
     *      @SWG\Parameter(
     *        in = "formData",
     *        name = "firDocument",
     *        description = "第一个轮转部门Id",
     *        required = true,
     *        type = "string"
     *     ),
     *      @SWG\Parameter(
     *        in = "formData",
     *        name = "secDocument",
     *        description = "第二个轮转部门Id",
     *        required = true,
     *        type = "string"
     *     ),
     *      @SWG\Parameter(
     *        in = "formData",
     *        name = "thirDocument",
     *        description = "第三个轮转部门Id",
     *        required = true,
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
    public function actionCreateRotationList()
    {
        //添加轮转列表，
        $data = Yii::$app->request->post();
        $firDocument = isset($data['firDocument']) ? $data['firDocument'] : '';
        $secDocument = isset($data['secDocument']) ? $data['secDocument'] : '';
        $thirDocument = isset($data['thirDocument']) ? $data['thirDocument'] : '';
        $name = isset($data['name']) ? $data['name'] : '';
        $calFromDate = isset($data['calFromDate']) ? $data['calFromDate'] : '';
        $calToDate = isset($data['calToDate']) ? $data['calToDate'] : '';

        if (empty($name) ||empty($firDocument) || empty($secDocument) || empty($thirDocument) || empty($calFromDate) || empty($calToDate)) {
            $this->serializer['status'] = false;
            $this->serializer['errno'] = 1;
            $this->serializer['message'] = '参数不正确';
            return false;
        }
        $catime = strtotime($calFromDate);
        $calFromDate = date('Y-m-d',$catime);
        $catimeTo = strtotime($calToDate);
        $calToDate = date('Y-m-d',$catimeTo);
        //$firDocument = implode('_', $firDocument);
        //$secDocument = implode('_', $secDocument);
        //$thirDocument = implode('_', $thirDocument);
        $RotaryShift = new RotaryShift();
        $RotaryShift->name = $name;
        $RotaryShift->date_from = $calFromDate;
        $RotaryShift->date_to = $calToDate;
        $RotaryShift->first_department_id = $firDocument;
        $RotaryShift->second_department_id = $secDocument;
        $RotaryShift->third_department_id = $thirDocument;
        if($RotaryShift->save()){
            return true;
        }else{
            $this->serializer['status'] = false;
            $this->serializer['errno'] = 1;
            $this->serializer['message'] = $RotaryShift->errors;
            return false;
        }
    }

    /**
     * @SWG\Post(path="/rotation/delete-rotation-list",
     *     tags={"云平台-Rotation-轮转接口"},
     *     summary="删除轮转规则",
     *     description="删除轮转规则",
     *     produces={"application/json"},
     *      @SWG\Parameter(
     *        in = "formData",
     *        name = "rotaryId",
     *        description = "轮转Id",
     *        required = true,
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
    public function actionDeleteRotationList()
    {
        //删除轮转id
        $rotaryId_list = Yii::$app->request->post('rotaryId');
        $rotaryId_list = isset($rotaryId_list) ? $rotaryId_list : '';

        if(!is_array($rotaryId_list)){
            $this->serializer['status'] = false;
            $this->serializer['errno'] = 1;
            $this->serializer['message'] = 'rotaryId格式不正确';
            return false;
        }
        //将状态改为0   假删除
        $RotaryShift = new RotaryShift();
        foreach($rotaryId_list as $key=>$val){
             $RotaryShift->deleteWorkShiftList($val);
        }
        return true;
    }



}