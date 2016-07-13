<?php

namespace app\controllers;

use Yii;
use app\models\Link;

class LinkController extends \yii\web\Controller
{


    public function actionGoto()
    {
        $slug = Yii::$app->request->get('slug');
        if (!$slug) {
            return $this->goHome();
        }

        $link = Link::findOneBySlug($slug);
        if (!$link) {
            Yii::warning('Link::goto faild! slug: ' . $slug);

            return $this->goHome();
        }
        
        $link->updateCounters(['click' => 1]);
        $cps = Link::replaceToCps($link->url);
        
        return $this->redirect($cps, 307);
    }

}
