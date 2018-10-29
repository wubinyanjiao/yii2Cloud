<?php
namespace frontend\controllers\v1;

use common\models\curriculum\Curriculum;
use common\models\curriculum\CurriculumFile;
use yii\web\Response;
use yii;

class CurriculumfileController extends \common\rest\Controller
{


    /**
     * @var string
     */
    public $modelClass = 'common\models\Curriculum';

    /**
     * @var array
     */
    public $serializer = [
        'class' => 'common\rest\Serializer',    // 返回格式数据化字段
        'collectionEnvelope' => 'data',       // 制定数据字段名称
        'message' => 'OK',                      // 文本提示
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


    /*
    * 上传课程文件
    * post
    * 文件信息file、文件名name
    * */
    public $documentPath = '/data/wwwroot/uploadfile/curriculumfile/';//上传路径
    public function actionUpload() {
        $cur_name = Yii::$app->request->post('name');
        $postdata = fopen($_FILES['file']['tmp_name'], "r");
        /* Get file extension */
        $extension = substr($_FILES['file']['name'], strrpos($_FILES['file']['name'], '.'));
        /* Generate unique name */
        $filename = $this->documentPath . uniqid() . $extension;
        /* Open a file for writing */
        $fp = fopen($filename, "w");
        /* Read the data 1 KB at a time
          and write to the file */
        while ($file = fread($postdata, 1024))
            fwrite($fp, $file);
        /* Close the streams */
        fclose($fp);
        fclose($postdata);
        /* the result object that is sent to client */
        $result['filename'] = $filename;
        $result['document'] = $_FILES['file']['name'];
        $result['create_time'] = date("Y-m-d H:i:s");

        $file = new Curriculumfile();
        $file->cur_name = $cur_name;
        $file->cur_url = $filename;
        $file->create_time = $result['create_time'];
        $data = $file->save();
        if ($data){
            $model['code'] = '200';
            $model['isSuccess'] = true;
            $model['message'] =  '添加成功';
            $model['result'] = $result;
            return $model;

        }else{
            return '添加失败';
        }

    }


    /*
     * 员工答题上传文件
     * post
     *员工ID emp_number   课程id cur_id 文件信息 file
     * **/
    public function actionUploademp() {
        $cur_id = Yii::$app->request->post("cur_id");
        $emp_number = Yii::$app->request->post("emp_number");
        $postdata = fopen($_FILES['file']['tmp_name'], "r");
        /* Get file extension */
        $extension = substr($_FILES['file']['name'], strrpos($_FILES['file']['name'], '.'));
        /* Generate unique name */
        $filename = $this->documentPath . uniqid() . $extension;
        /* Open a file for writing */
        $fp = fopen($filename, "w");
        /* Read the data 1 KB at a time
          and write to the file */
        while ($file = fread($postdata, 1024))
            fwrite($fp, $file);
        /* Close the streams */
        fclose($fp);
        fclose($postdata);
        /* the result object that is sent to client */
        $result['filename'] = $filename;
        $result['document'] = $_FILES['file']['name'];
        $result['create_time'] = date("Y-m-d H:i:s");

        $file = new CurriculumFile();
        $file->cur_id = $cur_id;
        $file->file_url = $filename;
        $file->file_name = $result['document'];
        $file->emp_number = $emp_number;
        $data = $file->save();
        if ($data){
            $model['code'] = '200';
            $model['isSuccess'] = true;
            $model['message'] =  '添加成功';
            $model['result'] = $result;
            return $model;
        }else{
            return '添加失败';
        }

    }


    /*
     * 课件下载
     * post
     *  课件路径 cur_url
     * **/
    public function actionDownload(){
        $cur_url = Yii::$app->request->post("cur_url");
        $cur_name = preg_replace('/.*\//','',$cur_url);
        $file=fopen($cur_url,"r");
        header("Content-Type: application/octet-stream");
        header("Accept-Ranges: bytes");
        header("Accept-Length: ".filesize($cur_url));
        header("Content-Disposition: attachment; filename=".$cur_name);
        echo fread($file,filesize($cur_url));
        fclose($file);
    }





}

