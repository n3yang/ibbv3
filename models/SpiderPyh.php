<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\httpclient\Client;
use yii\helpers\ArrayHelper;
// use yii\httpclient\
use app\models\SpiderBase;

/**
 * Spider for pyh
 */
class SpiderPyh extends SpiderBase
{

    protected $syncCacheKey = 'SPIDER_PYH_SYNC_STATE'; 

    public $urlReplaceCache = [];

    public $dataList = [];
    public $dataArticle = [];

    public $newestListApiUrl = '';
    public $babyListAPiUrl = '';
    public $foodListApiUrl = '';

    public $fromSite = Offer::SITE_PYH;

    public function __construct()
    {
        $this->requestUserAgent = 'mgpyh/1.1.9 CFNetwork/758.4.3 Darwin/15.5';

        $this->newestListApiUrl = 'http://www.mgpyh.com/api/v1/get_more/';
        $params = [
            'productid' => 'I1',
            'channel' => 'App Store',
            'osv' => '9.3.2',
            'request_key' => 'newest',
            'requesttime' => time(),
            'os' => 'iPhone OS',
            'clientversion' => '1.1.2',
            'platform' => 'ios',
            'imei' => md5('f**k api'),
            'signature' => md5('f**k'),
            'appkey' => 'pumpkin',
            'page' => '1',
            'resolution' => '375*667',
            'device' => 'iPhone8,1',
            'access_token' => '',
        ];
        $this->newestListApiUrl .= '?' . http_build_query($params);

    }


    public function syncArticle()
    {
        Yii::info('Syncing Article...');

        $last = Yii::$app->cache->get($this->syncCacheKey);
        $list = $this->fetchList();

        foreach ($list as $r) {
            $maxId = $r['article_id'] > $maxId ? $r['article_id'] : $maxId;
            if ($r['article_id'] <= $last['article_id']) {
                continue;
            }
            $this->fetchArticle($r['article_id']);
        }
        $last['article_id'] = $maxId;
        $last['action_time'] = date('Y-m-d H:i:s');

        Yii::$app->cache->set($this->syncCacheKey, $last);
        Yii::info('Syncing Finished. ' . json_encode($last));

        return true;
    }


    public function fetchList($url='')
    {

        $rs = $this->getHttpContent($this->listUrl);
        // $rs = $this->getHttpContent('http://www.mgpyh.com/api/v1/get_recommend/');
        $rs = json_decode($rs, 1);
        foreach ($rs['items'] as $r) {
            echo $r['category']."\n";
        }

        print_r($rs);

    }

    public function getArticleFromHtml()
    {
        
    }

    public function fetchArticle($url)
    {
        # code...
    }
}