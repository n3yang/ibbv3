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
                'format' => 'html',
                'value' => function($data){
                    $link = $data->url;
                    if (strlen($link)>50){
                        $url = substr($link, 0, 50);
                        $url .= ' [...]';
                    } else {
                        $url = $link;
                    }
                    return sprintf('<a href="%s">%s</a>', $link, $url);
                }
            ],
            'slug',
            'click',
            'created_at',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>

    
    <?= $this->render('_search', ['model' => $model]); ?>

</div>
