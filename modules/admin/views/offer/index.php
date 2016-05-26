<?php

use yii\helpers\Html;
use yii\grid\GridView;
use app\models\File;

/* @var $this yii\web\View */
/* @var $searchModel app\models\offerSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Offers';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="offer-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('Create Offer', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        // 'filterModel' => $searchModel,
        'columns' => [
            // ['class' => 'yii\grid\SerialColumn'],

            'id',
            'title',
            'price',
            [
                'format' => 'html',
                'value'=>function($model){
                    $src = File::getImageUrlById($model->thumb_file_id);
                    return sprintf('<img src="%s" width="40">', $src);
                }
            ],
            // 'link_slug',
            [
                'attribute' => 'site', 
                'value' => function($model){
                    $b = $model->getSiteLabel($model->site);
                    return is_string($b) ? $b : null ;
                }
            ],
            [
                'attribute' => 'b2c', 
                'value' => function($model){
                    return $model->getB2cLabel();
                }
            ],
            'created_at',
            ['attribute'=>'status', 'value'=>function($model){return $model->getStatusLabel($model->status);}],

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>

</div>
