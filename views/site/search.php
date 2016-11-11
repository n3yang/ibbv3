<?php

/* @var $this yii\web\View */
/* @var $o app\model\Offer */

use yii\widgets\LinkPager;
use yii\helpers\Html;
use yii\helpers\StringHelper;
use yii\helpers\Url;
use app\models\Offer;

// page title
$inputKeyword = Yii::$app->request->get('k');
$this->title = yii::$app->params['site']['title'] . ' - ' . '关键词搜索：' . $inputKeyword;

// keywords, description
$this->registerMetaTag([
    'property' => 'keywords',
    'content' =>  yii::$app->params['site']['keywords'] . ',搜索,结果,' . $inputKeyword,
]);
$this->registerMetaTag([
    'property' => 'description',
    'content' => yii::$app->params['site']['description'],
]);

// SEO Open Graph
$this->registerMetaTag(['property' => 'og:title', 'content' => $this->title]);
$this->registerMetaTag(['property' => 'og:image', 'content' => Url::base(true) . '/logo-mobile.jpg']);
$this->registerMetaTag(['property' => 'og:url', 'content' => Yii::$app->request->absoluteUrl]);
$this->registerMetaTag(['property' => 'og:type', 'content' => 'site']);


?>

        <div class="row row-offcanvas row-offcanvas-right">

            <div class="index col-sm-12 col-md-9">

                <ul class="breadcrumb">
                    <li><a href="/">首页</a></li>
                    <li><a href="#">搜索结果</a></li>
                    <li class="active breadcrumb-title">关键字：<i><?=Html::encode($inputKeyword)?></i></li>
                </ul>
<!-- 
                <div class="spo-row row">
                    <div class="col-md-12">
                        <ul class="nav nav-pills">
                            <li class="active"><a href="#">Home <span class="badge">42</span></a></li>
                            <li><a href="#">Messages <span class="badge">3</span></a></li>
                        </ul>
                    </div>
                </div>
 -->
                <? if ($pagination->totalCount == 0){ ?>
                <div class="spo-row row">
                    <div class="col-md-12">
                        <h4>没有找到相关的内容</h4>
                        <hr>
                        <p class="text-center">(＞﹏＜) 对不起，没有找到与您搜索匹配的项。请尝试不同的关键词。</p>
                    </div>
                </div>
                <? } ?>
                <? foreach ($offers as $o) { ?>
                <div class="spo-row row">
                    <div class="thumb col-xs-3">
                        <a href="<?=Url::to(['offer/view', 'id'=>$o->id])?>" class="thumbnail">
                            <img src="<?=$o->getCoverUrl()?>" class="img-responsive" alt="<?= Html::encode($o->title); ?>">
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
