<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\httpclient\Client;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
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

    public $siteUrl = 'http://www.mgpyh.com';
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
        $opt = [CURLOPT_USERAGENT=>$this->requestUserAgent];
        $rs = $this->getHttpContent($url, '', $opt);
        $rs = json_decode($rs, 1);
        if ($rs['status'] != '1') {
            Yii::warning('Fail to fetch list. API return: ' . var_export($rs, 1));
            return [];
        }
        foreach ($rs['items'] as &$r) {
            if ($r['is_top']) {
                unset($r);
                continue;
            }
            echo $r['category'] .'-'. static::getTagIdByCategoryName($r['category']) ."\n";
            $this->fetchArticle($r);
        }
        return $rs['items'];
    }

    public function fetchBabyList()
    {
        return $this->fetchList($this->babyListAPiUrl);
    }

    public function fetchFoodList()
    {
        return $this->fetchList($this->foodListApiUrl);
    }


    public function fetchArticle($a)
    {
        // title
        if (preg_match("/(^.*)\s(.*)$/", $a['post_title'], $matches)) {
            $title = $matches[1];
        } else {
            $title = $a['item_name'];
        }
        // price
        $price = $a['price'];
        // quick link
        $linkSlug = $this->replaceUrl($a['money_url'], $title);
        preg_match("/([\w]+)$/", $linkSlug, $matches);
        $linkSlug = $matches[1];
        // excerpt
        $excerpt = mb_substr(strip_tags($a['post']), 0, 80);
        // content
        $content = $this->parseContent($a['post']);
        // thumbnail
        $thumbnail = $this->addRemoteFile($a['thumbnail']);
        $thumb_file_id = $thumbnail['id'];
        // b2c
        $b2c = static::getB2cIdByName($a['shop']['name']);
        // site
        $fromSite = $this->fromSite;

        $offerDs = [
            'title'         => $title,
            'link_slug'     => $linkSlug,
            'content'       => $content,
            'price'         => $price,
            'site'          => $this->fromSite,
            'b2c'           => $b2c,
            'status'        => empty($linkSlug) ? Offer::STATUS_DRAFT : Offer::STATUS_PUBLISHED,
            'excerpt'       => $excerpt,
            'thumb_file_id' => $thumb_file_id,
        ];

        $tagId = static::getTagIdByCategoryName($a['category']);

        $this->addOffer($offerDs, [$tagId]);

    }

    public function parseContent($content)
    {
        Yii::info('Parsing content...');

        $content = strip_tags($content, '<p><a><br /><span><h2><strong><b><img>');

        $doc = new \DOMDocument();
        @$doc->loadHTML($content);
        $tags = $doc->getElementsByTagName('a');
        foreach ($tags as $i => $tag) {
            $url = $tag->getAttribute('href');
            if (empty($url)) {
                $i++;
                continue;
            }
            if (!preg_match('/goods\/\w+/', $url)){
                $content = str_replace($url, '#', $content);
            } else {
                // find the real url
                $title = utf8_decode($tags->item($i)->nodeValue);
                $myurl = self::replaceUrl($url, $title);
                // in content
                $content = str_replace($url, $myurl, $content);
            }
        }

        return $content;
    }

    public function replaceUrl($url, $title = '')
    {
        if (Url::isRelative($url)) {
            $slash = strpos($url, '/') === 0 ? '' : '/';
            $url = $this->siteUrl . $slash . $url;
        }
        $logstr = 'Replace url: ' . $url;

        if (array_key_exists($url, $this->urlReplaceCache)) {
            return $this->urlReplaceCache[$url];
        }

        $real = $this->getRealUrl($url);
        $logstr.= ' -> ' . $real;
        // yes, we found the real url, let's replace it to own.
        if ($real != $url) {
            // create new short url
            $link = parent::addLinkUniq($real, $title);
            $shortUrl = $link['shortUrl'];
        } else {
            $shortUrl = '';
        }
        // TODO: maybe we can find it in my shortUrl. try it!

        $this->urlReplaceCache[$url] = $shortUrl;
        
        Yii::info($logstr . ' -> ' . $shortUrl);

        return $shortUrl;
    }

    public function getRealUrl($url='')
    {
        stream_context_set_default([
            'http' => [
                'header' => 'User-agent: ' . SpiderBase::USER_AGENT
            ]
        ]);
        @$header = get_headers($url, 1);
        if ($header === false) {
            Yii::warning('Fail to get headers: ' . $url);
            // waiting 5 seconds... retry
            sleep(5);
            @$header = get_headers($url, 1);
            if ($header === false) {
                return '';
            }
        }
        $target = is_array($header['Location']) ? $header['Location'][0] : $header['Location'];
        return parent::getRealUrl($target);
    }

    public function addRemoteFile($src, $name = '')
    {
        $src = str_replace('!focus', '', $src);
        $src = str_replace('!show', '', $src);
        return parent::addRemoteFile($src, $this->siteUrl, $name);
    }

    public static function getTagIdByCategoryName($name)
    {
        $matches = [
            20 => ['安全座椅'],
            18 => ['婴儿推车', '餐椅摇椅'],
            17 => ['LEGO积木拼插', '健身玩具', '益智玩具', '毛绒布艺', '模型玩具', '乐器发声', '动漫相关'],
            14 => ['护肤', '洗护', '清洁', '洗浴'],
            11 => ['1段', '2段', '3段', '4段'],
            13 => ['布尿裤', 'M', 'L', 'S'],
            15 => ['奶瓶奶嘴', '餐具'],
            12 => ['辅食'],
        ];

        foreach ($matches as $k => $v) {
            if (in_array($name, $v)) {
                return $k;
            }
            // else {
            //     echo 'Fail to get tagId by category name! category name: ' . $name;
            //     Yii::warning('Fail to get tagId by category name! category name: ' . $name);
            // }
        }

        return '';
    }
}