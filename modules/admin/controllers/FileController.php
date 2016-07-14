<?php

namespace app\modules\admin\controllers;

use Yii;
use app\models\File;
use app\models\FileSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;
use yii\filters\VerbFilter;

/**
 * FileController implements the CRUD actions for File model.
 */
class FileController extends Controller
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
     * Lists all File models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new FileSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single File model.
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
     * Creates a new File model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new File();
        $model->scenario = File::SCENARIO_CREATE;

        if ($model->load(Yii::$app->request->post())) {
            $model->upfile = UploadedFile::getInstance($model, 'upfile');
            if ($model->upload()){
                $model->save();
            }
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing File model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $model->scenario = File::SCENARIO_UPDATE;

        if ($model->load(Yii::$app->request->post())) {
            $model->removeFile();
            $model->upfile = UploadedFile::getInstance($model, 'upfile');
            $model->upload();
            $model->save();
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing File model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $model->removeFile();
        $model->delete();

        return $this->redirect(['index']);
    }

    public function actionUploadByCkeditor()
    {
        $model = new File();
        $model->scenario = File::SCENARIO_CREATE;

        if (Yii::$app->request->post()) {
            $model->upfile = UploadedFile::getInstanceByName('upload');
            if ($model->upload()){
                $model->save();
                $imageUrl = $model->getImageUrl();
            }
            
            $ckFuncNum = Yii::$app->request->get('CKEditorFuncNum');
            echo "<script type='text/javascript'>window.parent.CKEDITOR.tools.callFunction($ckFuncNum, '$imageUrl', '');</script>";
        } else {
            echo "<script>alert('".var_dump($model)."')</script>";
        }

        return;
    }

    public function actionUploadByTinymce()
    {
        $model = new File();
        $model->scenario = File::SCENARIO_CREATE;

        if (Yii::$app->request->post()) {
            $model->upfile = UploadedFile::getInstanceByName('image');
            if ($model->upload()){
                $model->save();
                $imageUrl = $model->getImageUrl();
            }
            if ($imageUrl) {
                echo "top.$('.mce-btn.mce-open').parent().find('.mce-textbox').val('$imageUrl').closest('.mce-window').find('.mce-primary').click();";
            } else {
                echo "alert('Failt to upload')";
            }

            return;
        }
    }
    /**
     * Finds the File model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return File the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = File::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
