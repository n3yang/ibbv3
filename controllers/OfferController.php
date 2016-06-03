<?php

namespace app\controllers;

use yii;
use yii\data\Pagination;
use app\models\Offer;
use app\models\Tag;
use app\models\Category;

class OfferController extends \yii\web\Controller
{
    public function actionIndex()
    {
        
        // build a DB query to get all offer with status = 1
        $query = Offer::find()->where(['status' => Offer::STATUS_PUBLISHED]);

        $category = Yii::$app->request->get('category');
        if ($category) {
            $catObj = Category::findOne(['slug'=>$category]);
            $query->andWhere(['category_id' => $catObj->id]);
        }

        // get the total number of offer (but do not fetch the article data yet)
        $total = $query->count();
        
        // create a pagination object with the total count
        $pagination = new pagination([
            'totalCount'=>$total,
            'defaultPageSize'=>20,
        ]);
        
        // limit the query using the pagination and retrieve the offer
        $offers = $query->offset($pagination->offset)
            ->limit($pagination->limit)
            ->with('thumb')
            ->orderBy('id DESC')
            ->all();

        return $this->render('index',[
            'offers' => $offers,
            'pagination' => $pagination,
            'category' => $catObj,
        ]);
    }

    public function actionView()
    {
        $id = Yii::$app->request->get('id');
        $offer = Offer::findOne([
            'id'        => $id,
            'status'    => Offer::STATUS_PUBLISHED,
        ]);
        $offer->getThumb();

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

        // same tag id
        // $similarOffers = Offer::find()
        //     ->select('offer.*')
        //     ->leftJoin('tag_offer', 'offer.id=tag_offer.offer_id')
        //     ->where(['offer.status'=>Offer::STATUS_PUBLISHED])
        //     ->andWhere(['<', 'id', $id])
        //     ->andWhere(['tag_offer.tag_id'=>$tagId])
        //     ->with('thumb')
        //     ->orderBy('offer.id DESC')
        //     ->limit(4)
        //     ->all();

        // same category id
        $similarOffers = Offer::find()
            ->where([
                'status'        => Offer::STATUS_PUBLISHED,
                'category_id'   => $offer->category_id,
            ])
            ->with('thumb')
            ->orderBy('offer.id DESC')
            ->limit(4)
            ->all();


        // find next and pre
        $nextOffer = Offer::find()
            ->where(['status' => Offer::STATUS_PUBLISHED])
            ->andWhere(['>', 'id', $id])
            ->orderBy('id DESC')
            ->limit(1)
            ->asArray()
            ->one();
        $prevOffer = Offer::find()
            ->where(['status' => Offer::STATUS_PUBLISHED])
            ->andWhere(['<', 'id', $id])
            ->orderBy('id DESC')
            ->limit(1)
            ->asArray()
            ->one();


        return $this->render('view', [
            'offer'         => $offer,
            'nextOffer'     => $nextOffer,
            'prevOffer'     => $prevOffer,
            'similarOffers' => $similarOffers,
        ]);
    }

}
