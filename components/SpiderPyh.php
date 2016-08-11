<?php

namespace app\components;

use Yii;
use yii\base\Model;
use yii\httpclient\Client;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use PHPHtmlParser\Dom;
use app\components\SpiderBase;
use app\models\Offer;
/**
 * Spider for pyh
 */
class SpiderPyh extends SpiderBase
{

    protected $syncCacheKey = 'SPIDER_PYH_SYNC_STATE'; 

    public $urlReplaceCache = [];

    public $siteUrl = 'http://www.mgpyh.com';
    public $newListApiUrl = 'http://dpapi.mgpyh.com/api/v1/get_more/';
    public $babyListAPiUrl = 'http://dpapi.mgpyh.com/api/v1/category/';
    public $foodListApiUrl = 'http://dpapi.mgpyh.com/api/v1/category/';

    public $fromSite = Offer::SITE_PYH;

    public function __construct()
    {
        // special UA
        $this->requestUserAgent = 'mgpyh/1.1.9 CFNetwork/758.4.3 Darwin/15.5.0';
        $this->requestReferer = 'www.mgpyh.com';

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
        $query['cat_id'] = 20;
        $this->babyListAPiUrl .= '?' . http_build_query($query);
        $query['cat_id'] = 14;
        $this->foodListApiUrl .= '?' . http_build_query($query);
    }


    public function syncArticle()
    {
        Yii::info('Syncing Article...' . __CLASS__);

        $rs = $this->fetchBabyList();

        Yii::info('Syncing Finished. ' . __CLASS__);

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
        return $rs['items'];
    }

    public function fetchBabyList()
    {
        Yii::info('Fetch list from: ' . $this->babyListAPiUrl);
        $items = $this->fetchList($this->babyListAPiUrl);
        if (empty($items)) {
            return false;
        }

        $last = Yii::$app->cache->get($this->syncCacheKey.__FUNCTION__);

        foreach ($items as $r) {
            if ($r['is_top']) {
                continue;
            }
            // echo $r['category'] .'-'. static::getCategoryIdByCategoryName($r['category']) ."\n";
            // echo $r['shop']['name'] .'-'. static::getB2cIdByShopName($r['shop']['name']) ."\n";
            if ($r['id'] > $last['maxId']) {
                $this->fetchArticle($r);
            }
            $maxId = max($maxId, $r['id']);
        }
        $last['maxId'] = $maxId;
        $last['actionTime'] = date('Y-m-d H:i:s');
        Yii::$app->cache->set($this->syncCacheKey.__FUNCTION__, $last);
        Yii::info('Fetch finished. ' . json_encode($last));
    }

    public function fetchFoodList()
    {
        return $this->fetchList($this->foodListApiUrl);
    }


    public function fetchArticle($a)
    {
        Yii::info('Fetch article: ' . $a['post_title']);
        // title
        $title = mb_substr($a['post_title'], 0, mb_strripos($a['post_title'], ' '));
        if (empty($title)) {
            $title = $a['item_name'];
        }
        // price
        $price = $a['price'];
        // quick link
        $link = $this->replaceUrl($a['money_url'], $title);
        $linkId = empty($link['id']) ? null : $link['id'];
        // excerpt
        $excerpt = mb_substr(strip_tags($a['post']), 0, 150);
        // content
        $content = $this->parseContent($a['post'], $a['item_name']);
        // cover
        $cover = $this->addRemoteFile($a['thumbnail'], $a['item_name']);
        $cover = $cover['path'];
        // b2c
        $b2c = static::getB2cIdByShopName($a['shop']['name']);
        // site
        $fromSite = $this->fromSite;
        // fetch from
        $fetchedFrom = $this->siteUrl . $a['post_url'];
        
        // set status
        if (!$b2c || !$linkId) {
            $status = Offer::STATUS_DRAFT;
        } else {
            $status = Offer::STATUS_PUBLISHED;
        }

        $offerDs = [
            'title'         => $title,
            'link_id'       => $linkId,
            'content'       => $content,
            'price'         => $price,
            'site'          => $this->fromSite,
            'b2c'           => $b2c,
            'status'        => $status,
            'excerpt'       => $excerpt,
            'cover'         => $cover,
            'fetched_from'  => $fetchedFrom,
            'category_id'   => static::getCategoryIdByCategoryName($a['category']),
        ];

        // TODO
        $tagId = [];

        $offerId = $this->addOffer($offerDs, $tagId);

        if ($offerId) {
            Yii::info('Fetch article is finished... id: ' . $offerId. ' title: ' . $title);
            return true;
        } else {
            return false;
        }

    }

    public function parseContent($content, $articleTitle)
    {
        Yii::info('Parsing content...');

        $content = strip_tags($content, '<p><a><br /><span><h2><strong><b><img>');

        $dom = new Dom;
        $dom->load($content);
        $aTags = $dom->find('a');
        foreach ($aTags as $a) {
            $url = $a->getAttribute('href');
            if (!preg_match('/goods\/\w+/', $url)){
                $a->setAttribute('href', '#');
            } else {
                $title = strip_tags($a->innerHtml());
                $link = self::replaceUrl($url, $title);
                $myurl = $link['shortUrl'];
                $a->setAttribute('href', $myurl);
            }
        }
        $imgTags = $dom->find('img');
        foreach ($imgTags as $i => $img) {
            if ($i>2) {
                $img->delete();
            }
            $src = $img->getAttribute('src');
            // fetch and replace
            if (!Url::isRelative($src)) {
                $rs = $this->addRemoteFile($src, $articleTitle, [600, 400]);
                if (!empty($rs['url'])) {
                    $img->setAttribute('src', $rs['url']);
                    $img->setAttribute('alt', $articleTitle);
                    $img->removeAttribute('width');
                    $img->removeAttribute('height');
                }
            }
        }
        $content = $dom->outerHtml;

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
        } else {
            $link = [];
        }
        // TODO: maybe we can find it in my shortUrl. try it!

        $this->urlReplaceCache[$url] = $link;
        
        Yii::info($logstr . ' -> ' . $link['shortUrl']);

        return $link;
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

    public function addRemoteFile($src, $name = '', $size = [])
    {
        $src = str_replace('!focus', '', $src);
        $src = str_replace('!show', '', $src);
        return parent::addRemoteFile($src, $name, $size);
    }

    public static function getCategoryIdByCategoryName($name)
    {
        $matches = [
            11 => ['1段', '2段', '3段', '4段', '特配奶粉'],
            12 => ['辅食', '肉松', '孕产奶粉', '成人奶粉', '米粉汤粥', 'DHA', '钙铁锌/维生素',
                '清火开胃', '果泥/果汁', '益生菌/初乳', '宝宝零食'],
            13 => ['布尿裤','XXXL', 'XXL', 'XL', 'M', 'L', 'S', 'NB/S', '婴儿尿裤', '婴儿湿巾'],
            14 => ['护肤', '洗护', '清洁', '洗浴', '日常护理', '宝宝护肤', '驱蚊防蚊'],
            15 => ['奶瓶奶嘴', '餐具', '碗盘叉勺', '水壶/水杯'],
            17 => ['LEGO积木拼插', '健身玩具', '益智玩具', '毛绒布艺', '毛绒/布艺', '模型玩具', 
                '乐器发声', '动漫相关', '高达/模型/机器人', '轨道/惯性玩具', '毛绒/布艺', '手办/人偶/BJD/摆件',
                '电子/发光玩具', '遥控车/飞机/船', '床铃/摇铃', '爬行毯/垫', '创意/减压', '芭比娃娃',
                '立体拼插', '磁力玩具', '早教教具', '机器人', '车模/航模/建筑膜', '积木', '玩沙玩具', '节日聚会'],
            18 => ['婴儿推车', '餐椅摇椅', '家居家装', '伞车', '婴儿床', '手工彩泥', '儿童箱包'],
            19 => ['童鞋', '童装', '内外服饰', '运动户外', '运动鞋', '套装', '舞蹈鞋', '靴子', '外套', '休闲鞋', '凉鞋'],
            20 => ['安全座椅'],
            21 => ['儿童监护', '吸乳器', '产前产后', '产后塑身', '妈咪包/背婴带', '文胸/内裤', '孕妈护肤'],
            22 => ['图书'],
            23 => [],
            24 => ['辅食料理机'],
        ];

        foreach ($matches as $k => $v) {
            if (in_array($name, $v)) {
                return $k;
            }
        }
        // not found
        Yii::warning('Fail to get CategoryId by category name! category name: ' . $name);

        // not category
        return 10;
    }
}