<?php

/* @var $this yii\web\View */
/* @var $o app\model\Offer */

use yii\widgets\LinkPager;
use yii\helpers\Html;
use yii\helpers\StringHelper;
use yii\helpers\Url;

$this->title = yii::$app->params['site']['title'];
$this->title .= ' - ' . $category->name;
?>

        <div class="row row-offcanvas row-offcanvas-right">

            <div class="index col-sm-12 col-md-9">

                <? if ($category->name): ?>
                <ul class="breadcrumb hidden-xs">
                    <li><a href="/">首页</a></li>
                    <li class="active breadcrumb-title"><?=$category->name;?></li>
                </ul>
                <? endif; ?>

                <? foreach ($offers as $o) { ?>
                <div class="spo-row row">
                    <div class="thumb col-xs-3">
                        <a href="<?=Url::to(['offer/view', 'id'=>$o->id])?>" class="thumbnail">
                            <img src="<?=$o->getThumbUrl()?>" class="img-responsive">
                        </a>
                    </div>
                    <div class="info col-xs-9">
                        <a href="<?=Url::to(['offer/view', 'id'=>$o->id])?>"><h4 class="title"><?=$o->title?><span class="price"><?=$o->price?></span></h4></a>
                        <div class="detail hidden-xs"><?=StringHelper::truncate($o->excerpt, 90)?></div>
                        <div class="meta row text-muted">
                            <span class="mall col-xs-4"><span class="glyphicon glyphicon-shopping-cart hidden-xs" aria-hidden="true"></span> <?=$o->getB2cLabel()?></span>
                            <span class="time col-xs-3 "><span class="glyphicon glyphicon-time hidden-xs" aria-hidden="true"></span> <?=Yii::$app->formatter->asRelativeTime($o->created_at)?></span>
                            <span class="link col-xs-5 text-right"><a href="<?=$o->getLinkSlugUrl()?>" target="_blank" class="btn btn-primary" rel="nofollow">去看看</a></span>
                        </div>
                    </div>
                </div>
                <? } // end foreach ?>

                <div class="pagebar text-center">
                    <nav>
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