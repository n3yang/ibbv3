<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\tag;

/* @var $this yii\web\View */
/* @var $model app\models\offer */
/* @var $form yii\widgets\ActiveForm */

?>

<div class="offer-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'title')->textInput() ?>

    <?= $form->field($model, 'content')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'price')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'thumb_file_id')->textInput() ?>

    <?= $form->field($model, 'link_slug')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'site')->dropDownList($model->getSiteLabel()) ?>

    <?= $form->field($model, 'b2c')->dropDownList($model->getB2cLabel()) ?>

    <?= $form->field($model, 'created_at')->textInput() ?>

    <?= $form->field($model, 'status')->dropDownList($model->getStatusLabel()) ?>

    <?= $form->field($model, 'tags')->checkboxList($tags) ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
