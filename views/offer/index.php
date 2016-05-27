<?php

/* @var $this yii\web\View */
/* @var $o app\model\Offer */

use yii\widgets\LinkPager;
use yii\helpers\Html;
use yii\helpers\StringHelper;
use yii\helpers\Url;

$this->title = '';
?>

        <div class="row row-offcanvas row-offcanvas-right">

            <div class="index col-sm-12 col-md-9">

                <? foreach ($offers as $o) { ?>
                <div class="spo-row row">
                    <div class="thumb col-xs-3 col-sm-2">
                        <a href="<?=$o->getLinkSlugUrl()?>" target="_blank"><img src="<?=empty($o->thumb)?'':$o->thumb->getImageUrl();?>" class="img-thumbnail img-responsive"></a>
                    </div>
                    <div class="info col-xs-9 col-sm-10">
                        <a href="<?=Url::to(['offer/view', 'id'=>$o->id])?>"><h4 class="title"><?=$o->title?><span class="price"><?=$o->price?></span></h4></a>
                        <div class="detail hidden-xs"><?=StringHelper::truncate($o->excerpt, 90)?></div>
                        <div class="meta row text-muted">
                            <span class="mall col-xs-3"><?=$o->getB2cLabel()?></span>
                            <span class="time col-xs-3"><?=$o->created_at?></span>
                            <span class="link col-xs-6 text-right"><a href="<?=$o->getLinkSlugUrl()?>" target="_blank" class="btn btn-primary" rel="nofollow">去看看</a></span>
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

            <div class="col-sm-12 col-md-3 sidebar-offcanvas" id="sidebar">

                <div class="list-group">
                    <a href="#" class="list-group-item active">GOnggao</a>
                    <p class="list-group-item">即便有上面给出的四组栅格class，你也不免会碰到一些问即便有上面给出的四组栅格class，你也不免会碰到一些问即便有上面给出的四组栅格class，你也不免会碰到一些问即便有上面给出的四组栅格class，你也不免会碰到一些问即便有上面给出的四组栅格class，你也不免会碰到一些问即便有上面给出的四组栅格class，你也不免会碰到一些问即便有上面给出的四组栅格class，你也不免会碰到一些问即便有上面给出的四组栅格class，你也不免会碰到一些
                    <p>
                </div>

                <div class="list-group">
                    <a href="#" class="list-group-item active">今日热门</a>
                    <a href="#" class="list-group-item">Link</a>
                    <a href="#" class="list-group-item">Link</a>
                    <a href="#" class="list-group-item">Link</a>
                    <a href="#" class="list-group-item">Link</a>
                    <a href="#" class="list-group-item">Link</a>
                    <a href="#" class="list-group-item">Link</a>
                    <a href="#" class="list-group-item">Link</a>
                    <a href="#" class="list-group-item">Link</a>
                    <a href="#" class="list-group-item">Link</a>
                </div>
            </div><!--/.sidebar-offcanvas-->
<!--/.sidebar-offcanvas-->
            
        </div><!--/row-->

        <hr>