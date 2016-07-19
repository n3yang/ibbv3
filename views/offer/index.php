<?php

/* @var $this yii\web\View */
/* @var $o app\model\Offer */

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
// $this->registerMetaTag(['property' => 'og:image', 'content' => Url::base(true) . $offer->getThumbUrl()]);
$this->registerMetaTag(['property' => 'og:url', 'content' => Yii::$app->request->absoluteUrl]);
$this->registerMetaTag(['property' => 'og:type', 'content' => 'site']);

// auto loading
$this->registerJsFile('js/index-jquery-ias.js', ['depends' => 'app\assets\JqueryIasAsset']);
?>

        <div class="row row-offcanvas row-offcanvas-right">

            <div class="index col-sm-12 col-md-9">

                <? if ($category->name): ?>
                <ul class="breadcrumb hidden-xs">
                    <li><a href="/">首页</a></li>
                    <li class="active breadcrumb-title"><?=$category->name;?></li>
                </ul>
                <? endif; ?>

                <!-- <div class="spo-container"> -->
                <? foreach ($offers as $o) { ?>
                <div class="spo-row row">
                    <div class="thumb col-xs-3">
                        <a href="<?=Url::to(['offer/view', 'id'=>$o->id])?>" class="thumbnail">
                            <img src="<?=$o->getThumbUrl()?>" class="img-responsive" alt="<?= Html::encode($o->title); ?>">
                        </a>
                    </div>
                    <div class="info col-xs-9">
                        <a href="<?=Url::to(['offer/view', 'id'=>$o->id])?>"><h4 class="title"><?=$o->title?><span class="price"><?=$o->price?></span></h4></a>
                        <div class="detail hidden-xs"><?=StringHelper::truncate($o->excerpt, 90)?></div>
                        <div class="meta row text-muted">
                            <span class="mall col-xs-4"><span class="glyphicon glyphicon-shopping-cart hidden-xs" aria-hidden="true"></span> <?=$o->getB2cLabel()?></span>
                            <span class="time col-xs-3 "><span class="glyphicon glyphicon-time hidden-xs" aria-hidden="true"></span> <time datetime="<?=$o->created_at?>"><?=Yii::$app->formatter->asRelativeTime($o->created_at)?></time></span>
                            <span class="link col-xs-5 text-right"><a href="<?=$o->getLinkSlugUrl()?>" target="_blank" class="btn btn-primary" rel="nofollow">去看看</a></span>
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
