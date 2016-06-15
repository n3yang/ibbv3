<?php

namespace app\components;

use Yii;
use yii\base\Model;
use yii\httpclient\Client;
use yii\helpers\ArrayHelper;
use PHPHtmlParser\Dom;
use app\components\SpiderBase;
use app\models\Offer;


/**
 * Spider for zdm
 */
class SpiderZdm extends SpiderBase
{

    protected $syncCacheKey = 'SPIDER_ZDM_SYNC_STATE'; 
    /**
     * Valid Category Ids ( root category )
     * 75   母婴用品
     * 93   玩具乐器
     * 147  汽车用品
     * 57   服装鞋帽
     * 95   食品保健 =========
     * 
     * @var array
     */
    public static $validCategoryIds = ['75', '93', '147', '57'];

    public $urlReplaceCache = [];

    public $dataList = [];
    public $dataArticle = [];

    public $fetchListUrl = 'aHR0cHM6Ly9hcGkuc216ZG0uY29tL3YxL3lvdWh1aS9hcnRpY2xlcw==';
    public $fetchArticleUrl = 'aHR0cHM6Ly9hcGkuc216ZG0uY29tL3YxL3lvdWh1aS9hcnRpY2xlcy8=';

    public $fromSite = Offer::SITE_ZDM;

    public function __construct()
    {
        $this->requestUserAgent = self::USER_AGENT_MOBILE;
        $this->requestReferer = 'www.smzdm.com';
        $this->fetchListUrl = base64_decode($this->fetchListUrl);
        $this->fetchArticleUrl = base64_decode($this->fetchArticleUrl);
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




    public function fetchArticle($id)
    {

        Yii::info('Fetch article: ' . $id);

        $this->switchUserAgentToMobile();
        // build request data & get result
        $reqData = array(
            'f'             => 'iphone', 
            'filtervideo'   => 1,
            's'             => substr(uniqid().time(), 0, 19),
            'imgmode'       => 0
        );
        $url = $this->fetchArticleUrl . $id;
        $rdata = $this->getHttpContent($url, $reqData);
        $rdata = json_decode($rdata, 1);
        
        // get error ? log it.
        if ($rdata['error_code'] != 0) {
            Yii::warning('Fail to fetch article, return: ' . var_export($rdata, true));
            return array();
        } else {
            $a = $rdata['data'];
            $this->dataArticle[$id] = $a;
            $newOffer['fetched_from'] = $url . '?' . http_build_query($reqData);
        }

        // print_r($a);
        // pass invalid articles
        if ( !static::isValidArticle($a) ) {
            Yii::info('Find invalid article: ' . $a['article_id'] . ', ' . $a['article_title']);
            return array();
        }
        Yii::info('Find: ' . $a['article_id'] . ', ' . $a['article_title']);

        // add images
        // nothing in new API, reomove it ?
        /*
        foreach($a['article_content_img_list'] as $k => $image_url) {
            $image_url = str_replace('_e600.jpg', '', $image_url);

            $post_image = parent::addRemoteFile($image_url, $a['article_title']);
            Yii::info('Fetch image: ' . $post_image['id']);

            $post_images[] = $post_image;
            $post_images_ids[] = $post_image['id'];
            if ($k == count($a['article_content_img_list']) - 2) {
                break;
            }
        }
        */

        // set quick link
        $quickLink = $this->replaceUrl($a['article_link'], $a['article_title']);
        preg_match("/([\w]+)$/", $quickLink, $matches);
        $newOffer['link_slug'] = $matches[1];

        // the content
        $post_content = $this->parseContent($a['article_filter_content'], '<p><a><br><span><h2><strong><b>');

        // b2c id
        $b2c = self::convertMallId($a['article_mall_id']);
        if (!$b2c) {
            Yii::warning('Fail to convert mall: ' . $a['article_mall']);
        }
        
        // fetch thumbnail
        $thumbnail = $this->addRemoteFile($a['article_pic'], $a['article_title']);
        $newOffer['thumb_file_id'] = $thumbnail['id'];

        // set status
        if (!$b2c || !$newOffer['link_slug'] || !$thumbnail['id']) {
            $status = Offer::STATUS_DRAFT;
        } else {
            $status = Offer::STATUS_PUBLISHED;
        }
        // set property
        $newOffer['title']      = $a['article_title'];
        $newOffer['content']    = $post_content;
        $newOffer['price']      = $a['article_price'];
        $newOffer['site']       = $this->fromSite;
        $newOffer['b2c']        = $b2c;
        $newOffer['status']     = $status;
        $newOffer['excerpt']    = !empty($this->dataList[$id])
                                    ? $this->parseContent($this->dataList[$id]['article_filter_content'])
                                    : '';


        // get category
        // $aCategoryId = isset($a['article_category_list'][1]['ID']) ? $a['article_category_list'][1]['ID'] : $a['article_category_list'][0]['ID'];
        $categoryId = self::convertCategoryId($a['article_category_list']);
        if (!$categoryId) {
            Yii::warning('Fail to convert category id: ' . $a['article_category']['ID'] . ', name: ' . $a['article_category']['title']);
            Yii::warning('Fail to convert category list: ' . var_export($a['article_category_list'], 1));
            // not categorized, parse from article title
            $categoryId = parent::getCategoryIdByOfferTitle($a['article_title']);
            if (!$categoryId) {
                Yii::warning('Fail to convert category list by offer title. ');
                $categoryId = 10;
            }
        }
        $newOffer['category_id'] = $categoryId;

        // TODO
        $tagId = [];

        $offerId = $this->addOffer($newOffer, $tagId);
        Yii::info('Fetch article is finished... id: ' . $offerId. ' title: ' . $a['article_title']); 

        return $a;
    }

    /**
     * is Valid Article ?
     * @param  array  $article The article row
     * @return boolean         yes or no
     */
    public static function isValidArticle($article)
    {
        if ( empty($article) ) {
            return false;
        }

        // valid by keywords
        $keywords = ['儿童', '幼儿', '婴儿', '宝宝', '男宝', '女宝'];
        foreach ($keywords as $word) {
            if (strpos($article['article_title'], $word)!==false
                AND $article['link_title']!='白菜党') {
                
                return true;
            }
        }

        // valid by category id
        $categoryId = $article['article_category']['ID'];
        if ( !in_array($categoryId, self::$validCategoryIds) ) {
            return false;
        }
        // 147-安全座椅 57-服装鞋帽
        if ( in_array($categoryId, ['57', '147'])
            && strpos($article['article_title'], '儿童')===false ) {
            return false;
        }
        // 食品保健
        if ( in_array($categoryId, ['95']) 
            && strpos($article['article_title'], '酒')!==false ) {
            return false;
        }

        return true;
    }

    /**
     * 获取文章列表
     * 
     * @param  string  $category     目录ID
     * @param  integer $limit        获取条数
     * @param  string  $type         类型，youhui or faxian
     * @param  string  $date         开始时间，空为最新
     * @return array                 array
     */
    public function fetchList($category = '', $limit = 20, $date = '')
    {
        Yii::info('Fetch list... ');

        $this->switchUserAgentToMobile();
        $reqData = array(
            'f'         => 'iphone', 
            's'         => substr(uniqid().time(), 0, 19),
            'limit'     => $limit,
            'imgmode'   => 0
        );
        if (!empty($category)) {
            $reqData['category'] = $category;
        }
        if (!empty($date)) {
            $reqData['article_date'] = $date;
        }

        $url = $this->fetchListUrl;
        $rdata = $this->getHttpContent($url, $reqData);
        $rdata = json_decode($rdata, 1);

        if ($rdata['error_code'] == 0) {
            Yii::info('Fetch ' . count($rdata['data']['rows']) . ' rows');
            $this->dataList = ArrayHelper::index($rdata['data']['rows'], 'article_id');
            return $this->dataList;
        } else {
            // log warning
            Yii::warning('Fail to fetch list. API return: ' . var_export($rdata, 1));
            return array();
        }
    }



    public function parseContent($content = '', $allowedTags = '<p><a><br /><span><h2><strong><b><img>')
    {
        if (empty($content)) {
            return '';
        }
        Yii::info('Parsing content...');
        // remove javascript
        $content = preg_replace('/<head>(.*)<\/head>/is', '', $content);
        // remove not allowed tags
        $detail = trim(strip_tags($content, $allowedTags));
        // replace some text
        $detail = str_replace('值友', '网友', $detail);

        // get all tag A (link), and replace it to my short link (cps link)
        $dom = new Dom;
        $dom->load($detail);
        $aTags = $dom->find('a');
        foreach ($aTags as $a) {
            $url = $a->getAttribute('href');
            if (empty($url)) {
                continue;
            }
            if (strpos($url, 'zdm.com/p/')){
                $a->setAttribute('href', '#');
            } else {
                $title = strip_tags($a->innerHtml());
                $myurl = self::replaceUrl($url, $title);
                $a->setAttribute('href', $myurl);
            }
        }

        $detail = $dom->outerHtml;

        return $detail;
    }

    public function replaceUrl($url, $title = '')
    {
        $logstr = 'Replace url: ' . $url;
        // $url = str_replace('.com/URL/AC/', '.com/URL/AA/', $url);
        $url = str_replace('AC_YH', 'AA_YH', $url);

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
        // pass all posts;
        if (preg_match('/zdm.com\/p\/\d+/', $url)) {
            return $url;
        }

        $this->switchUserAgentToPc();
        $rpage = $this->getHttpContent($url);
        $mtimes = preg_match_all('/return p}\(\'(.*)\',\d+,\d+,\'(.*)\'\.split/', $rpage, $m);
        // var_dump($mtimes, $rpage, $m);
        if ($mtimes < 1) {
            // TODO
            // $this->log();
            Yii::warning('Fail to get encoded Javascript: ');
            return $url;
        } else {
            $js = self::decodeEval($m[1][0], $m[2][0]);
            // echo $js;
            $pattern = "/zdmhref=\\\'(.*)\\\';ga/";
            if (preg_match($pattern, $js, $matches)) {
                // print_r($matches);
                $real = parent::getRealUrl($matches[1]);
            } else {
                Yii::warning('Fail to get real url, JS: ' . $js);
                $real = $url;
            }
            
            return $real;
        }
    }

    public static function decodeEvalKey($c){
        return ($c<62 ? '' : self::decodeEvalKey(intval($c/62))) . (($c=$c%62)>35 ? chr($c+29) : base_convert($c, 10, 36));
    }
    public static function decodeEval($js, $word, $c=300){
        $word = explode('|', $word);
        while($c--){
            if ($word[$c]){
                $js = preg_replace("/\\b".self::decodeEvalKey($c)."\\b/", $word[$c], $js);
            }
        }
        return $js;
    }
    public function switchUserAgentToMobile()
    {
        $this->requestUserAgent = self::USER_AGENT_MOBILE;
    }

    public function switchUserAgentToPc()
    {
        $this->requestUserAgent = self::USER_AGENT;
    }

    public static function convertMallId($mallId='')
    {
        $mapping = [
            '153'   => Offer::B2C_DANGDANG,
            '269'   => Offer::B2C_AMAZON_CN,
            '4033'  => Offer::B2C_AMAZON_BB,
            '41'    => Offer::B2C_AMAZON_US,
            '271'   => Offer::B2C_AMAZON_JP,
            '279'   => Offer::B2C_AMAZON_UK,
            '183'   => Offer::B2C_JD,
            '3949'  => Offer::B2C_JD,
            '247'   => Offer::B2C_TMALL,
            '2537'  => Offer::B2C_TMALL_CS,
            '43'    => Offer::B2C_YHD,
            '239'   => Offer::B2C_SUNING,
            '4031'  => Offer::B2C_SUNING,
            '241'   => Offer::B2C_TAOBAO_JHS,
            '487'   => Offer::B2C_1IYAOWANG,
            '219'   => Offer::B2C_MUYINGZHIJIA,
            '3467'  => Offer::B2C_FENGQUHAITAO,
            '3981'  => Offer::B2C_KAOLA,
            '261'   => Offer::B2C_WOMAI,
            '691'   => Offer::B2C_SUPUY,
            '4039'  => Offer::B2C_SUPUY,
            '2897'  => Offer::B2C_TMALL_GJ,
            '4265'  => Offer::B2C_MIYA,
        ];
        if (!isset($mapping[$mallId])) {
            Yii::warning('Fail to convert mall id: ' . $mallId);
            return '';
        } else {
            return $mapping[$mallId];
        }
    }

    public static function convertCategoryId($categoryList = [])
    {

        $mapping = [
            '11'    => [77, 827], // 奶粉牛奶
            '12'    => [95, 97, 79], // 营养辅食
            '13'    => [81, ], // 尿裤湿巾
            '14'    => [85, 129, 1515, 747, 1141, 4275, 4261, 1565, 117, 113], // 洗护用品
            '15'    => [83, 1491], // 喂养用品
            '16'    => [1967, 2081, 4373, 53], // 家纺服饰
            '17'    => [981, 2067, 93, 1319, 251, 3213, 163, 5067, 3957], // 玩具乐器
            '18'    => [87, 1965, 937, 49, 51,], // 童车童床
            '19'    => [67, 57, 89, 955, 603], // 童装童鞋
            '20'    => [527], // 安全座椅
            '21'    => [967, 91], // 妈妈用品
            '22'    => [7, 9, 3099], // 图书影音
            '23'    => [111, 105, 107, 773, 1635], // 美食生鲜
            '24'    => [27, 35], // 家用电器
        ];

        $myCategoryId = null;
        for ($i=count($categoryList); $i > 0; $i--) { 
            $categoryId = $categoryList[$i-1]['ID'];
            foreach ($mapping as $k => $m) {
                if (in_array($categoryId, $m)) {
                    $myCategoryId = $k;
                    break;
                }
            }
            if (!empty($myCategoryId)) {
                break;
            }
        }

        return $myCategoryId;
    }
}


// 77    奶粉       
// 81    尿裤湿巾    
// 83    喂养用品    
// 981   玩具       
// 2067  婴儿玩具    
// 93    玩具       
// 97    保健品     
// 79    营养辅食    
// 85    洗护用品    
// 129   口腔护理    
// 75    母婴用品    
// 87    童车童床    
// 1965  婴儿家居安全
// 937   婴儿推车    
// 91    孕产妇用品  
// 7     图书音像    
// 9     电子书刊    
// 3099  少儿           
// 147    
// 57     
// 95    食品保健
// 1515  日用百货
// 7     图书影音
// 111   生鲜食品
// 105   粮油调味
// 35   个护健康
// 27   家用电器
// 1319 电吹风
// 967  妈妈护理
// 747  牙膏
// 129  口腔护理
// 937  婴儿推车
// 1141 衣物清洁
// 4275 洗衣皂
// 4261 洗衣液
// 1967 婴儿家纺
// 2081 婴儿枕头
// 4373 家居饰品
// 2447 仿真模型
// 985  模型
// 67   童装
// 251  运动器材
// 1565 美发护发
// 49   住宅家具
// 827  牛奶
// 3213 电子教育
// 603  儿童书包
// 51   灯具灯饰
// 107  饮料
// 1635 休闲食品
// 113  个护化妆
// 163  电脑数码
// 5067 泳衣
// 1491 水具酒具
// 3957 儿童表
