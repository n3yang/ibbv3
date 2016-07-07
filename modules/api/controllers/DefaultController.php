<?php

namespace app\modules\api\controllers;

use yii\web\Controller;
// use yii\rest\Controller;
// use yii\web\Response;


/**
 * Default controller for the `api` module
 */
class DefaultController extends Controller
{
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        throw new \yii\web\NotFoundHttpException("The requested resource was not found.");
    }
}
