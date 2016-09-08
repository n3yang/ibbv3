<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\tag;
use app\models\category;
use app\assets\JqueryFormAsset;
use dosamigos\ckeditor\CKEditor;
use dosamigos\tinymce\TinyMce;

/* @var $this yii\web\View */
/* @var $model app\models\Note */
/* @var $form yii\widgets\ActiveForm */

$this->registerJsFile('//cdn.bootcss.com/jquery.form/3.51/jquery.form.min.js', ['depends' => 'yii\web\JqueryAsset']);
?>

<div class="note-form">

    <?php $form = ActiveForm::begin(); ?>

    <?//= $form->field($model, 'user_id')->textInput() ?>

    <?= $form->field($model, 'title')->textInput() ?>

    <?= $form->field($model, 'category_id')->dropDownList(Category::getAllAsArrayIdName(Category::TYPE_NOTE)) ?>

<?
echo $form->field($model, 'content')->widget(TinyMce::className(), [
    'options' => ['rows' => 10],
    'clientOptions' => [
        'selector'=> 'textarea',
        'plugins' => [
            "advlist autolink lists link charmap print preview anchor",
            "searchreplace visualblocks code fullscreen",
            "insertdatetime media table contextmenu paste code emoticons image"
        ],
        'toolbar' => "undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image code | emoticons",
        'menubar' => false,
        'images_upload_url'=> '/admin/file/upload-by-ckeditor',
        'relative_urls' => false,
        'file_browser_callback'=> new yii\web\JsExpression("function(field_name, url, type, win) {
            if(type=='image') $('#uploadForm input').click();
        }"),

        'setup' => new yii\web\JsExpression("function(ed){
            ed.on('init', function(){
                this.getDoc().body.style.fontSize = '14px';
            });
        }"),

        'content_style' => new \yii\web\JsExpression('
            "span.span-img-wrap {display:block;text-align:center} .img-attach{display:block;margin:auto}"
        '),

        // content_css : '/myLayout.css'        
    ]
]);
?>

    <?= $form->field($model, 'excerpt')->textarea(['rows' => 4]) ?>

    <?= $form->field($model, 'keyword')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'cover')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'fetched_from')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'fetched_title')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'status')->textInput() ?>

    <?//= $form->field($model, 'tags')->checkboxList($tags) ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<?= Html::beginForm(['/admin/file/upload-by-tinymce'], 'post', ['enctype' => 'multipart/form-data', 'style' => "width:0px;height:0;overflow:hidden", 'id' => 'uploadForm']) ?>
    
    <input name="image" type="file" onchange="$('#uploadForm').ajaxSubmit({ success: function(d){eval(d);} });this.value='';">
<?= Html::endForm() ?>
