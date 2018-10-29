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
use common\models\group\GroupMenus;
use common\models\group\GroupAcl;
use common\base\Tree;
use cheatsheet\Time;

class GroupController extends \common\rest\Controller
{
    //public $modelClass = 'frontend\models\gedu\resources\User';
    private $_tree;

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
        
        if(!$this->_tree){
            $this->_tree = new Tree();
        }
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
     * @SWG\Post(path="/group/get-group-menus",
     *     tags={"云平台-GROUP-权限"},
     *     summary="获取权限列表",
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
     *        description = "权限id",
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
     *         description = "Data Validation Failed 账号或密码错误",
     *     )
     * )
     *
     */
    public function actionGetGroupMenus()
    {

        $GroupMenus = new GroupMenus();

        $post=Yii::$app->request->post();

        $id = !empty($post['id'])?trim($post['id']):'0'; 

        if($id){
            $info = $GroupMenus->getGroupMenusById($id);
            $parentid = $info['parentid'];
        }else{
            $parentid = -1;
        }
        $allmenus = $GroupMenus->getGroupMenusList();
        $array = array();
        foreach($allmenus as $r) {
            $r['cname'] = trim($r['name']);
            $r['selected'] = $r['id'] == $parentid ? 'selected' : '';
            $array[] = $r;
        }
        $str  = "<option value='\$id' \$selected>\$spacer \$cname</option>";
        $this->_tree->init($array);
        $tree_options = $this->_tree->get_tree(0, $str);

        $str_head = '<option value="0"> 顶级菜单</option>';
         //$result['status'] = true;
         //$result['message'] = '成功';
        $this->serializer['status'] = true;
        $this->serializer['errno'] = 0;
        $this->serializer['message'] = '查询成功';
        $result['data'] = $str_head.$tree_options;
        return $result;


    }


    /**
     * @SWG\Post(path="/group/add",
     *     tags={"云平台-GROUP-权限"},
     *     summary="添加/修改权限",
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
     *        name = "parentid",
     *        description = "父级",
     *        required = true,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "name",
     *        description = "名称",
     *        required = true,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "model",
     *        description = "模块",
     *        required = true,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "controller",
     *        description = "控制器",
     *        required = true,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "action",
     *        description = "方法",
     *        required = true,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "display",
     *        description = "是否显示 1显示  2不显示",
     *        required = true,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "sort",
     *        description = "排序 1",
     *        required = true,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "id",
     *        description = "权限ID",
     *        required = false,
     *        type = "string"
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = ""
     *     ),
     *     @SWG\Response(
     *         response = 422,
     *         description = "Data Validation Failed 账号或密码错误",
     *     )
     * )
     *
     */
    public function actionAdd()
    {

        $post=Yii::$app->request->post();
        if(empty($post)){
            // $result['status'] = false;
            // $result['message'] = '请输入信息';
            // return $result;die;
            $this->serializer['status'] = false;
            $this->serializer['errno'] = 0;
            $this->serializer['message'] = '请输入信息';
            return $result;
        }
        $id = !empty($post['id'])?trim($post['id']):'0'; 
        $parentid = !empty($post['parentid'])?trim($post['parentid']):'0'; 
        $name = !empty($post['name'])?trim($post['name']):''; 


        $model = !empty($post['model'])?trim($post['model']):'';

        $controller = !empty($post['controller'])?trim($post['controller']):''; 
        $action = !empty($post['action'])?trim($post['action']):'';
        $display = !empty($post['display'])?trim($post['display']):'2'; 
        $sort = !empty($post['sort'])?trim($post['sort']):'1';

        if(empty($name)){
            // $result['status'] = false;
            // $result['message'] = '名称不能为空';
            // return $result;die;
            $this->serializer['status'] = false;
            $this->serializer['errno'] = 0;
            $this->serializer['message'] = '名称不能为空';
            return $result;
        }

        if($id){
            $server = new GroupMenus();

            $GroupMenus = $server->getGroupMenusById($id);
        }else{
            $GroupMenus = new GroupMenus();
        }

        

        $GroupMenus->name = $name;
        $GroupMenus->m = $model;
        $GroupMenus->c = $controller;
        $GroupMenus->act = $action;
        $GroupMenus->parentid = $parentid;
        $GroupMenus->sort = $sort;
        $GroupMenus->display = $display;
        $GroupMenus->save();

        if($GroupMenus->id){
            // $result['status'] = true;
            // $result['message'] = '成功';
            // return $result;die;
            $this->serializer['status'] = true;
            $this->serializer['errno'] = 0;
            $this->serializer['message'] = '成功';
            return ;

        }else{
            // $result['status'] = false;
            // $result['message'] = '保存失败';
            // return $result;die;
            $this->serializer['status'] = false;
            $this->serializer['errno'] = 0;
            $this->serializer['message'] = '保存失败';
            return ;
        }

    }

    /**
     * @SWG\Post(path="/group/del",
     *     tags={"云平台-GROUP-权限"},
     *     summary="删除权限",
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
     *        description = "权限ID",
     *        required = false,
     *        type = "string"
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = ""
     *     ),
     *     @SWG\Response(
     *         response = 422,
     *         description = "Data Validation Failed 账号或密码错误",
     *     )
     * )
     *
     */
    public function actionDel()
    {

        $post=Yii::$app->request->post();
        if(empty($post)){
            // $result['status'] = false;
            // $result['message'] = '请输入信息';
            // return $result;die;
            $this->serializer['status'] = false;
            $this->serializer['errno'] = 0;
            $this->serializer['message'] = '请输入信息';
            return ;
        }
        $id = !empty($post['id'])?trim($post['id']):'0'; 
        

        if(empty($id)){
            // $result['status'] = false;
            // $result['message'] = 'id不能为空';
            // return $result;die;

            $this->serializer['status'] = false;
            $this->serializer['errno'] = 0;
            $this->serializer['message'] = 'id不能为空';
            return ;
        }


        $GroupMenus = new GroupMenus();

        $isDel = $GroupMenus->deleteGroupMenusById($id);

        if($isDel){
            // $result['status'] = true;
            // $result['message'] = '删除成功';
            // return $result;die;
            $this->serializer['status'] = true;
            $this->serializer['errno'] = 0;
            $this->serializer['message'] = '删除成功';
            return ;
        }else{
            // $result['status'] = false;
            // $result['message'] = '删除失败';
            // return $result;die;
            $this->serializer['status'] = false;
            $this->serializer['errno'] = 0;
            $this->serializer['message'] = '删除失败';
            return ;
        }

    }

    /**
     * @SWG\Post(path="/group/menus-index",
     *     tags={"云平台-GROUP-权限"},
     *     summary="权限列表首页",
     *     description="用户登录：成功返回用户信息；失败返回具体原因",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "Token",
     *        description = "60f5d74b625b79aafe22808f8bbddec4907f8204",
     *        required = true,
     *        type = "string"
     *     ),
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
    public function actionMenusIndex()
    {
        //Yii::$app->request->hostInfo;   //获取当前域名
        $GroupMenus = new GroupMenus();

        $allmenus = $GroupMenus->getGroupMenusList();
        $array = array();
        foreach($allmenus as $r) {
            $r['cname'] = trim($r['name']);
            $r['edithref'] ='/'.$this->_version.'/group/edit';
            $r['delhref'] = '/'.$this->_version.'/group/del';
            $r['addchildhref'] ='/'.$this->_version.'/group/addchild';
            $r['selected'] = $r['id'] == !empty($_GET['parentid']) ? 'selected' : '';
            $array[] = $r;
        }

        $str  = "<tr><td style='width:80px;padding-left:5px;'>\$id</td><td>\$spacer \$cname</td><td><a href='\$addchildhref/parentid/\$id'>添加子菜单</a>&nbsp;|&nbsp;<a  href='\$edithref/id/\$id'>编辑</a>&nbsp;|&nbsp;<a  href='\$delhref/id/\$id'>删除</a></td></tr>";
        $this->_tree->init($array);
        $tree_span = $this->_tree->get_tree(0, $str);
        // $result['status'] = true;
        // $result['message'] = '成功';
        // $result['data'] =$tree_span;
        // return $result;die;

        $this->serializer['status'] = true;
        $this->serializer['errno'] = 0;
        $this->serializer['message'] = '成功';
        $result['data'] =$tree_span;
        return $result;


    }

    /**
     * @SWG\Post(path="/group/get-menus-detail",
     *     tags={"云平台-GROUP-权限"},
     *     summary="根据ID获取权限信息",
     *     description="用户登录：成功返回用户信息；失败返回具体原因",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "Token",
     *        description = "60f5d74b625b79aafe22808f8bbddec4907f8204",
     *        required = true,
     *        type = "string"
     *     ),
     *     
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "id",
     *        description = "权限ID",
     *        required = false,
     *        type = "string"
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = ""
     *     ),
     *     @SWG\Response(
     *         response = 422,
     *         description = "Data Validation Failed 账号或密码错误",
     *     )
     * )
     *
     */
    public function actionGetMenusDetail()
    {

        $post=Yii::$app->request->post();
        if(empty($post)){
            // $result['status'] = false;
            // $result['message'] = '请输入信息';
            // return $result;die;

            $this->serializer['status'] = false;
            $this->serializer['errno'] = 0;
            $this->serializer['message'] = '请输入信息';
            return ;

        }
        $id = !empty($post['id'])?trim($post['id']):'0'; 

        if(empty($id)){
            // $result['status'] = false;
            // $result['message'] = 'id不能为空';
            // return $result;die;
            $this->serializer['status'] = false;
            $this->serializer['errno'] = 0;
            $this->serializer['message'] = 'id不能为空';
            return ;
        }

        $server = new GroupMenus();
        $GroupMenus = $server->getGroupMenusById($id);
        
        if($GroupMenus){
            $data['id'] = $GroupMenus->id;
            $data['name'] = $GroupMenus->name;
            $data['model'] = $GroupMenus->m;
            $data['controller'] = $GroupMenus->c;
            $data['action'] = $GroupMenus->act;
            $data['parentid'] = $GroupMenus->parentid;
            $data['display'] = $GroupMenus->display;
            $data['sort'] = $GroupMenus->sort;

            // $result['status'] = true;
            // $result['message'] = '成功';
            // $result['data'] =$data;
            // return $result;
            $this->serializer['status'] = true;
            $this->serializer['errno'] = 0;
            $this->serializer['message'] = '成功';
            $result['data'] =$data;
            return $result;
        }else{
            // $result['status'] = false;
            // $result['message'] = '查询失败';
            // return $result;die; 
            $this->serializer['status'] = false;
            $this->serializer['errno'] = 0;
            $this->serializer['message'] = '查询失败';
            return ;
        }

        

    }

    /**
     * @SWG\Post(path="/group/get-user-authorization",
     *     tags={"云平台-GROUP-权限"},
     *     summary="获取用户/角色的权限授权",
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
     *        name = "roleId",
     *        description = "权限ID",
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
     *     @SWG\Response(
     *         response = 200,
     *         description = ""
     *     ),
     *     @SWG\Response(
     *         response = 422,
     *         description = "Data Validation Failed 账号或密码错误",
     *     )
     * )
     *
     */
    public function actionGetUserAuthorization()
    {
        
        $GroupAcl = new GroupAcl();
        $Menus = new GroupMenus();

        $post=Yii::$app->request->post();

        $group_id = !empty($post['roleId'])?$post['roleId']:'';
        $empNumber = !empty($post['empNumber'])?$post['empNumber']:'';

        if($empNumber){
            $SystemUsers = new SystemUsers();

            $user = $SystemUsers->searchSystemUsersById($empNumber,true);
            if($user){
                $group_id = $user['user_role_id'];
                $search['empNumber'] = $empNumber;
                $search['groupId'] = $group_id;
                $groupList = $GroupAcl->getGroupAclList($search);
            }
        }else{

            if(empty($group_id)){
                // $result['status'] = false;
                // $result['message'] = '用户角色不能为空';
                // return $result;

                $this->serializer['status'] = false;
                $this->serializer['errno'] = 0;
                $this->serializer['message'] = '用户角色不能为空';
                return ;
            }
            $search['groupId'] = $group_id;
            $groupList = $GroupAcl->getGroupAclList($search);
        }
       
        $aclmenus = array();
        $default_module = array(11,12,13);
        $default_module = array();
        $default_check = array();
        $add_check = array();

        foreach ($groupList as $key => $value) {
            if(!in_array($value['menu_id'], $default_module)){
                array_push($aclmenus, $value['menu_id']);
            }
            if($value['emp_number']){
                array_push($add_check, $value['menu_id']);
            }else{
                array_push($default_check, $value['menu_id']);
            }
        }
        
        $allmenus = $Menus->getGroupMenusList();
        $array = array();
        foreach($allmenus as $r) {
            $r['cname'] = trim($r['name']);
            //$r['selected'] = $r['id'] == $_GET['parentid'] ? 'selected' : '';
            //$r['selected'] = 'selected';
            if(in_array($r['id'], $default_module)){
                $r['disable_selected'] = ' checked disabled ';
            }else{
                $r['disable_selected'] = '';
            }
            if($empNumber){
                if(in_array($r['id'], $default_check)){
                    $r['disable_selected'] = ' checked disabled ';
                }
                if(in_array($r['id'], $add_check)){
                    $r['onlychecked'] = ' checked ';
                }else{
                    $r['onlychecked'] = '';
                }
            }else{
                if(in_array($r['id'], $default_check)){
                    $r['onlychecked'] = ' checked ';
                }else{
                    $r['onlychecked'] = '';
                }
            }
            $array[] = $r;
        }

        $tree = $this->_tree;
        $str  = "<tr><td><span style='width:80px;padding-left:5px;'></span><input \$disable_selected \$onlychecked type='checkbox' name='menuid[]' value='\$id'>\$spacer \$cname</td></tr>";
        $tree->init($array);
        $tree_tr = $tree->get_tree(0, $str);

        // $result['status'] = true;
        // $result['message'] = '成功';
        // $result['data'] =$tree_tr;
        // return $result;

        $this->serializer['status'] = true;
        $this->serializer['errno'] = 0;
        $this->serializer['message'] = '成功';
        $result['data'] =$tree_tr;
        return $result;

    }

    /**
     * @SWG\Post(path="/group/add-authorization",
     *     tags={"云平台-GROUP-权限"},
     *     summary="添加/修改角色授权",
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
     *        name = "checkArr",
     *        description = "权限ID 数组格式",
     *        required = false,
     *        type = "string"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "roleId",
     *        description = "权限ID",
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
     *     @SWG\Response(
     *         response = 200,
     *         description = ""
     *     ),
     *     @SWG\Response(
     *         response = 422,
     *         description = "Data Validation Failed 账号或密码错误",
     *     )
     * )
     *
     */
    public function actionAddAuthorization()
    {
        
        $GroupAcl = new GroupAcl();
        $Menus = new GroupMenus();

        $post=Yii::$app->request->post();
        $checkArr = !empty($post['checkArr'])?$post['checkArr']:'';
        $group_id = !empty($post['roleId'])?$post['roleId']:'';
        $empNumber = !empty($post['empNumber'])?$post['empNumber']:'';

        $checkArr = explode(',', $checkArr);

        if($empNumber){
            $SystemUsers = new SystemUsers();

            $user = $SystemUsers->searchSystemUsersById($empNumber,true);
            if($user){
                $group_id = $user['user_role_id'];
                $search['empNumber'] = $empNumber;
                $search['groupId'] = $group_id;
                $groupList = $GroupAcl->getGroupAclList($search);
            }
        }else{
            if(empty($group_id)){
                // $result['status'] = false;
                // $result['message'] = '用户角色不能为空';
                // return $result;

                $this->serializer['status'] = false;
                $this->serializer['errno'] = 0;
                $this->serializer['message'] = '用户角色不能为空';
                return ;
            }
            $search['groupId'] = $group_id;
            $groupList = $GroupAcl->getGroupAclList($search);
        }

        

        if($groupList){
            $checkDefa = $checkArr;
            foreach ($groupList as $key => $value) {
                if(in_array($value['menu_id'],$checkArr)){
                    $c = (array) $value['menu_id'];
                    $checkDefa = array_diff($checkDefa,$c);

                }else{
                    $GroupAcl->deleteAclById($value['id']);
                }

            }

            foreach ($checkDefa as $key => $value) {
                $GroupAcl = new GroupAcl();
                if($empNumber){
                    $GroupAcl->emp_number = $empNumber;
                }
                $GroupAcl->group_id = $group_id;
                $GroupAcl->menu_id = $value;
                $GroupAcl->save();
            }
        }else{
            $data = array();
            foreach ($checkArr as $key => $value) {
                $GroupAcl = new GroupAcl();
                if($empNumber){
                    $GroupAcl->emp_number = $empNumber;
                }
                $GroupAcl->group_id = $group_id;
                $GroupAcl->menu_id = $value;
                $GroupAcl->save();

            }
        }

        // $result['status'] = true;
        // $result['message'] = '成功';
        // return $result;

        $this->serializer['status'] = true;
        $this->serializer['errno'] = 0;
        $this->serializer['message'] = '成功';
        return ;

    }





    
}
