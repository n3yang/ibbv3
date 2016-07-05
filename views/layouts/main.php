<?php

/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Html;
use yii\widgets\Breadcrumbs;
use app\assets\AppAsset;

AppAsset::register($this);

$navbarActive = Yii::$app->request->get('category');
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="renderer" content="webkit">
    <?= Html::csrfMetaTags() ?>
    <?php $this->head() ?>
    
    <title><?= Html::encode($this->title) ?></title>
    <!--[if lt IE 9]>
    <script src="//cdn.bootcss.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="//cdn.bootcss.com/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>
<body>
<?php $this->beginBody() ?>

    <nav class="navbar navbar-static-top navbar-default">
        <div class="container">
            <div class="navbar-header">
                <button class="navbar-toggle collapsed" aria-expanded="false" aria-controls="navbar" type="button" data-toggle="collapse" data-target="#navbar">
                    <span class="sr-only">打开导航</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand hidden-sm hidden-xs" href="/"><?=yii::$app->params['site']['title']?></a>
                <a class="navbar-brand-logo hidden-md hidden-lg" href="/"><img src="logo-mobile.jpg" class="nav-logo"></a>
            </div>
            <div class="navbar-collapse collapse" id="navbar" aria-expanded="false" style="height: 1px;">
                <ul class="nav navbar-nav">
                    <li<? if ($navbarActive=='') { echo ' class="active"'; } ?>><a href="/">首页</a></li>
                    <li<? if ($navbarActive=='yingyangfushi') { echo ' class="active"'; } ?>><a href="/sp/category/yingyangfushi">营养辅食</a></li>
                    <li<? if ($navbarActive=='niaokushijin') { echo ' class="active"'; } ?>><a href="/sp/category/niaokushijin">尿裤湿巾</a></li>
                    <li<? if ($navbarActive=='wanjuyueqi') { echo ' class="active"'; } ?>><a href="/sp/category/wanjuyueqi">玩具乐器</a></li>
                    <li<? if ($navbarActive=='weiyangyongpin') { echo ' class="active"'; } ?>><a href="/sp/category/weiyangyongpin">喂养用品</a></li>
                    <li<? if ($navbarActive=='xihuyongpin') { echo ' class="active"'; } ?>><a href="/sp/category/xihuyongpin">洗护用品</a></li>
                    <li<? if ($navbarActive=='naifenniunai') { echo ' class="active"'; } ?>><a href="/sp/category/naifenniunai">奶粉牛奶</a></li>
                    <li<? if ($navbarActive=='anquanzuoyi') { echo ' class="active"'; } ?>><a href="/sp/category/anquanzuoyi">安全座椅</a></li>
                    <li<? if ($navbarActive=='tongchejiaju') { echo ' class="active"'; } ?>><a href="/sp/category/tongchejiaju">童车家具</a></li>
                    <li<? if ($navbarActive=='mamayongpin') { echo ' class="active"'; } ?>><a href="/sp/category/mamayongpin">妈妈用品</a></li>
                    <li<? if ($navbarActive=='tongzhuangtongxie') { echo ' class="active"'; } ?>><a href="/sp/category/tongzhuangtongxie">童装童鞋</a></li>
                    <li class="dropdown">
                        <a class="dropdown-toggle" data-toggle="dropdown" href="#" id="download">更多类别</a>
                        <ul class="dropdown-menu" aria-labelledby="download">
                            <li><a href="/sp/category/jiayongdianqi">家用电器</a></li>
                            <li><a href="/sp/category/tushuyingyin">图书影音</a></li>
                            <li><a href="/sp/category/noname">未分类</a></li>
                        </ul>
                    </li>
                </ul>
            </div><!-- /.nav-collapse -->
        </div><!-- /.container -->
    </nav>

    <div class="container">

        <?= $content ?>

    </div>

    <!--footer start-->
    <footer class="footer">
        <div class="container">
            <p class="text-muted">&copy; 2016 Inc</p>
        </div>
    </footer>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>