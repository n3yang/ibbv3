<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\offerSearch */
/* @var $form yii\widgets\ActiveForm */

?>

<div class="offer-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'title') ?>

    <?= $form->field($model, 'category_id')->dropDownList([''=>'ALL'] + $categories) ?>

    <?//= $form->field($model, 'content') ?>

    <?//= $form->field($model, 'price') ?>

    <?//= $form->field($model, 'thumb_file_id') ?>

    <?php // echo $form->field($model, 'link_slug') ?>

    <?=$form->field($model, 'site')->dropDownList([''=>"ALL"] + $model->getSiteLabels()) ?>

    <?=$form->field($model, 'b2c')->dropDownList([''=>'ALL'] + $model->getB2cLabels()) ?>

    <?php // echo $form->field($model, 'created_at') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
