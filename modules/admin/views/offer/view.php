<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\offer */

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Offers', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="offer-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'title:text',
            [
                'attribute' => 'category_id',
                'value' => $model->category->name,
            ],
            'excerpt',
            'content:html',
            'price',
            'thumb_file_id',
            'link_slug',
            'site',
            'b2c',
            'fetched_from',
            'created_at',
            'updated_at',
            ['attribute'=>'status', 'value'=>$model->getStatusLabel($model->status)],
            'click',
        ],
    ]) ?>

</div>
