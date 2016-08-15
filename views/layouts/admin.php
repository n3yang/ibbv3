<?php

/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;

use app\assets\AdminAsset;
AdminAsset::register($this);

?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>



    <nav class="navbar navbar-inverse navbar-fixed-top">
      <div class="container-fluid">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="#">Project name</a>
        </div>
        <div id="navbar" class="navbar-collapse collapse">
          <ul class="nav navbar-nav navbar-right">
            <li><a href="#">Dashboard</a></li>
            <li><a href="#">Settings</a></li>
            <li><a href="#">Profile</a></li>
            <li><a href="#">Help</a></li>
            <?
                if (!Yii::$app->user->isGuest) {
                    echo '<li>'
                        . Html::beginForm(['/site/logout'], 'post')
                        . Html::submitButton(
                            'Logout (' . Yii::$app->user->identity->username . ')',
                            ['class' => 'btn btn-link']
                            )
                        . Html::endForm()
                        . '</li>';
                }
            ?>
          </ul>
          <form class="navbar-form navbar-right">
            <input type="text" class="form-control" placeholder="Search...">
          </form>
        </div>
      </div>
    </nav>

    <div class="container-fluid">
      <div class="row">
        <div class="col-sm-2 col-md-1 sidebar">

<?

$navItems = [
        [
            'label' => 'Dashboard',
            'url' => Url::toRoute('/admin/'),
            // 'active' => true,
        ],
        [
            'label' => 'Offer',
            'url' => Url::toRoute('/admin/offer'),
        ],
        [
            'label' => 'Link',
            'url' => Url::toRoute('/admin/link'),
        ],
        [
            'label' => 'Category',
            'url' => Url::toRoute('/admin/category'),
        ],
        [
            'label' => 'Tag',
            'url' => Url::toRoute('/admin/tag'),
        ],
        // [
        //     'label' => 'Offer Tag',
        //     'url' => Url::toRoute('/admin/offer-tag'),
        // ],
        [
            'label' => 'File',
            'url' => Url::toRoute('/admin/file'),
        ],
        [
            'label' => 'Note',
            'url' => Url::toRoute('/admin/note'),
        ],
        [
            'label' => 'User',
            'url' => Url::toRoute('/admin/user'),
        ],
        [
            'label' => 'Login',
            'url' => ['site/login'],
        ],
    ];

preg_match('/(\/admin\/[^\/]+)/', Yii::$app->request->url, $matches) ;
foreach ($navItems as $key=>$v) {
  if ($v['url'] == $matches[1]) {
    $navItems[$key]['active'] = true;
  }
}

echo Nav::widget([
    'items' => $navItems,
    'options' => ['class' =>'nav nav-sidebar'], // set this to nav-tab to get tab-styled navigation
]);
?>
          <ul class="nav nav-sidebar">
            <li><a href="">Nav item</a></li>
          </ul>

        </div>
        <div class="col-sm-10 col-sm-offset-2 col-md-11 col-md-offset-1 main">

          <?= $content ?>

        </div>
      </div>
    </div>



<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>