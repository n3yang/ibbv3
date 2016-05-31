<?php

namespace app\controllers;

class TagController extends \yii\web\Controller
{
    public function actionIndex()
    {
        $slug = Yii::$app->request->get('slug');
        
        // return $this->render('index');
    }

    public function actionView()
    {
        // $slug = Yii::$app->request->get('slug');
    }

}
