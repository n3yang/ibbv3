<?php

/* @var $this yii\web\View */
/* @var $note app\model\Note */

use yii\widgets\LinkPager;
use yii\helpers\Html;
use yii\helpers\StringHelper;
use yii\helpers\Url;

// index page
if (!$category->name) {
    $this->title = yii::$app->params['site']['title'];
    $categoryName = '';
} else {
    // category page
    $this->title = yii::$app->params['site']['title'] . ' - ' . $category->name;
    $categoryName = $category->name;
}

// keywords, description
$this->registerMetaTag([
    'property' => 'keywords',
    'content' =>  yii::$app->params['site']['keywords'] . ' ' . $categoryName,
]);
$this->registerMetaTag([
    'property' => 'description',
    'content' => yii::$app->params['site']['description'] . $categoryName,
]);

// SEO Open Graph
$this->registerMetaTag(['property' => 'og:title', 'content' => $this->title]);
// $this->registerMetaTag(['property' => 'og:image', 'content' => Url::base(true) . $note->getCoverUrl()]);
$this->registerMetaTag(['property' => 'og:url', 'content' => Yii::$app->request->absoluteUrl]);
$this->registerMetaTag(['property' => 'og:type', 'content' => 'site']);

// auto loading
// $this->registerJsFile('js/index-jquery-ias.js', ['depends' => 'app\assets\JqueryIasAsset']);
?>

        <div class="row row-offcanvas row-offcanvas-right">

            <div class="index col-sm-12 col-md-9">

                <ul class="breadcrumb">
                    <li><a href="/">首页</a></li>
                    <li class="breadcrumb-title"><?= Html::a('经验分享', Url::to(['/note'])); ?></li>
                    <? if ($category->name): ?>
                    <li class="active breadcrumb-title"><?=$category->name;?></li>
                    <? endif; ?>
                </ul>

                <!-- <div class="spo-container"> -->
                <? foreach ($notes as $note) { ?>
                <div class="note-row row">
                    <div class="thumb col-sm-4 col-xs-12">
                        <a href="<?=Url::to(['note/view', 'id'=>$note->id])?>" class="thumbnail">
                            <img src="<?=$note->getCoverUrl()?>" class="img-responsive" alt="<?= Html::encode($note->title); ?>">
                        </a>
                    </div>
                    <div class="info col-sm-8 col-xs-12">
                        <a href="<?=Url::to(['note/view', 'id'=>$note->id])?>"><h4 class="title"><?=$note->title?></h4></a>
                        <div class="detail hidden-xs"><?=StringHelper::truncate($note->excerpt, 180)?></div>
                        <div class="meta row text-muted hidden-xs">
                            <!-- <span class="mall col-xs-4"><span class="glyphicon hidden-xs" aria-hidden="true"></span></span> -->
                            <span class="time col-xs-4"><span class="glyphicon glyphicon-time" aria-hidden="true"></span> 更新于：<time datetime="<?=$note->created_at?>"><?=Yii::$app->formatter->asRelativeTime($note->created_at)?></time></span>
                        </div>
                    </div>
                </div>
                <? } // end foreach ?>
                <!-- </div> -->
                <div class="pagebar">
                    <nav class="text-center">
<?
echo LinkPager::widget([
    'pagination' => $pagination,
    'maxButtonCount' => 5,
]);
?>
                    </nav>
                </div>
            </div><!--/.col-xs-12.col-sm-9-->

            <?=$this->render('/public/sidebar');?>
<!--/.sidebar-offcanvas-->
            
        </div><!--/row-->

        <hr>
