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

    public $newListApiUrl = 'http://dpapi.mgpyh.com/api/v1/get_more/';
    public $babyListAPiUrl = 'http://dpapi.mgpyh.com/api/v1/category/';
    public $foodListApiUrl = 'http://dpapi.mgpyh.com/api/v1/category/';

    public $fromSite = Offer::SITE_PYH;

    public function __construct()
    {
        // special UA
        $this->requestUserAgent = 'mgpyh/1.1.9 CFNetwork/758.4.3 Darwin/15.5.0';
        // basic query
        $query = [
            'productid' => 'I1',
            'channel' => 'App Store',
            'osv' => '9.3.2',
            'request_key' => 'newest',
            'requesttime' => time(),
            'os' => 'iPhone OS',
            'clientversion' => '1.1.2',
            'platform' => 'ios',
            'imei' => md5('happy api'),
            'signature' => md5('happy'),
            'appkey' => 'pumpkin',
            'page' => '1',
            'resolution' => '375*667',
            'device' => 'iPhone8,1',
            'access_token' => '',
        ];
        $this->newListApiUrl .= '?' . http_build_query($query);
        // generate category url
        unset($query['request_key']);
        $query['cat_id'] = 55;
        $this->babyListAPiUrl .= '?' . http_build_query($query);
        $query['cat_id'] = 57;
        $this->foodListApiUrl .= '?' . http_build_query($query);
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
        $rs = $this->getHttpContent($url);
        $rs = json_decode($rs, 1);
        if ($rs['status'] != '1') {
            Yii::warning('Fail to fetch list. API return: ' . var_export($rs, 1));
            return [];
        }
        foreach ($rs['items'] as $r) {
            if ($r['is_top']) {
                continue;
            }

            // echo $r['category']."\n";
        }
        // print_r($rs);
    }

    public function fetchBabyList()
    {
        return $this->fetchList($this->babyListAPiUrl);
    }

    public function fetchFoodList()
    {
        return $this->fetchList($this->foodListApiUrl);
    }

    public function getRealUrl($url='')
    {
        stream_context_set_default([
            'http' => [
                'header' => 'User-agent: ' . SpiderBase::USER_AGENT
            ]
        ]);
        $header = get_headers($url, 1);
        $target = is_array($header['Location']) ? $header['Location'][0] : $header['Location'];
        return parent::getRealUrl($target);
    }
}