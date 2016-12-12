<?
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
?>

<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
    <h4 class="modal-title" id="myModalLabel">请登录</h4>
</div>
<div class="modal-body">
<!--
     <form>
        <div class="row">
            <div class="xs-cols-12"><input name="username" type="text"></div>
            <div class="xs-cols-12"><input name="password" type="password"></div>
        </div>
    </form>
-->
<?php $form = ActiveForm::begin([
    'id' => 'login-form-modal',
    'options' => ['class' => 'form-horizontal'],
    'fieldConfig' => [
        'template' => "<div class=\"col-sm-12\">{input}</div>\n<div class=\"col-sm-12\">{error}</div>",
        'labelOptions' => ['class' => 'col-sm-3 control-label hidden-xs'],
    ],
    'action' => '/site/login',
]);

    $loginFormModel = new \app\models\LoginForm;
 ?>

    <?= $form->field($loginFormModel, 'username')->textInput(['placeholder' => '请输入用户名', 'class' => 'col-sm-12 form-control']) ?>

    <?= $form->field($loginFormModel, 'password')->passwordInput(['placeholder' => '请输入密码', 'class' => 'col-sm-12 form-control']) ?>

    <?= $form->field($loginFormModel, 'rememberMe')->checkbox([
        'template' => "<div class=\"col-sm-12\">{input} 下次自动登录</div>\n",
    ]) ?>

    <div class="form-group">
        <div class="col-xs-offset-3 col-xs-6 text-center">
            <?= Html::submitButton('登录', ['class' => 'btn btn-primary', 'name' => 'login-button']) ?>
        </div>
    </div>

<?php ActiveForm::end(); ?>
</div>
<div class="modal-footer">
    <div class="text-center" style="top: -25px; position: relative;">
        <span style="background-color: #fff; padding: 0 10px">使用合作网站账号登录<span>
    </div>
    <div class="text-center">
        <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
        <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
    </div>
</div>