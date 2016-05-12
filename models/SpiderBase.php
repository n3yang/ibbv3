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
            'shortUrl' => Link::getSiteShortUrl($link->slug)
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
     * 将商品连接（B2C连接）转换为CPS平台的连接
     * @param  string $url B2C连接
     * @return string      CPS连接
     */
    public static function replaceToCps($url)
    {
        if (strpos($url, 'amazon')) {
            return $url .= '&t=ibaobr-23&tag=ibaobr-23';
        }
        // 检测商品所属商城，并转换对应的CPS平台的连接
        $matches = [
            'kaola.com'             =>  '1737'  ,
            // 'm.jd.com'              =>  '1146'  ,
            // 'www.jd.com'            =>  '61'    ,
            'yixun.com'             =>  '337',
            'm.yhd.com'             =>  '516'   ,
            'yhd.com'               =>  '58'    ,
            'm.dangdang.com'        =>  '468'   ,
            'dangdang.com'          =>  '64'    ,
            'm.gou.com'             =>  '1602'  ,
            'gou.com'               =>  '756'   ,
            'm.muyingzhijia.com'    =>  '897'   ,
            'muyingzhijia.com'      =>  '114'   ,
            'supumall.com'          =>  '927'   ,
            'supuy.com'             =>  '927'   ,
            'lamall.com'            =>  '1731'  ,
            'miyabaobei.com'        =>  '930'   ,
            'ymatou.com'            =>  '1419'  ,
            'suning.com'            =>  '84'    ,
            'm.suning.com'          =>  '501'   ,
            'm.gome.com.cn'         =>  '618'   ,
            'gome.com.cn'           =>  '236'   ,
            'womai.com'             =>  '334'   ,
            'moximoxi.net'          =>  '1728'  ,
            'xiji.com'              =>  '1752'  ,
            'ikjtao.com'            =>  '723'   ,
            'kjt.com'               =>  '1884'  ,
            'jgb.cn'                =>  '1872'  ,
            '111.com.cn'            =>  '256'   ,
            'fengqu.com'            =>  '2058'  ,
            'm.fengqu.com'          =>  '2307'  ,
            'haituncun.com'         =>  '2862'  ,
        ];
        foreach ($matches as $k => $v) {
            if (strpos($url, $k)) {
                $aid = $v;
                break;
            }
        }
        if (empty($aid))
            return $url;
        else
            return 'http://c.duomai.com/track.php?site_id=149193&aid='.$aid.'&euid=&t=' . urlencode($url);
    }

}