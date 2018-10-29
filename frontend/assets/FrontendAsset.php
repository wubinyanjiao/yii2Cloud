<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace frontend\assets;

use common\assets\Html5shiv;
use yii\bootstrap\BootstrapAsset;
use yii\web\AssetBundle;
use yii\web\YiiAsset;

/**
 * Frontend application asset
 */
class FrontendAsset extends AssetBundle
{
    /**
     * @var string
     */
    public $sourcePath = '@frontend/web/bundle';

    /**
     * @var array
     */
    public $css = [
        'style.css',
        'xing.css'
    ];

    /**
     * @var array
     */
    public $js = [
        'app.js',
        'analytics.js',
        'hm.js',
        'gtm.js',
        'jquery.min.js',
        'polyfill.min.js',
        'fastclick.js',
        'scripts.js',
        'tracker.js',
        'common.bundle.js',
        'vendor.bundle.js',
        'pages.bundle.js',
        'slick.min.js',
        'home.js',
        'jweixin-1.2.0.js',
        'wechat.v201803141700.js'

    ];

    /**
     * @var array
     */
    public $depends = [
        YiiAsset::class,
        BootstrapAsset::class,
        Html5shiv::class,
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
        'common\assets\AdminLte',
        'common\assets\Html5shiv',
    ];
}
