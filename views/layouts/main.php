<?php

/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Html;
use yii\widgets\Breadcrumbs;
use app\assets\AppAsset;

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="renderer" content="webkit">
    <meta name="description" content="<?=yii::$app->params['site']['description']?>">
    <meta name="keywords" content="<?=yii::$app->params['site']['keywords']?>">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
    
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
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="/">Project name</a>
            </div>
            <div class="navbar-collapse collapse" id="navbar" aria-expanded="false" style="height: 1px;">
                <ul class="nav navbar-nav">
                    <li class="active"><a href="/">Home</a></li>
                    <li><a href="#about">About</a></li>
                    <li><a href="#contact">Contact</a></li>
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