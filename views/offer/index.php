<?php

/* @var $this yii\web\View */
/* @var $o app\model\Offer */

use yii\widgets\LinkPager;
use yii\helpers\Html;
use yii\helpers\StringHelper;
use yii\helpers\Url;
use app\models\Offer;

// page title
$this->title = yii::$app->params['site']['title'];
$categoryName = '';
if ($category){
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
// $this->registerMetaTag(['property' => 'og:image', 'content' => Url::base(true) . $offer->getCoverUrl()]);
$this->registerMetaTag(['property' => 'og:url', 'content' => Yii::$app->request->absoluteUrl]);
$this->registerMetaTag(['property' => 'og:type', 'content' => 'site']);

// auto loading
$this->registerJsFile('js/index-jquery-ias.js', ['depends' => 'app\assets\JqueryIasAsset']);

// nav malls
$navMalls = [
    ['id' => Offer::B2C_JD            , name => '京东'],
    ['id' => Offer::B2C_TMALL         , name => '天猫'],
    ['id' => Offer::B2C_SUNING        , name => '苏宁'],
    ['id' => Offer::B2C_GOME          , name => '国美'],
    ['id' => Offer::B2C_DANGDANG      , name => '当当'],
    ['id' => Offer::B2C_TAOBAO        , name => '淘宝'],
    ['id' => Offer::B2C_AMAZON_CN     , name => '亚马逊'],
    ['id' => Offer::B2C_YHD           , name => '一号店'],
    ['id' => Offer::B2C_MUYINGZHIJIA  , name => '母婴之家'],
    ['id' => Offer::B2C_KAOLA         , name => '考拉海淘'],
    ['id' => Offer::B2C_TMALL_CS      , name => '天猫超市'],
    ['id' => Offer::B2C_WOMAI         , name => '中粮我买网'],
];
?>

        <div class="row row-offcanvas row-offcanvas-right">

            <div class="index col-sm-12 col-md-9">

                <ul class="breadcrumb hidden-xs">
                    <li><a href="/">首页</a></li>
                    <li><a href="/sp">优惠信息</a></li>
                    <? if ($category->name){ ?><li class="active breadcrumb-title"><?=$category->name;?></li><? } ?>
                </ul>

                <div class="spo-navbar row hidden-xs" id="spo-navbar">
                    <div class="row"><p></p></div>
                    <div class="col-xs-1 text-right head">分类</div>
                    <div class="col-xs-11 ">
                        <ul class="list-inline">
                            <li><a href="<?= Url::to(['/offer/index', 'category' => null, 'm' => yii::$app->request->get('m')]) ?>">不限分类</a></li>
                            <? foreach ($navCats as $cat) { ?>
                            <li<?= $cat->id == $category->id ? ' class="active"' : '' ?>><a href="<?=Url::to(['/offer/index', 'category' => $cat->slug, 'm' => yii::$app->request->get('m')])?>"><?=$cat->name?></a></li>
                            <? } ?>
                        </ul>
                    </div>
                    <div class="clearfix"></div>
                    <div class="col-xs-1 text-right head">商城</div>
                    <div class="col-xs-11">
                        <ul class="list-inline">
                            <li><a href="<?= Url::to(['/offer/index', 'm' => null, 'category' => yii::$app->request->get('category')]) ?>">不限商城</a></li>
<? foreach ($navMalls as $mall) { ?>
                            <li<?= $mall['id'] == Yii::$app->request->get('m') ? ' class="active"' : '' ?>><a href="<?= Url::to(['/offer/index', 'm' => $mall['id'], 'category' => yii::$app->request->get('category')]) ?>"><?= $mall['name'] ?></a></li>
<? } ?>
                        </ul>
                    </div>
                </div>

                <!-- <div class="spo-container"> -->
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
