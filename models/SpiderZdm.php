<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\httpclient\Client;
use yii\helpers\ArrayHelper;
// use yii\httpclient\
use app\models\SpiderBase;

/**
 * Spider for zdm
 */
class SpiderZdm extends SpiderBase
{

    protected $syncCacheKey = 'SPIDER_ZDM_SYNC_STATE'; 
    /**
     * Valid Category Ids
     * 75   营养辅食
     * 93   玩具乐器
     * 147  安全座椅
     * 57   服装鞋帽
     * 95   食品保健 =========
     * 
     * @var array
     */
    public static $validCategoryIds = ['75', '93', '147', '57'];

    public $urlReplaceCache = [];

    public $dataList = [];
    public $dataArticle = [];

    public $fetchListUrl = 'https://api.smzdm.com/v1/youhui/articles';
    public $fetchArticleUrl = 'https://api.smzdm.com/v1/youhui/articles/';

    public $fromSite = Offer::SITE_ZDM;

    public function __construct()
    {
        $this->requestUserAgent = self::USER_AGENT_MOBILE;
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
            Yii::info('Find: ' . $a['article_id'] . ', ' . $a['article_title'] . ', ignore...');
            return array();
        }

        // add images
        // nothing in new API, reomove it ?
        foreach($a['article_content_img_list'] as $k => $image_url) {
            $image_url = str_replace('_e600.jpg', '', $image_url);

            $post_image = parent::addRemoteFile($image_url, 'http://www.smzdm.com', $a['article_title']);
            Yii::info('Fetch image: ' . $post_image['id']);

            $post_images[] = $post_image;
            $post_images_ids[] = $post_image['id'];
            if ($k == count($a['article_content_img_list']) - 2) {
                break;
            }
        }

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
        // set property
        $newOffer['title']      = $a['article_title'];
        $newOffer['content']    = $post_content;
        $newOffer['price']      = $a['article_price'];
        $newOffer['site']       = $this->fromSite;
        $newOffer['b2c']        = $b2c;
        $newOffer['status']     = empty($newOffer['link_slug']) ? Offer::STATUS_DRAFT : Offer::STATUS_PUBLISHED;
        $newOffer['excerpt']    = !empty($this->dataList[$id]) ? $this->dataList[$id]['article_filter_content'] : '';

        // fetch thumbnail
        $thumbnail = $this->addRemoteFile($a['article_pic'], 'http://www.smzdm.com', $a['article_title']);
        $newOffer['thumb_file_id'] = $thumbnail['id'];

        // get category
        $tagId = self::convertCategoryId($a['article_category']['ID']);
        if ($tagId) {
            $this->addOffer($newOffer, [$tagId]);
        } else {
            Yii::warning('Fail to convert category id: ' . $a['article_category']['ID'] . ', name: ' . $a['article_category']['title']);
        }

        return true;
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
        $keywords = ['儿童', '幼儿', '婴儿'];
        foreach ($keywords as $word) {
            if (strpos($article['article_title'], $word)!==false) 
                return true;
        }

        // valid by category id
        $categoryId = $article['article_category']['ID'];
        if ( !in_array($categoryId, self::$validCategoryIds) ) {
            return false;
        }
        if ( in_array($categoryId, ['147', '57']) 
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

        // get all tag A (link), and replace it to my short link (cps link)
        $doc = new \DOMDocument();
        @$doc->loadHTML($detail);
        $tags = $doc->getElementsByTagName('a');
        foreach ($tags as $tag) {
            $url = $tag->getAttribute('href');
            if (empty($url)) {
                continue;
            }
            if (strpos($url, 'smzdm.com/p/')===0){
                $detail = str_replace($url, '#', $detail);
            } else {
                // find the real url
                $myurl = self::replaceUrl($url);
                // in content
                $detail = str_replace($url, $myurl, $detail);
            }
        }

        // replace some text
        $detail = str_replace('值友', '网友', $detail);

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
            $cps = parent::replaceToCps($real);
            // create new short url
            $link = parent::addLinkUniq($cps, $title);
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
            // yiqifa CPS平台
            if (strpos($js, 'p.yiqifa.com')) {
                preg_match('/&t=(https?:\/\/.*)\';/', $js, $m);
                $real = $m[1];
                // remove last '\'
                if (strpos($real, '\\') == (strlen($real) - 1) ) {
                    $real = substr($real, 0, strlen($real) -1);
                }
            }
            // yhd
            else if (strpos($js, 'yhd.com/')) {
                preg_match('/(https?:\/\/.*)\?/', $js, $m);
                $real = $m[1];
            }
            // jd
            else if (strpos($js, 'union.click.jd.com')) {
                preg_match('/(https?:\/\/union.click.jd.*).\\\';/', $js, $m);
                $ua = $this->requestUserAgent;
                $this->switchUserAgentToPc();
                $jda = $this->getHttpContent($m[1]);
                preg_match('/hrl=\'(.*).\' ;/', $jda, $mm);
                if (!empty($mm[1])) {
                        $header = get_headers($mm[1], 1);
                        $redurl = is_array($header['Location']) ? $header['Location'][0] : $header['Location'];
                        if (preg_match("/re.jd.com\/cps\/item\/(.*)\?/", $redurl, $mmm)) {
                            $real = 'http://item.jd.com/' . $mmm[1];
                        } else if (preg_match("/(red.jd.com\/.*)\?/", $redurl, $mmm)) {
                            $real = 'http://' . $mmm[1];
                        } else if (preg_match("/re.m.jd.com\/cps\/item\/(.*)\?/", $redurl, $mmm)) {
                            $real = 'http://item.m.jd.com/product/' . $mmm[1];
                        } else {
                            preg_match("/(https?:\/\/.*)\?/", $redurl, $mmm);
                            $real = $mmm[1];
                            Yii::warning('Fail to get jd real url: ' . $redurl);
                        }
                }
                $this->requestUserAgent = $ua;
            } else if (strpos($js, 'item.jd.com')) {
                preg_match('/(https?:\/\/item.jd.com.*).\\\';/', $js, $m);
                $real = $m[1];
            }
            // amazon.cn, amazon.com, and so on..
            else if (strpos($js, 'amazon')) {
                preg_match("/(https?:\/\/.*).\\\';/", $js, $m);
                $info = parse_url($m[1]);
                parse_str($info['query'], $params);
                unset($params['t'], $params['tag']);
                $real = $info['scheme'] . '://' . $info['host'] . $info['path'] . http_build_query($params);
            }
            // suning.com
            else if (strpos($js, 'union.suning.com') || strpos($js, 'sucs.suning.com')) {
                preg_match('/vistURL=(.*).\';/', $js, $m);
                $real = $m[1];
            }
            // dangdang.com
            else if (strpos($js, 'union.dangdang.com')) {
                preg_match('/backurl=(.*).\';/', $js, $m);
                $real = urldecode($m[1]);   
            }
            // m.dangdang.com
            else if (strpos($js, 'm.dangdang.com')) {
                preg_match("/smzdmhref=\\\\'(.*).\';/", $js, $m);
                $real = str_replace('&unionid=p-326920m-ACYH93', '', $m[1]);
            }
            // taobao
            else if (strpos($js, 's.click.taobao.com') || strpos($js, 's.taobao.com')) {
                preg_match("/smzdmhref=\\\\'(.*).\';/", $js, $m);
                $real = $m[1];
            }
            // taobao juhuasuan
            else if (strpos($js, 'detail.ju.taobao.com') || strpos($js, 'tmall.com')) {
                preg_match("/smzdmhref=\\\\'(.*).\';/", $js, $m);
                $real = $m[1];
            }
            else if (strpos($js, '111.com.cn')) {
                preg_match("/smzdmhref=\\\\'(.*)url=(.*).\';/", $js, $m);
                $real = $m[2];
            }
            // fengyu.com
            else if (strpos($js, 'fengyu.com')) {
                preg_match("/smzdmhref=\\\\'(.*).\';/", $js, $m);
                $real = str_replace('_src=smzdm5148', '', $m[1]);
            }
            // kaola.com
            else if (strpos($js, 'cps.kaola.com')) {
                preg_match("/smzdmhref=\\\\'(.*).\';/", $js, $m);
                $info = parse_url($m[1]);
                parse_str($info['query'], $params);
                $real = $params['targetUrl'];
            }
            // haituncun.com
            else if (strpos($js, 'associates.haituncun.com')) {
                preg_match("/smzdmhref=\\\\'(.*).\';/", $js, $m);
                $info = parse_url($m[1]);
                parse_str($info['query'], $params);
                $real = $params['url'];
            }
            // default 
            else {
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
            '271'   => Offer::B2C_AMAZON_JP,
            '183'   => Offer::B2C_JD,
            '3949'  => Offer::B2C_JD,
            '247'   => Offer::B2C_TMALL,
            '43'    => Offer::B2C_YHD,
            '239'   => Offer::B2C_SUNING,
            '241'   => Offer::B2C_TAOBAO_JHS,
            '487'   => Offer::B2C_1IYAOWANG,
            '219'   => Offer::B2C_MUYINGZHIJIA,
            '3467'  => Offer::B2C_FENGQUHAITAO,
            '3981'  => Offer::B2C_KAOLA,

        ];
        if (!isset($mapping[$mallId])) {
            Yii::warning('Fail to convert mall id: ' . $mallId);
            return '';
        } else {
            return $mapping[$mallId];
        }
    }

    public static function convertCategoryId($categoryId='')
    {
        $mapping = [
            '93'    => '17',
            '75'    => '12',
            '147'   => '20',
            '57'    => '19',
            '95'    => '12',
            '1515'  => '14', // 日用百货
        ];
        if (!isset($mapping[$categoryId])) {
            return '';
        } else {
            return $mapping[$categoryId];
        }
    }
}