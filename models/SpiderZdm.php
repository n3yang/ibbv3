<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\httpclient\Client;
// use yii\httpclient\
use app\models\SpiderBase;

/**
 * Spider for zdm
 */
class SpiderZdm extends SpiderBase
{

    /**
     * Valid Category Ids
     * @var array
     */
    public static $validCategoryIds = ['75', '93', '147'];

    public $urlReplaceCache = [];

    public function __construct()
    {
        $this->requestUserAgent = self::USER_AGENT_MOBILE;
    }


    public function fetchArticle($id)
    {

        Yii::info('Fetch article: ' . $a['article_id']);

        $this->switchUserAgentToMobile();
        // build request data & get result
        $reqData = array(
            'f'             => 'iphone', 
            'filtervideo'   => 1,
            's'             => substr(uniqid().time(), 0, 19),
            'imgmode'       => 0
        );
        $url = 'http://api.smzdm.com/v1/youhui/articles/' . $id;
        $rdata = $this->getHttpContent($url, $reqData);
        $rdata = json_decode($rdata, 1);
        
        // get error ? log it.
        if ($rdata['error_code'] != 0) {
            Yii::warning('Fail to fetch article, return: ' . var_export($rdata, true));
            return array();
        } else {
            $a = $rdata['data'];
            $newOffer['fetched_from'] = $url . '?' . http_build_query($reqData);
        }

        // pass invalid articles
        if ( !static::isValidArticle($a) ) {
            Yii::info('Find: ' . $a['article_id'] . ', ignore...');
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

        // set property
        $newOffer['title']      = $a['article_title'];
        $newOffer['content']    = $post_content;
        $newOffer['price']      = $a['article_price'];
        $newOffer['site']       = Offer::SITE_ZDM;
        $newOffer['b2c']        = '';
        $newOffer['status']     = Offer::STATUS_DRAFT;

        // fetch thumbnail
        $thumbnail = parent::addRemoteFile($a['article_pic'], 'http://www.smzdm.com', $a['article_title']);
        $newOffer['thumb_file_id'] = $thumbnail['id'];


        if (!parent::addOffer($newOffer)) {
            Yii::warning('Fail to save new offer. offer: ' . var_export($newOffer, 1) );
            return false;
        }


        $offerModel = new Offer;
        while ( list($key, $value) = each($newOffer) ) {
            $offerModel->{$key} = $value;
        }
        
        $offerModel->save();



        // set category
        if ($a['article_category']['ID'] == '93') {
            $tagId = '17';
        } else if ($a['article_category']['ID'] == '147'){
            $tagId = '20';
        } else {
            $tagId = '';
        }

        if ($tagId) {
            $offerModel->link('tags', Tag::findOne($tagId));
        }

        return ;

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
        if ( !in_array($article['article_category']['ID'], self::$validCategoryIds) ) {
            return false;
        }
        if ( $article['article_category']['ID']=='147' && strpos($article['article_title'], '儿童')===false ) {
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
    public function fetchList($category = '', $limit = 20, $type = 'youhui', $date = '')
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

        $url = 'http://api.smzdm.com/v1/' . $type . '/articles';
        $rdata = $this->getHttpContent($url, $reqData);
        $rdata = json_decode($rdata, 1);

        if ($rdata['error_code'] == 0) {
            Yii::info('Fetch ' . count($rdata['data']['rows']) . ' rows');
            return $rdata['data']['rows'];
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
            if (strpos($url, 'http://www.smzdm.com/p/')===0){
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
            if (strpos($js, 'http://p.yiqifa.com')) {
                preg_match('/&t=(http:\/\/.*)\';/', $js, $m);
                $real = $m[1];
                // remove last '\'
                if (strpos($real, '\\') == (strlen($real) - 1) ) {
                    $real = substr($real, 0, strlen($real) -1);
                }
            }
            // yhd
            else if (strpos($js, 'yhd.com/')) {
                preg_match('/(http:\/\/.*)\?/', $js, $m);
                $real = $m[1];
            }
            // jd
            else if (strpos($js, 'union.click.jd.com')) {
                preg_match('/(http:\/\/union.click.jd.*).\\\';/', $js, $m);
                $ua = $this->requestUserAgent;
                $this->switchUserAgentToPc();
                $jda = $this->getHttpContent($m[1]);
                preg_match('/hrl=\'(.*).\' ;/', $jda, $mm);
                if (!empty($mm[1])) {
                        $header = get_headers($mm[1], 1);
                        $redurl = is_array($header['Location']) ? $header['Location'][0] : $header['Location'];
                        preg_match('/(http:\/\/.*).\?/', $redurl, $mmm);
                        $replacement = [
                            '/re./'             => '',
                            '/\/cps\/item/'     => '',
                            '/http:\/\/jd/'     => 'http://item.jd',
                            '/.htm$/'           => '.html',
                        ];
                        $real = preg_replace(array_keys($replacement), array_values($replacement), $mmm[1]);
                }
                $this->requestUserAgent = $ua;
            } else if (strpos($js, 'http://item.jd.com/')) {
                preg_match('/(http:\/\/item.jd.com.*).\\\';/', $js, $m);
                $real = $m[1];
            }
            // amazon.cn
            else if (strpos($js, 'amazon')) {
                preg_match('/(http:\/\/.*).\\\';/', $js, $m);
                $replacement = [
                    '/t=joyo01y-23/'        => '',
                    '/tag=joyo01y-23/'      => '',
                    '/t=joyo01m0a-23/'      => '',
                    '/tag=joyo01m0a-23/'    => '',
                ];
                $real = preg_replace(array_keys($replacement), '', $m[1]);
            }
            // suning.com
            else if (strpos($js, 'union.suning.com')) {
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

    public static function convertB2cId($id='')
    {
        return [
            '153'   => Offer::B2C_DANGDANG
        ];
    }
}