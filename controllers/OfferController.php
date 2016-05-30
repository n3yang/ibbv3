<?php

namespace app\controllers;

use yii;
use yii\data\Pagination;
use app\models\Offer;
use app\models\Tag;

class OfferController extends \yii\web\Controller
{
    public function actionIndex()
    {
        // build a DB query to get all offer with status = 1
        $query = Offer::find()->where(['status' => Offer::STATUS_PUBLISHED]);

        // get the total number of offer (but do not fetch the article data yet)
        $total = $query->count();
        
        // create a pagination object with the total count
        $pagination = new pagination(['totalCount'=>$total, 'defaultPageSize'=>20]);
        
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

    public function actionView()
    {
        $id = Yii::$app->request->get('id');
        $offer = Offer::findOne([
            'id'        => $id,
            'status'    => Offer::STATUS_PUBLISHED,
        ]);
        $tagId = $offer->tags[0]->id;

        // not found
        if (!$offer) {
            throw new yii\web\NotFoundHttpException;
        }

        // same category
        // $tag = Tag::findOne($tagId);
        // $similarOffers = $tag->getOffers()
        //     ->where(['status'=>Offer::STATUS_PUBLISHED])
        //     ->andWhere(['<>', 'id', $id])
        //     ->limit(5)
        //     ->orderBy('id DESC')
        //     ->all();
        $similarOffers = Offer::find()
            ->select('offer.*')
            ->leftJoin('tag_offer', 'offer.id=tag_offer.offer_id')
            ->where(['offer.status'=>Offer::STATUS_PUBLISHED])
            ->andWhere(['<>', 'id', $id])
            ->orderBy('offer.id DESC')
            ->limit(5)
            ->all();
        foreach ($similarOffers as $key => $value) {
            var_dump($value->id);
        }

        // find next and pre
        $nextOffer = Offer::find()
            ->where(['status' => Offer::STATUS_PUBLISHED])
            ->andWhere(['>', 'id', $id])
            ->limit(1)
            ->one();

        $preOffer = Offer::find()
            ->where(['status' => Offer::STATUS_PUBLISHED])
            ->andWhere(['<', 'id', $id])
            ->limit(1)
            ->one();
        return $this->render('view', [
            'offer'     => $offer,
            'nextOffer' => $nextOffer,
            'preOffer'  => $preOffer,

        ]);
    }

}
