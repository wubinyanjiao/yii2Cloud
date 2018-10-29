<?php
/**
 * Created by PhpStorm.
 * User: zein
 * Date: 7/4/14
 * Time: 2:01 PM
 */

namespace frontend\controllers;

use common\models\Page;
use common\models\shift\Constraint;
use common\models\shift\Schedule;
use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

class PageController extends Controller
{
    public function actionView($slug,$schedule_id)
    {

        $model = Page::find()->where(['slug' => $slug, 'status' => Page::STATUS_PUBLISHED])->one();
        if (!$model) {
            throw new NotFoundHttpException(Yii::t('frontend', 'Page not found'));
        }

        //获取solve文件
        $path=Yii::getAlias('@base');
        $base_path=dirname($path).'/optaplannerxml/';
        $last=substr($schedule_id, -1);
        $xml_path=$base_path.'xml_'.$last.'/roster_'.$schedule_id.'.xml';

        $con = file_get_contents($xml_path);
		header("Content-type: text/xml");
        echo($con);exit;

    }
}
