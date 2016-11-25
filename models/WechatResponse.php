<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\User;
use app\components\XmlHelper;

/**
* 
*/
class WechatResponse extends Model
{
    /**
     * Recieved data from wechat server
     * @var array
     */
    public $data;


    /**
     * validate the signature
     * 
     * @return bool true or false
     */
    public static function checkSignature()
    {
        $tmpArr = [
            yii::$app->params['wechat']['appToken'],
            Yii::$app->request->get('timestamp'),
            Yii::$app->request->get('nonce'),
        ];
        sort($tmpArr, SORT_STRING);
        $mySignature = sha1(implode($tmpArr));

        return $mySignature == Yii::$app->request->get('signature');
    }

    /**
     * Get data from wechat server
     * 
     * @return array data from wechat
     */
    public function getData()
    {
        // fetch data from request
        $this->date = empty($this->data)
            ? XmlHelper::parse(Yii::$app->request->rawBody)
            : $this->data;

        return $this->data;
    }

    /**
     * Reply text
     * 
     * @param  string $message message
     * 
     * @return string          xml
     */
    public function replyText($message = '')
    {
        $replyData = [
            'ToUserName'    => $this->data['FromUserName'],
            'FromUserName'  => $this->data['ToUserName'],
            'CreateTime'    => time(),
            'MsgType'       => 'text',
            'Content'       => $message,
        ];

        return $replyData;
    }

    /**
     * Reply news
     * 
     * articles = [
     *     [
     *         'Title'          => '',
     *         'Description'    => '',
     *         'PicUrl'         => '',
     *         'Url'            => '',
     *     ],
     *     [
     *         'Title'          => '',
     *         'Description'    => '',
     *         'PicUrl'         => '',
     *         'Url'            => '',
     *     ],
     * ]
     * 
     * @param  array $articles articles dataset
     * 
     * @return string          xml
     */
    public function replyNews($articles)
    {
        $replyData = [
            'ToUserName'    => $this->data['FromUserName'],
            'FromUserName'  => $this->data['ToUserName'],
            'CreateTime'    => time(),
            'MsgType'       => 'news',
        ];
        $replyData[
            'Articles'      => $articles,
            'ArticleCount'  => count($articles),
        ];

        return $replyData;
    }
}
