<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\NoteSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Notes';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="note-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Note', ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        // 'filterModel' => $searchModel,
        'columns' => [
            // ['class' => 'yii\grid\SerialColumn'],

            'id',
            // 'user_id',
            ['attribute'=>'category_id', 'value'=>function($model){return $model->category->name;}],
            'title:ntext',
            // 'content:ntext',
            // 'excerpt:ntext',
            // 'cover',
            // 'keyword',
            // 'fetched_from',
            // 'fetched_title',
            // 'created_at',
            'updated_at',
            ['attribute'=>'status', 'value'=>function($model){return $model->getStatusLabel($model->status);}],

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>

    <?php echo $this->render('_search', ['model' => $searchModel]); ?>
</div>
