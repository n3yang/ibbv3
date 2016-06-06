<?php

namespace app\modules\admin\controllers;

use Yii;
use app\models\Offer;
use app\models\OfferSearch;
use app\models\Tag;
use app\models\Category;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;

/**
 * OfferController implements the CRUD actions for offer model.
 */
class OfferController extends Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ];
    }

    /**
     * Lists all offer models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new OfferSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single offer model.
     * @param string $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new offer model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new offer();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            
            // link tags
            $newTagIds = ArrayHelper::getValue(Yii::$app->request->post('Offer'), 'tags');
            foreach ($newTagIds as $tagId){
                $model->link('tags', Tag::findOne($tagId));
            }

            return $this->redirect(['view', 'id' => $model->id]);

        } else {
            
            // get all tags
            $tags = Tag::find()->asArray()->all();
            $tags = ArrayHelper::map($tags, 'id', 'name');

            // all category
            $categories = Category::find()
                ->where(['type'=>Category::TYPE_OFFER])
                ->asArray()
                ->all();
            $categories = ArrayHelper::map($categories, 'id', 'name');

            return $this->render('create', [
                'model' => $model,
                'tags'  => $tags,
                'categories' => $categories,
            ]);
        }
    }

    /**
     * Updates an existing offer model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        
        if ($model->load(Yii::$app->request->post()) && $model->save()) {

            // unlink all tags
            $model->unlinkAll('tags', true);

            // link new tags
            $newTagIds = ArrayHelper::getValue(Yii::$app->request->post('Offer'), 'tags');
            if ($newTagIds) {
                foreach ($newTagIds as $tagId){
                    $model->link('tags', Tag::findOne($tagId));
                }
            }
            
            return $this->redirect(['view', 'id' => $model->id]);

        } else {
            // get all tags
            $tags = Tag::find()->asArray()->all();
            $tags = ArrayHelper::map($tags, 'id', 'name');

            $selectedTags = $model->tags;

            // all category
            $categories = Category::find()
                ->where(['type'=>Category::TYPE_OFFER])
                ->asArray()
                ->all();
            $categories = ArrayHelper::map($categories, 'id', 'name');
            
            return $this->render('update', [
                'model' => $model,
                'tags' => $tags,
                'categories' => $categories,
            ]);
        }
    }

    /**
     * Deletes an existing offer model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the offer model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return offer the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = offer::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
