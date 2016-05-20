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
    /**
     * Valid Category Ids
     * 
     * @var array
     */
    public static $validCategoryIds = [];

    public $urlReplaceCache = [];

    public $dataList = [];
    public $dataArticle = [];

    // public $fetchListUrl = 'aHR0cDovL3d3dy5tZ3B5aC5jb20vY2F0ZWdvcnkvJUU3JThFJUE5JUU1JTg1JUI3JUU2JUFGJThEJUU1JUE5JUI0Lw==';
    public $babyListUrl = 'http://www.mgpyh.com/category/%E7%8E%A9%E5%85%B7%E6%AF%8D%E5%A9%B4/';
    public $foodListUrl = 'http://www.mgpyh.com/category/%E9%A3%9F%E5%93%81%E9%A5%AE%E6%96%99%E3%80%81%E9%85%92%E6%B0%B4%E3%80%81%E7%94%9F%E9%B2%9C/';

    public $fromSite = Offer::SITE_PYH;

    public function __construct()
    {
        $this->requestUserAgent = self::USER_AGENT_MOBILE;
        // $this->fetchListUrl = base64_decode($this->fetchListUrl);
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


    public function fetchList($url)
    {
        $doc = new \DOMDocument();
        @$doc->loadHTML($detail);
        // $doc->getElementsByTagNameNS()
    }

    public function getArticleFromHtml()
    {
        
    }

    public function fetchArticle($url)
    {
        # code...
    }
}