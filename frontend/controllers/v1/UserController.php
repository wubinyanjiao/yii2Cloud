<?php
namespace frontend\controllers\v1;

use common\helps\tools;
use common\models\attachment\Attachment;
use common\models\employee\Employee;
use common\models\emptitle\EmpTitle;
use common\models\subunit\Subunit;
use common\models\teach\Teach;
use common\models\user\Empexcel;
use common\models\user\Faculty;
use common\models\user\Keyan;
use common\models\user\Personnel;
use common\models\user\Picture;
use common\models\user\Record;
use common\models\user\TeacherTitle;
use common\models\user\Title;
use common\models\user\Tutor;
use common\models\user\User;
use common\models\user\WorkExcel;
use function Couchbase\fastlzCompress;
use Mpdf\Tag\U;
use yii\rest\ActiveController;
use yii\web\Response;
use yii;
class UserController extends \common\rest\Controller
{


    /**
     * @var string
     */
    public $modelClass = 'common\models\user\User';

    /**
     * @var array
     */
    public $serializer = [
        'class' => 'common\rest\Serializer',    // 返回格式数据化字段
        'collectionEnvelope' => 'result',       // 制定数据字段名称
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
     * @SWG\Post(path="/user/list",
     *     tags={"云平台-User-用户"},
     *     summary="用户列表",
     *     description="用户列表",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "Token",
     *        description = "Token",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "emp_firstname",
     *        description = "员工姓名",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "emp_marital_status",
     *        description = "婚姻状态",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "emp_gender",
     *        description = "性别",
     *        required = false,
     *        type = "string",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "job_title_code",
     *        description = "职称",
     *        required = false,
     *        type = "string",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "eeo_cat_code",
     *        description = "岗位",
     *        required = false,
     *        type = "string",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "work_station",
     *        description = "所在小组",
     *        required = false,
     *        type = "string",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "start_age",
     *        description = "年龄开始",
     *        required = false,
     *        type = "string",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "end_age",
     *        description = "年龄结束",
     *        required = false,
     *        type = "string",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "contract_status",
     *        description = "人事关系",
     *        required = false,
     *        type = "string",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "status",
     *        description = "员工状态",
     *        required = false,
     *        type = "string",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "start_retire",
     *        description = "退休开始",
     *        required = false,
     *        type = "string",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "end_retire",
     *        description = "退休结束",
     *        required = false,
     *        type = "string",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "page",
     *        description = "页码",
     *        required = true,
     *        type = "string",
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "员工管理列表"
     *     ),
     *     @SWG\Response(
     *         response = 403,
     *         description = "查询失败",
     *     )
     * )
     *
     **/
    public function actionList(){
        $data = Yii::$app->request->post();
        $isleader = $this->isLeader;
        $workstation = $this->workStation;
        $role = $this->userRoleId;
        $user = new User();
        $model = $user->userlist($data,$isleader,$workstation,$role);
        if($model){
            $this->serializer['message'] = '查找成功';
        }else{
            $this->serializer['message'] = '没有数据';
        }
        return $model;
    }



    /**
     * @SWG\Get(path="/user/selname",
     *     tags={"云平台-User-用户"},
     *     summary="姓名搜索",
     *     description="姓名搜索",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "query",
     *        name = "Token",
     *        description = "Token",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "query",
     *        name = "emp_firstname",
     *        description = "员工姓名",
     *        required = true,
     *        type = "string"
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "员工管理列表"
     *     ),
     *     @SWG\Response(
     *         response = 403,
     *         description = "查询失败",
     *     )
     * )
     *
     **/
    public function actionSelname(){
        $emp_firstname = Yii::$app->request->get('emp_firstname');
        $employee = new Employee();
        $where = "emp_firstname like '%$emp_firstname%'";
        if($this->userRoleId==2||$this->userRoleId==8){
            if($this->workStation){
                $where .= ' and work_station ='.$this->workStation;
            }
            
        }
        $query = $employee::find()->select(['emp_firstname'])->where($where)->all();
        return $query;
    }

    /**
     * @SWG\Post(path="/user/selname-bysub",
     *     tags={"云平台-User-用户"},
     *     summary="姓名搜索返回带组名称",
     *     description="姓名搜索返回带组名称",
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
     *        name = "emp_firstname",
     *        description = "员工姓名",
     *        required = true,
     *        type = "string"
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "返回小组列表"
     *     ),
     *     @SWG\Response(
     *         response = 403,
     *         description = "查询失败",
     *     )
     * )
     *
     **/
    public function actionSelnameBysub(){
        $firstName = trim(Yii::$app->request->post('emp_firstname'));

        if(empty($firstName)){
            $this->serializer['status'] = false;
            $this->serializer['errno'] = 0;
            $this->serializer['message'] = '请输入员工姓名';
            return false;
        }
        $query = Employee::find();
        $query->joinWith('subunit');
        $query->where('termination_id is null');

        if($this->userRoleId==2||$this->userRoleId==8){
            if($this->workStation){
                $query->andWhere('work_station =:workStation',[':workStation'=>$this->workStation]); 

            }
            
        }

        $query->andWhere(['like','emp_firstname',$firstName]);;

        $list = $query->all();
        $backArr = array();
        foreach ($list as $key => $value) {
            $arr = array();
            $arr['emp_firstname'] = $value->emp_firstname.'('.$value->subunit->name.')';
            $backArr[] = $arr;
        }
        
        $this->serializer['status'] = true;
        $this->serializer['errno'] = 0;
        $this->serializer['message'] = '';
        return array('data'=>$backArr);
    }



    /**
     * @SWG\Get(path="/user/deluser",
     *     tags={"云平台-User-用户"},
     *     summary="删除员工",
     *     description="删除员工",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "Token",
     *        description = "Token",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "query",
     *        name = "emp_number",
     *        description = "员工id",
     *        required = true,
     *        type = "string"
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "员工列表"
     *     ),
     *     @SWG\Response(
     *         response = 403,
     *         description = "查询失败",
     *     )
     * )
     *
     **/
    public function actionDeluser(){
        $emp_number = yii::$app->request->get('emp_number');
        $user = new User();
        $model = $user->deluser($emp_number);
        if($model){
            $this->serializer['message'] = '删除成功';
        }else{
            $this->serializer['message'] = '删除失败';
        }
        return $model;
    }




    /**
     * @SWG\Get(path="/user/selrecord",
     *     tags={"云平台-User-用户"},
     *     summary="查看变组详情",
     *     description="查看变组详情",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "query",
     *        name = "Token",
     *        description = "Token",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "query",
     *        name = "emp_number",
     *        description = "员工id",
     *        required = true,
     *        type = "string"
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "员工列表"
     *     ),
     *     @SWG\Response(
     *         response = 403,
     *         description = "查询失败",
     *     )
     * )
     *
     **/
    public function actionSelrecord(){
        $emp_number = Yii::$app->request->get('emp_number');
        $user = new User();
        $model = $user->selrecored($emp_number);
        if($model){
            $this->serializer['message'] = '查询成功';
        }else{
            $this->serializer['message'] = '没有数据';
        }
        return $model;
    }



    /**
     * @SWG\Get(path="/user/basic",
     *     tags={"云平台-User-用户"},
     *     summary="个人基本信息查询",
     *     description="个人基本信息查询",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "query",
     *        name = "Token",
     *        description = "Token",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "query",
     *        name = "emp_number",
     *        description = "emp_number",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "员工管理列表"
     *     ),
     *     @SWG\Response(
     *         response = 403,
     *         description = "查询失败",
     *     )
     * )
     *
     **/

    /**
     * @SWG\Post(path="/user/basic",
     *     tags={"云平台-User-用户"},
     *     summary="修改个人基本信息",
     *     description="修改个人基本信息",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "Token",
     *        description = "Token",
     *        required = false,
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
     *        name = "emp_gender",
     *        description = "性别",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "emp_other_id",
     *        description = "身份证号",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "joined_date",
     *        description = "入职时间",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "nation_code",
     *        description = "国家id",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "emp_marital_status",
     *        description = "婚姻状态",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "minzu_code",
     *        description = "民族id",
     *        required = false,
     *        type = "string",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "emp_politics",
     *        description = "政治面貌",
     *        required = false,
     *        type = "string",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "emp_birthday",
     *        description = "出生日期",
     *        required = false,
     *        type = "string",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "emp_retire",
     *        description = "退休日期",
     *        required = false,
     *        type = "string",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "emp_work_telephone",
     *        description = "电话",
     *        required = false,
     *        type = "string",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "emp_mobile",
     *        description = "手机",
     *        required = false,
     *        type = "string",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "province",
     *        description = "省id",
     *        required = false,
     *        type = "string",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "eec_name",
     *        description = "紧急联系人",
     *        required = false,
     *        type = "string",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "eec_mobile_no",
     *        description = "紧急联系人电话",
     *        required = false,
     *        type = "string",
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "员工列表"
     *     ),
     *     @SWG\Response(
     *         response = 403,
     *         description = "查询失败",
     *     )
     * )
     *
     **/
    public function actionBasic(){
        $user = new User();
        if(Yii::$app->request->isGet){
            $emp_number = Yii::$app->request->get('emp_number');
            if($emp_number == null){
                $emp_number = $this->empNumber;
                if($emp_number == ''){
                    return false;
                }
            }
            $model = $user->selbasic($emp_number);
            if($model){
                $this->serializer['message'] = '查询成功';
            }else{
                $this->serializer['message'] = '查询失败';
            }
            return $model;
        }else{
            $data = Yii::$app->request->post();
            $model = $user->upbasic($data);
            return $model;
        }
    }






    /**
     * @SWG\Get(path="/user/picture",
     *     tags={"云平台-User-用户"},
     *     summary="员工头像查询",
     *     description="员工头像查询",
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
     *        name = "emp_number",
     *        description = "员工id",
     *        required = true,
     *        type = "string"
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "员工管理列表"
     *     ),
     *     @SWG\Response(
     *         response = 403,
     *         description = "查询失败",
     *     )
     * )
     *
     **/
    public function actionPicture(){
        $emp_number = Yii::$app->request->get('emp_number');
        $user = new User();
        $model = $user->selpicture($emp_number);
        if($model){
            $this->serializer['message'] = '查询成功';
            return $model;
        }else{
            $this->serializer['message'] = '没有头像信息';
            return $model;
        }
    }


    /*
     * 修改头像路径
     *
     * **/
    public function actionSubstr(){
        $picture = Picture::find()->asArray()->all();
        $pic = new Picture();
        foreach ($picture as $k =>$v){
            $url = substr($v['epic_picture_url'],1);
            $pic = $pic::find()->where(['emp_number'=>$v['emp_number']])->one();
            $pic->epic_picture_url = $url;
            $pic->save();
        }
    }



    /*
     * 员工头像上传
     * 文件 file   员工id emp_number
     * **/
    public function actionPortrait(){
        $documentPath = '../public/emp_picture/';//上传路径
        $emp_number = Yii::$app->request->post("emp_number");
        $file = $_FILES['file'];
        $size = $file['size'];
        $postdata = fopen($file['tmp_name'], "r");
        $extension = substr($file['name'], strrpos($file['name'], '.'));
        $filename = $documentPath . uniqid() . $extension;
        $fp = fopen($filename, "w");
        while ($file = fread($postdata, 1024))
            fwrite($fp, $file);
        fclose($fp);
        fclose($postdata);
        $result['filename'] = $filename;
        $result['create_time'] = date("Y-m-d H:i:s");
        $img_info = getimagesize($filename);
        $arr=explode("/",$filename);
        $name=$arr[count($arr)-1];

        $user = new User();
        $model = $user->uploadportrait($emp_number,$size,$img_info,$name,$filename);
        return $model;

    }




    /**
     * @SWG\Get(path="/user/post",
     *     tags={"云平台-User-用户"},
     *     summary="员工岗位信息查询",
     *     description="员工岗位信息查询",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "query",
     *        name = "Token",
     *        description = "token",
     *        required = false,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "query",
     *        name = "emp_number",
     *        description = "员工id",
     *        required = false,
     *        type = "string"
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "员工管理列表"
     *     ),
     *     @SWG\Response(
     *         response = 403,
     *         description = "查询失败",
     *     )
     * )
     *
     **/

    /**
     * @SWG\Post(path="/user/post",
     *     tags={"云平台-User-用户"},
     *     summary="员工岗位信息修改",
     *     description="员工岗位信息修改",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "Token",
     *        description = "Token",
     *        required = true,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "emp_number",
     *        description = "员工id",
     *        required = true,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "incourtyard_date",
     *        description = "进院日期",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "work_station",
     *        description = "小组id",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "probation_date",
     *        description = "试用期截止时间",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "formal_date",
     *        description = "转正时间",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "eeo_cat_code",
     *        description = "岗位名称",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "workload_ranking",
     *        description = "是否参与工作量排名",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "is_scheduling",
     *        description = "是否排班",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "attime_education",
     *        description = "岗位描述",
     *        required = false,
     *        type = "string",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "attime_graduation",
     *        description = "毕业时间",
     *        required = false,
     *        type = "string",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "attime_graduation_school",
     *        description = "毕业学校",
     *        required = false,
     *        type = "string",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "attime_studymajor",
     *        description = "所学专业",
     *        required = false,
     *        type = "string",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "now_education",
     *        description = "现在学历",
     *        required = false,
     *        type = "string",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "now_graduationtime",
     *        description = "现学历毕业时间",
     *        required = false,
     *        type = "string",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "now_academic_degree",
     *        description = "现在学位",
     *        required = false,
     *        type = "string",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "now_academic_degreetime",
     *        description = "现学位毕业时间",
     *        required = false,
     *        type = "string",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "now_graduation_school",
     *        description = "现在学历毕业院校",
     *        required = false,
     *        type = "string",
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "员工管理列表"
     *     ),
     *     @SWG\Response(
     *         response = 403,
     *         description = "查询失败",
     *     )
     * )
     *
     **/
    public function actionPost(){
        $user = new User();
        if(Yii::$app->request->isGet){
            $emp_number = Yii::$app->request->get('emp_number');
            if($emp_number == null){
                $emp_number = $this->empNumber;
            }
            $model = $user->selpost($emp_number);
            return $model;
        }else{
            $role = $this->userRoleId;
            $data = Yii::$app->request->post();
            $arr = $user->uppost($data,$role);
            //return $arr;
            if($arr === 2){
                $this->serializer['status'] = false;
                $this->serializer['message'] = '您不是管理员 没有权限更改进组信息';
                return false;
            }else if ($arr === 3){
                $this->serializer['status'] = false;
                $this->serializer['message'] = '只能在周一更改变组信息';
                return false;
            }else if ($arr === true){
                return true;
            }else{
                return $arr;
            }


        }
    }


    /**
     * @SWG\Get(path="/user/aptitude",
     *     tags={"云平台-User-用户"},
     *     summary="资质详情",
     *     description="资质详情",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "query",
     *        name = "emp_number",
     *        description = "员工id",
     *        required = true,
     *        type = "string"
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "员工管理列表"
     *     ),
     *     @SWG\Response(
     *         response = 403,
     *         description = "查询失败",
     *     )
     * )
     *
     **/

    /**
     * @SWG\Post(path="/user/aptitude",
     *     tags={"云平台-User-用户"},
     *     summary="资质详情修改",
     *     description="资质详情修改",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "emp_number",
     *        description = "员工id",
     *        required = true,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "education_id",
     *        description = "学历",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "job_title_code",
     *        description = "职称",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "working_years",
     *        description = "工龄",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "emp_status",
     *        description = "职务",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "licenses_id",
     *        description = "执业资格",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "faculty_code",
     *        description = "师资",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "mutual_exclusion",
     *        description = "互斥人员",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "special_personnel",
     *        description = "特殊人员",
     *        required = false,
     *        type = "string",
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "员工管理列表"
     *     ),
     *     @SWG\Response(
     *         response = 403,
     *         description = "查询失败",
     *     )
     * )
     *
     **/
    public function actionAptitude(){
        $user = new User();
        if(Yii::$app->request->isGet){
            $emp_number = Yii::$app->request->get('emp_number');
            if($emp_number == null){
                $emp_number = $this->empNumber;
            }
            $model = $user->selaptitude($emp_number);
            if($model){
                $this->serializer['message'] = '查询成功';
                return $model;
            }else{
                $this->serializer['message'] = '没有信息';
                return $model;
            }
        }else{
            $data = Yii::$app->request->post();
            $model = $user->upaptitude($data);
            if($model){
                $this->serializer['message'] = '修改成功';
                return $model;
            }else{
                $this->serializer['message'] = '修改失败';
                return $model;
            }
        }
    }

    /*
    * 密码修改
    * get 查询 emp_number
    * post 修改
    * **/
    public function actionUppassword(){
        $user = new User();
        if(Yii::$app->request->isGet){
            $emp_number = Yii::$app->request->get('emp_number');
            $model = $user->selpassword($emp_number);
            return $model;
        }else{
            $data = Yii::$app->request->post();
            $model = $user->uppassword($data);
            return $model;
        }
    }

    /*
     * 附件上传
     * post
     *员工id：emp_number  文件：file 当前登录人：name 详情：details 哪个页面screen：
     * 个人详情页   personal
     * 岗位页  post
     * 资质页  aptitude
     * **/
    public function actionAddfile(){

        $documentPath = '../public/attachment/';//上传路径
        $emp_number = Yii::$app->request->post("emp_number");
        $name = Yii::$app->request->post("name");
        $details = Yii::$app->request->post("details");
        $screen = Yii::$app->request->post("screen");
        $file = $_FILES['file'];
        $size = $file['size'];
        $file_name = $file['name'];
        $type = $file['type'];


        $postdata = fopen($file['tmp_name'], "r");
        $extension = substr($file['name'], strrpos($file['name'], '.'));
        $filename = $documentPath . uniqid() . $extension;
        $fp = fopen($filename, "w");
        while ($file = fread($postdata, 1024))
            fwrite($fp, $file);
        fclose($fp);
        fclose($postdata);


        $user = new User();
        $model = $user->addfile($emp_number,$size,$name,$details,$screen,$file_name,$type,$filename);
        return $model;

    }






    /**
     * @SWG\Get(path="/user/filelist",
     *     tags={"云平台-User-用户"},
     *     summary="附件列表",
     *     description="附件列表",
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
     *        name = "emp_number",
     *        description = "员工id",
     *        required = true,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "query",
     *        name = "screen",
     *        description = "页面",
     *        required = true,
     *        type = "string"
     *     ),

     *     @SWG\Response(
     *         response = 200,
     *         description = "员工管理列表"
     *     ),
     *     @SWG\Response(
     *         response = 403,
     *         description = "查询失败",
     *     )
     * )
     *
     **/
    /*
     * 附件列表
     * get
     * 员工id：emp_number  页面screen：
     * 个人详情页   personal
     * 岗位页  post
     * 资质页  aptitude
     * **/
    public function actionFilelist(){
        $emp_number = Yii::$app->request->get('emp_number');
        $screen = Yii::$app->request->get('screen');
        $user = new User();
        $model = $user->filelist($emp_number,$screen);
        if($model){
            $this->serializer['message'] = '查询成功';
            return $model;
        }else{
            $this->serializer['message'] = '没有附件';
            return $model;
        }
    }

    /*
     * 附件详情以及修改
     * get 查询 附件id：eattach_id
     * post 修改
     * **/
    public function actionSelfile(){
        $user = new User();
        if(Yii::$app->request->isGet){
            $eattach_id = Yii::$app->request->get('eattach_id');
            $model = $user->selectfile($eattach_id);
            return $model;
        }else{
            $documentPath = '../public/attachment/';//上传路径
            $eattach_id = Yii::$app->request->post("eattach_id");
            $name = Yii::$app->request->post("name");
            $details = Yii::$app->request->post("details");
            $file = $_FILES['file'];
            $size = $file['size'];
            $file_name = $file['name'];
            $type = $file['type'];


            $postdata = fopen($file['tmp_name'], "r");
            $extension = substr($file['name'], strrpos($file['name'], '.'));
            $filename = $documentPath . uniqid() . $extension;
            $fp = fopen($filename, "w");
            while ($file = fread($postdata, 1024))
                fwrite($fp, $file);
            fclose($fp);
            fclose($postdata);


            $user = new User();
            $model = $user->upfile($size,$name,$details,$file_name,$type,$filename,$eattach_id);
            return $model;

        }
    }





    /**
     * @SWG\Post(path="/user/upcomment",
     *     tags={"云平台-User-用户"},
     *     summary="修改附件评论",
     *     description="修改附件评论",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "emp_number",
     *        description = "员工id",
     *        required = true,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "eattach_id",
     *        description = "附件id",
     *        required = true,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "details",
     *        description = "评论",
     *        required = true,
     *        type = "string"
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "员工管理列表"
     *     ),
     *     @SWG\Response(
     *         response = 403,
     *         description = "查询失败",
     *     )
     * )
     *
     **/
    public function actionUpcomment(){
        $eattach_id = Yii::$app->request->post('eattach_id');
        $details = Yii::$app->request->post('details');
        $attachment = new Attachment();
        $attachment = $attachment::find()->where(['eattach_id'=>$eattach_id])->one();
        $attachment->eattach_desc = $details;
        $query = $attachment->save();
        if($query){
            $this->serializer['message'] = '修改成功';
            return $query;
        }else{
            $this->serializer['message'] = '修改失败';
            return $query;
        }

    }



    /**
     * @SWG\Post(path="/user/city",
     *     tags={"云平台-User-用户"},
     *     summary="城市列表",
     *     description="城市列表",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "Token",
     *        description = "Token",
     *        required = true,
     *        type = "string"
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "员工管理列表"
     *     ),
     *     @SWG\Response(
     *         response = 403,
     *         description = "查询失败",
     *     )
     * )
     *
     **/
    public function actionCity(){
        $user = new User();
        $model = $user->getcity();
        return $model;
    }




    /*
     * 附件删除
     * get
     * 文件id : eattach_id
     * **/
    /*public function actionDelfile(){
        $id = Yii::$app->request->get('eattach_id');
        $attachment = new Attachment();
        foreach ($id as $k=>$v){
            $url = $attachment::find()->select(['eattach_attachment_url'])->where(['eattach_id'=>$v])->one();
            unlink($url['eattach_attachment_url']);
        }
        $query = $attachment::deleteAll(['eattach_id'=>$id]);
        if($query){
            return (['result'=>$query,"code"=>'200',"message"=>'删除成功',"isSuccess"=>true]);
        }else{
            return (['result'=>$query,"code"=>'403',"message"=>'删除失败',"isSuccess"=>false]);
        }
    }*/



    /**
     * @SWG\Get(path="/user/category",
     *     tags={"云平台-User-用户"},
     *     summary="岗位列表",
     *     description="岗位列表",
     *     produces={"application/json"},
     *     @SWG\Response(
     *         response = 200,
     *         description = "员工管理列表"
     *     ),
     *     @SWG\Response(
     *         response = 403,
     *         description = "查询失败",
     *     )
     * )
     *
     **/
    public function actionCategory(){
        $query = (new yii\db\Query())
            ->select('*')
            ->from('orangehrm_mysql.ohrm_job_category')
            ->all();
        if($query){
            $this->serializer['message'] = '查询成功';
            return $query;
        }else{
            $this->serializer['message'] = '没有信息';
            return $query;
        }
    }



    /**
     * @SWG\Get(path="/user/education",
     *     tags={"云平台-User-用户"},
     *     summary="学历列表",
     *     description="学历列表",
     *     produces={"application/json"},
     *     @SWG\Response(
     *         response = 200,
     *         description = "员工管理列表"
     *     ),
     *     @SWG\Response(
     *         response = 403,
     *         description = "查询失败",
     *     )
     * )
     *
     **/
    public function actionEducation(){
        $query = (new yii\db\Query())
            ->select('*')
            ->from('orangehrm_mysql.ohrm_education')
            ->all();
        if($query){
            $this->serializer['message'] = '查询成功';
            return $query;
        }else{
            $this->serializer['message'] = '没有信息';
            return $query;
        }
    }






    /**
     * @SWG\Get(path="/user/degree",
     *     tags={"云平台-User-用户"},
     *     summary="学位列表",
     *     description="学位列表",
     *     produces={"application/json"},
     *     @SWG\Response(
     *         response = 200,
     *         description = "员工管理列表"
     *     ),
     *     @SWG\Response(
     *         response = 403,
     *         description = "查询失败",
     *     )
     * )
     *
     **/
    public function actionDegree(){
        $query = (new yii\db\Query())
            ->select('*')
            ->from('orangehrm_mysql.ohrm_degree')
            ->all();
        if($query){
            $this->serializer['message'] = '查询成功';
            return $query;
        }else{
            $this->serializer['message'] = '没有信息';
            return $query;
        }
    }



    /**
     * @SWG\Get(path="/user/field",
     *     tags={"云平台-User-用户"},
     *     summary="职称列表",
     *     description="职称列表",
     *     produces={"application/json"},
     *     @SWG\Response(
     *         response = 200,
     *         description = "员工管理列表"
     *     ),
     *     @SWG\Response(
     *         response = 403,
     *         description = "查询失败",
     *     )
     * )
     *
     **/
    public function actionField(){
        $query = (new yii\db\Query())
            ->select(['id','job_title'])
            ->from('orangehrm_mysql.ohrm_job_title')
            ->all();
        if($query){
            $this->serializer['message'] = '查询成功';
            return $query;
        }else{
            $this->serializer['message'] = '没有信息';
            return $query;
        }
    }





    /**
     * @SWG\Get(path="/user/license",
     *     tags={"云平台-User-用户"},
     *     summary="执业资格列表",
     *     description="执业资格列表",
     *     produces={"application/json"},
     *     @SWG\Response(
     *         response = 200,
     *         description = "员工管理列表"
     *     ),
     *     @SWG\Response(
     *         response = 403,
     *         description = "查询失败",
     *     )
     * )
     *
     **/
    public function actionLicense(){
        $query = (new yii\db\Query())
            ->select('*')
            ->from('orangehrm_mysql.ohrm_license')
            ->all();
        if($query){
            $this->serializer['message'] = '查询成功';
            return $query;
        }else{
            $this->serializer['message'] = '没有信息';
            return $query;
        }
    }





    /**
     * @SWG\Get(path="/user/subunit",
     *     tags={"云平台-User-用户"},
     *     summary="小组列表",
     *     description="小组列表",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "query",
     *        name = "Token",
     *        description = "Token",
     *        required = false,
     *        type = "string",
     *     ),
     *     @SWG\Parameter(
     *        in = "query",
     *        name = "status",
     *        description = "status= 1 时返回所有 默认0 ",
     *        required = false,
     *        type = "string",
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "员工管理列表"
     *     ),
     *     @SWG\Response(
     *         response = 403,
     *         description = "查询失败",
     *     )
     * )
     *
     **/
    public function actionSubunit(){
        $status = Yii::$app->request->get('status');
        $role = $this->userRoleId;
        $work = $this->workStation;
        $customerId = $this->customerId;
        if(empty($status)){

            if($role != 1){
                $where = "id = '$work' and id != 1";
            }else{
                $where = 'id != 1';
            }
        }else{
            $where = " customer_id = '$customerId'";
        }
        
        
        $query = Subunit::find()->asArray()->where($where)->all();
        if($query){
            $this->serializer['message'] = '查询成功';
            return $query;
        }else{
            $this->serializer['message'] = '没有信息';
            return $query;
        }
    }

    /**
     * @SWG\Get(path="/user/nationality",
     *     tags={"云平台-User-用户"},
     *     summary="国家列表",
     *     description="国家列表",
     *     produces={"application/json"},
     *     @SWG\Response(
     *         response = 200,
     *         description = "员工管理列表"
     *     ),
     *     @SWG\Response(
     *         response = 403,
     *         description = "查询失败",
     *     )
     * )
     *
     **/
    public function actionNationality(){
        $query = (new yii\db\Query())
            ->select('*')
            ->from('orangehrm_mysql.ohrm_nationality')
            ->all();
        if($query){
            $this->serializer['message'] = '查询成功';
            return $query;
        }else{
            $this->serializer['message'] = '没有信息';
            return $query;
        }
    }





    /**
     * @SWG\Get(path="/user/minzu",
     *     tags={"云平台-User-用户"},
     *     summary="民族列表",
     *     description="民族列表",
     *     produces={"application/json"},
     *     @SWG\Response(
     *         response = 200,
     *         description = "员工管理列表"
     *     ),
     *     @SWG\Response(
     *         response = 403,
     *         description = "查询失败",
     *     )
     * )
     *
     **/
    public function actionMinzu(){
        $query = (new yii\db\Query())
            ->select('*')
            ->from('orangehrm_mysql.hs_hr_minzu')
            ->all();
        if($query){
            $this->serializer['message'] = '查询成功';
            return $query;
        }else{
            $this->serializer['message'] = '没有信息';
            return $query;
        }
    }




    /**
     * @SWG\Get(path="/user/edu",
     *     tags={"云平台-User-用户"},
     *     summary="学历分布图",
     *     description="学历分布图",
     *     produces={"application/json"},
     *     @SWG\Response(
     *         response = 200,
     *         description = "员工管理列表"
     *     ),
     *     @SWG\Parameter(
     *        in = "query",
     *        name = "Token",
     *        description = "Token",
     *        required = false,
     *        type = "string",
     *     ),
     *     @SWG\Response(
     *         response = 403,
     *         description = "查询失败",
     *     )
     * )
     *
     **/
    public function actionEdu(){
        $user = new User();
        $model = $user->education();
        return $model;
    }


    /**
     * @SWG\Get(path="/user/title",
     *     tags={"云平台-User-用户"},
     *     summary="职称分布图",
     *     description="职称分布图",
     *     produces={"application/json"},
     *     @SWG\Response(
     *         response = 200,
     *         description = "员工管理列表"
     *     ),
     *     @SWG\Parameter(
     *        in = "query",
     *        name = "Token",
     *        description = "Token",
     *        required = false,
     *        type = "string",
     *     ),
     *     @SWG\Response(
     *         response = 403,
     *         description = "查询失败",
     *     )
     * )
     *
     **/
    public function actionTitle(){
        $user = new User();
        $model = $user->title();
        return $model;
    }


    /**
     * @SWG\Get(path="/user/personnel",
     *     tags={"云平台-User-用户"},
     *     summary="人事关系",
     *     description="人事关系",
     *     produces={"application/json"},
     *     @SWG\Response(
     *         response = 200,
     *         description = "员工管理列表"
     *     ),
     *     @SWG\Parameter(
     *        in = "query",
     *        name = "Token",
     *        description = "Token",
     *        required = false,
     *        type = "string",
     *     ),
     *     @SWG\Parameter(
     *        in = "query",
     *        name = "isAdd",
     *        description = "是否是添加",
     *        required = false,
     *        type = "string",
     *     ),
     *     @SWG\Response(
     *         response = 403,
     *         description = "查询失败",
     *     )
     * )
     *
     **/
    public function actionPersonnel(){
        $is_add = yii::$app->request->get('isAdd');
        if($is_add == ''){
            $data = Personnel::find()->asArray()->all();
            if($data){
                $this->serializer['message'] = '查询成功';
                return $data;
            }else{
                $this->serializer['message'] = '没有信息';
                return $data;
            }
        }else{
            if($this->workStation === 0){
                $data = Personnel::find()->asArray()->all();
                return $data;
            }else{
                $data = array(['id'=>'8','name'=>'学生']);
                return $data;
            }


        }

    }

    /**
     * @SWG\Get(path="/user/status",
     *     tags={"云平台-User-用户"},
     *     summary="职务列表",
     *     description="职务列表",
     *     produces={"application/json"},
     *     @SWG\Response(
     *         response = 200,
     *         description = "员工管理列表"
     *     ),
     *     @SWG\Response(
     *         response = 403,
     *         description = "查询失败",
     *     )
     * )
     *
     **/
    public function actionStatus(){
        $query = (new yii\db\Query())
            ->select('')
            ->from('orangehrm_mysql.ohrm_employment_status')
            ->all();
        if($query){
            $this->serializer['message'] = '查询成功';
            return $query;
        }else{
            $this->serializer['message'] = '没有信息';
            return $query;
        }
    }

    /**
     * @SWG\Get(path="/user/empname",
     *     tags={"云平台-User-用户"},
     *     summary="所有员工人名",
     *     description="所有员工人名",
     *     produces={"application/json"},
     *     @SWG\Response(
     *         response = 200,
     *         description = "员工管理列表"
     *     ),
     *     @SWG\Response(
     *         response = 403,
     *         description = "查询失败",
     *     )
     * )
     *
     **/
    public function actionEmpname(){
        $query = (new yii\db\Query())
            ->select(['emp_number','emp_firstname'])
            ->from('orangehrm_mysql.hs_hr_employee')
            ->all();
        if($query){
            $this->serializer['message'] = '查询成功';
            return $query;
        }else{
            $this->serializer['message'] = '没有信息';
            return $query;
        }
    }


    /**
     * @SWG\Get(path="/user/selleader",
     *     tags={"云平台-User-用户"},
     *     summary="组长名",
     *     description="组长名",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "query",
     *        name = "id",
     *        description = "小组id",
     *        required = false,
     *        type = "string",
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "员工管理列表"
     *     ),
     *     @SWG\Response(
     *         response = 403,
     *         description = "查询失败",
     *     )
     * )
     *
     **/
    public function actionSelleader(){
        $id = Yii::$app->request->get('id');
        $user = new User();
        $model = $user->selleadder($id);
        if($model){
            $this->serializer['message'] = '查询成功';
            return $model;
        }else{
            $this->serializer['message'] = '没有信息';
            return $model;
        }
    }



    /**
     * @SWG\Get(path="/user/faculty",
     *     tags={"云平台-User-用户"},
     *     summary="师资列表",
     *     description="师资列表",
     *     produces={"application/json"},
     *     @SWG\Response(
     *         response = 200,
     *         description = "员工管理列表"
     *     ),
     *     @SWG\Response(
     *         response = 403,
     *         description = "查询失败",
     *     )
     * )
     *
     **/
    public function actionFaculty(){
       $query = Faculty::find()->asArray()->all();
       return $query;
    }


    /**
     * @SWG\Get(path="/user/keyan",
     *     tags={"云平台-User-用户"},
     *     summary="科研职称",
     *     description="科研职称",
     *     produces={"application/json"},
     *     @SWG\Response(
     *         response = 200,
     *         description = "员工管理列表"
     *     ),
     *     @SWG\Response(
     *         response = 403,
     *         description = "查询失败",
     *     )
     * )
     *
     **/
    public function actionKeyan(){
        $query = Keyan::find()->asArray()->all();
        return $query;
    }
    /**
     * @SWG\Get(path="/user/teacher-title",
     *     tags={"云平台-User-用户"},
     *     summary="教师职称",
     *     description="教师职称",
     *     produces={"application/json"},
     *
     *     @SWG\Response(
     *         response = 200,
     *         description = "员工管理列表"
     *     ),
     *     @SWG\Response(
     *         response = 403,
     *         description = "查询失败",
     *     )
     * )
     *
     **/
    public function actionTeacherTitle(){
        $query = TeacherTitle::find()->asArray()->all();
        return $query;
    }

    /**
     * @SWG\Get(path="/user/tutor",
     *     tags={"云平台-User-用户"},
     *     summary="硕博导",
     *     description="硕博导",
     *     produces={"application/json"},
     *     @SWG\Response(
     *         response = 200,
     *         description = "员工管理列表"
     *     ),
     *     @SWG\Response(
     *         response = 403,
     *         description = "查询失败",
     *     )
     * )
     *
     **/
    public function actionTutor(){
        $query = Tutor::find()->asArray()->all();
        return $query;
    }


/*    public function actionDemo(){
        $excel = Empexcel::find()->asArray()->all();
        foreach ($excel as $k => $v){
            $emp_number = Employee::find()->select(['emp_number'])->where(['emp_firstname'=>$v['user_name']])->one();
            $emp_number = $emp_number['emp_number'];

            $title_code = Title::find()->select(['id'])->where(['job_title'=>$v['job_title_code']])->one();
            $job_title_code = $title_code['id'];

            $time = substr_replace($v['job_title_time'], '-', 4, 0);
            $time = substr_replace($time, '-', 7, 0);

            $emptitle = new EmpTitle();
            $emptitle -> emp_number = $emp_number;
            $emptitle -> job_title_code = $job_title_code;
            $emptitle -> job_title_time = $time;
            $emptitle -> save();
        }
    }*/


    /*public function actionDemo(){
        $employee = Employee::find()->asArray()->all();

        foreach ($employee as $k => $v){
            $teach = new Teach();
            $teach->emp_number = $v['emp_number'];
            $teach->school = $v['attime_graduation_school'];
            $teach->major = $v['attime_studymajor'];
            $teach->record_id = $v['attime_education'];
            $teach->end_time = $v['attime_graduation'];
            $teach->save();
        }

        foreach ($employee as $k => $v){
            $teach = new Teach();
            $teach->emp_number = $v['emp_number'];
            $teach->school = $v['now_graduation_school'];
            $teach->record_id = $v['education_id'];
            $teach->degree_id = $v['now_academic_degree'];
            $teach->end_time = $v['now_graduationtime'];
            $teach->save();
        }
    }*/



/*    public function actionDemo(){
        $work = WorkExcel::find()->asArray()->orderBy('user_name,time_in')->all();

        foreach ($work as $k => $v){
            $yue = $this->getMonthNum($v['time_in'],$v['time_out'],'-');
            $workexcel = WorkExcel::find()->where(['id'=>$v['id']])->one();
            $workexcel->yue = $yue;
            $workexcel->save();
        }
    }*/


    public function actionJisuan(){
        $data = Record::find()->asArray()->all();
        foreach ($data as $k => $v){
            if($v['time_out'] != '至今'){
                $yue = $this->getMonthNum($v['time_in'],$v['time_out'],'-');
                $record = Record::find()->where(['id'=>$v['id']])->one();
                $record->total_month = $yue;
                $record->save();
            }

        }
    }

    function getMonthNum( $date1, $date2, $tags='-' ){
        $date1 = explode($tags,$date1);
        $date2 = explode($tags,$date2);
        return abs($date2[0] - $date1[0]) * 12 + ($date2[1] - $date1[1]);
    }

    /**
     * @SWG\Post(path="/user/get-all-employee",
     *     tags={"云平台-User-用户"},
     *     summary="获取所有可以操作的员工列表",
     *     description="获取所有可以操作的员工列表",
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
     *        description = "查询状态1时查询所有",
     *        required = false,
     *        type = "string"
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "员工管理列表"
     *     ),
     *     @SWG\Response(
     *         response = 403,
     *         description = "查询失败",
     *     )
     * )
     *
     **/
    public function actionGetAllEmployee(){
        $status = Yii::$app->request->post('status'); 
        $employee =new Employee();
        $workStation = 0;
        if($this->userRoleId==1||$this->userRoleId==4||$this->userRoleId==5){
            $workStation = 0;
        }else{
            if($this->userRoleId==2||$this->userRoleId==8){
                if($this->workStation){
                    $workStation = $this->workStation;
                }else{
                    $this->serializer['status'] = false;
                    $this->serializer['errno'] = 2;
                    $this->serializer['message'] = '获取列表失败'; 
                    return false;
                    
                }
                
            }
        }

        if($status){
            $workStation = 0;
        }

        $list = $employee->getEmpByWorkStation($workStation);
        $backArr = array();
        if($list){
            foreach ($list as $key => $value) {
                  $backArr[] = array('value'=>$value['emp_number'],'label'=>$value['emp_firstname']);  
            }
        }else{
            $backArr[] = array('value'=>'','label'=>'');
        }
        $this->serializer['status'] = true;
        $this->serializer['errno'] = 0;
        $this->serializer['message'] = ''; 
        return array('data'=>$backArr);

    }

    /*public function actionDemo(){
        $arr = [11,12,13];
        $user = new User();
        $model = $user->RotationEmployee($arr,'2019-02-04');
        return $model;
    }*/
}

