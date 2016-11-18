<?php

namespace app\modules\admin\controllers;

use Yii;
use app\models\Note;
use app\models\NoteSearch;
use app\models\File;
use app\models\Tag;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;

/**
 * NoteController implements the CRUD actions for Note model.
 */
class NoteController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Note models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new NoteSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->setSort(['defaultOrder'=> ['id' => SORT_DESC]]);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Note model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Note model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Note();

        if ($model->load(Yii::$app->request->post())) {
            // upload cover image
            $fileModel = new File;
            $fileModel->upfile = UploadedFile::getInstance($fileModel, 'upfile');
            if ($fileModel->upload()) {
                $model->cover = $fileModel->path;
            }
            // link tags
            $tags = ArrayHelper::getValue(Yii::$app->request->post('Note'), 'tags');
            if ($tags) {
                $newTags = $this->findTags($tags);
                foreach ($newTags as $tag){
                    $model->link('tags', $tag);
                }
            }
            $model->user_id = Yii::$app->user->identity->id;
            $model->save();

            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Note model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post())) {
            $fileModel = new File;
            $fileModel->upfile = UploadedFile::getInstance($fileModel, 'upfile');
            if ($fileModel->upload()) {
                $model->cover = $fileModel->path;
            }
            // unlink all tags, and link new
            $model->unlinkAll('tags', true);
            $tags = ArrayHelper::getValue(Yii::$app->request->post('Note'), 'tags');
            if ($tags) {
                $newTags = $this->findTags($tags);
                foreach ($newTags as $tag){
                    $model->link('tags', $tag);
                }
            }
            $model->save();

            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing Note model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Note model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Note the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Note::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    /**
     * format name of tags
     * @param  string|array $tags      标签名称的字符串或者数组
     * @param  string       $delimiter 如果是字符串，需要提供分隔符
     * @return array                   含有 app\models\Tag 的数组
     */
    protected function findTags($tags = null, $delimiter = ',')
    {
        if (!is_array($tags)) {
            $r = explode($delimiter, $tags);
        }

        $all = [];
        foreach ($r as $name) {
            $tag = Tag::findOne(['name' => $name]);
            if (!$tag) {
                $tag = new Tag;
                $tag->name = $name;
                $tag->save();
            }
            $all[] = $tag;
        }

        return $all;
    }
}
