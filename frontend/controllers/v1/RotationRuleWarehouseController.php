<?php
namespace frontend\controllers\v1;
use common\models\shift\RotationList;
use common\models\shift\RotationRule;
use common\models\shift\RotationRuleWarehouse;
use common\models\user\User;
use Yii;
use yii\web\Response;
use yii\web\Controller;
//use yii\helpers\ArrayHelper;
class RotationRuleWarehouseController extends \common\rest\Controller
{
    /**
     * @var string
     */
    public $modelClass = 'common\models\shift\RotationRuleWarehouse';

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
     * @SWG\Post(path="/rotation-rule-warehouse/list",
     *     tags={"云平台-rotationRuleWarehouse-轮转规则库"},
     *     summary="获取规则列表",
     *     description="获取规则列表",
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
     *        name = "groupId",
     *        description = "组id",
     *        required = false,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "ruleType",
     *        description = "规则类型：空默认out调出规则in调入规则",
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
    // 轮转规则库（rotationRuleWarehouse）
    public function actionList()
    {
        $ruleType = Yii::$app->request->post('ruleType');
        $rotationRuleWarehouse = new RotationRuleWarehouse();
        $list = $rotationRuleWarehouse->getRuleAll($ruleType);
        $data = [];
        $json = [];

        foreach ($list as $key=>$value){
            $data['ruleWarehouseId'] = $value['id'];
            $data['ruleTitle'] = $value['ruleTitle'];
            $select1 = json_decode($value['select1'],true);
            $select2 = json_decode($value['select2'],true);
            $input1 = json_decode($value['input1'],true);
            $input2 = json_decode($value['input2'],true);
            //是否开启
            if($value['status'] == 2){
                $data['status'] = false;
            }elseif ($value['status'] == 1){
                $data['status'] = true;
            }
            //是否多条
            if($value['isMultiple'] == 1){
                $data['isMultiple'] = 'yes';
                if($input1){
                    $obj = [];
                    $obj[] = [$select1,$input1];
                }else{
                    $obj = [];
                    $obj[] = [$select1];
                }
                $data['formData'] = $obj;
            }else{
                $data['isMultiple'] = 'no';
                $allJson = [$select1,$select2,$input1,$input2];
                $newArray = array_filter($allJson);
                $data['formData'] = array_values($newArray);
            }

            $json[$key] = $data;
        }
        $this->serializer['errno'] = 0;
        $this->serializer['status'] = true;
        $this->serializer['message'] = "获取成功";
        return $json;


         /**
         *
         *
         * ,取出调出 调入 规则行从rotationRuleWarehouse表
         * select id ruleTypeName,ruleTitle,select1,select2,input1,input2 from rotationRuleWarehouse ;
         *
         */
    }
    /**
     * @SWG\Post(path="/rotation-rule-warehouse/insert-rule",
     *     tags={"云平台-rotationRuleWarehouse-轮转规则库"},
     *     summary="获取规则列表数据",
     *     description="获取规则列表数据",
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
     *        name = "info",
     *        description = "返回信息",
     *        required = true,
     *        type = "string",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "rotationId",
     *        description = "轮转表id",
     *        required = true,
     *        type = "string",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "groupId",
     *        description = "组id",
     *        required = true,
     *        type = "string",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "ruleType",
     *        description = "规则类型：空默认out调出规则in调入规则",
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
    //从规则库页面获取数据 insert 轮转规则表（rotationRule）
    public function actionInsertRule()
    {


        $post = Yii::$app->request->post();
        $list = json_decode($post['info'],true);
        $rotationId = $post['rotationId'];
        $groupId = $post['groupId'];
        //获取组信息和轮转表id,维护到轮转表的groupInfo
        $user = new User();
        $userAll = $user->getSmallSubunit('xajdyfyyxb');
        //创建一个数组，用一个数组的值作为其键名，($userAll里的id作为键名)另一个数组的值作为其值
        $userAll = array_combine(array_column($userAll,'id'),$userAll);
        $rotationList = new RotationList();
        $rotationListOne = $rotationList->findOne($rotationId);
        $groupInfo = json_decode($rotationListOne->groupInfo,true);
        if(!empty($groupInfo)){
            $Info = array_column($groupInfo,'groupId');
            if(!in_array($groupId,$Info)){
                $groupInfo[] =['groupId'=>$groupId,'groupName'=>$userAll[$groupId]['name']];
            }
        }else{
            $groupInfo = [['groupId'=>$groupId,'groupName'=>$userAll[$groupId]['name']]];
        }

        $rotationListOne->groupInfo = json_encode($groupInfo,JSON_UNESCAPED_UNICODE);
        $rotationListOne->save();
        //存储规则
        $array = array();
        foreach($list as $key=>$value){
            //判断状态是否为 true
            if($value['status'] == 'true'){
                $array[$key]['rotationId'] = $post['rotationId'];
                $array[$key]['groupId'] = $post['groupId'];
                $array[$key]['ruleType'] = $post['ruleType'];
                $array[$key]['ruleWarehouseId'] = $value['ruleWarehouseId'];
                $array[$key]['status'] = $value['status'];
                //判断是否为空 为空跳出本次循环
                if(empty($value['formData'])){
                    continue;
                }
                foreach($value['formData'] as $k=>$v){
                    //因为数组 维数不同 判断
                    if(!empty($v[0])){
                        $select1 = [];
                        $input1 = [];
                        foreach($v as $k2=>$v2){
                            if($v2['id'] == 'select1'){
                                $select1[] = $v2['selected'];
                            }

                            if($v2['id'] == 'input1'){

                                $input1 = $v2['value'];
                            }
                        }
                        //判断如果有值就存 如果为空 就不存这个字段
                        if(!empty($select1)){
                            $array[$key]['select1'][] = $select1;
                        }
                        if(!empty($input1)){
                            $array[$key]['input1'][] = $input1;
                        }

                    }else{
                        //判断是否有值 如果没有值就不存这个字段
                        if(!empty($v['selected'])){
                            //因为$v['selected']这个值有数组和 字符串两种类型  判断
                            if($v['id'] == 'select1'){
                                if(is_array($v['selected'])){
                                    $array[$key]['select1'][] = $v['selected'];
                                }else{
                                    $array[$key]['select1'][] = [$v['selected']];
                                }
                            }
                            if($v['id'] == 'select2'){
                                if(is_array($v['selected'])){
                                    $array[$key]['select2'][] = $v['selected'];
                                }else{
                                    $array[$key]['select2'][] = [$v['selected']];
                                }
                            }
                        }
                        //判断是否有值 如果没有值就不存这个字段
                        if(!empty($v['value'])){
                            if($v['id'] == 'input1'){
                                $array[$key]['input1'][] = $v['value'];
                            }
                            if($v['id'] == 'input2'){
                                $array[$key]['input2'][] = $v['value'];
                            }
                        }
                    }
                }
            }else{
                $array[$key]['rotationId'] = $post['rotationId'];
                $array[$key]['groupId'] = $post['groupId'];
                $array[$key]['ruleType'] = $post['ruleType'];
                $array[$key]['ruleWarehouseId'] = $value['ruleWarehouseId'];
                $array[$key]['status'] = $value['status'];
            }
        }

        $array = array_merge($array); //重新生成数组索引  0,1,2
        foreach ($array as $key1=>$value1){
            $RotationRule = new RotationRule;
            $rotationId = $value1['rotationId'];
            $groupId = $value1['groupId'];
            $rotationRuleWarehouseId = $value1['ruleWarehouseId'];
            if($value1['status'] == true){

                $RotationRule->rotationId = $rotationId;
                $RotationRule->groupId = $groupId;
                $RotationRule->ruleType = $value1['ruleType'];
                $RotationRule->rotationRuleWarehouseId = $rotationRuleWarehouseId;

                if(array_key_exists('select1',$value1)){
                    $select1 = json_encode($value1['select1']);
                    $RotationRule->select1 = $select1;
                }
                if(array_key_exists('select2',$value1)){
                    $select2 = json_encode($value1['select2']);
                    $RotationRule->select2 = $select2;
                }
                if(array_key_exists('input1',$value1)){
                    $input1 = json_encode($value1['input1']);
                    $RotationRule->input1 = $input1;
                }
                if(array_key_exists('input2',$value1)){
                    $input2 = json_encode($value1['input2']);
                    $RotationRule->input2 = $input2;
                }

                $RuleOne = $RotationRule->find()->where([
                    'rotationId'=>$rotationId,
                    'groupId'=>$groupId,
                    'rotationRuleWarehouseId'=>$rotationRuleWarehouseId
                ])->one();

                if(!empty($RuleOne)){//如果不为空就修改
                    if(array_key_exists('select1',$value1)){
                        $select1 = json_encode($value1['select1']);
                        $RuleOne->select1 = $select1;
                    }
                    if(array_key_exists('select2',$value1)){
                        $select2 = json_encode($value1['select2']);
                        $RuleOne->select2 = $select2;
                    }
                    if(array_key_exists('input1',$value1)){
                        $input1 = json_encode($value1['input1']);
                        $RuleOne->input1 = $input1;
                    }
                    if(array_key_exists('input2',$value1)){
                        $input2 = json_encode($value1['input2']);
                        $RuleOne->input2 = $input2;
                    }
                    $RuleOne->save();

                }else{//如果为空就新增一条
                    $RotationRule->save();

                }
            }else{//如果选择关闭,就删除关闭的那一条
                RotationRule::deleteAll(['rotationId'=>$rotationId,'groupId'=>$groupId,'rotationRuleWarehouseId'=>$rotationRuleWarehouseId]);

            }

        }
        //处理轮转表里的groupInfo,
        $rotationList->updateGroupInfo();
        $this->serializer['errno'] = 0;
        $this->serializer['status'] = true;
        $this->serializer['message'] = "添加成功";
        return ;
        /**
         * 获取用户数据后存入规则库
         * insert into rotationRule (rotationId,groupId,ruleType,select1,select2,input1,input2) value(2,2,'on',1,'[["101", "102"], ["201", "202"]]');
         *添加规则数据时计算符合轮转条件的人员存入rotationResultTemp表格,直接用post传的值也行
         * select rotationId,groupId,ruleType,rotationRuleWarehouseId,select1,input1 from rotationRule;
         * 取出用户接口数据, 使用交集array_intersect查询符合条件人员, 存入rotationResultTemp表
         *
         *
         * 初始值只是显示满足条件的全部的人串(A组1月份一条,A组2月份一条,B组1月一条,B组2月一条,)
         * insert into rotationResultTemp (rotationId,rotationDate,groupId,usersRecommend) value(1,2018-02,3,'[{"userId": "1","userName": "A组满足人1"},{"userId": "2","userName": "A组满足人2"}]')
         *
         */

    }
}