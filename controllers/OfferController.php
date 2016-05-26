<?php

namespace app\controllers;

use yii;
use yii\data\Pagination;
use app\models\Offer;

class OfferController extends \yii\web\Controller
{
    public function actionIndex()
    {
        // build a DB query to get all offer with status = 1
        $query = Offer::find()->where(['status' => Offer::STATUS_PUBLISHED]);

        // get the total number of offer (but do not fetch the article data yet)
        $total = $query->count();
        
        // create a pagination object with the total count
        $pagination = new pagination(['totalCount'=>$total]);
        
        // limit the query using the pagination and retrieve the offer
        $offers = $query->offset($pagination->offset)
            ->limit($pagination->limit)
            ->with('thumb')
            ->orderBy('id DESC')
            ->all();

        return $this->render('index',[
            'offers' => $offers,
            'pagination' => $pagination
        ]);
    }

}
