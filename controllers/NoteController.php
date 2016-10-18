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
            $catObj = Category::findOne(['slug' => $category, 'type' => Category::TYPE_NOTE]);
            $query->andWhere(['category_id' => $catObj->id]);
        }

        // get the total number of offer (but do not fetch the article data yet)
        $total = $query->count();
        
        // create a pagination object with the total count
        $pagination = new pagination([
            'totalCount'=>$total,
            'defaultPageSize'=>10,
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

        // same category id
        // $similarNotes = $note->findSimilar();

        // find next and prev
        $broNote = $note->findBro();

        return $this->render('view', [
            'note'         => $note,
            'nextNote'     => $broNote['next'],
            'prevNote'     => $broNote['prev'],
            'similarNotes' => $similarNotes,
        ]);
    }

}
