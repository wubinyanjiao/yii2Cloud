<?php
namespace frontend\controllers\v1;

use common\helps\tools;
use common\models\attachment\Attachment;
use common\models\employee\Employee;
use common\models\user\Picture;
use common\models\user\User;
use yii\rest\ActiveController;
use yii\web\Response;
use yii;

class FileController extends \common\rest\Controller
{

    public $modelClass = 'common\models\attachment\Attachment';
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
     * @SWG\Post(path="/file/upload",
     *     tags={"云平台-File-附件上传"},
     *     summary="员工附件上传",
     *     description="员工附件上传",
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
     *        name = "emp_number",
     *        description = "员工id",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "sort_id",
     *        description = "分类表id",
     *        required = false,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "eattach_id",
     *        description = "附件表id  修改时填写",
     *        required = false,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "details",
     *        description = "评论",
     *        required = false,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "screen",
     *        description = "分类",
     *        required = true,
     *        type = "integer",
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "screen_name",
     *        description = "分类中文名",
     *        required = true,
     *        type = "integer",
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "返回列表"
     *     ),
     * )
     *
     **/
    public function actionUpload(){
        $data = Yii::$app->request->post();
        $emp_number = $data['emp_number'];
        $name = $this->firstName;
        $details = isset($data['details']) ? $data['details']:'';
        $sort_id = isset($data['sort_id']) ? $data['sort_id']:'';
        $eattach_id = isset($data['eattach_id']) ? $data['eattach_id']:'';
        $screen = isset($data['screen']) ? $data['screen']:'';
        $screen_name = isset($data['screen_name']) ? $data['screen_name']:'';


        $file = $_FILES['file'];
        $size = $file['size'];
        $type = $file['type'];

        if($screen == 'picture'){
            $documentPath = '/data/wwwroot/uploadfile/public/head_pic/';//上传路径
            $file_name = uniqid().'_200x200.jpg';

            $picture = new Picture();
            $pic = $picture::find()->where(['emp_number'=>$emp_number])->one();
            $filetype = tools::filetype();
            if(in_array($type,$filetype)){
                $info = move_uploaded_file($file["tmp_name"],$documentPath.$file_name);
                if($info){
                    if(empty($pic)){
                        $picture->emp_number = $emp_number;
                        $picture->epic_picture_url = 'public/head_pic/'.$file_name;
                        $picture->epic_filename = $file['type'];
                        $query = $picture->save();
                        $url =  env('STORAGE_HOST_INFO').'public/head_pic/'.$file_name;
                        $this->serializer['message']= '上传成功';
                        $data['url'] = $url;
                        return $data;
                    }else{
                        $delurl = '/data/wwwroot/uploadfile/'.$pic['epic_picture_url'];
                        if(file_exists($delurl)){
                            unlink($delurl);
                        }

                        $pic->emp_number = $emp_number;
                        $pic->epic_picture_url = 'public/head_pic/'.$file_name;
                        $pic->epic_filename = $file['type'];
                        $query = $pic->save();
                        $url =  env('STORAGE_HOST_INFO').'public/head_pic/'.$file_name;
                        $this->serializer['message']= '上传成功';
                        $data['url'] = $url;
                        return $data;
                    }
                }else{
                    $this->serializer['message']='上传失败';
                    return false;
                }

            }else{

                $this->serializer['message']='文件格式不对';
                return false;
            }
        }

        $userid = User::find()->select(['id'])->where(['emp_number'=>$emp_number])->one();

        $number = substr($userid['id'],-1);
        $documentPath = '/data/wwwroot/uploadfile/'.$screen.'/'.$number.'/';//上传路径



        //判断文件夹是否存在
        if(!file_exists($documentPath)) {
            mkdir($documentPath, 0777,true);
        }

        if($size>tools::filesize()){
            $this->serializer['message']='文件不能大于10M';
        }else{
            //判断文件类型
            $filetype = tools::filetype();
            if(in_array($type,$filetype)){
                $attachment = new Attachment();
                $num = $attachment::find()->where(['emp_number'=>$emp_number])->orderBy('eattach_id desc')->one();
                $user_name = Employee::find()->asArray()->select(['emp_firstname'])->where(['emp_number'=>$emp_number])->one();
                $user_number = User::find()->asArray()->select(['user_name'])->where(['emp_number'=>$emp_number])->one();
                $count = $attachment::find()->where(['emp_number'=>$emp_number,'screen'=>$screen])->count();
                $eattach_ids = $num['eattach_id'] + 1;
                if($count == 0){
                    $file_name = $userid['id'].$user_name['emp_firstname'].$user_number['user_name'].'_'.$screen_name.'.jpg';
                }else{
                    $file_name = $userid['id'].$user_name['emp_firstname'].$user_number['user_name'].'_'.$screen_name.'_'.$count.'.jpg';
                }

                $url = $screen.'/'.$number.'/'.$file_name;
                $info = move_uploaded_file($file["tmp_name"],$documentPath.$file_name);
                if($info){
                    if($eattach_id == ''){
                        $attachment->emp_number = $emp_number;
                        $attachment->sort_id = $sort_id;
                        $attachment->eattach_id = $eattach_ids;
                        $attachment->eattach_desc = $details;
                        $attachment->eattach_filename = $file_name;
                        $attachment->eattach_size = $size;
                        $attachment->eattach_attachment_url = $url;
                        $attachment->eattach_type = $type;
                        $attachment->screen = $screen;
                        $attachment->attached_by_name = $name;
                        $attachment->attached_time =  date('Y-m-d H:i:s',time());
                        $query = $attachment->save();
                        if($query){
                            $this->serializer['message']='添加成功';
                            return $query;
                        }else{
                            $this->serializer['message']='添加失败';
                        }
                    }else{
                        $atta = $attachment::find()->where(['emp_number'=>$emp_number,'eattach_id'=>$eattach_id])->one();
                        $delurl = '/data/wwwroot/uploadfile/'.$atta['eattach_attachment_url'];
                        if(file_exists($delurl)){
                            unlink($delurl);
                        }
                        $atta->emp_number = $emp_number;
                        $atta->eattach_id = $eattach_ids;
                        $atta->sort_id = $sort_id;
                        $atta->eattach_desc = $details;
                        $atta->eattach_filename = $file_name;
                        $atta->eattach_size = $size;
                        $atta->eattach_attachment_url = $url;
                        $atta->eattach_type = $type;
                        $atta->screen = $screen;
                        $atta->attached_by_name = $name;
                        $atta->attached_time =  date('Y-m-d H:i:s',time());
                        $query = $atta->save();

                        if($query){
                            $this->serializer['message']='修改成功';
                            return false;
                        }else{
                            $this->serializer['message']='修改失败';
                            return false;
                        }
                    }
                }else{
                    $this->serializer['message']='上传失败';
                    return false;
                }

            }else{
                $this->serializer['message']='文件格式不对';
                return false;
            }
        }

    }








    /**
     * @SWG\Post(path="/file/del",
     *     tags={"云平台-File-附件上传"},
     *     summary="附件删除",
     *     description="附件删除",
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
     *        name = "emp_number",
     *        description = "员工id",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "eattach_id",
     *        description = "附件表id 数组",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "返回列表"
     *     ),
     * )
     *
     **/
    public function actionDel(){
        $emp_number = yii::$app->request->post('emp_number');
        $id = yii::$app->request->post('eattach_id');
        foreach ($id as $k=>$v){
            $arr[$k]['id'] = $v;
            $arr[$k]['emp_number'] = $emp_number;
        }
        $attachment = new Attachment();
        foreach ($arr as $k=>$v){
            $url = $attachment::find()->select(['eattach_attachment_url'])->where(['eattach_id'=>$v['id'],'emp_number'=>$v['emp_number']])->one();
            if($url != ''){
                $delurl = '/data/wwwroot/uploadfile/'.$url['eattach_attachment_url'];
                if(file_exists($delurl)){
                    unlink($delurl);
                }
            }
            $query = $attachment::deleteAll(['eattach_id'=>$v['id'],'emp_number'=>$v['emp_number']]);
        }

        return $query;
    }




    /**
     * @SWG\Post(path="/file/update-desc",
     *     tags={"云平台-File-附件上传"},
     *     summary="仅保存评论",
     *     description="仅保存评论",
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
     *        name = "emp_number",
     *        description = "员工id",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "desc",
     *        description = "评论",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Parameter(
     *        in = "formData",
     *        name = "eattach_id",
     *        description = "附件id",
     *        required = true,
     *        type = "integer"
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "返回添加体检项目页"
     *     ),
     * )
     *
     **/
    public function actionUpdateDesc(){
        $data = yii::$app->request->post();
        $emp_number = $data['emp_number'];
        $desc = $data['desc'];
        $eattach_id = $data['eattach_id'];
        $attachment = new Attachment();
        $atta = $attachment::find()->where(['emp_number'=>$emp_number,'eattach_id'=>$eattach_id])->one();
        $atta->eattach_desc = $desc;
        $query = $atta->save();
        return $query;
    }


}