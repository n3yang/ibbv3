<?php

namespace app\modules\api\controllers;

use Yii;
// use yii\web\Controller;
use yii\rest\Controller;
use yii\web\Response;
use yii\base\Event;
use app\components\XmlHelper;
use app\models\WechatResponse;

/**
 * wechat controller for the `api` module
 */
class WechatController extends Controller
{

    public function init()
    {
        parent::init();
        // disable session
        Yii::$app->user->enableSession = false;

        /**
         * register response event: before send
         * format return data
         */
        Event::on(Response::className(), Response::EVENT_BEFORE_SEND, [$this, 'formatDataBeforeSend']);
    }

    public function formatDataBeforeSend($event)
    {
        $response = $event->sender;
        if ($response->statusCode == 200) {
            $response->format = Response::FORMAT_RAW;
            $response->data = XmlHelper::build($response->data);
        } else {
            $response->format = Response::FORMAT_HTML;
            $response->data = $response->data['message'];
        }
    }

    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        $wechat = new WechatResponse();
        if (!$wechat->checkSignature()) {
            throw new \yii\web\ForbiddenHttpException("The requested resource was forbidden.");
        }

        $rs = null;
        switch ($wechat->data['MsgType']) {
            case 'text':
                $rs = 'text';
                break;

            case 'event':
                if ($wechat->data['Event'] == 'CLICK') {
                    # code...
                }
                $rs = 'event';
                break;
            
            default:
                # code...
                break;
        }

        //
        return $rs ?: Yii::$app->request->get('echostr');
    }

    public function actionGetOffers($category='')
    {
        # code...
    }

    public function actionGetNotes($category='')
    {
        # code...
    }

    public function actionSearch($kw = '')
    {
        
    }
}
