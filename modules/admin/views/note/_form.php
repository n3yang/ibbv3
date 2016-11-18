<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use app\models\Tag;
use app\models\Category;
use app\models\File;
use app\assets\JqueryFormAsset;
use dosamigos\ckeditor\CKEditor;
use dosamigos\tinymce\TinyMce;

/* @var $this yii\web\View */
/* @var $model app\models\Note */
/* @var $form yii\widgets\ActiveForm */

app\assets\JqueryFormAsset::register($this);
app\assets\JquerySelectizeAsset::register($this);

?>

<div class="note-form">

    <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

    <div class="col-lg-8">
    <?//= $form->field($model, 'user_id')->textInput() ?>

    <?= $form->field($model, 'title')->textInput() ?>

<?
echo $form->field($model, 'content')->widget(TinyMce::className(), [
    'options' => ['rows' => 20],
    'clientOptions' => [
        'selector'=> 'textarea',
        'plugins' => [
            "advlist autolink lists link charmap print preview anchor",
            "searchreplace visualblocks code fullscreen",
            "insertdatetime media table contextmenu paste code emoticons image"
        ],
        'toolbar' => "undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image code | emoticons",
        'menubar' => true,
        'images_upload_url'=> '/admin/file/upload-by-ckeditor',
        'relative_urls' => false,
        'file_browser_callback'=> new yii\web\JsExpression("function(field_name, url, type, win) {
            if(type=='image') $('#uploadForm input').click();
        }"),
        'image_class_list' => [
            ['title' => 'img-attach', 'value' => 'img-attach'],
            ['title' => 'center-block', 'value' => 'center-block'],
            ['title' => 'None', 'value' => ''],
        ],

        // 'setup' => new yii\web\JsExpression("function(ed){
        //     ed.on('init', function(){
        //         this.getDoc().body.style.fontSize = '14px';
        //     });
        // }"),

        // 'content_style' => new \yii\web\JsExpression('
        //     ""
        // '),

        'content_css' => '/css/admin-tinymce-editor.css',

        'formats' => new \yii\web\JsExpression('{
            alignleft: [
                {selector: "img,table", collapsed: false, classes: "pull-left"}
            ],
            aligncenter: [
                {selector: "img,table", collapsed: false, classes: "center-block"}
            ],
            alignright: [
                {selector: "img,table", collapsed: false, classes: "pull-right"}
            ]
        }'),
    ]
]);

?>
    <?= $form->field($model, 'excerpt')->textarea(['rows' => 4]) ?>

    <?= $form->field($model, 'fetched_from')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'fetched_title')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'fetched_author')->textInput(['maxlength' => true]) ?>

    </div>

    <div class="col-lg-4">

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?= $form->field($model, 'category_id')->dropDownList(Category::getAllAsArrayIdName(Category::TYPE_NOTE)) ?>

    <?= $form->field($model, 'keyword')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'cover')->textInput(['maxlength' => true]) ?>

    <?= $form->field(new File, 'upfile')->fileInput()->label('上传封面图片') ?>

    <?= $form->field($model, 'status')->dropDownList($model->getStatusLabel()) ?>

    <?= $form->field($model, 'created_at')->textInput(['maxlength' => true]) ?>

    <?// $form->field($model, 'tags')->checkboxList(ArrayHelper::map(Tag::find()->asArray()->all(), 'id', 'name')) ?>

    <?= $form->field($model, 'tags')->textInput([
        'id' => 'input-tags',
        'value' => implode(',', ArrayHelper::getColumn($model->tags, 'name'))
        ]) ?>

    </div>
    
    <?php ActiveForm::end(); ?>

</div>

<?= Html::beginForm(['/admin/file/upload-by-tinymce'], 'post', ['enctype' => 'multipart/form-data', 'style' => "width:0px;height:0;overflow:hidden", 'id' => 'uploadForm']) ?>
    
    <input name="image" type="file" onchange="$('#uploadForm').ajaxSubmit({ success: function(d){eval(d);} });this.value='';">
<?= Html::endForm() ?>

<?
$js = <<<EOF
$('#input-tags').selectize({
    valueField: 'name',
    labelField: 'name',
    searchField: ['name', 'slug'],
    delimiter: ',',
    persist: false,
    create: true,
    load: function(query, callback) {
        if (!query.length) return callback();
        $.ajax({
            url: '/admin/tag/ajax-query-by-keyword',
            type: 'GET',
            data: {
                keyword: query,
            },
            error: function() {
                callback();
            },
            success: function(res) {
                callback(res.data);
            }
        });
    }
});
EOF;
$this->registerJs($js);
$this->registerCss('
.selectize-control.multi .selectize-input div.item{
    margin-right: 10px;
    color: #fff;
    background-color: #428bca;
}
.selectize-control.multi .selectize-input div.active{
    color: #000;
    background-color: #ddd;
}
');
?>
