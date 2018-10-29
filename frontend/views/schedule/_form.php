<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use \dmstr\bootstrap\Tabs;
use yii\helpers\StringHelper;

/**
* @var yii\web\View $this
* @var common\models\Schedule $model
* @var yii\widgets\ActiveForm $form
*/

?>

<div class="schedule-form">

    <?php $form = ActiveForm::begin([
    'id' => 'Schedule',
    'layout' => 'horizontal',
    'enableClientValidation' => true,
    'errorSummaryCssClass' => 'error-summary alert alert-danger',
    'fieldConfig' => [
             'template' => "{label}\n{beginWrapper}\n{input}\n{hint}\n{error}\n{endWrapper}",
             'horizontalCssClasses' => [
                 'label' => 'col-sm-2',
                 #'offset' => 'col-sm-offset-4',
                 'wrapper' => 'col-sm-8',
                 'error' => '',
                 'hint' => '',
             ],
         ],
    ]
    );
    ?>

    <div class="">
        <?php $this->beginBlock('main'); ?>

        <p>
            

<!-- attribute name -->
			<?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

<!-- attribute shift_date -->
			<?= $form->field($model, 'shift_date')->textInput() ?>

<!-- attribute copy_type -->
			<?= $form->field($model, 'copy_type')->textInput(['maxlength' => true]) ?>

<!-- attribute status -->
			<?= $form->field($model, 'status')->textInput(['maxlength' => true]) ?>

<!-- attribute create_at -->
			<?= $form->field($model, 'create_at')->textInput() ?>

<!-- attribute run_java_time -->
			<?= $form->field($model, 'run_java_time')->textInput() ?>

<!-- attribute location_id -->
			<?= $form->field($model, 'location_id')->textInput() ?>

<!-- attribute is_confirm -->
			<?= $form->field($model, 'is_confirm')->textInput() ?>

<!-- attribute is_show -->
			<?= $form->field($model, 'is_show')->textInput() ?>

<!-- attribute roll_name -->
			<?= $form->field($model, 'roll_name')->textInput(['maxlength' => true]) ?>
        </p>
        <?php $this->endBlock(); ?>
        
        <?=
    Tabs::widget(
                 [
                    'encodeLabels' => false,
                    'items' => [ 
                        [
    'label'   => Yii::t('models', 'Schedule'),
    'content' => $this->blocks['main'],
    'active'  => true,
],
                    ]
                 ]
    );
    ?>
        <hr/>

        <?php echo $form->errorSummary($model); ?>

        <?= Html::submitButton(
        '<span class="glyphicon glyphicon-check"></span> ' .
        ($model->isNewRecord ? 'Create' : 'Save'),
        [
        'id' => 'save-' . $model->formName(),
        'class' => 'btn btn-success'
        ]
        );
        ?>

        <?php ActiveForm::end(); ?>

    </div>

</div>

