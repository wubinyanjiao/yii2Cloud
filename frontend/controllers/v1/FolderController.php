<?php
namespace frontend\controllers\v1;
use common\models\folder\File;
use common\models\folder\FileList;
use common\models\folder\Folder;
use yii\web\Response;
use yii;

class FolderController extends \common\rest\Controller
{


    /**
     * @var string
     */
    public $modelClass = 'common\models\Folder';

    /**
     * @var array
     */
    public $serializer = [
        'class' => 'common\rest\Serializer',    // 返回格式数据化字段
        'collectionEnvelope' => 'data',       // 制定数据字段名称
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
     * @SWG\Post(path="/folder/folder-list",
     *     tags={"云平台-Folder-文件管理"},
     *     summary="文件列表",
     *     description="文件列表",
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
     *        name = "folder_id",
     *        description = "文件夹id",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "page",
     *        description = "页数",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "返回文件列表信息"
     *     ),
     * )
     *
     **/
    public function actionFolderList(){
        $folder_id = yii::$app->request->post('folder_id');
        $page = yii::$app->request->post('page');
        $customerId = $this->customerId;
        $folder = new Folder();
        $model = $folder->folderlist($folder_id,$customerId,$page);
        return $model;
    }


    /**
     * @SWG\Post(path="/folder/folder-add",
     *     tags={"云平台-Folder-文件管理"},
     *     summary="添加文件夹",
     *     description="添加文件夹",
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
     *        name = "folder_name",
     *        description = "文件夹名",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "folder_id",
     *        description = "文件夹表id 根目录为0",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "返回文件信息"
     *     ),
     * )
     *
     **/
    public function actionFolderAdd(){
        $folder_name = Yii::$app->request->post('folder_name');
        $folder_id = Yii::$app->request->post('folder_id');
        $customerId = $this->customerId;
        if($folder_id == 0){
            $str = $folder_name;
        }else{
            $folder = Folder::find()->where(['id'=>$folder_id])->one();
            $str = $folder['url'].'/'.$folder_name;
        }
        $time = date('Y-m-d H:i:s',time());
            $folder = new Folder();
            $folder->folder_name = $folder_name;
            $folder->url =  $str;
            $folder->level_id = $folder_id;
            $folder->time = $time;
            $folder->customer_id = $this->customerId;
            $info = $folder->save();
            $id = $folder->id;
            if($info){
                $filelist = new FileList();
                $filelist->file_id = $id;
                $filelist->is_folder = 1;
                $filelist->file_name = $folder_name;
                $filelist->file_real_name = $folder_name;
                $filelist->file_url = $str;
                $filelist->file_size = 1;
                $filelist->fu_id = $folder_id;
                $filelist->file_time = $time;
                $filelist->customer_id = $this->customerId;
                $info = $filelist->save();
                return $info;
            }
    }




    /**
     * @SWG\Post(path="/folder/file-add",
     *     tags={"云平台-Folder-文件管理"},
     *     summary="添加文件",
     *     description="添加文件",
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
     *        name = "file",
     *        description = "文件",
     *        required = true,
     *        type = "file"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "folder_id",
     *        description = "文件夹表id 根目录为0",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "返回文件信息"
     *     ),
     * )
     *
     **/
    public function actionFileAdd(){
        $file = $_FILES['file'];
        $name = $file['name'];
        $houzhui = substr(strrchr($name, '.'), 1);
        $id = uniqid();
        $url_name =  $id.'.'.$houzhui;
        $size = $file['size'];
        $folder_id = Yii::$app->request->post('folder_id');
        $path = env('STORAGE_BASE_URL');
        $documentPath = $path.'/netdisk/'.$this->customerId;
        if(!file_exists($documentPath)) {
            mkdir($documentPath, 0777,true);
        }
        $info = move_uploaded_file($file['tmp_name'],$documentPath.'/'.$url_name);
        $time = date('Y-m-d H:i:s',time());
        $url = Folder::find()->select(['url'])->where(['id'=>$folder_id])->one();
        if($info){
            $file = new File();
            $file->file_name = $name;
            $file->url_name = $url_name;
            $file->file_size = $size;
            $file->folder_id = $folder_id;
            $file->file_time = $time;
            $file->customer_id = $this->customerId;
            $info = $file->save();
            $id = $file->id;
            if($info){
                $filelist = new FileList();
                $filelist->file_id = $id;
                $filelist->is_folder = 0;
                $filelist->file_name = $url_name;
                $filelist->file_real_name = $name;
                $filelist->file_url = $url['url'];
                $filelist->file_size = $size;
                $filelist->fu_id = $folder_id;
                $filelist->file_time = $time;
                $filelist->customer_id = $this->customerId;
                $info = $filelist->save();
                return $info;
            }
        }else{
            return $info;
        }
    }




    /**
     * @SWG\Post(path="/folder/file-del",
     *     tags={"云平台-Folder-文件管理"},
     *     summary="删除文件",
     *     description="删除文件",
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
     *        name = "file_id",
     *        description = "文件或文件夹id",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "is_folder",
     *        description = "是否是文件夹 1：是    0：不是",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "返回文件信息"
     *     ),
     * )
     *
     **/
    public function actionFileDel(){
        $data = yii::$app->request->post('file_list');
        $folder = new Folder();
        $model = $folder->filedel($data);
        return $model;

    }




}

