<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\httpclient\Client;
use yii\helpers\Url;
use yii\helpers\FileHelper;


use app\models\Offer;
use app\models\File;
use app\models\Link;
/**
 * Spider Base Class
 */
class SpiderBase extends \yii\base\Component
{

    const USER_AGENT = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_5) AppleWebKit/600.5.17 (KHTML, like Gecko) Version/7.1.5 Safari/537.85.14';
    const USER_AGENT_MOBILE = '5.4 rv:11 (iPhone; iPhone OS 6.1.2; zh_CN)';

    public $requestUserAgent = '';

    public static $fileExtension = ['jpg', 'jpeg', 'gif', 'png'];
    public static $fileType = ['image/jpeg', 'image/gif', 'image/png'];

    function __construct()
    {
        $this->requestUserAgent = self::USER_AGENT;
    }

    /**
     * add new offer
     * @param array  $offer  
     * @param array  $tagIds [description]
     */
    public function addOffer($newOffer, $tagIds = [])
    {
        $offer = new Offer;
        while ( list($key, $value) = each($newOffer) ) {
            $offer->{$key} = $value;
        }
        
        if ($offer->save()) {
            foreach ($tagIds as $tagId) {
                $offer->link('tags', Tag::findOne($tagId));
            }
            return true;
        } else {
            return false;
        }
    }

    public function addFile()
    {

    }


    public function addRemoteFile($url, $referer = '', $name = '' )
    {

        $tempfile = '/tmp/' . basename($url);
        // get file
        $curlopt = [CURLOPT_REFERER=>$referer];
        $content = $this->getHttpContent($url, '', $curlopt);
        $contentHash = md5($content);
        if ( file_put_contents($tempfile, $content) < 1 ) {
            Yii::warning('Fail to save remote tempfile');
            return false;
        }

        // validate the file extension
        $extension = strtolower(pathinfo($tempfile, PATHINFO_EXTENSION));
        if ( !in_array($extension, static::$fileExtension) ) {
            Yii::warning('Error file extension: ' . $extension);
            return false;
        }

        // validate the file type
        $mimetype = FileHelper::getMimeType($tempfile);
        if ( !in_array($mimetype, static::$fileType) ) {
            Yii::warning('Error file mimetype: ' . $mimetype);
            return false;
        }

        // move to app upload dir and remove tempfile
        // if the file had been uploaded by spider, just read from DB
        $fileModel = File::findOneByMd5($contentHash);
        if ( $fileModel && $fileModel->user_id=='' ) {
            unlink($tempfile);
            return [
                'id'    => $fileModel->id,
                'url'   => Yii::$aliases['@uploadUrl'] . '/' . $fileModel->path,
                'name'  => $fileModel->name,
            ];
        } else {
            $fileModel = new File;
        }
        // yii::info('file model ->'. gettype($fileModel));

        if ( $fileModel->uploadByLocal($tempfile, true) && $fileModel->save() ) {
            return [
                'id'  => $fileModel->id,
                'url' => Yii::$aliases['@uploadUrl'] . '/' . $fileModel->path,
                'name' => $name,
            ];
        } else {
            yii::warning('Fail to upload by local');
            return false;
        }
    }

    public static function addLinkUniq($url, $name = '')
    {
        $slug = Link::generateSlug($url);
        
        $link = Link::findOneBySlug($slug);

        if (!$link) {
            $link = new Link;
            $link->url  = $url;
            $link->name = $name;
            $link->slug = $slug;
            $link->save();
        }

        return [
            'url'      => $link->url,
            'slug'     => $link->slug,
            'shortUrl' => Link::REDIRECT_SLUG_PREFIX . '/' . $link->slug,
        ];
    }

    /**
     * 使用http get方式获取连接地址内容
     * @param  string $url     链接地址
     * @param  string $param   get请求参数
     * @param  array  $curlopt curl扩展设置参数
     * @return string          连接内容
     */
    public function getHttpContent($url, $param='', $curlopt=array())
    {
        if (is_array($param)) {
            $reqData = http_build_query($param);
        }
        $option = array(
            CURLOPT_URL             => $url . ( empty($reqData) ? '' : ('?' . $reqData) ),
            CURLOPT_RETURNTRANSFER  => 1,
            CURLOPT_USERAGENT       => $this->requestUserAgent,
            CURLOPT_COOKIESESSION   => false,
        );
        
        if (!empty($curlopt)) {
            $option = $option + $curlopt;
        }

        // log
        Yii::info('Curl get: ' . $option[CURLOPT_URL]);

        $ch = curl_init();
        curl_setopt_array($ch, $option);
        $returnData = curl_exec($ch);
        if ( $returnData===false ) {
            Yii::warning('Curl error: ' . curl_error($ch));
        }
        $ch = curl_close($ch);

        // log return data
        // Yii::info('Curl get data: ' . var_export($returnData, 1));

        return $returnData;
    }


    /**
     * get the real url from cps url
     * 
     * @param  string $url cps url
     * @return string      return the real url, if cant, return input
     */
    public function getRealUrl($url)
    {
        // yiqifa CPS平台
        if (strpos($url, 'p.yiqifa.com')) {
            $real = static::getQueryValueFromUrl('t', $url);
            // TODO: encoded url
        }
        // duomai CPS
        else if (strpos($url, 'c.duomai.com/track.php')) {
            $real = static::getQueryValueFromUrl('t', $url);
            // TODO: encoded url
        }
        // yhd
        else if (strpos($url, 'click.yhd.com/')) {
            $header = get_headers($url, 1);
            $real = is_array($header['Location']) ? $header['Location'][0] : $header['Location'];
        }
        // jd
        else if (strpos($url, 'union.click.jd.com')) {
            preg_match('/(https?:\/\/union.click.jd.*)/', $url, $m);
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
                } else if (preg_match("/coupon.jd.com/", $redurl, $mmm)) {
                    $real = 'http://' . static::getQueryValueFromUrl('to', $redurl);
                } else {
                    preg_match("/(https?:\/\/.*)\?/", $redurl, $mmm);
                    $real = $mmm[1];
                    Yii::warning('Fail to get jd real url: ' . $redurl);
                }
            }
            $this->requestUserAgent = $ua;
        } else if (strpos($url, 'item.jd.com')) {
            // preg_match('/(https?:\/\/item.jd.com.*)/', $url, $m);
            $real = $url;
        }
        // amazon.cn, amazon.com, and so on..
        else if (strpos($url, 'amazon')) {
            $info = parse_url($url);
            parse_str($info['query'], $params);
            unset($params['t'], $params['tag']);
            $real = $info['scheme'] . '://' . $info['host'] . $info['path'] . http_build_query($params);
        }
        // suning.com
        else if (strpos($url, 'union.suning.com') || strpos($url, 'sucs.suning.com')) {
            $real = static::getQueryValueFromUrl('vistURL', $url);
        }
        // dangdang.com
        else if (strpos($url, 'union.dangdang.com')) {
            $real = static::getQueryValueFromUrl('backurl', $url);
        }
        // m.dangdang.com
        else if (strpos($url, 'm.dangdang.com')) {
            $real = str_replace('&unionid=p-326920m-ACYH93', '', $url);
        }
        // taobao alimama
        // else if (strpos($js, 's.click.taobao.com') || strpos($js, 's.taobao.com')) {
        //     preg_match("/smzdmhref=\\\\'(.*).\';/", $js, $m);
        //     $real = $m[1];
        // }
        // taobao
        // 
        else if (strpos($url, 'taobao.com') || strpos($url, 'tmall.com')) {
            $real = $url;
        }
        else if (strpos($url, '111.com.cn')) {
            $real = static::getQueryValueFromUrl('url', $url);
        }
        // fengyu.com
        else if (strpos($url, 'fengyu.com')) {
            $real = str_replace('_src=smzdm5148', '', $url);
        }
        // kaola.com
        else if (strpos($url, 'cps.kaola.com')) {
            $real = static::getQueryValueFromUrl('targetUrl', $url);
        }
        // haituncun.com
        else if (strpos($url, 'associates.haituncun.com')) {
            $real = static::getQueryValueFromUrl('url', $url);
        }
        // womai.com
        // default 
        else {
            Yii::warning('Fail to get real url, URL: ' . $url);
            $real = $url;
        }

        return $real;
    }

    public static function getQueryValueFromUrl($queryKey, $url)
    {
        parse_str(parse_url($url, PHP_URL_QUERY), $query);
        return $query[$queryKey];
    }
}