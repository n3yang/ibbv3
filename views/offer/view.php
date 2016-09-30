<?php

/* @var $this yii\web\View */
use yii\helpers\Html;
use yii\helpers\StringHelper;
use yii\helpers\Url;


// SEO Open Graph
$this->registerMetaTag(['property' => 'og:title', 'content' => $offer->title . $offer->price]);
$this->registerMetaTag(['property' => 'og:image', 'content' => $offer->getCoverUrl()]);
$this->registerMetaTag(['property' => 'og:url', 'content' => Yii::$app->request->absoluteUrl]);
$this->registerMetaTag(['property' => 'og:type', 'content' => 'article']);
$this->registerMetaTag(['property' => 'og:description', 'content' => $offer->excerpt]);

// SEO title
$this->title = yii::$app->params['site']['title'];
$this->title .= ' - ' . $offer->title;
// keywords, description
$this->registerMetaTag([
    'property'  => 'keywords', 
    'content'   => $offer->title
]);
$this->registerMetaTag([
    'property'  => 'description', 
    'content'   => mb_substr($offer->excerpt, 0, 100)
]);

// SEO next and prev
if ($nextOffer) {
    $this->registerLinkTag([
        'rel'   => 'next',
        'title' => $nextOffer['title'],
        'href'  => Url::to(['offer/view', 'id'=>$nextOffer['id']], true)
    ]);
}
if ($prevOffer) {
    $this->registerLinkTag([
        'rel'   => 'prev',
        'title' => $prevOffer['title'],
        'href'  => Url::to(['offer/view', 'id'=>$prevOffer['id']], true)
    ]);
}

// for weibo share
$this->registerJsFile('http://tjs.sjs.sinajs.cn/open/api/js/wb.js');
?>

        <div class="row row-offcanvas row-offcanvas-right">

            <div class="spo col-sm-12 col-md-9">

                <ul class="breadcrumb hidden-xs">
                    <li><a href="/">首页</a></li>
                    <li><a href="/sp">优惠资讯</a></li>
                    <li class="breadcrumb-title"><a href="<?=Url::toRoute(['offer/index', 'category'=>$offer->category->slug])?>"><?=$offer->category->name?></a></li>
                    <li class="breadcrumb-title"><?=$offer->title?></li>
                </ul>
                <ul class="breadcrumb visible-xs">
                    <li><a href="/" onclick="history.back(1)">返回</a></li>
                </ul>
                <div class="info">
                    <div class="row">
                        <div class="thumb col-xs-8 col-xs-offset-2 col-sm-3 col-sm-offset-0">
                            <span class="thumbnail"><img src="<?=$offer->getCoverUrl()?>" class="img-responsive" alt="<?= Html::encode($offer->title); ?>"></span>
                        </div>

                        <div class="meta col-xs-12 col-xs-offset col-sm-9 hidden-xs">
                            <span class="col-xs-12"><h4 class="title"><?=$offer->title?><span class="price"><?=$offer->price?></span></h4></span>
                            <span class="time col-sm-6 text-muted"><label>时间：</label><?=$offer->created_at?></span>
                            <span class="mall col-sm-6 text-muted"><label>商城：</label><a href="<?=Url::to(['/offer/index', 'm' => $offer->b2c])?>"><?=$offer->getB2cLabel()?></a></span>
                            <span class="tag col-sm-6 text-muted"><label>分类：</label><a href="<?=Url::toRoute(['offer/index', 'category'=>$offer->category->slug])?>"><?=$offer->category->name?></a></span>
                            <span class="tag col-sm-6 text-muted"><label>来自：</label><?=$offer->getSiteLabel()?></span>
                            <span class="link col-sm-12 col-sm-offset-10"><a href="<?=$offer->getLinkSlugUrl()?>" class="btn btn-primary" rel="nofollow">去看看</a> </span>
                        </div>

                        <div class="col-xs-12 col-sm-12">
                            <h4 class="title visible-xs"><?=$offer->title?><span class="price"><?=$offer->price?></span></h4>
                            <div class="detail">
                                <p class="lead text-warning">优惠详情</p>
                                <?=$offer->content?>
                            </div>
                            <wb:share-button appkey="1823418018" addition="simple" type="button" ralateUid="5649379034"></wb:share-button>
                        </div>

                        <div class="meta visible-xs">
                            <a href="<?=$offer->getLinkSlugUrl()?>" class="link btn btn-primary text-center col-xs-8 col-xs-offset-2" rel="nofollow">去看看</a>
                            <div class="time text-muted col-xs-5 col-xs-offset-1"><label>时间：</label><?=Yii::$app->formatter->asRelativeTime($offer->created_at)?></div>
                            <div class="mall text-muted col-xs-6"><label>商城：</label><?=$offer->getB2cLabel()?></div>
                            <div class="tag text-muted col-xs-11 col-xs-offset-1"> <label>分类：</label><?=$offer->category->name?></div>
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
                            <a href="<?=Url::to(['offer/view', 'id'=>$o->id])?>" class="thumbnail">
                                <img class="img-responsive" src="<?=$o->getCoverUrl()?>">
                            </a>
                            <div class="caption"><h5><?=StringHelper::truncate($o->title, 30)?></h5></div>
                        </div>
                        <? } // end foreach ?>
                    </div>

                    <div class="row"></div>
                </div>

            </div><!--/.sidebar-offcanvas-->

            <?=$this->render('/public/sidebar');?>

