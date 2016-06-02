<?php

/* @var $this yii\web\View */
use yii\helpers\Html;
use yii\helpers\StringHelper;
use yii\helpers\Url;

$this->title = '';
?>

        <div class="row row-offcanvas row-offcanvas-right">

            <div class="spo col-sm-12 col-md-9">

                <ul class="breadcrumb hidden-xs">
                    <li><a href="/">首页</a></li>
                    <li class="active breadcrumb-title"><?=$offer->category->name?></li>
                    <li class="active breadcrumb-title"><?=$offer->title?></li>
                </ul>
                <ul class="breadcrumb visible-xs">
                    <li><a href="/" onclick="history.back(1)">返回</a></li>
                </ul>
                <div class="info">
                    <div class="row">
                        <div class="thumb col-xs-8 col-xs-offset-2 col-sm-3 col-sm-offset-0">
                            <img src="<?=$offer->thumb->getImageUrl()?>" class="img-thumbnail img-responsive">
                        </div>

                        <div class="meta col-xs-12 col-xs-offset col-sm-9 hidden-xs">
                            <span class="col-xs-12"><h4 class="title"><?=$offer->title?><span class="price"><?=$offer->price?></span></h4></span>
                            <span class="time col-sm-6 text-muted"><label>时间：</label><?=$offer->created_at?></span>
                            <span class="mall col-sm-6 text-muted"><label>商城：</label><?=$offer->getB2cLabel()?></span>
                            <span class="tag col-sm-6 text-muted"><label>分类：</label><a href="<?=Url::toRoute(['offer/index', 'category'=>$offer->category->slug])?>"><?=$offer->category->name?></a></span>
                            <span class="link col-sm-12 col-sm-offset-10"><a href="<?=$offer->getLinkSlugUrl()?>" class="btn btn-primary" rel="nofollow">去看看</a> </span>
                        </div>

                        <div class="col-xs-12 col-sm-12">
                            <h4 class="title visible-xs"><?=$offer->title?><span class="price"><?=$offer->price?></span></h4>
                            <div class="detail">
                                <p class="lead text-warning">优惠详情</p>
                                <?=$offer->content?>
                            </div>
                        </div>

                        <div class="meta visible-xs">
                            <a href="<?=$offer->getLinkSlugUrl()?>" class="link btn btn-primary text-center col-xs-8 col-xs-offset-2" rel="nofollow">去看看</a>
                            <div class="time text-muted col-xs-6"><label>时间：</label><?=Yii::$app->formatter->asRelativeTime($o->created_at)?></div>
                            <div class="mall text-muted col-xs-6"><label>商城：</label><?=$offer->getB2cLabel()?></div>
                            <div class="tag text-muted col-xs-12"> <label>分类：</label><?=$offer->category->name?></div>
                        </div>

                        <ul class="pager col-sm-6 col-xs-12">
                            <li class="previous">
                                <a href="<?=empty($prevOffer['id']) ? '###' : Url::to(['offer/view', 'id'=>$prevOffer['id']])?>">
                                    ← <? $t = empty($prevOffer['title'])?'无':$prevOffer['title']; echo StringHelper::truncate($t, 20)?>
                                </a>
                            </li>
                        </ul>
                        <ul class="pager col-sm-6 col-xs-12">
                            <li class="next">
                                <a href="<?=empty($nextOffer['id']) ? '###' : Url::to(['offer/view', 'id'=>$nextOffer['id']])?>">
                                    <? $t = empty($nextOffer['title'])?'无':$nextOffer['title']; echo StringHelper::truncate($t, 20)?> →
                                </a>
                            </li>
                        </ul>
                    </div>

                    <div class="related row">
                        <div class="col-xs-12 col-sm-12">
                            <div class="lead text-warning">同类推荐</div>
                        </div>
                        <? foreach ($similarOffers as $k => $o) { ?>
                        <div class="col-xs-6 col-sm-3">
                            <a href="<?=Url::to(['offer/view', 'id'=>$o->id])?>" class="">
                                <img class="img-responsive img-thumbnail" src="<?=$o->thumb->getImageUrl()?>">
                                <div class="caption"><h6><?=StringHelper::truncate($o->title, 30)?></h4></div>
                            </a>
                        </div>
                        <? } // end foreach ?>
                    </div>

                    <div class="row"></div>
                </div>

            </div><!--/.sidebar-offcanvas-->

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

