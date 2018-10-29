<?php

namespace frontend\controllers;

use frontend\models\ContactForm;
use Yii;
use yii\web\Controller;

/**
 * Site controller
 */
class SiteController extends Controller
{

    public function beforeAction($action)
    {
        if (parent::beforeAction($action)) {
            Yii::$app->getResponse()->headers->set('Access-Control-Allow-Origin', "*");
            Yii::$app->getResponse()->headers->set('Access-Control-Allow-Headers', "content-type");
        };
        return parent::beforeAction($action);
    }


    /**
     * @inheritdoc
     */
    public function actions()
    {

        return [
            'error' => [
                'class' => 'yii\web\ErrorAction'
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null
            ],
            'set-locale' => [
                'class' => 'common\actions\SetLocaleAction',
                'locales' => array_keys(Yii::$app->params['availableLocales'])
            ],
            'doc' => [
                'class' => 'light\swagger\SwaggerAction',
                // 'restUrl' => url::to(['/site/api'], true),
                'restUrl' => \yii\helpers\Url::to(['/site/api'], true),
            ],
            'api' => [
                'class' => 'light\swagger\SwaggerApiAction',
                'scanDir' => [
                    Yii::getAlias('@frontend/controllers/v1'),

                ],
                 'api_key' => 'test'
            ],
            
        ];
    }


    public function actionIndex()
    {
        if(strtoupper($_SERVER['REQUEST_METHOD'])== 'OPTIONS'){
            exit;
        }
        return $this->render('index');
    }

    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post())) {
            if ($model->contact(Yii::$app->params['adminEmail'])) {
                Yii::$app->getSession()->setFlash('alert', [
                    'body' => Yii::t('frontend', 'Thank you for contacting us. We will respond to you as soon as possible.'),
                    'options' => ['class' => 'alert-success']
                ]);
                return $this->refresh();
            } else {
                Yii::$app->getSession()->setFlash('alert', [
                    'body' => \Yii::t('frontend', 'There was an error sending email.'),
                    'options' => ['class' => 'alert-danger']
                ]);
            }
        }

        return $this->render('contact', [
            'model' => $model
        ]);
    }
}
