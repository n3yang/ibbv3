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
        'template' => "<div class=\"col-sm-12\">{label} {input}</div>\n<div class=\"col-sm-8\">{error}</div>",
        'labelOptions' => ['class' => 'col-sm-3 control-label hidden-xs'],
    ],
    'action' => '/site/login',
]);

    $loginFormModel = new \app\models\LoginForm;
 ?>

    <?= $form->field($loginFormModel, 'username')->textInput(['placeholder' => '请输入用户名', 'class' => 'col-sm-8']) ?>

    <?= $form->field($loginFormModel, 'password')->passwordInput(['placeholder' => '请输入密码', 'class' => 'col-sm-8']) ?>

    <?= $form->field($loginFormModel, 'rememberMe')->checkbox([
        'template' => "<div class=\"col-sm-offset-2 col-sm-8\">{input} 自动登录</div>\n",
    ]) ?>

    <div class="form-group">
        <div class="col-xs-offset-4 col-xs-4">
            <?= Html::submitButton('Login', ['class' => 'btn btn-primary', 'name' => 'login-button']) ?>
        </div>
    </div>

<?php ActiveForm::end(); ?>
</div>
<div class="modal-footer">
    <div class="text-left">使用合作网站账号登录</div>
    <div class="">
        <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
    </div>
</div>