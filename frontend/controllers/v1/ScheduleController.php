<?php
namespace frontend\controllers\v1;

use Yii;
use yii\web\Response;
use yii\helpers\Url;
use yii\web\Controller;
use yii\helpers\ArrayHelper;
use common\models\shift\Schedule;
use common\models\shift\ShiftDate;
use common\models\shift\ShiftType;
use common\models\shift\Shift;
use common\models\shift\ShiftAssignment;
use common\models\shift\Constraint;
use common\models\ConfigCustomer;
use common\models\employee\Employee;
use common\models\shift\ShiftResultConfirm;
use common\models\shift\ShiftResult;
use common\models\shift\ShiftResultOrange;
use common\models\shift\ShiftOrderBy;
use common\models\shift\ShiftModel;

use common\models\leave\LeaveEntitlement;

class ScheduleController extends \common\rest\Controller
{
    /**
     * @var string
     */
    public $modelClass = 'common\models\shift\Schedule';

    /**
     * 
     * @var array
     */
    public $serializer = [
        'class' => 'common\rest\Serializer',    // 返回格式数据化字段
        'collectionEnvelope' => 'result',       // 制定数据字段名称
        'message' => 'OK',                      // 文本提示
        'errno'   => 0,
        'status'  =>'',
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
     * @SWG\Post(path="/schedule/create",
     *     tags={"云平台-schedule-排班计划"},
     *     summary="创建排班计划",
     *     description="新建班次计划",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "Token",
     *        description = "Token",
     *        required = true,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "name",
     *        description = "计划名称",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "shift_date",
     *        description = "班次第一天",
     *        required = true,
     *        type = "string",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "copy_type",
     *        description = "选择复制类型，'one':一周, 'two':两周",
     *        required = false,
     *        type = "string",
     *        default = 0,
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "shift_type",
     *        description = "选择班次类型，格式[121,122,177,178,217,220,223,226]",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *     ),
    

     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "schedule_type",
     *        description = "排班方式，1循环排班，2，自动排班；3自选排班",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *        enum = {1,2,3}
     *     ),

     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "constrain_id",
     *        description = "规则模板",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *     ),

     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "orange_data",
     *        description = "基础数据",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *     ),

     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "model_type",
     *        description = "基础数据类型",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *     ),


     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "status",
     *        description = "状态，1是开启，0时关闭",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *        enum = {0,1}
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "is_confirm",
     *        description = "是否已经发布, 0,未发布。1发布",
     *        required = false,
     *        type = "integer",
     *        default = 0,
     *        enum = {0,1}
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "is_model",
     *        description = "是否引用模版 1不引用。0引用",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *        enum = {0,1}
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "is_show",
     *        description = "是否显示 1显示。0不显示",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *        enum = {0,1}
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
    public function actionCreate()
    {
        $post = Yii::$app->request->post();

        $location_id = $this->workStation;
        $isLeader=$this->isLeader;
        $isShiftManager=$this->isShiftManager;
        if(!$isShiftManager && !$isLeader){
            $this->serializer['errno']   = '422';
            $this->serializer['status']   = false;
            $this->serializer['message'] = '权限不够，只有排班管理员权限可以';
            return [];
        }

        //判断是否已经建立过该天的班次

        $schedulemodel=new Schedule;

        $scheduleList=array();
        $scheduleList=$schedulemodel->getScheduleListArr($location_id);
        $scheduleList=array_column($scheduleList, 'id');
        $shiftDateList=array();
        //根据计划查找shiftdate
        if(count($scheduleList)>0){
            $datemodel = new ShiftDate;
            $shiftDateList=$datemodel->getShiftDateListBySchedule($scheduleList);
            $shiftDateList=array_column($shiftDateList, 'shift_date');
        }

        

        $copy_type = $post['copy_type'];
        $shift_date = $post['shift_date'];

        if($copy_type=='one'){
            $data_to=date("Y-m-d",strtotime("+6 day",strtotime($shift_date)));
        }else if($copy_type=='two') {
            $data_to=date("Y-m-d",strtotime("+13 day",strtotime($shift_date)));
        }else if($copy_type=='three') {
            $data_to=date("Y-m-d",strtotime("+18 day",strtotime($shift_date)));
        }else{
             $data_to=date("Y-m-d",strtotime("+24 day",strtotime($shift_date)));
        }
        $date_durn=prDates($shift_date,$data_to);
        $if_diff=array();
        $if_diff= array_intersect($shiftDateList,$date_durn);


       if(count($if_diff)>0){
            $this->serializer['errno']   = 0;
            $this->serializer['status']   = false;
            $this->serializer['message'] = "已有重复日期班次"; 
            return [];
       }

       $first_week=get_week($shift_date);

       if($first_week!='1'){
            $this->serializer['errno']   = 0;
            $this->serializer['status']   = false;
            $this->serializer['message'] = "请从周一开始建立班次"; 
            return [];
       }

        

        $transaction = Yii::$app->db->beginTransaction();
        try {
            if (Yii::$app->request->isPost) {

                
                $name = $post['name'];
                $shift_date = $post['shift_date'];
                
                
                $shift_type =json_decode($post['shift_type']);
                $status = '0';
                $is_confirm =0;
                $is_model = 1;
                $create_at=date('Y-m-d',time());
                $schedule_type = (int)$post['schedule_type'];
                $constrain_id = (int)$post['constrain_id'];
                $model_type = (int)$post['model_type'];
                if($post['orange_data']){
                    $orange_data =(int)$post['orange_data'];
                }else{
                     $orange_data=0;
                }
                
                $is_show = 1;
                //保存计划

                $shift_date=strtotime($shift_date);

                $shift_date=date('Y-m-d',$shift_date);
                $schedulemodel=new Schedule;
                $schedulemodel->scenario='create';
                $schedulemodel->name=$name;
                $schedulemodel->shift_date=$shift_date;
                $schedulemodel->copy_type=$copy_type;
                $schedulemodel->status=$status;
                $schedulemodel->location_id=$location_id;
                $schedulemodel->is_show=$is_show;
                $schedulemodel->is_model=$is_model;
                $schedulemodel->is_confirm=$is_confirm;
                $schedulemodel->schedule_type=$schedule_type;
                $schedulemodel->constrain_id=$constrain_id;
                $schedulemodel->orange_data=$orange_data;
                $schedulemodel->model_type=$model_type;
                $schedulemodel->create_at=$create_at;

                if (!$schedulemodel->save()) {
                    throw new \Exception();
                }
                $shiftdatemodel=new ShiftDate;
                $scheduleid = $schedulemodel->getPrimaryKey();
                $start_date=$schedulemodel->shift_date;
                $copy_type=$schedulemodel->copy_type;
                $work_station=$schedulemodel->location_id;

               //保存排班计划日期
                foreach ($date_durn as $shiftdateoone) {
                    $model = new ShiftDate;
                    $shiftdate['shift_date'] = $shiftdateoone;
                    $shiftdate['schedule_id'] = $scheduleid;
                    $shiftdate['work_station'] = $work_station;
                    $data['ShiftDate']=$shiftdate;
                    if (!$model->add($data)) {
                        throw new \Exception();
                    }
                }

                //获取该计划的日期信息
                $shiftDateList=ShiftDate::find()->where('schedule_id=:scheduleid',[':scheduleid'=>$scheduleid])->asArray()->all();
                
                foreach ($shiftDateList as $key_date => $value_date) {
                    $value_date['week']=get_week($value_date['shift_date']);
                    $date_week[$key_date]=$value_date;
                }


                //创建shift
                foreach ($shift_type as $key => $typeid) {
                    $typeid=(int)$typeid;
                    $shiftType = ShiftType::find()->where('id = :id', [':id' => $typeid])->asArray()->one();
                    $required_employee=$shiftType['require_employee'];
                    if (null==$shiftType) {
                        throw new \Exception();
                    }

                    $week_select=json_decode($shiftType['week_select']);
                    $date_on_shift=array();
                    //获取星期对应的日期id
                    foreach ($date_week as $key_date2 => $value_date2) {
                        // var_dump($value_date2);exit;
                        if(in_array($value_date2['week'], $week_select)){
                            $date_on_shift[$key][$key_date2]=$value_date2;
                        }
                    }

                    //创建shift
                    $shift_entity=array();
                    foreach ($date_on_shift[$key] as $k => $v) {
                       
                        $shift_entity['Shift']['name']=$shiftType['name'];
                        $shift_entity['Shift']['hours_per_day']='12';
                        $shift_entity['Shift']['start_time']=$shiftType['start_time'];
                        $shift_entity['Shift']['end_time']=$shiftType['end_time'];
                        $shift_entity['Shift']['schedule_id']=$scheduleid;
                        $shift_entity['Shift']['required_employee']=$shiftType['require_employee'];
                        $shift_entity['Shift']['shift_type_id']=$typeid;
                        $shift_entity['Shift']['shiftdate_id']=$v['id'];
                        $shift_entity['Shift']['status']='0';
                        $shift_entity['Shift']['create_at']=$create_at;
            
                        $shiftmodel = new Shift;
                    
                        if(!$shiftmodel->addShifts($shift_entity)){
                            throw new \Exception();
                        }else{

                            $shift_id = $shiftmodel->getPrimaryKey();
                            for($i=0;$i<$required_employee;$i++){ 
                                $shiftAssignment = new ShiftAssignment;
                                $shift_assignment['ShiftAssignment']['schedule_id']=$scheduleid;
                                $shift_assignment['ShiftAssignment']['shift_id']=$shift_id;
                                $shift_assignment['ShiftAssignment']['shift_index']=$i;
                                $shift_assignment['ShiftAssignment']['shift_type_id']=$typeid;
                                $shift_assignment['ShiftAssignment']['shift_date']=$v['id'];
                                $shift_assignment['ShiftAssignment']['work_station']=$work_station;
                                if(!$shiftAssignment->addShiftAssigiment($shift_assignment)){
                                    throw new \Exception();
                                }
                            }
                        }
                    }

                }

            }
            $transaction->commit();

            $this->serializer['errno']   = 0;
            $this->serializer['status']   = true;
            $this->serializer['message'] = "创建成功"; 
            $dataschedule['schedule_id']=$scheduleid;
            return $dataschedule;

        }catch(\Exception $e) {
            $transaction->rollback();
            $this->serializer['errno']   = 0;
            $this->serializer['status']   = false;
            $this->serializer['message'] = "创建失败"; 
        }
        
    }


    /**
     * @SWG\Post(path="/schedule/constraint",
     *     tags={"云平台-schedule-排班计划"},
     *     summary="排班规则",
     *     description="新建班次计划",
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
     *        name = "name",
     *        description = "约束模板名称",
     *        required = true,
     *        type = "string"
     *     ),


     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "nightAfterNightLeisureShiftSelect",
     *        description = "夜班后指定班次(班次)",
     *        required = false,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "nightAfterNightLeisureWeight",
     *        description = "夜班后指定班次(权重)",
     *        required = false,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "nightAfterNightLeisureStatus",
     *        description = "夜班后指定班次(状态)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *        enum = {0,1}
     *     ),




     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "twoNightShiftSelect",
     *        description = "两夜班内指定班次和休假(班次)",
     *        required = false,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "twoNightWeight",
     *        description = "两夜班内指定班次和休假(权重)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "twoNightStatus",
     *        description = "两夜班内指定班次和休假(状态)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *        enum = {0,1}
     *     ),


    
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "shiftdOnlyforManShiftSelect",
     *        description = "班次只分配给男性(班次)",
     *        required = false,
     *        type = "string",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "shiftdOnlyforManWeight",
     *        description = "班次只分配给男性(权重)",
     *        required = false,
     *        type = "string",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "shiftdOnlyforManStatus",
     *        description = "班次只分配给男性(状态)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *        enum = {0,1}
     *     ),


     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "averageAssignmentShiftSelect",
     *        description = "班次尽量平均分配(班次)",
     *        required = false,
     *        type = "string",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "averageAssignment",
     *        description = "班次尽量平均分配(每人分配次数)",
     *        required = false,
     *        type = "string",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "averageAssignmentWeight",
     *        description = "班次尽量平均分配(权重)",
     *        required = false,
     *        type = "string",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "averageAssignmentStatus",
     *        description = "班次尽量平均分配(状态)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *        enum = {0,1}
     *     ),


     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "assignmentAfterIntervalShiftSelect",
     *        description = "该班次分配后间隔后再分配(班次)",
     *        required = false,
     *        type = "string",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "assignmentAfterIntervalEmployee",
     *        description = "该班次分配后间隔后再分配(选择间隔几周)",
     *        required = false,
     *        type = "string",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "assignmentAfterIntervalWeight",
     *        description = "该班次分配后间隔后再分配(权重)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "assignmentAfterIntervalStatus",
     *        description = "该班次分配后间隔后再分配(状态)",
     *        required = false,
     *        type = "string",
     *     ),
    


     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "shiftNotForEmployeeShiftSelect",
     *        description = "该班次不分配给某员工(班次)",
     *        required = false,
     *        type = "string",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "shiftNotForEmployee",
     *        description = "该班次不分配给某员工(选择员工)",
     *        required = false,
     *        type = "string",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "shiftDate",
     *        description = "该班次不分配给某员工(选择日期)",
     *        required = false,
     *        type = "string",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "shiftNotForEmployeeWeight",
     *        description = "该班次不分配给某员工(权重)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "shiftNotForEmployeeStatus",
     *        description = "该班次不分配给某员工(状态)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *        enum = {0,1}
     *     ),





     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "shiftForEmployeeShiftSelect",
     *        description = "该班次分配给某员工(班次)",
     *        required = false,
     *        type = "string",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "shiftForEmployee",
     *        description = "该班次分配给某员工(选择员工)",
     *        required = false,
     *        type = "string",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "shiftDateForEmployee",
     *        description = "该班次分配给某员工(选择日期)",
     *        required = false,
     *        type = "string",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "shiftForEmployeeWeight",
     *        description = "该班次分配给某员工(权重)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "shiftForEmployeeStatus",
     *        description = "该班次分配给某员工(状态)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *        enum = {0,1}
     *     ),


     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "noShiftDayEmp",
     *        description = "员工指定某天不上班(班次)",
     *        required = false,
     *        type = "string",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "noShiftDay",
     *        description = "员工指定某天不上班(日期)",
     *        required = false,
     *        type = "string",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "noShiftDayWeight",
     *        description = "员工指定某天不上班(权重)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "noShiftDayStatus",
     *        description = "员工指定某天不上班(状态)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *        enum = {0,1}
     *     ),



     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "assignmentAfterShiftSelect",
     *        description = "该班次分配后持续分配(班次)",
     *        required = false,
     *        type = "string",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "assignmentAfterShiftDays",
     *        description = "该班次分配后持续分配(选择天数)",
     *        required = false,
     *        type = "string",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "assignmentAfterShiftWeight",
     *        description = "该班次分配后持续分配(权重)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "assignmentAfterShiftStatus",
     *        description = "该班次分配后持续分配(状态)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *        enum = {0,1}
     *     ),


     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "startShiftSelect",
     *        description = "不希望此班次后继续班次(班次)",
     *        required = false,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "nextShiftSelect",
     *        description = "不希望此班次后继续班次(不希望继续的班次)",
     *        required = false,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "restAfterOneShiftWeight",
     *        description = "不希望此班次后继续班次(权重)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "restAfterOneShiftStatus",
     *        description = "不希望此班次后继续班次(状态)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *        enum = {0,1}
     *     ),


     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "threeStartShiftSelect",
     *        description = "不希望持续的三个班(第一个班)",
     *        required = false,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "threeNextShiftSelect",
     *        description = "不希望持续的三个班(第二个班)",
     *        required = false,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "threeThirdShiftSelect",
     *        description = "不希望持续的三个班(第三个班)",
     *        required = false,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "threeShiftWeight",
     *        description = "不希望持续的三个班(权重)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "threeShiftStatus",
     *        description = "不希望持续的三个班(状态)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *        enum = {0,1}
     *     ),



     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "freeTwoDaysSelect",
     *        description = "每周公休分配(选择天数)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "freeTwoDaysWeight",
     *        description = "每周公休分配(权重)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "freeTwoDaysStatus",
     *        description = "每周公休分配(状态)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *        enum = {0,1}
     *     ),



     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "allowWeekendShift",
     *        description = "最多允许连续工作几个周末(允许几个周末)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "maxWeekendShiftWeight",
     *        description = "最多允许连续工作几个周末(权重)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "maxWeekendShiftStatus",
     *        description = "最多允许连续工作几个周末(状态)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *        enum = {0,1}
     *     ),


     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "minWorkDayCount",
     *        description = "每周每人最少分配班次数(最少工作天数)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "minWorkDayWeight",
     *        description = "每周每人最少分配班次数(权重)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "minWorkDayStatus",
     *        description = "每周每人最少分配班次数(状态)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *        enum = {0,1}
     *     ),



     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "maxWorkDayCount",
     *        description = "每周每人最多分配班次数(最多工作天数)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "maxWorkDayWeight",
     *        description = "每周每人最多分配班次数(权重)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "maxWorkDayStatus",
     *        description = "每周每人最多分配班次数(状态)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *        enum = {0,1}
     *     ),


     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "gravidaCount",
     *        description = "孕妇不分配低于设置人数的班次(人数)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "gravidaWeight",
     *        description = "孕妇不分配低于设置人数的班次(权重)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "gravidaStatus",
     *        description = "孕妇不分配低于设置人数的班次(状态)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *        enum = {0,1}
     *     ),


     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "shiftsMax",
     *        description = "轮转人员先休假(最多安排几天班)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "holidaysMax",
     *        description = "轮转人员先休假(最多剩余假期)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "holidaysWeight",
     *        description = "轮转人员先休假(权重)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "holidaysStatus",
     *        description = "轮转人员先休假(状态)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *        enum = {0,1}
     *     ),

     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "maxHolidayCount",
     *        description = "最多连续休假天数(天数)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "maxHolidayWeight",
     *        description = "最多连续休假天数(权重)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "maxHolidayStatus",
     *        description = "最多连续休假天数(状态)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *        enum = {0,1}
     *     ),


     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "minHolidayCount",
     *        description = "最少连续休假天数(天数)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "minHolidayWeight",
     *        description = "最少连续休假天数(权重)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "minHolidayStatus",
     *        description = "最少连续休假天数(状态)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *        enum = {0,1}
     *     ),



     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "maxWorkCount",
     *        description = "最多连续工作天数(天数)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "maxWorkWeight",
     *        description = "最多连续工作天数(权重)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "maxWorkStatus",
     *        description = "最多连续工作天数(状态)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *        enum = {0,1}
     *     ),

     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "minWorkCount",
     *        description = "最少连续工作天数(天数)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "minWorkWeight",
     *        description = "最少连续工作天数(权重)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "minWorkStatus",
     *        description = "最少连续工作天数(状态)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *        enum = {0,1}
     *     ),



     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "restOnStaAndSunOn",
     *        description = "周六日连休(选择权限)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *        enum = {0,1}
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "restOnStaAndSunWeight",
     *        description = "周六日连休(权重)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "restOnStaAndSunStatus",
     *        description = "周六日连休(状态)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *        enum = {0,1}
     *     ),

     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "restOnTuOrTuesWeight",
     *        description = "周六工作在周二或周四安排调休(权重)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "restOnTuOrTuesStatus",
     *        description = "周六工作在周二或周四安排调休(状态)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *        enum = {0,1}
     *     ),


     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "continuWeekOneShiftWeight",
     *        description = "连续周末分配同一班次(权重)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "continuWeekOneShiftStatus",
     *        description = "连续周末分配同一班次(状态)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *        enum = {0,1}
     *     ),

     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "minWorkWeekendCount",
     *        description = "最少允许连续工作几个周末(允许周末数)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "minWorkWeekendStatus",
     *        description = "最少允许连续工作几个周末(状态)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *        enum = {0,1}
     *     ),


     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "diffShiftWeight",
     *        description = "周内分配不同班次(权重)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "diffShiftStatus",
     *        description = "周内分配不同班次(状态)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *        enum = {0,1}
     *     ),


     *     @SWG\Response(
     *         response = 422,
     *         description = "Data Validation Failed 创建失败",
     *     )
     * )
     *
    **/
    public function actionConstraint()
    {
        $work_station=$this->workStation;
        if (Yii::$app->request->isPost) {
                $post = Yii::$app->request->post();

                $data['Constraint']['contranct']=json_encode($post,JSON_NUMERIC_CHECK);

                $data['Constraint']['name']=$post['name'];
                $data['Constraint']['work_station']=$work_station;
                $model=new Constraint;
                if($model->add($data)){
                    $this->serializer['errno']   = 0;
                    $this->serializer['status']   = true;
                    $this->serializer['message'] = "保存成功"; 
                    return $post;
                }else{
                 

                    $this->serializer['errno']   = 0;
                    $this->serializer['status']   = false;
                    $this->serializer['message'] = "保存失败"; 
                }

            }

    }

    /**
     * @SWG\Post(path="/schedule/get-constraint",
     *     tags={"云平台-schedule-排班计划"},
     *     summary="获取小组约束",
     *     description="获取小组约束",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "Token",
     *        description = "Token",
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
    public function actionGetConstraint()
    {
        $model=new Constraint;
        $work_station=$this->workStation;
        $constraintlList=$model->getConstraint($work_station);
        if(count($constraintlList)>0){

            $this->serializer['errno']   = 0;
            $this->serializer['status']   = true;
            $this->serializer['message'] = "获取成功"; 
            return  $constraintlList;    
        }else{

            $this->serializer['errno']   = 20006000;
            $this->serializer['status']   = true;
            $this->serializer['message'] = "没有约束信息";
        }
    }

    /**
     * @SWG\Post(path="/schedule/get-constraint-one",
     *     tags={"云平台-schedule-排班计划"},
     *     summary="获取某个约束",
     *     description="获取小组约束",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "Token",
     *        description = "Token",
     *        required = true,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "constraint_id",
     *        description = "约束id",
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
    public function actionGetConstraintOne()
    {
        if (Yii::$app->request->isPost) {
            $post = Yii::$app->request->post();
            $model=new Constraint;
            $constraint_id=$post['constraint_id'];
            $constraint=$model->getConstraintOne($constraint_id);
            $constraint->contranct=json_decode($constraint['contranct']);
            if(isset($constraint)){

                $this->serializer['errno']   = 0;
                $this->serializer['status']   = true;
                $this->serializer['message'] = "获取成功"; 
                return  $constraint;    
            }else{

                $this->serializer['errno']   = 20006000;
                $this->serializer['status']   = true;
                $this->serializer['message'] = "没有约束信息";
            }
        }
        
    }


    /**
     * @SWG\Post(path="/schedule/constraint-delete",
     *     tags={"云平台-schedule-排班计划"},
     *     summary="删除小组",
     *     description="删除小组",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "Token",
     *        description = "Token",
     *        required = true,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "constraint_id",
     *        description = "约束id组[1,2]",
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
    public function actionConstraintDelete()
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
        
        $constraintmodel=new Constraint;
        $constraint_id=json_decode($post['constraint_id']);

        
        try{
            foreach ($constraint_id as $key => $cid) {
                $query=$constraintmodel::deleteAll(['id'=>$cid]);
            }
            $this->serializer['errno']   = 0;
            $this->serializer['status']   = true;
            $this->serializer['message'] = "删除成功"; 

        }catch(\Exception $e) {
            $this->serializer['errno']   = 200006000;
            $this->serializer['status']   = false;
            $this->serializer['message'] = "删除失败"; 
        }
        
    }

    /**
     * @SWG\Post(path="/schedule/constraint-update",
     *     tags={"云平台-schedule-排班计划"},
     *     summary="排班规则",
     *     description="更新班次规则",
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
     *        name = "constraint_id",
     *        description = "约束id",
     *        required = true,
     *        type = "string"
     *     ),

     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "name",
     *        description = "约束模板名称",
     *        required = true,
     *        type = "string"
     *     ),


     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "nightAfterNightLeisureShiftSelect",
     *        description = "夜班后指定班次(班次)",
     *        required = false,
     *        type = "string",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "nightAfterNightLeisureWeight",
     *        description = "夜班后指定班次(权重)",
     *        required = false,
     *        type = "string",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "nightAfterNightLeisureStatus",
     *        description = "夜班后指定班次(状态)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *        enum = {0,1}
     *     ),




     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "twoNightShiftSelect",
     *        description = "两夜班内指定班次和休假(班次)",
     *        required = false,
     *        type = "string",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "twoNightWeight",
     *        description = "两夜班内指定班次和休假(权重)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "twoNightStatus",
     *        description = "两夜班内指定班次和休假(状态)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *        enum = {0,1}
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "shiftdOnlyforManShiftSelect",
     *        description = "班次只分配给男性(班次)",
     *        required = false,
     *        type = "string",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "shiftdOnlyforManWeight",
     *        description = "班次只分配给男性(权重)",
     *        required = false,
     *        type = "string",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "shiftdOnlyforManStatus",
     *        description = "班次只分配给男性(状态)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *        enum = {0,1}
     *     ),


     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "averageAssignmentShiftSelect",
     *        description = "班次尽量平均分配(班次)",
     *        required = false,
     *        type = "string",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "averageAssignment",
     *        description = "班次尽量平均分配(每人分配次数)",
     *        required = false,
     *        type = "string",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "averageAssignmentWeight",
     *        description = "班次尽量平均分配(权重)",
     *        required = false,
     *        type = "string",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "averageAssignmentStatus",
     *        description = "班次尽量平均分配(状态)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *        enum = {0,1}
     *     ),


     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "assignmentAfterIntervalShiftSelect",
     *        description = "该班次分配后间隔后再分配(班次)",
     *        required = false,
     *        type = "string",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "assignmentAfterIntervalEmployee",
     *        description = "该班次分配后间隔后再分配(选择间隔几周)",
     *        required = false,
     *        type = "string",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "assignmentAfterIntervalWeight",
     *        description = "该班次分配后间隔后再分配(权重)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "assignmentAfterIntervalStatus",
     *        description = "该班次分配后间隔后再分配(状态)",
     *        required = false,
     *        type = "string",
     *     ),
    


     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "shiftNotForEmployeeShiftSelect",
     *        description = "该班次不分配给某员工(班次)",
     *        required = false,
     *        type = "string",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "shiftNotForEmployee",
     *        description = "该班次不分配给某员工(选择员工)",
     *        required = false,
     *        type = "string",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "shiftDate",
     *        description = "该班次不分配给某员工(选择日期)",
     *        required = false,
     *        type = "string",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "shiftNotForEmployeeWeight",
     *        description = "该班次不分配给某员工(权重)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "shiftNotForEmployeeStatus",
     *        description = "该班次不分配给某员工(状态)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *        enum = {0,1}
     *     ),





     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "shiftForEmployeeShiftSelect",
     *        description = "该班次分配给某员工(班次)",
     *        required = false,
     *        type = "string",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "shiftForEmployee",
     *        description = "该班次分配给某员工(选择员工)",
     *        required = false,
     *        type = "string",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "shiftDateForEmployee",
     *        description = "该班次分配给某员工(选择日期)",
     *        required = false,
     *        type = "string",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "shiftForEmployeeWeight",
     *        description = "该班次分配给某员工(权重)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "shiftForEmployeeStatus",
     *        description = "该班次分配给某员工(状态)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *        enum = {0,1}
     *     ),


     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "noShiftDayEmp",
     *        description = "员工指定某天不上班(班次)",
     *        required = false,
     *        type = "string",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "noShiftDay",
     *        description = "员工指定某天不上班(日期)",
     *        required = false,
     *        type = "string",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "noShiftDayWeight",
     *        description = "员工指定某天不上班(权重)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "noShiftDayStatus",
     *        description = "员工指定某天不上班(状态)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *        enum = {0,1}
     *     ),



     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "assignmentAfterShiftSelect",
     *        description = "该班次分配后持续分配(班次)",
     *        required = false,
     *        type = "string",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "assignmentAfterShiftDays",
     *        description = "该班次分配后持续分配(选择天数)",
     *        required = false,
     *        type = "string",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "assignmentAfterShiftWeight",
     *        description = "该班次分配后持续分配(权重)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "assignmentAfterShiftStatus",
     *        description = "该班次分配后持续分配(状态)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *        enum = {0,1}
     *     ),


     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "startShiftSelect",
     *        description = "不希望此班次后继续班次(班次)",
     *        required = false,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "nextShiftSelect",
     *        description = "不希望此班次后继续班次(不希望继续的班次)",
     *        required = false,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "restAfterOneShiftWeight",
     *        description = "不希望此班次后继续班次(权重)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "restAfterOneShiftStatus",
     *        description = "不希望此班次后继续班次(状态)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *        enum = {0,1}
     *     ),


     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "threeStartShiftSelect",
     *        description = "不希望持续的三个班(第一个班)",
     *        required = false,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "threeNextShiftSelect",
     *        description = "不希望持续的三个班(第二个班)",
     *        required = false,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "threeThirdShiftSelect",
     *        description = "不希望持续的三个班(第三个班)",
     *        required = false,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "threeShiftWeight",
     *        description = "不希望持续的三个班(权重)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "threeShiftStatus",
     *        description = "不希望持续的三个班(状态)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *        enum = {0,1}
     *     ),



     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "freeTwoDaysSelect",
     *        description = "每周公休分配(选择天数)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "freeTwoDaysWeight",
     *        description = "每周公休分配(权重)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "freeTwoDaysStatus",
     *        description = "每周公休分配(状态)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *        enum = {0,1}
     *     ),



     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "allowWeekendShift",
     *        description = "最多允许连续工作几个周末(允许几个周末)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "maxWeekendShiftWeight",
     *        description = "最多允许连续工作几个周末(权重)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "maxWeekendShiftStatus",
     *        description = "最多允许连续工作几个周末(状态)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *        enum = {0,1}
     *     ),


     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "minWorkDayCount",
     *        description = "每周每人最少分配班次数(最少工作天数)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "minWorkDayWeight",
     *        description = "每周每人最少分配班次数(权重)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "minWorkDayStatus",
     *        description = "每周每人最少分配班次数(状态)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *        enum = {0,1}
     *     ),



     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "maxWorkDayCount",
     *        description = "每周每人最多分配班次数(最多工作天数)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "maxWorkDayWeight",
     *        description = "每周每人最多分配班次数(权重)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "maxWorkDayStatus",
     *        description = "每周每人最多分配班次数(状态)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *        enum = {0,1}
     *     ),


     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "gravidaCount",
     *        description = "孕妇不分配低于设置人数的班次(人数)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "gravidaWeight",
     *        description = "孕妇不分配低于设置人数的班次(权重)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "gravidaStatus",
     *        description = "孕妇不分配低于设置人数的班次(状态)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *        enum = {0,1}
     *     ),


     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "shiftsMax",
     *        description = "轮转人员先休假(最多安排几天班)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "holidaysMax",
     *        description = "轮转人员先休假(最多剩余假期)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "holidaysWeight",
     *        description = "轮转人员先休假(权重)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "holidaysStatus",
     *        description = "轮转人员先休假(状态)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *        enum = {0,1}
     *     ),

     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "maxHolidayCount",
     *        description = "最多连续休假天数(天数)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "maxHolidayWeight",
     *        description = "最多连续休假天数(权重)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "maxHolidayStatus",
     *        description = "最多连续休假天数(状态)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *        enum = {0,1}
     *     ),


     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "minHolidayCount",
     *        description = "最少连续休假天数(天数)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "minHolidayWeight",
     *        description = "最少连续休假天数(权重)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "minHolidayStatus",
     *        description = "最少连续休假天数(状态)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *        enum = {0,1}
     *     ),



     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "maxWorkCount",
     *        description = "最多连续工作天数(天数)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "maxWorkWeight",
     *        description = "最多连续工作天数(权重)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "maxWorkStatus",
     *        description = "最多连续工作天数(状态)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *        enum = {0,1}
     *     ),

     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "minWorkCount",
     *        description = "最少连续工作天数(天数)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "minWorkWeight",
     *        description = "最少连续工作天数(权重)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "minWorkStatus",
     *        description = "最少连续工作天数(状态)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *        enum = {0,1}
     *     ),



     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "restOnStaAndSunOn",
     *        description = "周六日连休(选择权限)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *        enum = {0,1}
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "restOnStaAndSunWeight",
     *        description = "周六日连休(权重)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "restOnStaAndSunStatus",
     *        description = "周六日连休(状态)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *        enum = {0,1}
     *     ),

     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "restOnTuOrTuesWeight",
     *        description = "周六工作在周二或周四安排调休(权重)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "restOnTuOrTuesStatus",
     *        description = "周六工作在周二或周四安排调休(状态)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *        enum = {0,1}
     *     ),


     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "continuWeekOneShiftWeight",
     *        description = "连续周末分配同一班次(权重)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "continuWeekOneShiftStatus",
     *        description = "连续周末分配同一班次(状态)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *        enum = {0,1}
     *     ),

     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "minWorkWeekendCount",
     *        description = "最少允许连续工作几个周末(允许周末数)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "minWorkWeekendStatus",
     *        description = "最少允许连续工作几个周末(状态)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *        enum = {0,1}
     *     ),


     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "diffShiftWeight",
     *        description = "周内分配不同班次(权重)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "diffShiftStatus",
     *        description = "周内分配不同班次(状态)",
     *        required = false,
     *        type = "integer",
     *        default = 1,
     *        enum = {0,1}
     *     ),


     *     @SWG\Response(
     *         response = 422,
     *         description = "Data Validation Failed 创建失败",
     *     )
     * )
     *
    **/
    public function actionConstraintUpdate()
    {
        if (Yii::$app->request->isPost) {
            $post = Yii::$app->request->post();
            $constraint_id=$post['constraint_id'];
            $contranct=json_encode($post,JSON_NUMERIC_CHECK);
            $name=$post['name'];

            if(Constraint::updateAll(['name' => $name,'contranct' => $contranct], 'id = :cid', [':cid' => $constraint_id])){
                $this->serializer['errno']   = 0;
                $this->serializer['status']   = true;
                $this->serializer['message'] = "修改成功"; 
            }else{

                $this->serializer['errno']   = 0;
                $this->serializer['status']   = true;
                $this->serializer['message'] = "修改失败"; 
            }

        }

    }

    /**
     * @SWG\Post(path="/schedule/get-schedule",
     *     tags={"云平台-schedule-排班计划"},
     *     summary="获取小组班次计划",
     *     description="获取小组班次计划",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "Token",
     *        description = "Token",
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
    public function actionGetSchedule()
    {
        $model=new Schedule;
        $work_station=$this->workStation;
        $scheudleList=$model->getSchedule($work_station);
        if(count($scheudleList)>0){

            $this->serializer['errno']   = 0;
            $this->serializer['status']   = true;
            $this->serializer['message'] = "获取成功"; 
            return  $scheudleList;    
        }else{
            $this->serializer['errno']   = 20006000;
            $this->serializer['status']   = false;
            $this->serializer['message'] = "没有约束信息";
        }
    }

    /**
     * @SWG\Post(path="/schedule/get-shift-model",
     *     tags={"云平台-schedule-排班计划"},
     *     summary="获取小组班次模板",
     *     description="获取小组班次模板",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "Token",
     *        description = "Token",
     *        required = true,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "type",
     *        description = "排班方式：1循环排班，2，自动排班；3自选排班 ",
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
    public function actionGetShiftModel()
    {
        $model=new ShiftModel;
        $schedulemodel=new Schedule;
        $post=Yii::$app->request->post();
        $type=$post['type'];
        $work_station=$this->workStation;
        $modelList=$model->getShiftModel($work_station,$type);
        $scheudleList=$schedulemodel->getScheduleListArr($work_station);
        $data1=array();
        $data2=array();
        foreach ($modelList as $key => $value) {
            $value['select_type']=1;
            $data1[$key]=$value;
        }

        foreach ($scheudleList as $key2 => $value2) {

            $value2['select_type']=2;
            $data2[$key2]=$value2;
        }

        $data=array_merge($data1,$data2);
        if(count($data)>0){
            $this->serializer['errno']   = 0;
            $this->serializer['status']   = true;
            $this->serializer['message'] = "获取成功"; 
            return  $data;    
        }else{
            $this->serializer['errno']   = 20006000;
            $this->serializer['status']   = false;
            $this->serializer['message'] = "没有模板信息";
        }
    }

    /**
     * @SWG\Post(path="/schedule/create-xml",
     *     tags={"云平台-schedule-排班计划"},
     *     summary="启动引擎，开始排班",
     *     description="启动引擎，开始排班",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "Token",
     *        description = "Token",
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
    public function actionCreateXml()
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
        $constraintmodel=new Constraint;
        $schedule=Schedule::find()->where('id =:schedule_id ',[':schedule_id'=>$post['schedule_id']])->one();
        if(!isset($schedule)){
            $this->serializer['errno']   = '4000060000';
            $this->serializer['status']   = false;
            $this->serializer['message'] = '没有该计划';
            return [];
        }
        //获取约束模板id
        $constraint_id=$schedule->constrain_id;
        //获取基础数据ID
        $orange_data=$schedule->orange_data;
        //获取排班方式
        $schedule_type=$schedule->schedule_type;

        if($schedule_type==1){//1循环排班
           $this->serializer['errno']   = 0;
           $this->serializer['status']   = false;
           $this->serializer['message'] = "循环排班";   
        }else if($schedule_type==2){//自动排班

            //判断是否已经存在模板,然后数据存入规则中
            $confirmmodel = new ShiftResultConfirm;
            $model_result=array();
            //获取上班的人
            $model_result = $confirmmodel->getConfrimNoRest($schedule_id);
            //获取不上班的人
            $model_result_no = $confirmmodel->getConfrimIsRest($schedule_id);
            $constraintdata=Constraint::find()->where('id=:cid',[':cid'=>$constraint_id])->asArray()->one();
            $constraint=json_decode($constraintdata['contranct']);
            $constraint= object_array($constraint);
            $data=array();
            //循环插入指定某人上某个班
            foreach ($model_result as $key_model => $value_model) {
               $week_model=get_week($value_model['shift_date']);
               $data[$key_model]['value']='';
               $data[$key_model]['index']='1';
               $data[$key_model]['status']='1';
               $data[$key_model]['shiftForEmployeeShiftSelect']=$value_model['shift_type_id'];
               $data[$key_model]['shiftForEmployeeWeight']='100';
               $data[$key_model]['shiftForEmployee'][]=$value_model['emp_number'];
               $data[$key_model]['shiftDateForEmployee'][]=$week_model;
               $data[$key_model]['shiftForEmployeeStatus']='1';
            }
            if(isset($constraint['shiftForEmployee'])){
                $constraint['shiftForEmployee']=array_merge($constraint['shiftForEmployee'], $data);
            }else{
                $constraint['shiftForEmployee']=$data;
            }
            //循环插入指定某人不上某个班
            $data2=array();
            //循环插入指定某人上某个班
            foreach ($model_result_no as $key_model2 => $value_model2) {
               $week_model2=get_week($value_model2['shift_date']);
               $data2[$key_model2]['value']='';
               $data2[$key_model2]['index']='1';
               $data2[$key_model2]['status']='1';
               $data2[$key_model2]['noShiftDayWeight']='100';
               $data2[$key_model2]['noShiftDayEmp'][]=$value_model2['emp_number'];
               $data2[$key_model2]['noShiftDay'][]=$week_model2;
               $data2[$key_model2]['noShiftDayStatus']='1';
            }
            if(isset($constraint['noShiftDay'])){
                $constraint['noShiftDay']=array_merge($constraint['noShiftDay'], $data2);
            }else{
                $constraint['noShiftDay']=$data2;
            }

            $if_create=$constraintmodel->createXml($post['schedule_id'],$work_station,$constraint);
            if($if_create){
                Schedule::updateAll(['status' => 3], 'id = :sid', [':sid' => $post['schedule_id']]);
                //查询域名信息
                $domain=ConfigCustomer::find()->where('id=:did',[':did'=>1])->one()->domin;
                if(env('YII_ENV')=='dev'){
                    $domain_url='dev.'.$domain.'.api';
                }else{
                    $domain_url='api';
                }

                $runResult=runJava($post['schedule_id'],"one",$domain_url);

                if($runResult==1){
                    $this->serializer['errno']   = 0;
                    $this->serializer['status']   = true;
                    $this->serializer['message'] = "开始排班";   
                }else{
                    $this->serializer['errno']   = 2000060000;
                    $this->serializer['status']   = false;
                    $this->serializer['message'] = "排班错误";   
                }
            }

        }else{//自选排班
            $this->serializer['errno']   = 0;
            $this->serializer['status']   = false;
            $this->serializer['message'] = "自选排班";  
        }
    } 


    /**
     * @SWG\Post(path="/schedule/insert-xml",
     *     tags={"云平台-schedule-排班计划"},
     *     summary="引擎xml写入数据库",
     *     description="引擎xml写入数据库",
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
    public function actionInsertXml()
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
        $schedule=Schedule::find()->where('id =:schedule_id ',[':schedule_id'=>$post['schedule_id']])->one();

        if(!isset($schedule)){
            $this->serializer['errno']   = '422';
            $this->serializer['status']   = false;
            $this->serializer['message'] = '班次计划不存在';
            return [];
        }

        $scheduleID =$schedule->id;
        $schedule_status=$schedule->status;
        $is_insert=$schedule->is_insert;

        if($schedule_status==Schedule::SCHEUDLE_STATUS_SUCESS && $is_insert==Schedule::IS_INSERT_NO ){//排班已经完成

            $this->serializer['errno']   = 200;
            $this->serializer['status']   = false;
            $this->serializer['message'] = "排班结果生成中，请稍等";

        }else if($schedule_status==Schedule::SCHEUDLE_STATUS_ON && $is_insert==Schedule::IS_INSERT_NO){
            $this->serializer['errno']   = 200;
            $this->serializer['status']   = false;
            $this->serializer['message'] = "正在排班，请稍等";
        }else if($schedule_status==Schedule::SCHEUDLE_STATUS_FALSE && $is_insert==Schedule::IS_INSERT_NO) {
            $this->serializer['errno']   = 100;
            $this->serializer['status']   = false;
            $this->serializer['message'] = "排班失败"; 
        }else if($schedule_status==Schedule::SCHEUDLE_STATUS_SUCESS && $is_insert==Schedule::IS_INSERT){
            $this->serializer['errno']   = 100;
            $this->serializer['status']   = true;
            $this->serializer['message'] = "排班结果已经生成"; 
        }else if($schedule_status==Schedule::SCHEUDLE_STATUS_SUCESS && $is_insert==Schedule::IS_INSERT_FALSE) {
            $this->serializer['errno']   = 300;
            $this->serializer['status']   = false;
            $this->serializer['message'] = "排班结果插入失败"; 
        }

    
    }

    /**
     * @SWG\Post(path="/schedule/schedule-list",
     *     tags={"云平台-schedule-排班计划"},
     *     summary="排班计划列表",
     *     description="排班计划列表",
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
     *        name = "name",
     *        description = "计划名",
     *        required = false,
     *        type = "string",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "page",
     *        description = "页码",
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
    public function actionScheduleList()
    {
        $post=Yii::$app->request->post();
        $work_station=$this->workStation;

        $start_date=isset($post['start_date'])?$post['start_date']:'';
        $end_date=isset($post['end_date'])?$post['end_date']:'';
        $name=isset($post['name'])?$post['name']:'';
        $page=isset($post['page'])?$post['page']:'0';

        $schedulemodel=new Schedule;

        $pageSize = Yii::$app->params['pageSize']['shift'];

        $isLeader=$this->isLeader;


        $data=$schedulemodel->getScheduleList($work_station,$page,$pageSize,$start_date,$end_date,$name,$isLeader);

        if(isset($data)&&count($data)>0){
            $this->serializer['errno']   = 0;
            $this->serializer['status']   = true;
            $this->serializer['message'] = "获取排班成功"; 
            return $data;
        }else{
            $this->serializer['errno']   = 2000060000;
            $this->serializer['status']   = false;
            $this->serializer['message'] = "没有排班计划"; 
        }
    }


  /**
     * @SWG\Post(path="/schedule/delete",
     *     tags={"云平台-schedule-排班计划"},
     *     summary="删除计划",
     *     description="删除计划",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "Token",
     *        description = "Token",
     *        required = true,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "schedule_id",
     *        description = "技能类型ID,格式[1,2,3]",
     *        required = true,
     *        type = "integer",
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "返回班次类型列表"
     *     ),
     * )
     *
    **/
    public function actionDelete()
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
        
        $schedulemodel=new Schedule;
        $shiftdatemodel=new ShiftDate;
        $shiftassignment = new ShiftAssignment;
        $shiftorderbymodel = new ShiftOrderBy;

        $shifresultmodel = new ShiftResult;
        $shiftconfirmmodel = new ShiftResultConfirm;
        $shiftorangemodel = new ShiftResultOrange;
        $schedule_list=json_decode($post['schedule_id']);
        $transaction = Yii::$app->db->beginTransaction();
        $datamessage='';

        $time_now = date("Y-m-d");

        try{
            foreach ($schedule_list as $key => $schedule_id) {
                $schedule=Schedule::find()->where('id =:schedule_id ',[':schedule_id'=>$schedule_id])->one();
                if($schedule->is_confirm==1  && strtotime($schedule->shift_date)< strtotime($time_now)){
                    $datamessage='已发布表不允许删除';
                    throw new \Exception();
                }else{
                    //删除假期信息
                    $de=new LeaveEntitlement;
                    $de->returnSchedulingById($schedule_id);
                    $schedulemodel::deleteAll(['id'=>$schedule_id]);
                    $shiftdatemodel::deleteAll(['schedule_id'=>$schedule_id]);
                    $shiftassignment::deleteAll(['schedule_id'=>$schedule_id]);
                    $shiftorderbymodel::deleteAll(['schedule_id'=>$schedule_id]);
                    $shifresultmodel::deleteAll(['schedule_id'=>$schedule_id]);
                    $shiftconfirmmodel::deleteAll(['schedule_id'=>$schedule_id]);
                    $shiftorangemodel::deleteAll(['schedule_id'=>$schedule_id]);
                }
                
            }

            $transaction->commit();
            $this->serializer['errno']   = 0;
            $this->serializer['status']   = true;
            $this->serializer['message'] = "删除成功"; 

        }catch(\Exception $e) {
            $transaction->rollback();
            $this->serializer['errno']   = 0;
            $this->serializer['status']   = false;
            $this->serializer['message'] = !empty($datamessage)?$datamessage : "删除失败"; 
        }
    }

}

?>