<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\tag;
use app\assets\JqueryFormAsset;
use dosamigos\ckeditor\CKEditor;
use dosamigos\tinymce\TinyMce;


/* @var $this yii\web\View */
/* @var $model app\models\offer */
/* @var $form yii\widgets\ActiveForm */

JqueryFormAsset::register($this);
?>

<div class="offer-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'title')->textInput() ?>

    <?= $form->field($model, 'category_id')->dropDownList($categories) ?>

    <?= $form->field($model, 'excerpt')->textarea(['rows' => 4]) ?>

    <?//= $form->field($model, 'content')->textarea(['rows' => 6]) ?>
    <?
/*
    echo $form->field($model, 'content')->widget(CKEditor::className(), [
        'options' => ['rows' => 18],
        'preset' => 'basic',
        'clientOptions' => [
            'toolbarGroups' => [
                ['name' => 'undo'],
                ['name' => 'basicstyles', 'groups' => ['basicstyles', 'cleanup']],
                ['name' => 'colors'],
                ['name' => 'links', 'groups' => ['links', 'insert']],
                ['name' => 'others', 'groups' => ['others', 'about', 'mode']],
            ],
            'filebrowserUploadUrl' => '/admin/file/upload-by-ckeditor',
            // 'filebrowserImageBrowseLinkUrl' => '',
            // 'filebrowserImageBrowseUrl' => '/browser/browse.php?type=Images',
            'resize_enabled' => true,
            'height' => 400,
            ]
        ]);
*/

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
    ]
]);


    ?>

    <?= $form->field($model, 'price')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'thumb_file_id')->textInput() ?>

    <?= $form->field($model, 'link_slug')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'site')->dropDownList($model->getSiteLabels()) ?>

    <?= $form->field($model, 'b2c')->dropDownList($model->getB2cLabels()) ?>

    <?= $form->field($model, 'created_at')->textInput() ?>

    <?= $form->field($model, 'status')->dropDownList($model->getStatusLabel()) ?>

    <?= $form->field($model, 'click')->textInput() ?>

    <?= $form->field($model, 'tags')->checkboxList($tags) ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<?= Html::beginForm(['/admin/file/upload-by-tinymce'], 'post', ['enctype' => 'multipart/form-data', 'style' => "width:0px;height:0;overflow:hidden", 'id' => 'uploadForm']) ?>
    
    <input name="image" type="file" onchange="$('#uploadForm').ajaxSubmit({ success: function(d){eval(d);} });this.value='';">
<?= Html::endForm() ?>
