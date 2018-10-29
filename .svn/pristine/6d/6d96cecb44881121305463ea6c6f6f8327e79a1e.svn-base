<?php
namespace frontend\controllers\v1;
use common\models\shift\RotationDown;
use common\models\shift\RotationEmployee;
use common\models\shift\RotationList;
use common\models\shift\RotationResultNewest;
use common\models\shift\RotationResultOriginal;
use common\models\shift\RotationResultTemp;
use common\models\shift\RotationRule;
use common\models\shift\RotationVersion;
use common\models\shift\RotationVersionResult;
use common\models\user\Record;
use common\models\user\User;
use Yii;
use yii\db\Command;
use yii\web\Response;
use yii\web\Controller;

//use yii\helpers\ArrayHelper;
class RotationResultTempController extends \common\rest\Controller
{
    /**
     * @var string
     */
    public $modelClass = 'common\models\shift\RotationResultTemp';

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
     * @SWG\Post(path="/rotation-result-temp/create-version",
     *     tags={"云平台-rotationResultTemp-轮转中间表"},
     *     summary="创建轮转计划",
     *     description="新建轮转计划",
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
     *        name = "rotationId",
     *        description = "轮转表id",
     *        required = true,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "versionName",
     *        description = "模板名称",
     *        required = true,
     *        type = "integer",
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
    //创建轮转版本表（rotationVersion）
    public function actionCreateVersion()
    {
        $post = Yii::$app->request->post();
        $rotationId = $post['rotationId'];
        $versionName = $post['versionName'];
        $rotationVersion = new RotationVersion();
        $rotationResultTemp = new RotationResultTemp();
        $rotationVersion->versionName = $versionName;
        $rotationVersion->rotationId = $rotationId;

        if($rotationVersion->save()){
            $rotationVersionId = $rotationVersion->attributes['id'];
            $ResultTempAll = $rotationResultTemp->getRotationResultTempPublish($rotationId);
            foreach ($ResultTempAll as $key=>$value)
            {
                $rotationVersionResult = new RotationVersionResult();
                $rotationVersionResult->rotationVersionId = $rotationVersionId;
                $rotationVersionResult->rotationId = $value['rotationId'];
                $rotationVersionResult->rotationDate = $value['rotationDate'];
                $rotationVersionResult->groupId = $value['groupId'];
                $rotationVersionResult->usersRecommend = $value['usersRecommend'];
                $rotationVersionResult->usersRecommendUnselected = $value['usersRecommendUnselected'];
                $rotationVersionResult->usersPrepareIn = $value['usersPrepareIn'];
                $rotationVersionResult->usersPrepareOut = $value['usersPrepareOut'];
                $rotationVersionResult->rotationUserCount = $value['rotationUserCount'];
                $rotationVersionResult->save();
            }
            $this->serializer['errno'] = 0;
            $this->serializer['status'] = true;
            $this->serializer['message'] = "添加成功";
            return[];
        }else{
            $this->serializer['errno'] = 0;
            $this->serializer['status'] = false;
            $this->serializer['message'] = "添加失败";
            return[];
        }




        /**
         * 获取用户输入信息的模板名称, 前端返回的轮转表id
         * insert into rotationVersion (versionName,rotationId,status) value('计划1',2,0);
         *
         */

    }
    /**
     * @SWG\Post(path="/rotation-result-temp/publish",
     *     tags={"云平台-rotationResultTemp-轮转中间表"},
     *     summary="发布",
     *     description="发布",
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
     *        name = "rotationId",
     *        description = "轮转id",
     *        required = true,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "rotationVersionId",
     *        description = "轮转版本表id",
     *        required = true,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "status",
     *        description = "发布或者调整",
     *        required = true,
     *        type = "integer",
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
    //确认发布按钮
    public function actionPublish()
    {
        $rotationVersionId = Yii::$app->request->post('rotationVersionId');
        $rotationId = Yii::$app->request->post('rotationId');
        $status = Yii::$app->request->post('status');
        $rotationResultTemp = new RotationResultTemp();
        $all = $rotationResultTemp->getRotationResultTempPublish($rotationId);
        $list = array();
        $newarray = array();
        /*if($status == 1){
            $rotationNewest = new RotationResultNewest();
            $newest = $rotationNewest->getNewestAll($rotationId);
            foreach ($newest as $k=>$v)
            {
                $date = date("Ym",strtotime($v['rotationDate']));
                $list[$date][] = $v;
            }
            foreach ($list as $key=>$value)
            {
                foreach ($value as $k=>$v)
                {
                    $usersRecommend = json_decode($v['usersRecommend'],true);
                    $mmend = array_column($usersRecommend,'userId');
                    if(!empty($v['usersPrepareOut'])){
                        $userOut = json_decode($v['usersPrepareOut'],true);
                        $count = count($value);
                        foreach ($userOut as $k1=>$v1)
                        {
                            if(in_array($v1['userId'],$mmend)){
                                for ($i=0;$i<$count;$i++)
                                {
                                    if(!empty($value[$i]['usersPrepareIn'])){
                                        $userIn = json_decode($value[$i]['usersPrepareIn'],true);
                                        $in = array_column($userIn,'userId');
                                        if(in_array($v1['userId'],$in)){
                                            $newarray[] = ['time_in'=>$value[$i]['rotationDate'],'emp_number'=>$v1['userId'],'work_station'=>$value[$i]['groupId']];

                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }*/
//        var_dump($newarray);
//        exit;
        $array = array();
        $data = array();
        foreach ($all as $k=>$v)
        {
            $date = date("Ym",strtotime($v['rotationDate']));
            $date1 = date("Y年m月",strtotime($v['rotationDate']));
            $array[$date][] = $v;
            $countIn = json_decode($v['usersPrepareIn'],true);
            $countOut = json_decode($v['usersPrepareOut'],true);
            if((count($countIn)-count($countOut)) !==0){
                $this->serializer['errno']   = 0;
                $this->serializer['status']   = false;
                $this->serializer['message'] = $date1."人数不匹配,请调整后发布";
                return;
            }
            /*if(!empty($countIn)){
                foreach ($countIn as $key=>$in)
                {
                    if($in['errorInfo'] != ''){
                        $this->serializer['errno']   = 0;
                        $this->serializer['status']   = true;
                        $this->serializer['message'] = $date."有违背规则的，真的忽略这些直接发布吗？";
                    }
                    $row['time_in'] = $v['rotationDate'];
                    $row['work_station'] = $v['groupId'];
                    $row['emp_number'] = $in['userId'];
                    $data[]  = $row;
                }
            }*/
        }

        foreach ($array as $key=>$value)
        {
            foreach ($value as $k=>$v)
            {
                if(!empty($v['usersRecommend'])){
                    $usersRecommend = json_decode($v['usersRecommend'],true);
                    $mmend = array_column($usersRecommend,'userId');
                }
                if(!empty($v['usersPrepareOut'])){
                    $count = count($value);
                    $countOut = json_decode($v['usersPrepareOut'],true);
                    foreach ($countOut as $k1=>$v1)
                    {
                        if(in_array($v1['userId'],$mmend)){
                            for ($i=0;$i<$count;$i++)
                            {
                                if(!empty($value[$i]['usersPrepareIn'])){
                                    $countIn = json_decode($value[$i]['usersPrepareIn'],true);
                                    $in = array_column($countIn,'userId');
                                    if(in_array($v1['userId'],$in)){
                                        $data[] = ['time_in'=>$value[$i]['rotationDate'],'emp_number'=>$v1['userId'],'work_station'=>$value[$i]['groupId']];
                                        break;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        $user = new User();
        if(!empty($data)){
            $user->putinSubunit($data);
            $rotation = RotationList::findOne($rotationId);
            $rotation->status = 1;
            $rotation->pushTime = date('Y-m-d',time());
            $rotation->save();
        $rotationResultTemp->createOriginal($all);
            $this->serializer['errno']   = 0;
            $this->serializer['status']   = true;
            $this->serializer['message'] = "发布成功";
            return;
        }else{
            $this->serializer['errno']   = 0;
            $this->serializer['status']   = false;
            $this->serializer['message'] = "没有数据";
            return;
        }
        /**
         * 查询轮转结果中间表rotationResultTemp
         * select rotationDate,groupId,usersRecommendUnselected,usersPrepareIn,usersPrepareOut,rotationUserCount where rotationId=2;
         * 获取数据后存入,轮转结果原始表rotationResultOriginal ,此表只存一次
         * insert into rotationResultOriginal (rotationVersionId,rotationId,rotationDate,groupId,usersRecommend...rotationUserCount) value(...);
         * 同时更改 轮转表（rotationList）status的状态为1 已发布
         * update rotationList set status=1 pushTime=time() where rotationId=2;
         *
         */
    }
    /**
     * @SWG\Post(path="/rotation-result-temp/update",
     *     tags={"云平台-rotationResultTemp-轮转中间表"},
     *     summary="显示年月",
     *     description="显示年月",
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
     *         description = "返回班次类型列表"
     *     ),
     *     @SWG\Response(
     *         response = 422,
     *         description = "Data Validation Failed 创建失败",
     *     )
     * )
     *
     **/
    //暂时不需要
    public function actionUpdate()
    {
        $a = '[{
                "label": "此轮转计划未发布时显示：",
                "status":1,
                
                "name": "2"
            }
            ]';
        $b = json_decode($a);
        $this->serializer['errno']   = 0;
        $this->serializer['status']   = true;
        $this->serializer['message'] = "获取成功";
        return $b;
    }
    /**
     * @SWG\Post(path="/rotation-result-temp/show-date",
     *     tags={"云平台-rotationResultTemp-轮转中间表"},
     *     summary="显示年月",
     *     description="显示年月",
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
     *        name = "rotationId",
     *        description = "轮转id",
     *        required = true,
     *        type = "integer",
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
    //显示年月2019年02月  03月 ... 2020年01月
    public function actionShowDate()
    {
        //status为确认发布或者确认调整,'label'=>'确认发布或者确认调整'  status值1代表确认发布
        $b = [
                  'label'=>'确认发布',"id"=>"1","status"=>"1","name"=>"2",
                  'time'=>[
                        ["label"=>"2019年01月","name"=>"1"],
                        ["label"=>"07月","name"=>"7"],
                        ["label"=>"08月","name"=>"8"],
                        ["label"=>"09月","name"=>"9"],
                        ["label"=>"10月","name"=>"10"],
                        ["label"=>"11月","name"=>"11"],
                        ["label"=>"12月","name"=>"12"],
                        ["label"=>"2020-01","name"=>"13"],
                  ],
        ];
        $rotationId = Yii::$app->request->post('rotationId');
        $RotationList = new RotationList();
        $RotationListOne = $RotationList->getRotationOne($rotationId);
        $rotationDateBegin = $RotationListOne['rotationDateBegin'];
        $rotationDateEnd = $RotationListOne['rotationDateEnd'];
        $status = $RotationListOne['status'];
        $hellp = getMonths($rotationDateBegin,$rotationDateEnd);
        $year = '';
        $arr = array();
        $list = array();
        foreach ($hellp as $k=>$v){
            $y = date('Y',strtotime($v));
            if(empty($year)){
                $year = $y;
                $arr[] = date('Y年m月',strtotime($v));
            }else{
                $y = date('Y',strtotime($v));
                if($y==$year){
                    $arr[] = date('m月',strtotime($v));
                }else{
                    $year = $y;
                    $arr[] = date('Y年m月',strtotime($v));;
                }
            }
        }
        $data = array();
        foreach ($arr as $key=>$value){
            $data[$key]['label'] = $value;
            foreach ($hellp as $k=>$v){
                $data[$k]['name'] =$v;
            }
        }
        if($status == 1){
            $list['label'] = '确认调整';
        }else{
            $list['label'] = '确认发布';
        }
            $list['status'] = $status;
        $list['name'] = $rotationDateBegin;
        $list['time'] = $data;
        $this->serializer['errno'] = 0;
        $this->serializer['status'] = true;
        $this->serializer['message'] = "获取成功";
        return $list;
        /**
         * 从轮转表（rotationList）取出开始时间和结束时间, 接收轮转表的id
         * select rotationDateBegin,rotationDateEnd from rotationList where id = 2;
         * 用时间函数转成月份的格式
         */


    }
    /**
     * @SWG\Post(path="/rotation-result-temp/show-version",
     *     tags={"云平台-rotationResultTemp-轮转中间表"},
     *     summary="显示版本表",
     *     description="显示版本表",
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
     *        name = "rotationId",
     *        description = "轮转表id",
     *        required = true,
     *        type = "integer",
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
    //显示轮转版本表（rotationVersion）
    public function actionShowVersion()
    {

        $rotationId = Yii::$app->request->post('rotationId');
        $RotationList = new RotationList();
        $RotationListOne = $RotationList->getRotationOne($rotationId);
        $rotationVersion = new RotationVersion();
        $RotationListStatus = $RotationListOne['status'];
        $list = $rotationVersion->getVersionAll($rotationId,$RotationListStatus);
        $data = [];
        $b = [];
        foreach ($list as $key=>$value){
            $data[$key]['label'] = $value['versionName'];
            $data[$key]['value'] = $value['id'];
        }

        /*$b = [
            ["label"=>"恢复到某版本(单选)","value"=>"-1"],
        ];
        $data = array_merge($b,$data);*/
        $this->serializer['errno'] = 0;
        $this->serializer['status'] = true;
        $this->serializer['message'] = "获取成功";
        return $data;
        /**查询已发布的版本
         * select versionName form rotationVersion where rotationId=2 and status=1;
         */

    }
    /**
     * @SWG\Post(path="/rotation-result-temp/show-person",
     *     tags={"云平台-rotationResultTemp-轮转中间表"},
     *     summary="显示满足人数",
     *     description="显示满足人数",
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
     *        name = "versionid",
     *        description = "轮转版本表id",
     *        required = true,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "status",
     *        description = "状态(调整或发布)",
     *        required = true,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "rotationId",
     *        description = "轮转表id",
     *        required = true,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "rotationDate",
     *        description = "轮转日期",
     *        required = true,
     *        type = "string",
     *     ),
     *
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
    public function actionShowPerson()
    {
        $post = Yii::$app->request->post();
        $rotationId = $post['rotationId'];
        $rotationDate = $post['rotationDate'];
        $versionid = $post['versionid'];
        $rotationList = new RotationList();
        $user = new User();
        $RotationEmployee = new RotationEmployee();
        $RotationResultTemp = new RotationResultTemp();
        $orderByOne = RotationResultTemp::find()
            ->where(['rotationId'=>$rotationId])
            ->andWhere(['<','rotationDate',$rotationDate])
            ->orderBy(['rotationDate'=>SORT_DESC])
            ->one();
        if(!empty($orderByOne)){
            $dateAll = $RotationResultTemp->getRotationResultTemp($rotationId,$orderByOne->rotationDate);
            /*foreach ($dateAll as $k=>$v)
            {
                $in = count(json_decode($v['usersPrepareIn'],true));

                $out = count(json_decode($v['usersPrepareOut'],true));
                if(($out - $in) !=0){
                    $newMouth = date("Y年m月",strtotime($orderByOne->rotationDate));
                    $this->serializer['errno'] = 0;
                    $this->serializer['status'] = false;
                    $this->serializer['message'] = "请调平".$newMouth."数据";
                    $rotationDate = $orderByOne->rotationDate;
                    return ['name'=>$rotationDate];
                    break;
                }else{
                    if($rotationDate == $orderByOne->rotationDate){
                        $rotationDate = $orderByOne->rotationDate;
                    }else{
                        $rotationDate = date("Y-m-d",strtotime($orderByOne->rotationDate."+1 month"));
                    }
                    $this->serializer['errno'] = 0;
                    $this->serializer['status'] = true;
                    $this->serializer['message'] = "获取成功5";
                }
            }*/
//            var_dump($rotationDate);exit;
            /*if($rotationDate == $Date){
                $result = ['name'=>$Date];
                return $result;
            }*/
        }
        //通过轮转id获取一条轮转计划 从而抽取下面参加轮转的组信息
        $RotationOne = $rotationList->getRotationOne($rotationId);
        $groupInfo = json_decode($RotationOne['groupInfo'],true);
        //从轮转表获取$groupInfo为数组形式[11,12]
        $groupInfo = array_column($groupInfo,'groupId');
        $ruleType = 'out';
        //从基础人员里筛选初步参加轮转的人存储到 轮转人员信息临时表(rotationEmployee)
        $empcount = $RotationEmployee->EmployeeCount($rotationId);

        if(count($empcount) == 0)
        {
            //需要组信息  时间段参数获取基础人员信息
            $userAll = $user->RotationEmployee($groupInfo,$rotationDate);
            var_dump($userAll);exit;
            //存储到 轮转人员信息临时表(rotationEmployee)调用createEmployee方法
            $RotationEmployee->createEmployee($rotationDate,$userAll,$rotationId);

        }

        $ResultTempCount = $RotationResultTemp->ResultTempCount($rotationId);
        //判断临时表是否有数据,没有的话就从军伟接口取值,有的话从临时表上一个月取值
        if($ResultTempCount === '0')
        {
            //存储到中间表（rotationResultTemp）
            foreach ($groupInfo as $key=>$groupId)
            {
                //获取轮转id ,组, 和调出调入类型的规则
                $RotationRule = new RotationRule();
                $ruleAll = $RotationRule->getRuleAll($rotationId,$groupId,$ruleType);
                //调取  规则筛选函数
                $RotationRule->checkRuleOut($ruleAll,$groupId);
                //获取到每个组符合调出条件的全部人串  存储到轮转人员信息临时表(rotationEmployee)
                $rotationEmployee = new RotationEmployee();
                //根据小组,时间月份rotationDate  轮转表id 状态isDel
                $rotationEmployeIsDel = $rotationEmployee->getempAll($rotationId,$groupId);
                if(!empty($rotationEmployeIsDel)){
                    //存储到  轮转结果中间表（rotationResultTemp）
                    $list = array();
                    foreach ($rotationEmployeIsDel as $k=>$v){
                        $list[$k]['userId'] = $v['emp_number'];
                        $list[$k]['userName'] = $v['emp_firstname'];
                    }
                    $RotationResultTemp = new RotationResultTemp();
                    $RotationResultTempOne = $RotationResultTemp->getRotationResultTempOne($rotationId,$rotationDate,$groupId);
                    if(empty($RotationResultTempOne)){
                        $RotationResultTemp->rotationId = $rotationId;
                        $RotationResultTemp->rotationDate = $rotationDate;
                        $RotationResultTemp->groupId = $groupId;
                        $RotationResultTemp->usersRecommend = json_encode($list,JSON_UNESCAPED_UNICODE);
                        $RotationResultTemp->usersRecommendUnselected = json_encode($list,JSON_UNESCAPED_UNICODE);
                        $RotationResultTemp->save();

                    }
                }
            }
        }else{
            //判断post接收日期是否和一个月日期相同 ,不相同的存储post接收的日期的值
            if($rotationDate != $RotationOne['rotationDateBegin']){
                //查询post接收值前一个月的临时表值(比方接收的4月,查询3月份数据
                $orderBy = RotationResultTemp::find()
                    ->where(['rotationId'=>$rotationId])
                    ->andWhere(['<','rotationDate',$rotationDate])//小于
                    ->orderBy(['rotationDate'=>SORT_DESC])
                    ->one();
                //查询3月份的值,主要取出剩余满足人串
                $all = $RotationResultTemp->getRotationResultTemp($rotationId,$orderBy->rotationDate);
                foreach ($all as $k=>$v)
                {
                    //查询想要添加的一个组数据,有值就修改,没值就新增
                    $findOne = RotationResultTemp::find()
                        ->where(['rotationId'=>$rotationId])
                        ->where(['rotationDate'=>$rotationDate])
                        ->andWhere(['groupId'=>$v['groupId']])
                        ->one();
                    //如果为空就新增
                    if(empty($findOne)){
                        $RotationResultTemp = new RotationResultTemp();
                        $RotationResultTemp->rotationId = $rotationId;
                        $RotationResultTemp->rotationDate = $rotationDate;
                        $RotationResultTemp->groupId = $v['groupId'];
                        $RotationResultTemp->usersRecommend = $v['usersRecommendUnselected'];
                        $RotationResultTemp->usersRecommendUnselected = $v['usersRecommendUnselected'];
                        $RotationResultTemp->save();
                    }else{
                        //判断上一个月的剩余满足人串和本月的全部满足人串是否相等
                        //数量 不相等  修改,
                        $beforeCount = count(json_decode($v['usersRecommendUnselected'],true));
                        $nowCount = count(json_decode($findOne['usersRecommend'],true));
                        if($beforeCount != $nowCount){
                            $findOne->usersRecommend = $v['usersRecommendUnselected'];
                            $findOne->usersRecommendUnselected = $v['usersRecommendUnselected'];
                        }
                        $findOne->rotationId = $rotationId;
                        $findOne->rotationDate = $rotationDate;
                        $findOne->groupId = $v['groupId'];
                        $findOne->save();
                    }
                }
            }
        }
        //点击回滚版本
        if($versionid>0){
            $rotationVersionResult = new RotationVersionResult();
            $versionResultAll = $rotationVersionResult->VersionResultAll($rotationId,$versionid);
            RotationResultTemp::deleteAll(['rotationId'=>$rotationId]);
            foreach ($versionResultAll as $k=>$v)
            {
                $rotationResultTemp = new RotationResultTemp();
                $rotationResultTemp->rotationId = $v['rotationId'];
                $rotationResultTemp->rotationDate = $v['rotationDate'];
                $rotationResultTemp->groupId = $v['groupId'];
                $rotationResultTemp->usersRecommend = $v['usersRecommend'];
                $rotationResultTemp->usersRecommendUnselected = $v['usersRecommendUnselected'];
                $rotationResultTemp->usersPrepareIn = $v['usersPrepareIn'];
                $rotationResultTemp->usersPrepareOut = $v['usersPrepareOut'];
                $rotationResultTemp->rotationUserCount = $v['rotationUserCount'];
                $rotationResultTemp->save();
            }
        }
        //从 轮转结果中间表（rotationResultTemp）取出轮转表id,时间 为条件
        $rotationResultTempAll = $RotationResultTemp->getRotationResultTemp($rotationId,$rotationDate);
        $result = array();
        $userGroupName = $user->getSmallSubunit('xajdyfyyxb');
        //创建一个数组，用一个数组的值作为其键名，($userAll里的id作为键名)另一个数组的值作为其值
        $userGroupName = array_combine(array_column($userGroupName,'id'),$userGroupName);
        foreach ($rotationResultTempAll as $key=>$value)
        {
            //剩余人串
            $usersRecommendUnselected = json_decode($value['usersRecommendUnselected'],true);
            //调入的人串
            $usersPrepareIn = json_decode($value['usersPrepareIn'],true);
            //调出的人串
            $usersPrepareOut = json_decode($value['usersPrepareOut'],true);
            $result[$key]['groupName'] = $userGroupName[$value['groupId']]['name'];
            $result[$key]['id'] = $value['groupId'];
            $count1 = count($usersRecommendUnselected);
            $count2 = count($usersPrepareIn);
            $count3 = count($usersPrepareOut);
            $count = $count2-$count3;
            $max = max($count1,$count2,$count3);
            $row = array();
            for ($i=0;$i<$max;$i++)
            {
                if(!empty($usersPrepareIn[$i])){
                    if(array_key_exists('isError',$usersPrepareIn[$i])){
                        $row[$i][] = ['id'=>$usersPrepareIn[$i]['userId'],
                            'name'=>$usersPrepareIn[$i]['userName'],
                            'type'=>'0',
                            'isError'=>$usersPrepareIn[$i]['isError'],
                            'errorInfo'=>$usersPrepareIn[$i]['errorInfo']
                        ];
                    }else{
                        $row[$i][] = ['id'=>$usersPrepareIn[$i]['userId'],
                            'name'=>$usersPrepareIn[$i]['userName'],
                            'type'=>'0',
                            'isError'=>false,
                            'errorInfo'=>''
                        ];
                    }

                }else{
                    $row[$i][] = ['id'=>'','name'=>'','type'=>'0','isError'=>false,'errorInfo'=>''];
                }
                if(!empty($usersRecommendUnselected[$i])){
                    $userOne = RotationEmployee::findOne(['emp_number'=>$usersRecommendUnselected[$i]['userId']]);
                    $row[$i][] = ['id'=>$usersRecommendUnselected[$i]['userId'],
                        'name'=>$usersRecommendUnselected[$i]['userName'].' '.$userOne->work_time."月",
                        'type'=>'1',
                        'isError'=>false,
                        'errorInfo'=>''
                    ];
                }else{
                    $row[$i][] = ['id'=>'','name'=>'','type'=>'1','isError'=>false,'errorInfo'=>''];
                }
                if(!empty($usersPrepareOut[$i])){
                    $row[$i][] = ['id'=>$usersPrepareOut[$i]['userId'],
                        'name'=>$usersPrepareOut[$i]['userName'],
                        'type'=>'2',
                        'isError'=>false,
                        'errorInfo'=>''
                    ];
                }else{
                    $row[$i][] = ['id'=>'',
                        'name'=>'',
                        'type'=>'2',
                        'isError'=>false,
                        'errorInfo'=>''
                    ];
                }
            }

            $result[$key]['count'] = $count;
            $result[$key]['row'] = $row;

        }
//        $result = ['name'=>$rotationDate,'date'=>$result];
        $this->serializer['errno']   = 0;
        $this->serializer['status']   = true;
        $this->serializer['message'] = "获取完成";
         return $result;

        /**
         *
         *第一次进来没有结果时会全部显示满足条件的全部人串, 拟调入和拟调出人串都为空
         * select usersRecommend,usersPrepareIn,usersPrepareOut  where  rotationId=3
         */

    }
    /**
     * @SWG\Post(path="/rotation-result-temp/show-mutual",
     *     tags={"云平台-rotationResultTemp-轮转中间表"},
     *     summary="显示满足人数",
     *     description="显示满足人数",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "Token",
     *        description = "Token",
     *        required = true,
     *        type = "string",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "firstgroupid",
     *        description = "第一个组id",
     *        required = true,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "secondgroupid",
     *        description = "第二个组id",
     *        required = true,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "firstemp",
     *        description = "第一个员工id",
     *        required = true,
     *        type = "integer",
     *     ),

     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "secondemp",
     *        description = "第二个员工id",
     *        required = true,
     *        type = "integer",
     *     ),

     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "rotationDate",
     *        description = "时间月份",
     *        required = true,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "rotationId",
     *        description = "轮转表id",
     *        required = true,
     *        type = "integer",
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
    //数据交互
    public function actionShowMutual()
    {

        $firstgroupid = Yii::$app->request->post('firstgroupid');
        $secondgroupid = Yii::$app->request->post('secondgroupid');
        $firstemp = Yii::$app->request->post('firstemp');
        $secondemp = Yii::$app->request->post('secondemp');
        $rotationDate = Yii::$app->request->post('rotationDate');
        $rotationId = Yii::$app->request->post('rotationId');
        if(date('Ym',strtotime($rotationDate)) <= date('Ym')){
            $this->serializer['errno'] = 22;
            $this->serializer['status'] = false;
            $this->serializer['message'] = "计划已过期";
            return;
        }
        //判断是否是同一组人员
        if($firstgroupid == $secondgroupid){
            $this->serializer['errno'] = 22;
            $this->serializer['status'] = false;
            $this->serializer['message'] = "不能在同组操作";
            return;
        }
        $ruleType = 'in';
        $transaction = Yii::$app->db->beginTransaction();

        try{
            $RotationResultTemp = new RotationResultTemp();
            $rotationEmployee = new RotationEmployee();
            $RotationRule = new RotationRule();

            //取出将要调入另一个组的人员信息(第一个组id,第一个员工id)
            $firstEmp = $rotationEmployee->getrotationEmployeeOne($firstgroupid,$firstemp);
            //检测要接收此人员的条件是否符合(将要进入的组,第二个组id)
            $SecondGroupRull = $RotationRule->getRuleAll($rotationId,$secondgroupid,$ruleType);

            $firstGroupSave = RotationResultTemp::findOne(['rotationId'=>$rotationId,'rotationDate'=>$rotationDate,'groupId'=>$firstgroupid]);
            $firstRecommendUnselected = json_decode($firstGroupSave->usersRecommendUnselected,true);
            $firstPrepareOut = json_decode($firstGroupSave->usersPrepareOut,true);

            //把第一个组的剩余满足人串删除一个人员 unset
            if(!empty($firstRecommendUnselected)){
                foreach ($firstRecommendUnselected as $key=>$emp)
                {
                    if($firstemp == $emp['userId']){
                        if(count($firstRecommendUnselected) == 1){
                            unset($firstRecommendUnselected);
                        }else{
                            unset($firstRecommendUnselected[$key]);
                        }
                    }
                }
            }
            if(empty($firstRecommendUnselected)){
                $firstRecommendUnselected = null;
            }else{
                $firstRecommendUnselected = json_encode(array_values($firstRecommendUnselected),JSON_UNESCAPED_UNICODE);
            }
            //直接向第一个组的调出人串加一个
            $firstPrepareOut[] = ['userId'=>$firstemp,'userName'=>$firstEmp['emp_firstname']];
            //从第一个组里的 剩余人串(满足条件)减一个人  调出人串加一个人
            $firstGroupSave->usersRecommendUnselected = $firstRecommendUnselected;
            $firstGroupSave->usersPrepareOut = json_encode($firstPrepareOut,JSON_UNESCAPED_UNICODE);
            $firstGroupSave->save();

            //从第二个组里的调入人串加一个人,就是第一个人员信息
            $secondGroupSave = RotationResultTemp::findOne(['rotationId'=>$rotationId,'rotationDate'=>$rotationDate,'groupId'=>$secondgroupid]);
            $secondGroupPrepareIn = json_decode($secondGroupSave->usersPrepareIn,true);
            $secondGroupPrepareIn[] = ['userId'=>$firstemp,'userName'=>$firstEmp['emp_firstname']];
            $secondGroupSave->usersPrepareIn = json_encode($secondGroupPrepareIn,JSON_UNESCAPED_UNICODE);
            $secondGroupSave->save();
            $secondGroup = RotationResultTemp::findOne(['rotationId'=>$rotationId,'rotationDate'=>$rotationDate,'groupId'=>$secondgroupid]);
                //调用调入规则
            $message = $RotationRule->checkRuleIn($SecondGroupRull,$secondGroup);
//            var_dump($message);
            $transaction->commit();
            $this->serializer['errno'] = 0;
            $this->serializer['status'] = true;
            $this->serializer['message'] = "获取成功";
            return;
        }catch (\Exception $e){
            $transaction->rollBack();
            $this->serializer['errno'] = 0;
            $this->serializer['status'] = false;
            $this->serializer['message'] = "拖拽失败了";
            return;
        }


        /**
         *
         * 获取轮转表id,A组成员user1id,B组成员id,首先检查A组user1符合调入规则 再检查B组是否符合A组调入条件
         * select rotationRuleWarehouse.ruleTypeName,rotationRuleWarehouse.ruleTitle,rotationRule.ruleType,rotationRule.rotationRuleWarehouseId,rotationRule.select1,rotationRule.select2
         *  inner join rotationRule.ruleType=rotationRuleWarehouse.rotationRuleWarehouseId
         * 修改拟调入人串 unset()函数,
         * update rotationResultTemp set usersPrepareIn="",usersPrepareOut="" where rotationId=2 and rotationDate=5;
         * groupId=2 and userid=3
         */
    }
    /**
     * @SWG\Post(path="/rotation-result-temp/del-emp",
     *     tags={"云平台-rotationResultTemp-轮转中间表"},
     *     summary="差掉组人员",
     *     description="显示满足人数",
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
     *        name = "firstgroupid",
     *        description = "第一个组id",
     *        required = true,
     *        type = "integer",
     *     ),

     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "firstemp",
     *        description = "第一个员工id",
     *        required = true,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "firstempname",
     *        description = "第一个员工姓名",
     *        required = true,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "rotationDate",
     *        description = "时间月份",
     *        required = true,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "rotationId",
     *        description = "轮转表id",
     *        required = true,
     *        type = "integer",
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
    public function actionDelEmp()
    {
        $post = Yii::$app->request->post();
        $firstgroupid = $post['firstgroupid'];//第一个组id
        $firstemp = $post['firstemp'];//第一个员工id
        $rotationDate = $post['rotationDate'];//时间月份
        $rotationId = $post['rotationId'];//轮转表id
        if(date('Ym',strtotime($rotationDate)) <= date('Ym')){
            $this->serializer['errno'] = 22;
            $this->serializer['status'] = false;
            $this->serializer['message'] = "计划已过期";
            return;
        }
        $transaction = Yii::$app->db->beginTransaction();

        try{
            $RotationResultTemp = new RotationResultTemp();
            $firstGroup = $RotationResultTemp->getRotationResultTempOb($rotationId,$rotationDate,$firstgroupid);
            $usersPrepareIn = json_decode($firstGroup->usersPrepareIn,true);

            //取得调入人串后unset删除键值
            if(!empty($usersPrepareIn)){
                foreach ($usersPrepareIn as $key=>$emp)
                {
                    if($firstemp == $emp['userId']){
                        if(count($usersPrepareIn) == 1){
                            unset($usersPrepareIn);
                        }else{
                            unset($usersPrepareIn[$key]);

                        }
                    }
                }
            }
            if(empty($usersPrepareIn)){
                $usersPrepareIn = null;
            }else{
                $usersPrepareIn = json_encode(array_values($usersPrepareIn),JSON_UNESCAPED_UNICODE);
            }
            //更新拟调入字段, 减掉叉掉的人员
            $firstGroup->usersPrepareIn = $usersPrepareIn;
            $firstGroup->save();

            //取出全部数据,找出原来属于的组
            $RotationResultTempList = $RotationResultTemp->getRotationResultTemp($rotationId,$rotationDate);
            $group = array();
            foreach ($RotationResultTempList as $key=>$value)
            {
                //循环原始的满足条件的全部人串
                $usersRecommend = json_decode($value['usersRecommend'],true);
                foreach ($usersRecommend as $k=>$v)
                {
                    if($firstemp == $v['userId']){
                        $group['groupId'] = $value['groupId'];
                        $group['userName'] = $v['userName'];
                    }
                }
            }

            //获取到属于原来的组,查询 ,恢复到剩余人串 并且拟调出字段减掉此人员
            $RotationResultTempBack = $RotationResultTemp->getRotationResultTempOb($rotationId,$rotationDate,$group['groupId']);
            //调出
            $usersPrepareOutBack = json_decode($RotationResultTempBack->usersPrepareOut,true);
            //全部满足
            $usersRecommendBack = json_decode($RotationResultTempBack->usersRecommend,true);
            //剩余满足
            $usersRecommendUnselectedBack = json_decode($RotationResultTempBack->usersRecommendUnselected,true);
            //把原来组的调出人串删除
            if(!empty($usersPrepareOutBack)){
                foreach ($usersPrepareOutBack as $key=>$emp)
                {
                    if($firstemp == $emp['userId']){
                        if(count($usersPrepareOutBack) == 1){
                            unset($usersPrepareOutBack);
                        }else{
                            unset($usersPrepareOutBack[$key]);
                        }
                    }
                }
            }
            //判断调出字段删除一个人员后 是否为空
            if(empty($usersPrepareOutBack)){
                $usersPrepareOutBack = null;
            }else{
                $usersPrepareOutBack = json_encode(array_values($usersPrepareOutBack),JSON_UNESCAPED_UNICODE);
            }
            $usersRecommendUnselectedBack[] = ['userId'=>$firstemp,'userName'=>$group['userName']];

            $Back1 = array_column($usersRecommendBack,'userId');
            $Back2 = array_column($usersRecommendUnselectedBack,'userId');
            //获取两者的差集,然后从逐个从全部满足条件中剔除
            $diff = array_values(array_diff($Back1,$Back2));

            //计算出全部满足条件和剩余满足条件的差集,然后再从全部满足条件剔除差集结果,
            foreach ($diff as $k=>$v)
            {
                foreach ($usersRecommendBack as $key=>$emp)
                {
                    if($v == $emp['userId']){
                        if(count($usersRecommendBack) == 1){
                            unset($usersRecommendBack);
                        }else{
                            unset($usersRecommendBack[$key]);
                        }
                    }

                }
            }
            $RotationResultTempBack->usersPrepareOut = $usersPrepareOutBack;
            $RotationResultTempBack->usersRecommendUnselected = json_encode(array_values($usersRecommendBack),JSON_UNESCAPED_UNICODE);
            $RotationResultTempBack->save();

            $transaction->commit();
            $this->serializer['errno']   = 0;
            $this->serializer['status']   = true;
            $this->serializer['message'] = "删除成功";
            return;
        }catch (\Exception $e){

            $transaction->rollBack();
            $this->serializer['errno'] = 0;
            $this->serializer['status'] = false;
            $this->serializer['message'] = "删除失败";
            return;
        }
        /**
         *
         * 如果从A组叉掉一个人,此人员应返回到原来的组满足条件位置传入的时候带着原来的组id和人员id
         * update rotationResultTemp set usersPrepareIn where rotationId=2 and rotationDate=4 and groupid=2
         *
         */
    }
    /**
     * @SWG\Post(path="/rotation-result-temp/recount",
     *     tags={"云平台-rotationResultTemp-轮转中间表"},
     *     summary="重新计算",
     *     description="重新计算",
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
     *        name = "rotationId",
     *        description = "轮转表id",
     *        required = true,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "rotationDate",
     *        description = "时间月份",
     *        required = true,
     *        type = "integer",
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
    public function actionRecount()
    {
        $rotationId = Yii::$app->request->post('rotationId');
        $rotationDate = Yii::$app->request->post('rotationDate');
        RotationResultTemp::deleteAll(['and',['rotationId'=>$rotationId],['>','rotationDate',$rotationDate]]);
        $this->serializer['errno']   = 0;
        $this->serializer['status']   = true;
        $this->serializer['message'] = "重新计算成功";
        return;
        /**
         * 判断当前月份必须小于未来的轮转计划,清楚未来的轮转计划,重新设置计划
         * delete from rotationResultTemp where rotationDate<time() and rotationId=1;
         */
    }
    /**
     * @SWG\Post(path="/rotation-result-temp/down",
     *     tags={"云平台-rotationResultTemp-轮转中间表"},
     *     summary="下载",
     *     description="下载",
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
     *        name = "rotationId",
     *        description = "轮转表id",
     *        required = true,
     *        type = "integer",
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
    public function actionDown()
    {

        $rotationId = Yii::$app->request->post('rotationId');
        $RotationResultTemp = new RotationResultTemp();
        $RotationList = new RotationList();
        $RotationDown = new RotationDown();
        $user = new User();
        //获取轮转人员
        $useAll = $RotationResultTemp->getRotationResultTempPublish($rotationId);
        $result = $RotationResultTemp->getResult($useAll);
        $userGroupName = $user->getSmallSubunit('xajdyfyyxb');
        //创建一个数组，用一个数组的值作为其键名，($userAll里的id作为键名)另一个数组的值作为其值
        $userGroupName = array_combine(array_column($userGroupName,'id'),$userGroupName);
        $data = array();
        RotationDown::deleteAll(['rotationId'=>$rotationId]);
        foreach ($result as $k=>$v)
        {
            $down = new RotationDown();
            $empOne = RotationEmployee::findOne(['emp_number'=>$v['emp_number']]);
            $ingroupName = $userGroupName[$v['ingroupId']]['name'];
            $outgroupName = $userGroupName[$v['outgroupId']]['name'];
            $down->rotationId = $rotationId;
            $down->inuserId = $v['emp_number'];
            $down->inuserName = $v['userName'];
            $down->ingroupId = $v['ingroupId'];
            $down->ingroupName = $ingroupName;
            $down->work_time = $empOne->work_time;
            $down->outuserId = $v['emp_number'];
            $down->outuserName = $v['userName'];
            $down->outgroupId = $v['outgroupId'];
            $down->outgroupName = $outgroupName;
            $down->intime = $v['time_in'];
            $down->save();
        }

        //获取日期
        $date = $RotationList->getRotationOne($rotationId);
        $DateBegin = $date['rotationDateBegin'];
        $DateEnd = $date['rotationDateEnd'];
        $months = getMonths($DateBegin,$DateEnd);
        $downUser = $RotationDown->getDownUser($rotationId);

        include_once '../../common/phpexcel/PHPExcel.php';
        $phpexcel = new \PHPExcel();
        $da = $date['rotationName'].date("Y-m-d").time();
        $path = "/data/wwwroot/uploadfile/";
        $path1 = "public/perexcel/";
        $filename = $path.$path1.$da.'.xlsx';
        if(file_exists($filename)){
            @unlink($filename);
        }
        $phpexcel->getActiveSheet()->setTitle('轮转结果');
        //设置表头
        $key = ord("D1");
        $span = chr(68);//D
        foreach ($months as $k=>$v)
        {
            $phpexcel->setActiveSheetIndex(0)
                ->setCellValue('C1', '在组时间')
                ->setCellValue($span.'1', $v);
            $span++;
        }
        $phpexcel->getDefaultStyle()->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $phpexcel->getDefaultStyle()->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);

        $i=2;
        foreach($downUser as $key=>$value){
            $months_key = array_search($value['intime'],$months);
            $phpexcel->setActiveSheetIndex(0)
                ->setCellValue('A'.$i,$value['outuserName'])
                ->setCellValue('B'.$i,$value['outgroupName'])
                ->setCellValue('C'.$i,$value['work_time'])
                ->setCellValue(chr($months_key + 68).$i,$value['ingroupName']);
            $i++;
        }
        $obj_Writer = \PHPExcel_IOFactory::createWriter($phpexcel,'Excel5');
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
        $obj_Writer->save($filename);//输出
        $url = env('STORAGE_HOST_INFO');
        $fileurl = $url.$path1.$da.'.xlsx';
        $url = ['url'=>$fileurl];
        $this->serializer['errno']   = 0;
        $this->serializer['status']   = true;
        $this->serializer['message'] = "获取成功";
        return $url;

    }
}