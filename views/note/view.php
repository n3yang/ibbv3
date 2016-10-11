<?php

/* @var $this yii\web\View */
use yii\helpers\Html;
use yii\helpers\StringHelper;
use yii\helpers\Url;


// SEO Open Graph
$this->registerMetaTag(['property' => 'og:title', 'content' => $note->title]);
$this->registerMetaTag(['property' => 'og:image', 'content' => $note->getCoverUrl()]);
$this->registerMetaTag(['property' => 'og:url', 'content' => Yii::$app->request->absoluteUrl]);
$this->registerMetaTag(['property' => 'og:type', 'content' => 'article']);
$this->registerMetaTag(['property' => 'og:description', 'content' => $note->excerpt]);

// SEO title
$this->title = yii::$app->params['site']['title'];
$this->title .= ' - ' . $note->title;
// keywords, description
$this->registerMetaTag([
    'property'  => 'keywords', 
    'content'   => $note->keyword ?: $note->title
]);
$this->registerMetaTag([
    'property'  => 'description', 
    'content'   => mb_substr($note->excerpt, 0, 100)
]);

// SEO next and prev
/*
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
*/

// for weibo share
$this->registerJsFile('http://tjs.sjs.sinajs.cn/open/api/js/wb.js');
?>

        <div class="row row-offcanvas row-offcanvas-right">


            <div class="note col-sm-12 col-md-9">

                <ul class="breadcrumb hidden-xs">
                    <li><a href="/">首页</a></li>
                    <li><a href="/note">经验分享</a></li>
                    <li><a href="<?=Url::toRoute(['note/index', 'category'=>$note->category->slug])?>"><?=$note->category->name?></a></li>
                    <li class="active breadcrumb-title"><?=$note->title?></li>
                </ul>
                <ul class="breadcrumb visible-xs">
                    <li><a href="/" onclick="history.back(1)">返回</a></li>
                </ul>
                <div class="info">
                    <div class="row">
                        <div class="title col-xs-12">
                            <h3 class="title"><?=$note->title?></h3>
                        </div>
                        <div class="detail col-xs-12">
                            <?=$note->formatedContent?>
                        </div>

                        <div class="meta col-xs-12">
                            <div class="small text-muted col-xs-12 text-right"></div>
                            <div class="small text-muted col-xs-12 text-right"><?=$note->created_at?></div>
                            <div class="small text-muted col-xs-12 text-right">分类：<?=$note->category->name?></div>
                            <div class="small text-muted col-xs-12 text-right">来自：<?=$note->fetched_from ? '网络' : '原创';?></div>
                            <div class="small text-muted col-xs-12 text-right">原作者：<?=$note->fetched_author?></div>
                        </div>

                        <ul class="pager col-sm-6 col-xs-6">
                            <li class="previous"><a href="#">← </a></li>
                        </ul>
                        <ul class="pager col-sm-6 col-xs-6">
                            <li class="next"><a href="#"> →</a></li>
                        </ul>

                    </div>
<!--
                    <div class="related row">
                        <div class="col-sm-12">
                            <div class="lead text-warning">相关文章</div>
                        </div>

                        <ul class="list-unstyled col-sm-12">
                            <li>
                                <a href="#" class=""><h5>果用上一夏天就准备扔那就</h5></a>
                            </li>
                            <li>
                                <a href="#" class=""><h5>果用上一夏天就准备扔那就果用上一夏天就准备扔那就</h5></a>
                            </li>
                            <li>
                                <a href="#" class=""><h5>果用上一夏天就准备扔那就果用上一夏天就准备扔那就</h5></a>
                            </li>
                        </ul>
                    </div>
-->

                </div>

            </div><!--/.sidebar-offcanvas-->

            <?=$this->render('/public/sidebar');?>
