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

    // static $

    public function __construct()
    {
        $this->requestUserAgent = self::USER_AGENT_MOBILE;
    }


    public function fetchArticle($id)
    {

        $this->switchUserAgentToMobile();
        $request_data = array(
            'f'             => 'iphone', 
            'filtervideo'   => 1,
            's'             => substr(uniqid().time(), 0, 19),
            'imgmode'       => 0
        );
        $url = 'http://api.smzdm.com/v1/youhui/articles/' . $id;
        $rdata = $this->getHttpContent($url, $request_data);
        $rdata = json_decode($rdata, 1);
        if ($rdata['error_code'] == 0) {
            $a = $rdata['data'];
        } else {
            Yii::warning('Fail to fetch article, return: ' . var_export($rdata));
            return array();
        }
        // pass invalid articles
        if (empty($a)
            || !in_array($a['article_category']['ID'], [75, 93, 147])
            || ($a['article_category']['ID']=='147' && strpos($a['article_title'], '儿童')===false)) {
            Yii::info('Find: ' . $a['article_id'] . ', ignore...');
            return array();
        }

        Yii::info('Fetch article: ' . $a['article_id']);

        // add images
        print_r($a);
        foreach($a['article_content_img_list'] as $k => $image_url) {
            $image_url = str_replace('_e600.jpg', '', $image_url);

            $post_image = parent::addRemoteFile($image_url, 'http://www.smzdm.com', '', $a['article_title']);
            Yii::info('Fetch image: ' . $post_image['id']);

            $post_images[] = $post_image;
            $post_images_ids[] = $post_image['id'];
            if ($k == count($a['article_content_img_list']) - 2) {
                break;
            }
        }

        var_dump($post_image);

        return;
        // add post and meta
        $post_content = $this->parseContent($a['article_filter_content'], '<p><a><br><span><h2><strong><b>');
        $post = array(
            'post_author'   => $this->post_author_id,
            'post_content'  => $post_content, // (mixed) The post content. Default empty.
            'post_title'    => $a['article_title'], // (string) The post title. Default empty.
            'post_excerpt'  => '', // (string) The post excerpt. Default empty.
            'post_status'   => 'draft', // (string) The post status. Default 'draft'.
            'post_date'     => date('Y-m-d H:i:s'),
        );
        $meta = array(
            'product_price' => $a['article_price'],
            'product_band'  => $a['article_brand'],
            'product_url'   => $this->replaceUrl($a['article_link']),
            'article_from'  => $a['article_url'],
            'show_attach'   => json_encode($post_images_ids),
        );
        $post_id = parent::addPost($post, $meta);
        if (!$post_id) {
            $this->log();
            return false;
        }
        // set thumbnail
        if (!empty($post_images)){
            parent::setPostThumbnail($post_id, $post_images[0]['id']);
            foreach ($post_images as $k => $v) {
                parent::updatePost(['ID'=>$v['id'], 'post_parent'=>$post_id]);
            }
        }

        // set category
        if ($a['article_category']['ID'] == '93') {
            $category_id = $this->default_toy_category_id;
        } else if ($a['article_category']['ID'] == '147'){
            $category_id = 18;
        } else {
            $category_id = $this->default_category_id;
        }
        parent::setPostCategory($post_id, $category_id);


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



    public function parseContent($content = '', $allowable_tags = '<p><a><br /><span><h2><strong><b><img>')
    {
        if (empty($content)) {
            return '';
        }
        yii::info('Parsing content...');
        // remove javascript
        $content = preg_replace('/<head>(.*)<\/head>/is', '', $content);
        $detail = trim(strip_tags($content, $allowable_tags));
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
                $myurl = self::replaceUrl($url);
                $detail = str_replace($url, $myurl, $detail);
            }
        }

        return $detail;
    }

    public function replaceUrl($url)
    {
        $logstr = 'Replace url: ' . $url;
        $url = str_replace('.com/URL/AC/', '.com/URL/AA/', $url);
        if (array_key_exists($url, $this->url_replace_mapping)) {
            return $this->url_replace_mapping[$url];
        }
        $real = $this->getRealUrl($url);
        $logstr.= ' -> ' . $real;
        // yes, we found the real url, let's replace it with own.
        if ($real != $url) {
            $cps = parent::getCpsUrl($real);
            $redurl = parent::getRedUrl($cps);
        } else {
            $redurl = $url;
        }
        // TODO: maybe we can find it in my redurl. try it!

        $this->url_replace_mapping[$url] = $redurl;
        $this->log($logstr . ' -> ' . $redurl);
        return $redurl;
    }


    public function getRealUrl($url='')
    {
        $this->switchUserAgentPc();
        $rpage = $this->getContent($url);
        $mtimes = preg_match_all('/return p}\(\'(.*)\',\d+,\d+,\'(.*)\'\.split/', $rpage, $m);
        // var_dump($mtimes, $rpage, $m);
        if ($mtimes < 1) {
            // TODO
            $this->log();
            return $url;
        } else {
            $js = self::decodeEval($m[1][0], $m[2][0]);
            echo $js;
            // yiqifa CPS平台
            if (strpos($js, 'http://p.yiqifa.com')) {
                preg_match('/&t=(http:\/\/.*)\';/', $js, $m);
                $real = $m[1];
                // remove last '\'
                if (strpos($real, '\\') == (strlen($real) - 1) ) {
                    $real = substr($real, 0, strlen($real) -1);
                }
            // yhd
            } elseif (strpos($js, 'yhd.com/')) {
                preg_match('/(http:\/\/.*)\?/', $js, $m);
                $real = $m[1];
            // jd
            } elseif (strpos($js, 'union.click.jd.com')) {
                preg_match('/(http:\/\/union.click.jd.*).\\\';/', $js, $m);
                $ua = $this->request_user_agent;
                $this->switchUserAgentPc();
                $jda = $this->getContent($m[1]);
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
                $this->request_user_agent = $ua;
            } else if (strpos($js, 'http://item.jd.com/')) {
                preg_match('/(http:\/\/item.jd.com.*).\\\';/', $js, $m);
                $real = $m[1];
            // amazon.cn
            } else if (strpos($js, 'amazon')) {
                preg_match('/(http:\/\/.*).\\\';/', $js, $m);
                $replacement = [
                    '/t=joyo01y-23/'        => '',
                    '/tag=joyo01y-23/'      => '',
                    '/t=joyo01m0a-23/'      => '',
                    '/tag=joyo01m0a-23/'    => '',
                ];
                $real = preg_replace(array_keys($replacement), '', $m[1]);
            // suning.com
            } else if (strpos($js, 'union.suning.com')) {
                preg_match('/vistURL=(.*).\';/', $js, $m);
                $real = $m[1];
            }

            // TODO
            if (empty($real)) {
                $this->log();
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
}