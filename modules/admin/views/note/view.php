<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Note */

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Notes', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
$this->registerCssFile('/css/admin-tinymce-editor.css');
?>
<div class="note-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('修改', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('删除', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
        <?= Html::a('查看', ['/note/view', 'id' => $model->id], ['class' => 'btn btn-success', 'target' => '_blank']); ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'user_id',
            'category_id',
            'title:ntext',
            // 'content:html',
            'formatedContent:raw',
            'excerpt:ntext',
            'cover',
            'coverUrl:image',
            'keyword',
            'fetched_from',
            'fetched_title',
            'created_at',
            'updated_at',
            'status',
        ],
    ]) ?>

</div>
