<?php

namespace app\controllers;

use Yii;
use yii\data\Pagination;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use app\models\LoginForm;
use app\models\ContactForm;
use app\models\FrontSearchForm;
use app\models\Offer;
use app\models\Note;
use app\models\Category;

class SiteController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    public function actionLogin()
    {
        if (!\Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    public function actionIndex()
    {
        // notes top 8
        $notes = Note::find()
            ->where(['status' => Note::STATUS_PUBLISHED])
            ->orderBy('created_at DESC')
            ->limit(8)
            ->all();

        // the navbar list of category
        $navCats = Category::getIndexPageNav();

        // find offers
        $query = Offer::find()->where(['status' => Offer::STATUS_PUBLISHED]);
        $total = $query->count();
        // create a pagination object with the total count
        $pagination = new pagination([
            'totalCount' => $total,
            'defaultPageSize' => 20,
        ]);
        
        // limit the query using the pagination and retrieve the offer
        $offers = $query->offset($pagination->offset)
            ->limit($pagination->limit)
            ->with('link')
            ->orderBy('id DESC')
            ->all();

        return $this->render('index',[
            'offers' => $offers,
            'pagination' => $pagination,
            'navCats' => $navCats,
            'notes' => $notes,
        ]);
    }

    public function actionSearch()
    {

        $form = new FrontSearchForm();
        $form->load(Yii::$app->request->get());
        $form->search();

        // create a pagination object with the total count
        $pagination = new pagination([
            'totalCount' => $form->total,
            'defaultPageSize' => $form->limit,
        ]);
    }
}
