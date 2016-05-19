<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Links';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="link-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Link', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            'id',
            'name',
            [
                'attribute' => '原始链接',
                'format' => 'raw',
                'value' => function($data){
                    $href = $data->url;
                    $url = strlen($href)>50 ? (substr($href, 0, 50) . ' [...]') : $href;
                    return sprintf('<a target="_blank" href="%s">%s</a>', $href, $url);
                }
            ],
            [
                'attribute' => '跳转链接',
                'format' => 'raw',
                'value'  => function($data) {
                    $href = $data::REDIRECT_SLUG_PREFIX . '/' . $data->slug;
                    return sprintf('<a target="_blank" href="%s">%s</a>', $href, $data->slug);
                }
            ],
            'click',
            'created_at',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>

    
    <?= $this->render('_search', ['model' => $model]); ?>

</div>
