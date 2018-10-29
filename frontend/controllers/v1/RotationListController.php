<?php
namespace frontend\controllers\v1;
use common\models\shift\RotationList;
use common\models\shift\RotationRule;
use common\models\user\User;
use Yii;
use yii\web\Response;
use yii\web\Controller;
//use yii\helpers\ArrayHelper;
class RotationListController extends \common\rest\Controller
{
    /**
     * @var string
     */
    public $modelClass = 'common\models\shift\RotationList';

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
     * @SWG\Post(path="/rotation-list/create",
     *     tags={"云平台-rotationList-轮转接口"},
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
     *        name = "rotationName",
     *        description = "轮转名称",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "rotationDateBegin",
     *        description = "起始日期",
     *        required = true,
     *        type = "string",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "rotationDateEnd",
     *        description = "截止日期",
     *        required = true,
     *        type = "string",
     *        default = 0,
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "status",
     *        description = "状态：0默认1已发布2已删除",
     *        required = false,
     *        type = "integer",
     *        default = 0,
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "groupInfo",
     *        description = "相关所有组信息，格式json",
     *        required = false,
     *        type = "string",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "createTime",
     *        description = "创建时间",
     *        required = false,
     *        type = "string",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "editTime",
     *        description = "最后修改时间",
     *        required = false,
     *        type = "string",
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
        $rotationDateBegin = $post['rotationDateBegin'];
        $rotationDateEnd = $post['rotationDateEnd'];
        $rotationList = new RotationList;
        $list = $rotationList->getRotationAll();
        //$date = RotationList::find()->where('rotationName = :rotationName', [':rotationName' => $post['rotationName']])->one();

        foreach ($list as $key=>$value){
            $array = $value;
            $array['date'] = $value['rotationDateBegin'] .','.$value['rotationDateEnd'];
            $array['str'] = explode(',',$array['date']);
            //判断填写的开始时间是否在轮转计划的开始时间和结束时间之间
            if(strtotime($rotationDateBegin) > strtotime($array['str'][0]) && strtotime($rotationDateBegin) < strtotime($array['str'][1])){
                $this->serializer['errno'] = 11;
                $this->serializer['status'] = false;
                $this->serializer['message'] = "您添加的开始时间已经在计划之内了";
                return [];
            }
            //判断填写的结束时间是否在轮转计划的开始时间和结束时间之间
            if(strtotime($rotationDateEnd)>strtotime($array['str'][0]) && strtotime($rotationDateEnd)<strtotime($array['str'][1])){
                $this->serializer['errno'] = 11;
                $this->serializer['status'] = false;
                $this->serializer['message'] = "您添加的结束时间已经在计划之内了";
                return [];
                }
        }

        //判断填写的开始时间是否为星期一,如果不是的话自动转成星期一
        $week = get_week($rotationDateBegin);
        if($week>1){
            $day = $week-1;
            $date = date('Y-m-d',strtotime("-$day day",strtotime($rotationDateBegin)));
        }else{
            $date = date('Y-m-d',strtotime("+1 day",strtotime($rotationDateBegin)));
        }
//        var_dump(date('Y-m-d',strtotime($post['rotationDateEnd'])));exit;

        $rotationList->rotationName = $post['rotationName'];
        $rotationList->rotationDateBegin = $date;
        $rotationList->rotationDateEnd = date('Y-m-d',strtotime($rotationDateEnd));
        $rotationList->status = 0;
        $rotationList->createTime = date('Y-m-d',time());

        if($rotationList->save()){
            //得到上次插入的Insert id
            $id = $rotationList->attributes['id'];
            $this->serializer['errno'] = 0;
            $this->serializer['status'] = true;
            $this->serializer['message'] = "添加成功";
            return $id;
        }else{
            $this->serializer['errno'] = 233;
            $this->serializer['status'] = true;
            $this->serializer['message'] = "添加失败";
        }

        /**
         * 从post或者数据,判断开始时间是否为周一;结束时间是否小于开始时间,如果是提醒
         * insert into rotationList (rotationName,rotationDateBegin,rotationDateEnd,createTime) value("2018年计划","2018-9-17","2020-9-17","2019-9-19");
         * 数据添加成功后,返回刚添加的一条数据id
         * select rotationName,rotationDateBegin,rotationDateEnd where id=2;
         *
         *  在轮转结果页确认发布后status状态修改为已发布
         * 在RotationResultTempController控制器的actionPublish()方法修改发布状态
         */


    }
    /**
     * @SWG\Post(path="/rotation-list/list",
     *     tags={"云平台-rotationList-轮转接口"},
     *     summary="获取轮转计划列表",
     *     description="获取轮转计划列表",
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
    public function actionList()
    {
        /*$a = [
            'errno'=>0,
            'message'=>'获取成功',
            'status'=>true,
            "result"=>[
                ["id"=>"1",
                "rotationName"=>"2018年计划",
                "rotationDateBegin"=>"2018-09-11 至 2019-9-11",
                "status"=>"已发布",
                "groupInfo"=>"商务组 拿药组 扫地组"],
                ["id"=>"2",
                    "rotationName"=>"2000年计划",
                    "rotationDateBegin"=>"2018-09-11 至 2019-9-11",
                    "status"=>"-",
                    "groupInfo"=>""],
            ],
        ];

        echo json_encode($a,JSON_UNESCAPED_UNICODE);exit;*/
        $rotationList = new RotationList;
        $rotationRule = new RotationRule();
        $list = $rotationList->getRotationAll();
        /*foreach ($list as $k=>$v)
        {
            $rotationId = $v['id'];
            $groupinfo = json_decode($v['groupInfo'],true);
            if(!empty($groupinfo))
            {
                $info = array();
                foreach ($groupinfo as $key=>$val){
                    $groupid = $val['groupId'];
                    $rullone = RotationRule::find()->where(['rotationId'=>$rotationId])->andWhere(['groupId'=>$groupid])->asArray()->one();

                    if(!empty($rullone)){
                        $info[$key]['groupId'] = $val['groupId'];
                        $info[$key]['groupName'] = $val['groupName'];
                    }

                }
                $rotationone = RotationList::findOne($rotationId);
//                var_dump($info);exit;
                if(empty($info)){
                    $info = null;
                }else{
                    $info = json_encode(array_values($info),JSON_UNESCAPED_UNICODE);
                }
                $rotationone->groupInfo = $info;
                $rotationone->save();
            }
        }*/
        $array = [];
        $data = [];

        foreach ($list as $key=>$value){
            $array['id'] = $value['id'];
            $array['rotationName'] = $value['rotationName'];
            $array['rotationDateBegin'] = $value['rotationDateBegin'].'至'.$value['rotationDateEnd'];

            if($value['groupInfo'] == ''){
                $array['groupInfo'] = '';
            }else{
                $info = array_column(json_decode($value['groupInfo'],true),'groupName');
                $array['groupInfo'] = implode(' ',$info);
            }

            if($value['status'] == 1){
                $array['status'] = '已发布';
            }else{
                $array['status'] = '-';
            }
            $data[$key] = $array;
        }

        $this->serializer['errno']   = 0;
        $this->serializer['status']   = true;
        $this->serializer['message'] = "获取成功";
        return $data;
        /**
         * select id,rotationName,rotationDateBegin,groupInfo from rotationList;
         */

    }
    /**
     * @SWG\Post(path="/rotation-list/list-one",
     *     tags={"云平台-rotationList-轮转接口"},
     *     summary="获取单个独立计划",
     *     description="获取单个独立计划",
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
     *        name = "id",
     *        description = "轮转表id",
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
    public function actionListOne()
    {
        /*$a = [
                    "rotationId"=>"1",//轮转表id
                    "rotationName"=>"2018年计划",
                    "rotationDateBegin"=>"2018-09-11 至 2019-9-11",
                    "groupInfo"=>[
                        ["groupId"=>"1","groupName"=>"商务组"],
                        ["groupId"=>"2","groupName"=>"拿药组"],
                        ["groupId"=>"3","groupName"=>"扫地组"],
                    ],
                    "ruleInfo"=>[
                        ["ruleId"=>"out","ruleName"=>"调出规则"],
                        ["ruleId"=>"in","ruleName"=>"调入规则 "],
                        ],

        ];
        $this->serializer['errno']   = 0;
        $this->serializer['status']   = true;
        $this->serializer['message'] = "获取成功";
        return $a;*/

        $post = Yii::$app->request->post();
        //$scheduleList = self::find()->select('id,name')->where(' is_confirm > 0 and is_show >0 and location_id = :work_station', [':work_station' => $work_station])->orderBy('id desc')->all();
        $rotationId = $post['id'];
//        $where = " id != 1 and customer_id = '$customer_id'";
        $RotationList = new RotationList();
        $list = $RotationList->getRotationOne($rotationId);

        if(count($list)>0){
            //查询小组信息
            $user = new User();
            $userAll = $user->getSmallSubunit('xajdyfyyxb');
            $group = [];
            foreach ($userAll as $key=>$value){
                $info['groupId'] = $value['id'];
                $info['groupName'] = $value['name'];
                $group[$key] = $info;
            }
            //如果列表没有小组信息,则查询原始小组,代表没有配置规则
            if($list['groupInfo']== null){
                $group = array_values($group);
            }else{
                $group = array_values($group);
            }

            $data['rotationId'] = $list['id'];
            $data['rotationName'] = $list['rotationName'];
            $data['rotationDateBegin'] = $list['rotationDateBegin'].'至'.$list['rotationDateEnd'];
            $data['groupInfo'] = $group;
            $data['ruleInfo'] = [
                ["ruleId"=>"out","ruleName"=>"调出规则"],
                ["ruleId"=>"in","ruleName"=>"调入规则 "],
            ];
            $this->serializer['errno']   = 0;
            $this->serializer['status']   = true;
            $this->serializer['message'] = "获取成功";
            return $data;
        }
        /**
         *接收前端返回的轮转表id
         * select rotationName,rotationDateBegin,rotationDateEnd,groupInfo from rotationList where id=2 ;
         * 判断groupInfo是有信息,有的话直接返回groupInfo信息;  如果没有从小组的接口里取出
         * select groupid,groupName from group;
         * 把组信息和调出调入规则  拼装返回json数据
         */
    }
    /**
     * @SWG\Post(path="/rotation-list/del",
     *     tags={"云平台-rotationList-轮转接口"},
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
     *        name = "id",
     *        description = "id种子格式[1,2,3]",
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
    public function actionDel()
    {
        $post = Yii::$app->request->post();
        $Rotation = new RotationList();
        $id_list = $post['id'];
        $i = 0;

        //循环post接收的id ["101", "102"] ,["101"]
        foreach ($id_list as $key=>$value){
            $RotationeOne = $Rotation->getRotationOne($value);
            //状态为0(默认)的能修改状态,为1的不能修改状态
            if($RotationeOne['status']==0){
                RotationList::updateAll(['status'=>2],'id=:rotationId',[':rotationId'=>$value]);
            }else{
                $i++;
            }
        }
        if($i<count($id_list)){
            $this->serializer['errno']   = 0;
            $this->serializer['status']   = true;
            $this->serializer['message'] = "删除".(count($id_list)-$i)."个,失败".$i.'个,原因:已发布的不能删除';
        }else{
            $this->serializer['errno']   = 11;
            $this->serializer['status']   = false;
            $this->serializer['message'] = "删除失败,原因:已发布的不能删除";
        }

        /**
         *根据接收的id查询
         * select status from rotationList where id in(1,2);
         * 循环判断状态是否为0,如果为1是已发布状态,不能删除
         * update rotationList set status=2 where id in(1,2);
         */

    }
}