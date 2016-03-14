<?php

namespace app\modules\admin\controllers;

use yii;
use yii\web\Controller;


class DefaultController extends Controller
{
    public function actionIndex()
    {
        // print_r(Yii::$app);
        if (Yii::$app->user->isGuest) {
            $this->redirect('/site/login', 302);
        }
        return $this->render('index');
    }
}
