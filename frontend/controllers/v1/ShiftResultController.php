<?php
namespace frontend\controllers\v1;

use Yii;
use yii\web\Response;
use yii\helpers\Url;
use yii\web\Controller;
use yii\helpers\ArrayHelper;
use common\models\shift\Schedule;
use common\models\shift\ShiftDate;
use common\models\shift\ShiftResultConfirm;
use common\models\shift\ShiftType;
use common\models\shift\ShiftOrderBy;
use common\models\shift\TypeSkill;
use common\models\shift\ShiftResult;
use common\models\shift\ShiftResultOrange;
use common\models\leave\LeaveEntitlement;
use common\models\leave\LeaveType;
use common\models\employee\Employee;
use common\models\shift\EmpSkill;
use common\models\shift\ShiftModel;
use common\models\user\User;

class ShiftResultController extends \common\rest\Controller
{
    /**
     * @var string
     */
    public $modelClass = 'common\models\ShiftResult';

    /**
     * 
     * @var array
     */
    public $serializer = [
        'class' => 'common\rest\Serializer',    // 返回格式数据化字段
        'collectionEnvelope' => 'result',       // 制定数据字段名称
        'message' => 'OK',                      // 文本提示
        'errno'   => 0,
        'status'  =>''
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

        return $action;
    }

    /**
     * @param  [type]
     * @param  [type]
     * @return [type]
     */
    public function afterAction($action, $result){
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
     * @SWG\Post(path="/shift-result/list",
     *     tags={"云平台-shift-result-排班结果"},
     *     summary="查看排班结果",
     *     description="查看排班结果",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "schedule_id",
     *        description = "排班计划ID",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "shift_date",
     *        description = "选择日期",
     *        required = true,
     *        type = "string",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "copy_type",
     *        description = "选择查看天数，'one':一周, 'two':两周",
     *        required = false,
     *        type = "string",
     *        default = 1,
     *        enum = {1,2}
     *     ),
     *     @SWG\Response(
     *         response = 422,
     *         description = "Data Validation Failed 创建失败",
     *     )
     * )
     *
    **/

    public function actionList()
    {
        $post=Yii::$app->request->post();


        $modelClass =  new $this->modelClass;
        if($modelClass->selectReslut($post)){

            $data = ShiftResult::find()->asArray()->all();

            $this->serializer['errno']   = 0;
            $this->serializer['status']   = true;
            $this->serializer['message'] = "获取成功";
            return $data;
             
        }else{
            $this->serializer['errno']   = 2000600;
            $this->serializer['status']   = false;
            $this->serializer['message'] = "获取失败"; 
        }
        

    }


    /**
     * @SWG\Post(path="/shift-result/change-shift",
     *     tags={"云平台-shift-result-排班结果"},
     *     summary="修改排班结果",
     *     description="修改排班结果",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "schedule_id",
     *        description = "排班计划ID",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "first_result_id",
     *        description = "第一个班次id",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "first_emp_num",
     *        description = "第一个班次员工工资号",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "first_type_id",
     *        description = "第一个班次类型id号",
     *        required = true,
     *        type = "integer"
     *     ),

     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "second_type_id",
     *        description = "第二个班次类型id号",
     *        required = true,
     *        type = "integer"
     *     ),

     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "second_result_id",
     *        description = "第二个班次id",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "second_emp_num",
     *        description = "第二个班次员工工资号",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "Token",
     *        description = "Token",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "返回班次类型列表"
     *     ),
     *     @SWG\Response(
     *         response = 422,
     *         description = "Data Validation Failed 创建失败",
     *     )
     * )
     *
    **/
    public function actionChangeShift()
    {   
        $isLeader=$this->isLeader;
        $isShiftManager=$this->isShiftManager;
        if(!$isShiftManager && !$isLeader){
            $this->serializer['errno']   = '422';
            $this->serializer['status']   = false;
            $this->serializer['message'] = '权限不够，只有排班管理员权限可以';
            return [];
        }
        $post=Yii::$app->request->post();
        $first_result_id=$post['first_result_id'];
        $second_result_id=$post['second_result_id'];
        $schedule_id=$post['schedule_id'];

        $first_emp_num=$post['first_emp_num'];
        $second_emp_num=$post['second_emp_num'];

        $first_type_id=$post['first_type_id'];
        $second_type_id=$post['second_type_id'];

        $work_station=$this->workStation;
        $confirmmodel=new ShiftResultConfirm;
        $resultmodel=new ShiftResult;
        $typemodel=new ShiftType;
        $transaction = Yii::$app->db->beginTransaction();
        $isConfirm=0;
        
        try{ 
            //更新
            $confirm_fir=$confirmmodel->getConfrimResultById($first_result_id,$schedule_id);
            $confirm_sec=$confirmmodel->getConfrimResultById($second_result_id,$schedule_id);

            $type_fir=$confirm_fir->shift_type_id;
            $type_sec=$confirm_sec->shift_type_id;
            $shift_date=$confirm_sec->shift_date;

            //判断是不是夜班,如果是夜班调班，连同后一天一替换
            $night=$typemodel->getNightType($work_station);
            $night_id=isset($night)?$night->id:'';



            if($type_fir==$night_id || $type_sec==$night_id){
    
                $shift_date_next=date("Y-m-d",strtotime("+1 day",strtotime($shift_date)));
                $next_frist= $confirmmodel->getConfrimByEmpAndDate($first_emp_num,$shift_date_next,$schedule_id);
                $next_sec= $confirmmodel->getConfrimByEmpAndDate($second_emp_num,$shift_date_next,$schedule_id);

                if(isset($next_frist)){
                    if(!$confirmmodel->updateResultEmp($next_frist->id,$second_emp_num,$schedule_id)){
                        throw new \Exception();
                    }
                    if(!$confirmmodel->updateResultEmp($next_sec->id,$first_emp_num,$schedule_id)){
                        throw new \Exception();
                    }
                }
            }

            if(!$confirmmodel->updateResultEmp($first_result_id,$second_emp_num,$schedule_id)){
                throw new \Exception();
            }
            if(!$confirmmodel->updateResultEmp($second_result_id,$first_emp_num,$schedule_id)){
                throw new \Exception();
            }


            //获取换班后的数据
            $one=$confirmmodel->getConfrimResultById2($first_result_id,$schedule_id);
            $two=$confirmmodel->getConfrimResultById2($second_result_id,$schedule_id);

            $emp_fir=$one['emp_number'];
            $emp_sec=$two['emp_number'];

            $confirmmodel->setLeaves($schedule_id,$emp_fir);
            $confirmmodel->setLeaves($schedule_id,$emp_sec);
    
            $result_fir=$confirmmodel->getShiftOneEmpFormat($schedule_id,$work_station,$emp_fir);
            $result_sec=$confirmmodel->getShiftOneEmpFormat($schedule_id,$work_station,$emp_sec);

            $data['first']['cells']=$result_sec[$emp_sec]['cells'];
            $data['second']['cells']=$result_fir[$emp_fir]['cells'];
 

            $transaction->commit();
            $this->serializer['errno']   = 200;
            $this->serializer['status']   = true;
            $this->serializer['message'] = "调班完成"; 
            return $data;

        }catch(\Exception $e) {
            $transaction->rollback();
            $this->serializer['errno']   = 2000060000;
            $this->serializer['status']   = false;
            $this->serializer['message'] = '调班失败'; 
        }

    }

    /**
     * @SWG\Post(path="/shift-result/week",
     *     tags={"云平台-shift-result-排班结果"},
     *     summary="获取周信息",
     *     description="获取周信息",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "schedule_id",
     *        description = "排班计划ID",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "Token",
     *        description = "Token",
     *        required = true,
     *        type = "integer"
     *     ),

     *     @SWG\Response(
     *         response = 200,
     *         description = "返回日期列表"
     *     ),
     *     @SWG\Response(
     *         response = 422,
     *         description = "Data Validation Failed 获取失败",
     *     )
     * )
     *
    **/
    public function actionWeek()
    {
       
        $post=Yii::$app->request->post();
        $data=array();
        $tmp=array();
        $shift_date=array();
        $shiftDate=new ShiftDate;
        $shift_date=$shiftDate->getDatesBySchedule($post['schedule_id']);
        $weekArr=array('1'=> '周一', '2'=> '周二', '3'=>'周三','4'=>'周四', '5'=> '周五', '6'=>'周六','0'=>'周日');
        foreach ($shift_date as $key => $value) {
           $index=get_week($value['shift_date']);
           if($index==1){
                $tmp=$key;
           }
  
           $week['id']=$value['id'];
           $week['title']=$weekArr[$index];
           $week['type']=0;

           $date['id']=$value['id'];
           $date['date']=$value['shift_date'];
           $date['type']=0;

           $data['titleList'][$key]=$week;
           $data['dateList'][$key]=$date;
        }

        $tmp2=array();

        $tmp2['id']='';
        $tmp2['title']='';
        $tmp2['type']='2';

        $tmp_arr1['titleList']=$tmp2;
        $tmp_arr2['dateList']=$tmp2;

        array_splice($data['titleList'], $tmp, 0, $tmp_arr1);
        array_splice($data['dateList'], $tmp, 0, $tmp_arr2);

        array_unshift($data['titleList'],$tmp2);
        array_unshift($data['dateList'],$tmp2);

        if(count($data)>0){
            $this->serializer['status']   = true;
            $this->serializer['message'] = "获取日期列表成功";
            return $data;
       
        }else{
            $this->serializer['errno']   = 2000060000;
            $this->serializer['status']   = false;
            $this->serializer['message'] = "日期列表为空";
        }
    }

     /**
     * @SWG\Post(path="/shift-result/result",
     *     tags={"云平台-shift-result-排班结果"},
     *     summary="排班结果",
     *     description="排班结果",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "schedule_id",
     *        description = "排班计划ID",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "Token",
     *        description = "Token",
     *        required = true,
     *        type = "integer"
     *     ),

     *     @SWG\Response(
     *         response = 200,
     *         description = "返回日期列表"
     *     ),
     *     @SWG\Response(
     *         response = 422,
     *         description = "Data Validation Failed 获取失败",
     *     )
     * )
     *
    **/
    public function actionResult()
    {
       
        $post=Yii::$app->request->post();

        $schedule_id=$post['schedule_id'];
        $confirmmodel = new ShiftResultConfirm;
        $confirmmodel->setLeaves($schedule_id);
        $orangemmodel = new ShiftResultOrange;
        $typemode=new ShiftType;
        $orderbymode=new ShiftOrderBy;
        $resultmodel=new ShiftResult;
        $shiftmodel=new ShiftModel;
        $schedulemodel = new Schedule;
        $usermodel=new User;
        $data=array();
        $tmp=array();
        $shift_date=array();
        $orange_result=array();
        $schedule=Schedule::find()->where('id =:schedule_id ',[':schedule_id'=>$schedule_id])->one();
        if(!$schedule){
            $this->serializer['errno']   = '422';
            $this->serializer['status']   = false;
            $this->serializer['message'] = '没有该计划';
            return [];
        }
        //获取基础数据ID
        $orange_data=$schedule->orange_data;
        //判断模板类型
        $model_type=$schedule->model_type;
        $is_confirm=$schedule->is_confirm;
        $is_status=$schedule->status;
        $copy_type=$schedule->copy_type;
        $first_date=$schedule->shift_date;
        $schedule_type=$schedule->schedule_type;
        $is_insert=$schedule->is_insert;//是否已经生成排班并且插入
        $workStation=$this->workStation;
        $shiftTypes=$typemode->getShifType($workStation);
        $typeList=array_column($shiftTypes, NULL,'id');
        $color = array_column($shiftTypes, 'color', 'id');

        $newclor[0]['backgroundColor']='#FAF822';
        $newclor[-200]['backgroundColor']='#8BD931';
        foreach ($color as $key_color => $value_color) {
            $newclor[$key_color]['backgroundColor']=!empty($value_color)?$value_color:'#FFFFFF';
        }

        $shiftDate=new ShiftDate;
        $shift_date=$shiftDate->getDatesBySchedule($post['schedule_id']);
        if(!isset($shift_date)){
            $this->serializer['errno']   = '40060000';
            $this->serializer['status']   = false;
            $this->serializer['message'] = '没有日期数据';
            return [];
        }

        foreach ($shift_date as $key_date => $value_date) {
            $weeks=get_week($value_date['shift_date']);
            $tmp[$weeks][$key_date]=$value_date;
        }
        //查看confrim表是否有数据
        $model_result=array();
        $model_result = $confirmmodel->getRosterResultConfirm($schedule_id);

        //获取轮转进出人员
        $empInOut=$usermodel->getRotationEmpnumber($first_date,$workStation);

        //如果没有将引擎数据插入表中
        if($is_insert==0){
            if(count($model_result)==0){//如果confirm表为空
                if($is_status==0||$is_status==2){//如果状态是为开始排班或者排班失败
                    //判断基础数据来源类型
                    if($model_type==2){// 以已经发布的表为基础数据
                        //基础数据以原始表中的为准,orange_data为计划id
                        $orange_result=$confirmmodel->getShiftResultConfrim($orange_data);
                        $schedule_model_id=$orange_data; 
                    }else if($model_type==1){//以模板为基础数据 
                        //基础数据以原始表中的为准,orange_data为模板id
                        $modelentity=$shiftmodel->getShiftModelOne($orange_data);
                        $schedule_model_id=$modelentity->schedule_id;
                        //获取模板数据
                        $orange_result=$orangemmodel->getShiftResult($schedule_model_id);
                    }
    
                    if(count($orange_result)==0){//如果没有模板数据来源
                        //获取员工列表
                        $employee=new Employee;
                        $emp_new_all=$usermodel->FutureEmployee($workStation,$first_date);


                        $orderbymode->delshiftindex($schedule_id);
                        $i=0;
                        foreach ($emp_new_all as $key_order => $value_order) {
                            $orderbymodel=new ShiftOrderBy;
                            $orderdata1['ShiftOrderBy']['emp_number']=$value_order['emp_number'];
                            $orderdata1['ShiftOrderBy']['work_station']=$workStation;
                            $orderdata1['ShiftOrderBy']['shift_index']=$i;
                            $orderdata1['ShiftOrderBy']['schedule_id']=$schedule_id;
                            $i++;
                            if(!$orderbymodel->addShiftOrder($orderdata1)){
                                    throw new \Exception();
                            }
                        }
                        foreach ($emp_new_all as $key_order_1 => $value_order_1){
                            foreach ($shift_date as $key_d => $value_d) {

                                $confirmmodel=new ShiftResultConfirm;
                                $data2['ShiftResultConfirm']['schedule_id']= $schedule_id;
                                $data2['ShiftResultConfirm']['emp_number']= $value_order_1['emp_number'];
                                $data2['ShiftResultConfirm']['shift_type_id']=ShiftResult::NO_REST_SHIFT;
                                $data2['ShiftResultConfirm']['shift_date']= $value_d['shift_date'];
                                $data2['ShiftResultConfirm']['shift_type_name']= '';
                                $data2['ShiftResultConfirm']['rest_type']='0';
                                $data2['ShiftResultConfirm']['leave_type']='0';
                                $data2['ShiftResultConfirm']['shift_type_id_backup']=0;
                                $data2['ShiftResultConfirm']['frist_type_id']='';
                                $data2['ShiftResultConfirm']['second_type_id']='';
                                $data2['ShiftResultConfirm']['third_type_id']='';
                                if(!$confirmmodel->addConfrim($data2)){
                                    throw new \Exception();
                                }
                            }
                        }
                    }else{//如果存在模板数据，将数据插入到confirm表中
                        $shiftOrderBy=$orderbymode->getShiftOrderBy($schedule_model_id);
                        $orderbymode->delshiftindex($schedule_id);
                        
                        foreach ($shiftOrderBy as $key_order => $value_order) {
                            $in_emps=$empInOut['in_subunit'];
                            if(in_array($value_order['emp_number'], $empInOut['out_subunit'])){
                                $S=array_rand($in_emps);
                                $emp_new=$in_emps[$S];
                                unset($in_emps[$S]);
                            }else{
                                $emp_new=$value_order['emp_number'];
                            }

                            $orderbymodel=new ShiftOrderBy;
                            $orderdata['ShiftOrderBy']['emp_number']=$emp_new;
                            $orderdata['ShiftOrderBy']['work_station']=$value_order['work_station'];
                            $orderdata['ShiftOrderBy']['shift_index']=$value_order['shift_index'];
                            $orderdata['ShiftOrderBy']['schedule_id']=$schedule_id;
                            if(!$orderbymodel->addShiftOrder($orderdata)){
                                    throw new \Exception();
                            }
                        }

                        //查询数据复制到confirm表中
                        foreach ($orange_result as $key_1 => $value_1) {
                           $week2=get_week($value_1['shift_date']);
                           foreach ($tmp[$week2] as $key_2 => $value_2) {
                                $in_emps2=$empInOut['in_subunit'];
                                if(in_array($value_1['emp_number'], $empInOut['out_subunit'])){
                                    $S=array_rand($in_emps2);
                                    $emp_new2=$in_emps2[$S];
                                    unset($in_emps2[$S]);
                                }else{
                                    $emp_new2=$value_1['emp_number'];
                                }

                                $confirmmodel=new ShiftResultConfirm;
                                $datas['ShiftResultConfirm']['schedule_id']= $schedule_id;
                                $datas['ShiftResultConfirm']['emp_number']= $emp_new2;
                                $datas['ShiftResultConfirm']['shift_type_id']= $value_1['shift_type_id'];
                                $datas['ShiftResultConfirm']['shift_date']= $value_2['shift_date'];
                                $datas['ShiftResultConfirm']['shift_type_name']= $value_1['shift_type_name'];
                               
                                $datas['ShiftResultConfirm']['leave_type']=$value_1['leave_type'];
                                $datas['ShiftResultConfirm']['rest_type']=$value_1['rest_type'];
                                $datas['ShiftResultConfirm']['leave_type_id']=$value_1['leave_type_id'];
                                

                                if(isset($typeList[$value_1['shift_type_id']])){
                                    $typeEmtity=$typeList[$value_1['shift_type_id']];
                                    if(($typeEmtity['start_time']!='00:00:00'&&!empty($typeEmtity['start_time']))|| ($typeEmtity['end_time_afternoon']!='00:00:00'&&!empty($typeEmtity['start_time']))){
                                        $datas['ShiftResultConfirm']['frist_type_id']=$value_1['shift_type_id'];
                                    }else{
                                         $datas['ShiftResultConfirm']['frist_type_id']='';
                                    }

                                    if(($typeEmtity['start_time_afternoon']!='00:00:00'&&!empty($typeEmtity['start_time_afternoon']))|| ($typeEmtity['end_time']!='00:00:00'&&!empty($typeEmtity['end_time']))){
                                        $datas['ShiftResultConfirm']['second_type_id']=$value_1['shift_type_id'];
                                    }else{
                                         $datas['ShiftResultConfirm']['second_type_id']='';
                                    }

                                    if(($typeEmtity['time_start_third']!='00:00:00'&&!empty($typeEmtity['time_start_third']))|| ($typeEmtity['time_end_third']!='00:00:00'&&!empty($typeEmtity['time_end_third']))){
                                        $datas['ShiftResultConfirm']['third_type_id']=$value_1['shift_type_id'];
                                    }else{
                                         $datas['ShiftResultConfirm']['third_type_id']='';
                                    }


                                }else{
                                    $datas['ShiftResultConfirm']['frist_type_id']='';
                                    $datas['ShiftResultConfirm']['second_type_id']='';
                                    $datas['ShiftResultConfirm']['third_type_id']='';

                                }
                                
                                if(!$confirmmodel->addConfrim($datas)){
                                    throw new \Exception();
                                }
                              
                           }
                        }
                    }
                }
            }else if(count($model_result)!=0 && $schedule_type==2){//如果表中有历史数据，并且是java引擎排班，但是未插入引擎最新结果
                $sa=$schedulemodel->shiftResultInsert($schedule_id,$workStation); 
            }
        }
        

        $weekArr=array('1'=> '周一', '2'=> '周二', '3'=>'周三','4'=>'周四', '5'=> '周五', '6'=>'周六','0'=>'周日');

        foreach ($shift_date as $key => $value) {
           $index=get_week($value['shift_date']);
           if($index==1){
                $tmp=$key;
           }

            $shift_types=$confirmmodel->typeCountJudge($schedule_id,$value['shift_date'],$shiftTypes);

            $counttype=array_column($shift_types, 'diff');
          
            $tmp1=0;
            $tmp2=0;
            foreach ($counttype as $k => $v) {
               if($v<0){
                 $tmp1+=$v;
               }
               if($v>0){
                $tmp2+=$v;
               }
            }

            if($is_confirm==1){
                $message='';
                $isError=false;
            }else{
                if($tmp1==0 && $tmp2==0){
                 $message='';
                 $isError=false;
                }else if($tmp1==0 && $tmp2!=0){
                 $message='+'.$tmp2;
                 $isError=true;
                }else if($tmp1!=0 && $tmp2==0){
                 $message=$tmp1;
                 $isError=true;
                }else{
                  $message=$tmp1.',+'.$tmp2;
                  $isError=true;
                }
            }




           $week['id']=$value['id'];
           $week['title']=$weekArr[$index];
           $week['type']=0;
           $week['isError']=$isError;
           $week['errorMessage']=$message;

           $date['id']=$value['id'];
           $date['date']=$value['shift_date'];
           $date['type']=0;

           $data['dateList'][$key]=$date;
           $data['titleList'][$key]=$week;
        }


        $tmp2=array();

        $tmp2['id']='';
        $tmp2['title']='';
        $tmp2['type']='2';
        $tmp2['date']='';

        $tmp_arr1['titleList']=$tmp2;
        $tmp_arr2['dateList']=$tmp2;

        array_splice($data['titleList'], $tmp, 0, $tmp_arr1);
        array_splice($data['dateList'], $tmp, 0, $tmp_arr2);


        if($copy_type=="two"){
            array_unshift($data['titleList'],$tmp2);
            array_unshift($data['dateList'],$tmp2);
        }

        $new_result=$resultmodel->formatData($schedule_id,$workStation,$data,$is_confirm,$copy_type,$schedule);
        $new_result=array_values($new_result);
        
        $last['is_confirm']=$is_confirm;
        $last['is_status']=(int)$is_status;
        $last['scheudle_type']=(int)$schedule_type;
        $last['titleData']=$data;
        $last['cellBackground']=$newclor;
        $last['table']=$new_result;
        
        if(count($new_result)>0){
            $this->serializer['status']   = true;
            $this->serializer['message'] = "获取排班列表成功";
            return $last;
       
        }else{
            $this->serializer['errno']   = 2000060000;
            $this->serializer['status']   = false;
            $this->serializer['message'] = "获取失败";
        }
    }
    /**
     * @SWG\Post(path="/shift-result/shift-type",
     *     tags={"云平台-shift-result-排班结果"},
     *     summary="获取班次信息",
     *     description="获取班次信息",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "Token",
     *        description = "Token 310aa76f13eb634e0894b43bd25f0bfefa196b4b",
     *        required = true,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "schedule_id",
     *        description = "班次计划id",
     *        required = true,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "shift_date",
     *        description = "日期",
     *        required = false,
     *        type = "integer",
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "返回技能类型列表"
     *     ),
     * )
     *
    **/
    public function actionShiftType()
    {
        $post=Yii::$app->request->post();
        $shiftType=new ShiftType;
        $confirmmodel=new ShiftResultConfirm;
        $work_station=$this->workStation;;
        $shiftTypeList=$shiftType->getShifType($work_station);
        $shift_date=isset($post['shift_date'])?$post['shift_date']:'1970-01-01';
        $schedule_id=$post['schedule_id'];
        $week=get_week($shift_date);
        $shift_date=strtotime($shift_date);
        $shift_date=date('Y-m-d',$shift_date);
        $dateforshift=$confirmmodel->getShiftResultByDate($schedule_id,$shift_date);

        $shift_types=array();
        if(isset($dateforshift)&&!empty($dateforshift)){
            foreach ($shiftTypeList as $key_1 => $value_1) {
                $weeks=json_decode($value_1['week_select']);
                $typeid=$value_1['id'];
                foreach ($weeks as $key_2 => $value_2) {
                   $format[$typeid][$value_2]['week']=$value_2;
                   $format[$typeid][$value_2]['id']=$typeid;
                   $format[$typeid][$value_2]['require_employee']=$value_1['require_employee'];
                   $format[$typeid][$value_2]['name']=$value_1['name'];
                }
            }


            //获取某一天的所有班次
            foreach ($dateforshift as $key_3 => $value_3) {
                $week_3=get_week($value_3['shift_date']);
                $typeid_3=$value_3['shift_type_id'];
                $format3[$typeid_3][$week_3]['week']=$week_3;
                $format3[$typeid_3][$week_3]['id']=$typeid_3;
                $format3[$typeid_3][$week_3]['totaltype']=$value_3['totaltype'];
            }


            foreach ($format as $key_4 => $value_4) {
                
                //原则上班次特定天所需人数
                if(isset($value_4[$week])){
                    $required=$value_4[$week]['require_employee'];
                    
                    if(isset($format3[$key_4][$week])){
                        //实际该天该班次个数
                        $count_now=$format3[$key_4][$week]['totaltype'];
                    }else{
                        $count_now=0;
                    }

                    $data['id']=$value_4[$week]['id'];
                    $data['name']=$value_4[$week]['name'];
                    $data['require_employee']=$required;
                    $data['now_have']=$count_now;
                    $data['diff']=$count_now-$required;
                    $shift_types[$key_4]=$data;

                }

            }
        }else{
            foreach ($shiftTypeList as $key => $value) {
                $data['id']=$value['id'];
                $data['name']=$value['name'];
                $data['require_employee']=$value['require_employee'];
                $data['diff']=0;
                $shift_types[$key]=$data;
                
            }
        }



        if(count($shift_types)>0){
        
            $this->serializer['status']   = true;
            $this->serializer['message'] = "获取班次成功";
            return $shift_types;
            
        }else{
            $this->serializer['errno']   = 2000060000;
            $this->serializer['status']   = false;
            $this->serializer['message'] = "改天没有班次";
            
        }
    }

    /**
     * @SWG\Post(path="/shift-result/shift-type-judge",
     *     tags={"云平台-shift-result-排班结果"},
     *     summary="判断所有天的班次类型是否足够",
     *     description="判断所有天的班次类型是否足够",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "Token",
     *        description = "Token 310aa76f13eb634e0894b43bd25f0bfefa196b4b",
     *        required = true,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "schedule_id",
     *        description = "班次计划id",
     *        required = true,
     *        type = "integer",
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "返回技能类型列表"
     *     ),
     * )
     *
    **/
    public function actionShiftTypeJudge()
    {
        $post=Yii::$app->request->post();
        $shiftType=new ShiftType;
        $confirmmodel=new ShiftResultConfirm;
        $orderbymode=new ShiftOrderBy;
        $datemmodel=new ShiftDate;
        $resultmodel=new ShiftResult;
        $work_station=$this->workStation;
        $shiftTypeList=$shiftType->getShifType($work_station);
       
        $schedule_id=$post['schedule_id'];

        $schedule=Schedule::find()->where('id =:schedule_id ',[':schedule_id'=>$schedule_id])->one();
        $is_confirm=$schedule->is_confirm;
        if(!$schedule){
            $this->serializer['errno']   = '422';
            $this->serializer['status']   = false;
            $this->serializer['message'] = '没有该计划';
            return [];
        }
    
        $copy_type=$schedule->copy_type;

        //根据schedule获取日期列别
        $date_list=$datemmodel->getShiftDateListBySchedule($schedule_id);


        $weekArr=array('1'=> '周一', '2'=> '周二', '3'=>'周三','4'=>'周四', '5'=> '周五', '6'=>'周六','0'=>'周日');

        foreach ($date_list as $key => $value) {
           $index=get_week($value['shift_date']);
           if($index==1){
                $tmp=$key;
           }

            $shift_types=$confirmmodel->typeCountJudge($schedule_id,$value['shift_date'],$shiftTypeList);

            $counttype=array_column($shift_types, 'diff');
          
            $tmp1=0;
            $tmp2=0;
            foreach ($counttype as $k => $v) {
               if($v<0){
                 $tmp1+=$v;
               }
               if($v>0){
                $tmp2+=$v;
               }
            }
            if($is_confirm==1){
                $message='';
                $isError=false;
            }else{
                if($tmp1==0 && $tmp2==0){
                 $message='';
                 $isError=false;
                }else if($tmp1==0 && $tmp2!=0){
                 $message='+'.$tmp2;
                 $isError=true;
                }else if($tmp1!=0 && $tmp2==0){
                 $message=$tmp1;
                 $isError=true;
                }else{
                  $message=$tmp1.',+'.$tmp2;
                  $isError=true;
                }
            }
            

           $week['id']=$value['id'];
           $week['title']=$weekArr[$index];
           $week['type']=0;
           $week['isError']=$isError;
           $week['errorMessage']=$message;

           $date['id']=$value['id'];
           $date['date']=$value['shift_date'];
           $date['type']=0;

           $data['dateList'][$key]=$date;
           $data['titleList'][$key]=$week;

        }


        $tmp2=array();

        $tmp2['id']='';
        $tmp2['title']='';
        $tmp2['type']='2';
        $tmp2['date']='';

        $tmp_arr1['titleList']=$tmp2;
        $tmp_arr2['dateList']=$tmp2;

        array_splice($data['titleList'], $tmp, 0, $tmp_arr1);
        array_splice($data['dateList'], $tmp, 0, $tmp_arr2);


        if($copy_type=="two"){
            array_unshift($data['titleList'],$tmp2);
            array_unshift($data['dateList'],$tmp2);
        }

        $new_result=$resultmodel->formatData($schedule_id,$work_station,$data,$is_confirm,$copy_type,$schedule);

        foreach ( $new_result as $keyr => $valuer) {
           unset($valuer['cells']);
           $newresult[$keyr]=$valuer;
        }

       
        
        $newresult=array_values($newresult);
        $data['table']=$newresult;
        if(count($shift_types)>0){
        
            $this->serializer['status']   = true;
            $this->serializer['message'] = "获取成功";
            return $data;
            
        }else{
            $this->serializer['errno']   = 2000060000;
            $this->serializer['status']   = false;
            $this->serializer['message'] = "获取失败";
            
        }
    }


    /**
     * @SWG\Post(path="/shift-result/leave-type",
     *     tags={"云平台-shift-result-排班结果"},
     *     summary="获取假期信息",
     *     description="获取假期信息",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "Token",
     *        description = "Token 310aa76f13eb634e0894b43bd25f0bfefa196b4b",
     *        required = true,
     *        type = "integer",
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "返回技能类型列表"
     *     ),
     * )
     *
    **/
    public function actionLeaveType()
    {

        $post=Yii::$app->request->post();
        $leavemodel=new LeaveEntitlement;

        $leaveTypeList=$leavemodel->getLeaveTypeList();
        if(count($leaveTypeList)>0){
            foreach ($leaveTypeList as $key => $value) {
                if($value['id'] < 5){
                    $data['id']=1;
                    $data['name']='假';
                    $leave_types[0]=$data;
                }else{
                    $data['id']=$value['id'];
                    $data['name']=$value['name'];
                    $leave_types[]=$data;
                }
                
                
            }

            $rest['10']['id']='-3';
            $rest['10']['name']='公休';
            $rest['11']['id']='-1';
            $rest['11']['name']='夜休';
            $rest['12']['id']='-2';
            $rest['12']['name']='补休';
            $leave_types=array_merge($rest,$leave_types);
     
            $this->serializer['status']   = true;
            $this->serializer['message'] = "获取假期成功";
            return $leave_types;
            
        }else{
            $this->serializer['errno']   = 2000060000;
            $this->serializer['status']   = false;
            $this->serializer['message'] = "获取假期失败";
            
        }
    }

    /**
     * @SWG\Post(path="/shift-result/shift-del",
     *     tags={"云平台-shift-result-排班结果"},
     *     summary="班->休息，假->休",
     *     description="班->休息，假->休",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "Token",
     *        description = "Token 310aa76f13eb634e0894b43bd25f0bfefa196b4b",
     *        required = true,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "schedule_id",
     *        description = "班次计划id",
     *        required = true,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "type_id",
     *        description = "班次类型id",
     *        required = true,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "result_id",
     *        description = "员工班次排班班次id",
     *        required = true,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "add_type",
     *        description = "3班次变休息/2休假表为休息",
     *        required = true,
     *        type = "integer",
     *        default = 3,
     *        enum = {3,2,1}
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "返回技能类型列表"
     *     ),
     * )
     *
    **/
    public function actionShiftDel()
    {
        $isLeader=$this->isLeader;
        $isShiftManager=$this->isShiftManager;
        if(!$isShiftManager && !$isLeader){
            $this->serializer['errno']   = '422';
            $this->serializer['status']   = false;
            $this->serializer['message'] = '权限不够，只有排班管理员权限可以';
            return [];
        }
        $post=Yii::$app->request->post();
        $schedule_id=$post['schedule_id'];
        $result_id=$post['result_id'];
        $add_type=$post['add_type'];
        $confirmmodel=new ShiftResultConfirm;
        $typemodel=new ShiftType;
        $work_station=$this->workStation;

        if($confirmmodel->delShiftOrLeave($schedule_id,$result_id,$add_type)){
            $entity=$confirmmodel->getConfrimResultById($result_id,$schedule_id);
            $emp_number=$entity->emp_number;

            $confirmmodel->setLeaves($schedule_id,$emp_number);
            $result=$confirmmodel->getShiftOneEmpFormat($schedule_id,$work_station,$emp_number);
    
            $data['cells']=$result[$emp_number]['cells'];

            $this->serializer['status']   = true;
            $this->serializer['message'] = "修改成功";
            return $data;
        }else{
            $this->serializer['errno']   = 2000060000;
            $this->serializer['status']   = false;
            $this->serializer['message'] = "修改失败";
        }
    }

    /**
     * @SWG\Post(path="/shift-result/shift-add",
     *     tags={"云平台-shift-result-排班结果"},
     *     summary="班->休，班->假，班->班",
     *     description="班->休，班->假，班->班",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "Token",
     *        description = "Token 310aa76f13eb634e0894b43bd25f0bfefa196b4b",
     *        required = true,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "schedule_id",
     *        description = "班次计划id",
     *        required = true,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "result_id",
     *        description = "员工班次排班班次id",
     *        required = true,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "add_type",
     *        description = "1班->休，2班->假，3班->班",
     *        required = true,
     *        type = "integer",
     *        default = 1,
     *        enum = {1,2,3}
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "orange_type_id",
     *        description = "原班次类型ID",
     *        required = true,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "type_id",
     *        description = "班次或假期类型id",
     *        required = true,
     *        type = "integer",
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "返回技能类型列表"
     *     ),
     * )
     *
    **/
    public function actionShiftAdd()
    {

        $isLeader=$this->isLeader;
        $isShiftManager=$this->isShiftManager;
        if(!$isShiftManager && !$isLeader){
            $this->serializer['errno']   = '422';
            $this->serializer['status']   = false;
            $this->serializer['message'] = '权限不够，只有排班管理员权限可以';
            return [];
        }

        $post=Yii::$app->request->post();
        $schedule_id=$post['schedule_id'];
        $result_id=$post['result_id'];
        $add_type=$post['add_type'];
        $type_id=$post['type_id'];
        $confirmmodel=new ShiftResultConfirm;
        $empskillmodel=new EmpSkill;
        $resultmodel=new ShiftResult;
        $empskill=array();
        $requireskill=array();
        $errLeaveMes='';
        $errSkillMes='';
        $work_station=$this->workStation;
        $emp_skill_new=array();
        //判断被调班员工一周是否超过两天公休，如果超过两天公休，且新增班次为半天班，则赋予半天假
        $orangeEntity=$confirmmodel->getConfrimResultById($result_id,$schedule_id);
        $emp_number=$orangeEntity->emp_number;
        $if_rest_over=$confirmmodel->ifRestOver($schedule_id,$orangeEntity->emp_number);
        if($confirmmodel->updateShiftRest($schedule_id,$result_id,$add_type,$type_id,$if_rest_over)){

            $confirmmodel->setLeaves($schedule_id,$emp_number);
            $result=$confirmmodel->getShiftOneEmpFormat($schedule_id,$work_station,$emp_number);
    
            $data['cells']=$result[$emp_number]['cells'];
            $this->serializer['status']   = true;
            $this->serializer['message'] = "修改成功";
            return  $data;
        }else{
            $this->serializer['errno']   = 2000060000;
            $this->serializer['status']   = false;
            $this->serializer['message'] = "修改失败";
        }
    }

    /**
     * @SWG\Post(path="/shift-result/leave-add",
     *     tags={"云平台-shift-result-排班结果"},
     *     summary="假->班，假->假，假->休",
     *     description="假->班，假->假，假->休",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "Token",
     *        description = "Token 310aa76f13eb634e0894b43bd25f0bfefa196b4b",
     *        required = true,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "schedule_id",
     *        description = "班次计划id",
     *        required = true,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "result_id",
     *        description = "员工班次排班班次id",
     *        required = true,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "add_type",
     *        description = "3假->班，2假->假，1假->休",
     *        required = true,
     *        type = "integer",
     *        default = 1,
     *        enum = {1,2,3}
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "type_id",
     *        description = "班次或假期类型id",
     *        required = true,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "orange_type_id",
     *        description = "原始班次类型ID",
     *        required = true,
     *        type = "integer",
     *        default = 1,
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "返回技能类型列表"
     *     ),
     * )
     *
    **/
    public function actionLeaveAdd()
    {
        $isLeader=$this->isLeader;
        $isShiftManager=$this->isShiftManager;
        if(!$isShiftManager && !$isLeader){
            $this->serializer['errno']   = '422';
            $this->serializer['status']   = false;
            $this->serializer['message'] = '权限不够，只有排班管理员权限可以';
            return [];
        }
        $post=Yii::$app->request->post();
        $schedule_id=$post['schedule_id'];
        $result_id=$post['result_id'];
        $add_type=$post['add_type'];
        $type_id=$post['type_id'];
        $confirmmodel=new ShiftResultConfirm;
        $work_station=$this->workStation;
        $orangeEntity=$confirmmodel->getConfrimResultById($result_id,$schedule_id);
        $emp_number=$orangeEntity->emp_number;
        if($confirmmodel->updateShiftLeave($schedule_id,$result_id,$add_type,$type_id)){

            $confirmmodel->setLeaves($schedule_id,$emp_number);
            $result=$confirmmodel->getShiftOneEmpFormat($schedule_id,$work_station,$emp_number);
            $data['cells']=$result[$emp_number]['cells'];
            $this->serializer['status']   = true;
            $this->serializer['message'] = "修改成功";
            return  $data;
            
        }else{
            $this->serializer['errno']   = 2000060000;
            $this->serializer['status']   = false;
            $this->serializer['message'] = "修改失败";
        }
    }

    /**
     * @SWG\Post(path="/shift-result/shift-public",
     *     tags={"云平台-shift-result-排班结果"},
     *     summary="确认发布",
     *     description="确认发布",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "Token",
     *        description = "Token 310aa76f13eb634e0894b43bd25f0bfefa196b4b",
     *        required = true,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "schedule_id",
     *        description = "班次计划id",
     *        required = true,
     *        type = "integer",
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "返回技能类型列表"
     *     ),
     * )
     *
    **/
    public function actionShiftPublic()
    {

        $isLeader=$this->isLeader;
        $isShiftManager=$this->isShiftManager;
        if(!$isShiftManager && !$isLeader){
            $this->serializer['errno']   = '422';
            $this->serializer['status']   = false;
            $this->serializer['message'] = '权限不够，只有排班管理员权限可以';
            return [];
        }

        $post=Yii::$app->request->post();
        $schedule_id=$post['schedule_id'];
        $if_leave=isset($post['if_leave'])?$post['if_leave']:1;
        $work_station=$this->workStation;
        $confirmmodel=new ShiftResultConfirm;
        $shiftresultmodel=new ShiftResult;
        $empmodel=new Employee;
        $typemodel=new ShiftType;
        $typeList=array();
        $typeList=$typemodel->getShifType($work_station);
        $typeList=array_column($typeList, NULL,'id');
        $confirmresult=$confirmmodel->getRosterResultConfirm($schedule_id);
        

        foreach ($confirmresult as $key1 => $value1) {

            if($value1['shift_type_id']==-100){

                $this->serializer['errno']   = '422';
                $this->serializer['status']   = false;
                $this->serializer['message'] = '存在未安排的班次，不允许发布';
                return [];

            }

        }
        $transaction = Yii::$app->db->beginTransaction();
        $stat=array();
        $stat['status']   = true;
        $stat['message']   = '';
        try{ 
            if(count($confirmresult)>0){

                $sa=$shiftresultmodel->delResult($schedule_id);

                $public_static=array();
                $public_static['status']=true;
                $public_static['message']='';
  
                $public_static=$confirmmodel->publicShiftVerify($schedule_id,$work_station,$confirmresult,$if_leave);
                if($public_static['status']==false){
                    $this->serializer['status']   = false;
                    $this->serializer['message'] = $public_static['message'];
                    throw new \Exception();
                }
                
                foreach ($confirmresult as $key => $value) {
                    
                    //判断员工在其他组中是否已经有过排班
                    $shiftresultmodel=new ShiftResult;
                    $if_exist=$shiftresultmodel->getShiftByDateAndEmp($value['emp_number'],$value['shift_date']);
                    if(isset($if_exist)){
                        $emp=$empmodel->getEmpByNum($value['emp_number']);
                        $emp_name=$emp['emp_firstname'];
                        $stat['status']   = false;
                        $stat['message'] = '员工'.$emp_name.$value['shift_date'].'当天已经存在班次';
                        throw new \Exception();
                    }else{
                        $data['ShiftResult']['schedule_id']=(int)$value['schedule_id'];
                        $data['ShiftResult']['emp_number']=$value['emp_number'];
                        $data['ShiftResult']['shift_type_id']=$value['shift_type_id'];
                        $data['ShiftResult']['shift_date']=$value['shift_date'];
                        $data['ShiftResult']['shift_type_name']=$value['shift_type_name'];
                        $data['ShiftResult']['leave_type']=$value['leave_type'];
                        $data['ShiftResult']['rest_type']=$value['rest_type'];
                        $data['ShiftResult']['shift_type_id_backup']=$value['shift_type_id_backup'];

                        $data['ShiftResult']['frist_type_id']=$value['frist_type_id'];
                        $data['ShiftResult']['second_type_id']=$value['second_type_id'];
                        $data['ShiftResult']['third_type_id']=$value['third_type_id'];



                        if(!$shiftresultmodel->addShiftResult($data)){
                            $this->serializer['status']   = false;
                            $this->serializer['message'] = $shiftresultmodel->getErrors();
                            throw new \Exception();
                        }
                    }

                    
                }
                //更新schedule状态
                Schedule::updateAll(['is_confirm'=>1],['id'=>$schedule_id]);

            }


            $transaction->commit();
            $this->serializer['errno']   = 0;
            $this->serializer['status']   = true;
            $this->serializer['message'] = "发布完成"; 
        }catch(\Exception $e) {
            $transaction->rollback();
            $this->serializer['errno']   = 2000060000;
            $this->serializer['status']   = false;
            if($public_static['status']==false){
                 $this->serializer['message'] = $public_static['message']; 
            }else{
                $this->serializer['message'] = ($stat['status']==false)?$stat['message']:"发布失败"; 
            }
            
        }

    }

    /**
     * @SWG\Post(path="/shift-result/shift-as-orange",
     *     tags={"云平台-shift-result-排班结果"},
     *     summary="保存为模板",
     *     description="保存为模板",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "Token",
     *        description = "Token 310aa76f13eb634e0894b43bd25f0bfefa196b4b",
     *        required = true,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "schedule_id",
     *        description = "班次计划id",
     *        required = true,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "name",
     *        description = "模板名称",
     *        required = true,
     *        type = "integer",
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "返回技能类型列表"
     *     ),
     * )
     *
    **/
    public function actionShiftAsOrange()
    {
        $isLeader=$this->isLeader;
        $isShiftManager=$this->isShiftManager;
        if(!$isShiftManager && !$isLeader){
            $this->serializer['errno']   = '422';
            $this->serializer['status']   = false;
            $this->serializer['message'] = '权限不够，只有排班管理员权限可以';
            return [];
        }
        $post=Yii::$app->request->post();
        $schedule_id=$post['schedule_id'];
        $name=$post['name'];
        $work_station=$this->workStation;

        $confirmmodel=new ShiftResultConfirm;
        $shiftorangemodel=new ShiftResultOrange;
        $shiftmodel=new ShiftModel;

        $schedule=Schedule::find()->where('id =:schedule_id ',[':schedule_id'=>$schedule_id])->one();

        if($schedule->copy_type=='two'){
            $this->serializer['errno']   = 2000060000;
            $this->serializer['status']   = false;
            $this->serializer['message'] = '只允许以一周为模板'; 
            return [];
        }
       
        $schedule_type=$schedule->schedule_type;
        $confirmresult=$confirmmodel->getRosterResultConfirm($schedule_id);
        $shiftresult=$shiftorangemodel->getShiftResult($schedule_id);
        $transaction = Yii::$app->db->beginTransaction();
        try{ 
            if(count($confirmresult)>0){

                //保存模板
                $modeldata['ShiftModel']['name']=$name;
                $modeldata['ShiftModel']['type']=$schedule_type;
                $modeldata['ShiftModel']['work_station']=$work_station;
                $modeldata['ShiftModel']['schedule_id']=$schedule_id;

                if(!$shiftmodel->addModel($modeldata)){
                     throw new \Exception();
                }

                $shiftmodel_key=$shiftmodel->getPrimaryKey();


                // $shiftorangemodel->delResult($schedule_id);
                foreach ($confirmresult as $key => $value) {
                    $shiftorangemodel=new ShiftResultOrange;
                    $data['ShiftResultOrange']['schedule_id']=(int)$value['schedule_id'];
                    $data['ShiftResultOrange']['emp_number']=$value['emp_number'];
                    $data['ShiftResultOrange']['shift_type_id']=$value['shift_type_id'];
                    $data['ShiftResultOrange']['shift_date']=$value['shift_date'];
                    $data['ShiftResultOrange']['shift_type_name']=$value['shift_type_name'];
                    $data['ShiftResultOrange']['leave_type']=$value['leave_type'];
                    $data['ShiftResultOrange']['rest_type']=$value['rest_type'];
                    $data['ShiftResultOrange']['leave_type_id']=$value['leave_type_id'];
                    $data['ShiftResultOrange']['model_id']=$shiftmodel_key;
                    if(!$shiftorangemodel->addShiftOrange($data)){
                        $this->serializer['status']   = false;
                        $this->serializer['message'] = $shiftorangemodel->getErrors();
                        throw new \Exception();
                    }
                }

            }
            $transaction->commit();
            $this->serializer['errno']   = 0;
            $this->serializer['status']   = true;
            $this->serializer['message'] = "保存完成"; 
        }catch(\Exception $e) {
            $transaction->rollback();
            $this->serializer['errno']   = 2000060000;
            $this->serializer['status']   = false;
            $this->serializer['message'] = $shiftorangemodel->getErrors(); 
        }

    }

    /**
     * @SWG\Post(path="/shift-result/shift-roll",
     *     tags={"云平台-shift-result-排班结果"},
     *     summary="循环排班表",
     *     description="循环排班表",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "Token",
     *        description = "Token 310aa76f13eb634e0894b43bd25f0bfefa196b4b",
     *        required = true,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "schedule_id",
     *        description = "班次计划id",
     *        required = true,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "emp_nums",
     *        description = "员工工资号，格式为[800,801,802]",
     *        required = true,
     *        type = "string",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "shift_dates",
     *        description = "时间组，格式为[2018-08-13,2018-08-14,2018-08-13]",
     *        required = true,
     *        type = "string",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "roll_order",
     *        description = "循环顺序:1正循环,2 反循环",
     *        required = true,
     *        type = "string",
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "返回技能类型列表"
     *     ),
     * )
     *
    **/
    public function actionShiftRoll()
    {   
        $isLeader=$this->isLeader;
        $isShiftManager=$this->isShiftManager;
        if(!$isShiftManager && !$isLeader){
            $this->serializer['errno']   = '422';
            $this->serializer['status']   = false;
            $this->serializer['message'] = '权限不够，只有排班管理员权限可以';
            return [];
        }
        $post=Yii::$app->request->post();
        $schedule_id=$post['schedule_id'];
        $work_station=$this->workStation;
        //获取要要循环的ID
        $change_shift=array();
        $assignment_list=array();
        $emp_news=array();
        $change_shift=json_decode($post['emp_nums']);
        $shift_dates=array();
        $shift_dates=json_decode($post['shift_dates']);
        $confirmmodel=new ShiftResultConfirm;
        $shiftordermodel=new ShiftOrderBy;
        $employeemodel=new Employee;
        $roll_sort=isset($post['roll_order'])?$post['roll_order']:'1';
        $transaction = Yii::$app->db->beginTransaction();
        try{
         
            $assignment_list=$confirmmodel->getShiftResultByContions($schedule_id,$change_shift,$shift_dates);

            $emp_news=$shiftordermodel->getShiftOrderBy($schedule_id);

            $emp_new=array_column($emp_news, 'emp_number');
            if(count($emp_new)>0){
                $employ_list=$emp_new;
            }else{
                $employeeList=$employeemodel->group($work_station);
                $employ_list=array_column($employeeList,'emp_number');
            }
            $employ_list=array_unique($employ_list);
            $employ_list_assignment=array_column($assignment_list,'emp_number');
            $employ_list_assignment=array_unique($employ_list_assignment);
        
            //参与循环的顺序替换
            $employee_array=array();
            foreach ($change_shift as $key => $employee) {
                if(in_array($employee, $employ_list_assignment)){
                    foreach ($assignment_list as $k => $assignment) {
                        if($assignment['emp_number']==$employee){
                            $employee_array[$employee][]=$assignment;
                        }
                    }
                }else{
                    $employee_array[$employee][]='';
                }

                if(in_array($employee, $emp_new)){
                     foreach ($emp_news as $k => $orderIndex) {
                        if($orderIndex['emp_number']==$employee){
                            $orderByEmp[$employee][]=$orderIndex;
                        }
                     }
                }
            }

            $arr_keys=array_keys($employee_array);
            $order_keys=array_keys($orderByEmp); 

            //循环排序
            if($roll_sort==1){
                array_unshift($arr_keys, array_pop($arr_keys));
                array_unshift($order_keys, array_pop($order_keys));
            }else{
                array_push($arr_keys, array_shift($arr_keys));
                array_push($order_keys, array_shift($order_keys));
            }

            $array_ab=array_combine($arr_keys,$employee_array);
            $order_ab=array_combine($order_keys,$orderByEmp);

            //存储交换的排班结果
            if(count($array_ab)>0){
                foreach ($array_ab as $key => $new_result) {
                    foreach ($new_result as $k => $v) {
                        if(null!=$v){
                            $shiftconfirmodel=new ShiftResultConfirm;
                            if(!$shiftconfirmodel->updateResultEmp($v['id'],$key)){
                                throw new \Exception();
                            }
                        }
                    }
                }
            }

            if(count($order_ab)>0){
                foreach ($order_ab as $key_o => $new_o) {
                    foreach ($new_o as $k_o => $v_o) {
                        if(null!=$v_o){
                            $shifordermodel=new ShiftOrderBy;
                            if(!$shifordermodel->updateOrderby($v_o['id'],$key_o)){
                                throw new \Exception();
                            }
                        }
                    }
                }
            }

            $transaction->commit();
            $this->serializer['errno']   = 0;
            $this->serializer['status']   = true;
            $this->serializer['message'] = "循环成功"; 

        }catch(\Exception $e) {
            $transaction->rollback();
            $this->serializer['errno']   = 2000060000;
            $this->serializer['status']   = false;
            $this->serializer['message'] = "循环失败"; 
        }
        

    }

    /**
     * @SWG\Post(path="/shift-result/change-emp-order",
     *     tags={"云平台-shift-result-排班结果"},
     *     summary="人员顺序替换",
     *     description="人员顺序替换",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "Token",
     *        description = "Token 310aa76f13eb634e0894b43bd25f0bfefa196b4b",
     *        required = true,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "schedule_id",
     *        description = "班次计划id",
     *        required = true,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "first_emp",
     *        description = "第一个员工工资号",
     *        required = true,
     *        type = "string",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "second_emp",
     *        description = "第二个员工工资号",
     *        required = true,
     *        type = "string",
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "返回技能类型列表"
     *     ),
     * )
     *
    **/
    public function actionChangeEmpOrder(){
        $post=Yii::$app->request->post();

        $first_emp=$post['first_emp'];
        $second_emp=$post['second_emp'];
        $schedule_id=$post['schedule_id'];

        $shiftordermode=new ShiftOrderBy;
        $transaction = Yii::$app->db->beginTransaction();
        try{

            $first_entity=$shiftordermode->getEmpShiftOrderBy($schedule_id,$first_emp);
            $second_entity=$shiftordermode->getEmpShiftOrderBy($schedule_id,$second_emp);

            $first_order=$first_entity->shift_index;
            $second_order=$second_entity->shift_index;

            if($first_order < $second_order){//由上往下拖动
                //拖动的员工顺序变成脆后一个
                //其余的顺序同时减去1
                $emp_list=$shiftordermode->getEmpShiftOrderDur($schedule_id,$first_order,$second_order);
                foreach ($emp_list as $key => $value) {

                    if($value->shift_index==$first_order){
                        $value->shift_index=$second_order;
                    }else{
                        $value->shift_index=$value->shift_index-1;
                    }

                    if(!$value->save()){
                        throw new \Exception();
                    }
                }
            }
            if($first_order > $second_order){//由上往下拖动
                //发起拖动的变为第一个，
                //其余的顺序同时+1
                $emp_list=$shiftordermode->getEmpShiftOrderDur($schedule_id,$second_order,$first_order);
                foreach ($emp_list as $key => $value) {
                    if($value->shift_index==$first_order){
                        $value->shift_index=$second_order;
                    }else{
                        $value->shift_index=$value->shift_index+1;
                    }

                    if(!$value->save()){
                        throw new \Exception();
                    }
                    
                }

            }
        
            $transaction->commit();
            $this->serializer['errno']   = 0;
            $this->serializer['status']   = true;
            $this->serializer['message'] = "调序完成"; 
        }catch(\Exception $e) {
            $transaction->rollback();
            $this->serializer['errno']   = 2000060000;
            $this->serializer['status']   = false;
            $this->serializer['message'] = '调序失败'; 
        }
    }


    /**
     * @SWG\Post(path="/shift-result/result-find",
     *     tags={"云平台-shift-result-排班结果"},
     *     summary="搜索排班",
     *     description="搜索排班",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "Token",
     *        description = "Token 310aa76f13eb634e0894b43bd25f0bfefa196b4b",
     *        required = true,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "start_date",
     *        description = "开始时间",
     *        required = false,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "end_date",
     *        description = "结束时间",
     *        required = false,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "type_id",
     *        description = "type_id",
     *        required = false,
     *        type = "string",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "emp_num",
     *        description = "选择员工",
     *        required = false,
     *        type = "string",
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "返回技能类型列表"
     *     ),
     * )
     *
    **/
    public function actionResultFind()
    {
        $isLeader=$this->isLeader;
        $isShiftManager=$this->isShiftManager;
        if(!$isShiftManager && !$isLeader){
            $this->serializer['errno']   = '422';
            $this->serializer['status']   = false;
            $this->serializer['message'] = '权限不够，只有排班管理员权限可以';
            return [];
        }

        $post=Yii::$app->request->post();
        $work_station=$this->workStation;

        $start_date=isset($post['start_date'])?$post['start_date']:'';
        $end_date=isset($post['end_date'])?$post['end_date']:'';

        $start_date=strtotime($start_date);
        $start_date=date('Y-m-d',$start_date);

        $end_date=strtotime($end_date);
        $end_date=date('Y-m-d',$end_date);
        $type_id=isset($post['type_id'])?$post['type_id']:'';
        $emp_num=isset($post['emp_num'])?$post['emp_num']:'';
        $resultmodel=new ShiftResult;
        //获取部门员工
        $empmodel=new Employee;
        $emplist=$empmodel->getEmpByWorkStation($work_station);
        $emplist=array_column($emplist, "emp_firstname",'emp_number');

        $typemodel  = new ShiftType;
        $typelist = $typemodel->getShifType($work_station);
        $shiftTypes = array_column($typelist, 'name', 'id');

        foreach ($shiftTypes as $key_3 => $value_3) {
            $newtype[$key_3]['title']=$value_3;
        }

        $pageSize = Yii::$app->params['pageSize']['shift'];
        $datas=$resultmodel->getResultList($work_station,$start_date,$end_date,$type_id,$emp_num);
        $new_combine=array();
        foreach ($datas as $key => $value) {
            $new_combine[$key]['id']=$key+1;
            $new_combine[$key]['empName']=$emplist[$value['emp_number']];
            $types=explode(',', $value['type']);
            $new_type=array_count_values($types);
 
            foreach ($shiftTypes as $key_2 => $value_2) {
               $combine[$key_2]['count']=0;
               $combine[$key_2]['name']=$value_2;
               foreach ($new_type as $key_1 => $value_1){
                    if($key_1==$key_2){
                        $combine[$key_2]['count']=$value_1;
                        $combine[$key_2]['name']=$value_2;
                    }
               }
            }

            $new_combine[$key]['cell']=array_values($combine);
            
        }

        $data_format['titleList']=array_values($newtype);
        $data_format['tableData']=$new_combine;

        if(isset($new_combine)&&count($new_combine)>0){
            $this->serializer['errno']   = 0;
            $this->serializer['status']   = true;
            $this->serializer['message'] = "获取成功"; 
            return $data_format;
        }else{
            $this->serializer['errno']   = 0;
            $this->serializer['status']   = true;
            $this->serializer['message'] = "没有班次信息"; 

            return $data_format;
        }
    }

    /**
     * @SWG\Post(path="/shift-result/statistics",
     *     tags={"云平台-shift-result-排班结果"},
     *     summary="统计排班",
     *     description="统计排班",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "Token",
     *        description = "Token 310aa76f13eb634e0894b43bd25f0bfefa196b4b",
     *        required = true,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "shift_date",
     *        description = "开始时间",
     *        required = false,
     *        type = "integer",
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "返回技能类型列表"
     *     ),
     * )
     *
    **/
    public function actionStatistics()
    {
        $isAdmin=$this->userRoleId;
        if(!$isAdmin==1){
            $this->serializer['errno']   = '422';
            $this->serializer['status']   = false;
            $this->serializer['message'] = '权限不够，只有管理员可以看';
            return [];
        }

        $customerId=$this->customerId;
       
        $post=Yii::$app->request->post();
        $usermodel=new User;
        $employeemodel=new Employee;
        $shifttypemodel=new ShiftType;
        $shiftconfirmodel=new ShiftResultConfirm;
        $work_station_list_all=$usermodel->getSmallSubunit($customerId);
        $work_station_list=array_column($work_station_list_all, 'id');
        $shifresultmodel = new ShiftResult;
        $shift_date=$post['shift_date'];
        $shift_date=strtotime($shift_date);
        $shift_date=date('Y-m-d',$shift_date);
        $data=$shifresultmodel->searchShifts($shift_date,$work_station_list);
        $employeeList=$employeemodel->getEmployeeByDocument($work_station_list);
        $emp_num_list=array_column($employeeList, 'emp_number');
        foreach ($employeeList as $emp_key => $emp_value) {
            $emp_tmp[$emp_value['work_station']][]=$emp_value;

        } 

        $employeeList=array_column($employeeList,null, 'emp_number');
        $allShiftTypeList=$shifttypemodel->getShifType($work_station_list);
        $allShiftTypeList=array_column($allShiftTypeList,null, 'id');
        foreach($data as $key => $value) {
            $work_station_id=$value['location_id'];
            $type_id=$value['shift_type_id'];
            $work_station[$work_station_id]=$work_station_id;
            $type_name=$allShiftTypeList[$type_id]['name'];
            $empNumber=$value['emp_number'];

            $employee=isset($employeeList[$empNumber])?$employeeList[$empNumber]['emp_firstname']:'';
            $allShift[$work_station_id][$key]['name']=$employee;
            $allShift[$work_station_id][$key]['gongzihao']=$empNumber;
            $allShift[$work_station_id][$key]['shift_type_id']=$type_id;
            $allShift[$work_station_id][$key]['shiftName']=$type_name;
        }

        $document_have=array_keys($allShift);
        $document_all=$work_station_list;
       
        //没有班次的部门
        $emp_ary=array();
        $emp_doc=array_diff($document_all, $document_have);
        
        foreach ($emp_doc as $ke=> $ve) {
            $emp_ary[$ve]=array();
        }
        foreach ($allShift as $k => $v) {
            $a=array_column($v, 'gongzihao');
            $b=array_column($emp_tmp[$k], 'emp_number');
            $diff=array();
            $empty_emp=array();
            $diff=array_diff($b, $a);
            $on_count[$k]=count($v);
            if(count($diff)>0){
                foreach ($diff as $diff_key => $diff_value) {
                    $empty_emp[$diff_key]['name']=$employeeList[$diff_value]['emp_firstname'];
                    $leaveEmp=$shiftconfirmodel->getLeaveOfEmpByEmpAndDate($diff_value,$shift_date);
                    //查询该天是否有员工休假；
                    if($leaveEmp){ 
                        $empty_emp[$diff_key]['shiftName']='休假';
                    }else{
                        $empty_emp[$diff_key]['shiftName']='休息';
                    }
                }
                $v=array_merge($v,$empty_emp);
                $count[$k]=count($v);
                $allShift[$k]=array_values($v);
            }
        }
        
        $max=max($count);
        for($i=0;$i<$max;$i++){
            foreach ($document_all as $key_2 => $value_2) {
                if(isset($allShift[$value_2][$i])){
                     $tmp[$i][$value_2]=isset($allShift[$value_2][$i])?$allShift[$value_2][$i]:'';
                }else{
                    $tmp[$i][$value_2]['name']='';
                    $tmp[$i][$value_2]['shiftName']='';

                }
               
            }
        }

        foreach ($tmp as $key_2 => $value_2) {
            $index=$key_2+1;
            $new[$key_2]['num']=$index;
            $new[$key_2]['shift']=array_values($value_2);
        }

        foreach ($work_station_list_all as $key_3 => $value_3) {
           
           $titleList[$key_3]['title']=$value_3['name'];
        }

        $datanew['titleList']=array_values($titleList);
        $datanew['tableData']=$new;

        if(isset($titleList)&&count($titleList)>0){
            $this->serializer['errno']   = 0;
            $this->serializer['status']   = true;
            $this->serializer['message'] = "获取成功"; 
            return $datanew;
        }else{
            $this->serializer['errno']   = 2000060000;
            $this->serializer['status']   = true;
            $this->serializer['message'] = "没有班次信息";
            return $datanew; 
        }
        
    }

}

?>