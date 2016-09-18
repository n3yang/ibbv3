<?php

namespace app\controllers;

use yii;
use yii\data\Pagination;
use yii\helpers\Url;
use app\models\Note;
use app\models\Tag;
use app\models\Category;

class NoteController extends \yii\web\Controller
{
    public function actionIndex()
    {
        
        // build a DB query to get all offer with status = 1
        $query = Note::find()->where(['status' => Note::STATUS_PUBLISHED]);

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
        $notes = $query->offset($pagination->offset)
            ->limit($pagination->limit)
            ->orderBy('id DESC')
            ->all();

        return $this->render('index',[
            'notes' => $notes,
            'pagination' => $pagination,
            'category' => $catObj,
        ]);
    }

    public function actionView($id)
    {
        $note = Note::findOne([
            'id'        => $id,
            // 'status'    => Note::STATUS_PUBLISHED,
        ]);

        // not found
        if (!$note) {
            throw new yii\web\NotFoundHttpException;
        }

        // $note->getLink();
        // the counter
        // $offer->updateCounters(['click' => 1]);

        // same category id
        $similarOffers = $note->findSimilar();

        // find next and pre
        $nextNote = Note::find()
            ->where(['status' => Note::STATUS_PUBLISHED])
            ->andWhere(['>', 'id', $id])
            ->orderBy('id ASC')
            ->limit(1)
            ->asArray()
            ->one();
        $prevNote = Note::find()
            ->where(['status' => Note::STATUS_PUBLISHED])
            ->andWhere(['<', 'id', $id])
            ->orderBy('id DESC')
            ->limit(1)
            ->asArray()
            ->one();

        return $this->render('view', [
            'note'          => $note,
            'nextNote'     => $nextNote,
            'prevNote'     => $prevNote,
            'similarOffers' => $similarOffers,
        ]);
    }

}
