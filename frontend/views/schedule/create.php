<?php

use yii\helpers\Html;

/**
* @var yii\web\View $this
* @var common\models\Schedule $model
*/

$this->title = Yii::t('models', 'Schedule');
$this->params['breadcrumbs'][] = ['label' => Yii::t('models', 'Schedules'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="giiant-crud schedule-create">

    <h1>
        <?= Yii::t('models', 'Schedule') ?>
        <small>
                        <?= $model->name ?>
        </small>
    </h1>

    <div class="clearfix crud-navigation">
        <div class="pull-left">
            <?=             Html::a(
            'Cancel',
            \yii\helpers\Url::previous(),
            ['class' => 'btn btn-default']) ?>
        </div>
    </div>

    <hr />

    <?= $this->render('_form', [
    'model' => $model,
    ]); ?>

</div>
