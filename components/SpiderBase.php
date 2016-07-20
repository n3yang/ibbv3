<?php

namespace app\components;

use Yii;
use yii\base\Model;
use yii\httpclient\Client;
use yii\helpers\Url;
use yii\helpers\FileHelper;
use Imagine\Image\Box;
use Imagine\Image\Point;

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
    public $requestReferer = '';

    public $fileTempDir = '/tmp';
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
                if ($tagId) {
                    $offer->link('tags', Tag::findOne($tagId));
                }
            }
        }
        
        return $offer->id;
    }

    public function addFile()
    {

    }


    public function addRemoteFile($url, $name = '', $widthHeight = [0, 0])
    {

        $tempfile = $this->fileTempDir . '/' . basename($url);
        // get file
        $curlopt = [ CURLOPT_REFERER => $this->requestReferer ];
        $content = $this->getHttpContent($url, '', $curlopt);
        
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

        // reisize image
        if (empty($widthHeight[0]) && empty($widthHeight[1])) {
            $width = Yii::$app->params['thumbnailSize']['width'];
            $height = Yii::$app->params['thumbnailSize']['height'];
        } else {
            $width = $widthHeight[0];
            $height = $widthHeight[1];
        }
        $newFileName = base_convert(uniqid(), 16, 32) . '.jpg';
        $newFile = $this->fileTempDir . '/' . $newFileName;
        
        if (class_exists('Imagick')) {
            $imagine = new \Imagine\Imagick\Imagine;
        } else {
            $imagine = new \Imagine\Gd\Imagine;
        }
        $imagine->open($tempfile)
                ->thumbnail(new Box($width, $height))
                ->save($newFile, ['quality' => 90]);
        unlink($tempfile);

        // move to app upload dir and remove tempfile
        // if the file had been uploaded by spider, just read from DB
        $fileHash = md5_file($newFile);
        $fileModel = File::findOneByMd5($fileHash);
        if ( $fileModel && $fileModel->user_id=='' ) {
            unlink($newFile);
            return [
                'id'    => $fileModel->id,
                'url'   => Yii::$aliases['@uploadUrl'] . '/' . $fileModel->path,
                'name'  => $fileModel->name,
            ];
        } else {
            $fileModel = new File;
        }
        // yii::info('file model ->'. gettype($fileModel));

        if ( $fileModel->uploadByLocal($newFile, true, $name) && $fileModel->save() ) {
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
        else if (strpos($url, 'count.chanet.com.cn/click.cgi')) {
            $real = static::getQueryValueFromUrl('url', $url);
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
                } else if (preg_match("/(https?:\/\/sale.jd.com\/act\/.*)\?/", $redurl, $mmm)) {
                    $real = $mmm[1];
                } else {
                    preg_match("/(https?:\/\/.*)\?/", $redurl, $mmm);
                    $real = $mmm[1];
                    Yii::warning('Fail to get jd real url: ' . $redurl);
                }
            }
            $this->requestUserAgent = $ua;
        } else if (strpos($url, 'ccc.x.jd.com')) {
            $real = static::getQueryValueFromUrl('to', $url);
        } else if (strpos($url, 'item.jd.com')) {
            // preg_match('/(https?:\/\/item.jd.com.*)/', $url, $m);
            $real = $url;
        }
        // amazon.cn, amazon.com, and so on..
        else if (strpos($url, 'amazon')) {
            $real = static::removeQueryFromUrl(['t', 'tag'], $url);
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
        else if (strpos($url, 'm.dangdang.com') || strpos($url, 't.dangdang.com')) {
            $real = static::removeQueryFromUrl('unionid', $url);
        }
        // taobao alimama / taobaoke
        else if (strpos($url, 's.click.taobao.com')) {
            $real = static::getRealUrlFromTaobaoClick($url);
        }
        // taobao
        else if (strpos($url, 'taobao.com') || strpos($url, 'tmall.com') || strpos($url, 's.taobao.com')) {
            $real = static::removeQueryFromUrl('pid', $url);
        }
        else if (strpos($url, '111.com.cn')) {
            $real = static::getQueryValueFromUrl('url', $url);
        }
        // fengyu.com
        else if (strpos($url, 'fengyu.com')) {
            $real = static::removeQueryFromUrl('_src', $url);
        }
        // kaola.com
        else if (strpos($url, 'cps.kaola.com')) {
            $real = static::getQueryValueFromUrl('targetUrl', $url);
        }
        // haituncun.com
        else if (strpos($url, 'associates.haituncun.com')) {
            $real = static::getQueryValueFromUrl('url', $url);
        }
        // mia.com
        else if (strpos($url, 'mia.com')) {
            $real = static::removeQueryFromUrl(['from', 'utm_source', 'utm_medium', 'utm_campaign'], $url);
        }
        // gome.com.cn gomehigo.hk
        else if (strpos($url, 'gome.com.cn') || strpos($url, 'gomehigo.hk')) {
            $real = static::removeQueryFromUrl(['cmpid', 'feedback', 'sid', 'wid'], $url);
        }
        // yunhou.com
        else if (strpos($url, 'yunhou.com')) {
            $real = static::removeQueryFromUrl(['utm_source', 'tk'], $url);
        }
        else if (strpos($url, 'fengqu.com')) {
            $real = static::removeQueryFromUrl(['_src'], $url);
        }
        // CJ AFFILIATE
        else if (strpos($url, 'www.jdoqocy.com') || strpos($url, 'www.kqzyfj.com') || strpos($url, 'www.tkqlhce.com')) {
            $real = static::removeQueryFromUrl(['sid'], static::getQueryValueFromUrl('url', $url));
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

    public static function removeQueryFromUrl($queryKey, $url)
    {
        $info = parse_url($url);
        parse_str($info['query'], $query);

        // single key
        if (is_string($queryKey)) {
            unset($query[$queryKey]);
        }
        // multi keys
        if (is_array($queryKey)) {
            foreach ($queryKey as $key) {
                unset($query[$key]);
            }
        }
        // build full url
        $full = $info['scheme'] . '://' . $info['host'] . $info['path'];
        if (count($query) > 0) {
            $full .= '?' . http_build_query($query);
        }

        return $full;
    }

    /**
     * get real url from s.click.taobao.com
     * 
     * @param  string $url s.click.taobao.com
     * @return string      real url     
     */
    public static function getRealUrlFromTaobaoClick($url)
    {
        $headers = get_headers($url, true);
        $requestReferer = $headers['Location'];
        $toUrl = self::getQueryValueFromUrl('tu', $requestReferer);

        $ch = curl_init();
        $opt = [
            CURLOPT_URL         => $toUrl,
            CURLOPT_REFERER     => $requestReferer,
            CURLOPT_HEADER      => true,
            // CURLOPT_NOBODY      => true, // set method HEAD, removed is OK.
            CURLOPT_RETURNTRANSFER => 1,
        ];
        curl_setopt_array($ch, $opt);

        $result = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);
        
        $targetUrl = self::getQueryValueFromUrl('tar', $info['redirect_url']);
        if (empty($targetUrl)) {
            $targetUrl = $info['redirect_url'];
        }

        $targetUrl = self::removeQueryFromUrl('ali_trackid', $targetUrl);

        return $targetUrl;

        // another way: redirection twice
        // curl_setopt($ch, CURLOPT_URL, $toUrl);
        // curl_setopt($ch, CURLOPT_REFERER, $requestReferer);
        // curl_setopt($ch, CURLOPT_HEADER, false);
        // curl_setopt($ch, CURLOPT_NOBODY,1);
        // curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // curl_setopt($ch, CURLOPT_FOLLOWLOCATION,true);
        // curl_setopt($ch, CURLOPT_MAXREDIRS,2);
        // curl_exec($ch);
        // $info = curl_getinfo($ch);
        // $targetUrl = $info['url'];
    }

    public static function getB2cIdByShopName($name)
    {

        $matches = [
            Offer::B2C_JD            => ['京东', '京东全球购'],
            Offer::B2C_TMALL         => ['天猫'],
            Offer::B2C_SUNING        => ['苏宁', '苏宁易购'],
            Offer::B2C_GOME          => ['国美', '国美海外购'],
            Offer::B2C_MIYA          => ['蜜牙', '蜜芽网'],
            Offer::B2C_DANGDANG      => ['当当'],
            Offer::B2C_AMAZON_CN     => ['亚马逊', '亚马逊中国', '中国亚马逊'],
            Offer::B2C_AMAZON_BB     => ['亚马逊海外购'],
            Offer::B2C_AMAZON_US     => ['美国亚马逊'],
            Offer::B2C_AMAZON_UK     => ['英国亚马逊'],
            Offer::B2C_AMAZON_JP     => ['日本亚马逊'],
            Offer::B2C_AMAZON_DE     => ['德国亚马逊'],
            Offer::B2C_AMAZON_FR     => ['法国亚马逊'],
            Offer::B2C_AMAZON_ES     => ['西班牙亚马逊'],
            Offer::B2C_YHD           => ['一号店', '1号店'],
            Offer::B2C_TAOBAO_JHS    => ['淘宝聚划算'],
            Offer::B2C_TAOBAO        => ['淘宝'],
            Offer::B2C_1IYAOWANG     => ['1药网'],
            Offer::B2C_MUYINGZHIJIA  => ['母婴之家'],
            Offer::B2C_AMAZON_JP     => ['日本亚马逊'],
            Offer::B2C_FENGQUHAITAO  => ['丰趣海淘'],
            Offer::B2C_KAOLA         => ['考拉海淘', '网易考拉海购'],
            Offer::B2C_HAITUNCUN     => ['海豚村'],
            Offer::B2C_WOMAI         => ['中粮我买网'],
            Offer::B2C_TMALL_CS      => ['天猫超市'],
            Offer::B2C_SUPUY         => ['速普母婴'],
            Offer::B2C_YUNHOU        => ['云猴', '云猴网'],
        ];
        foreach ($matches as $k => $v) {
            if (in_array($name, $v)) {
                return $k;
            }
        }

        // not found
        Yii::warning('Fail to get B2cId by shop name! shop name: ' . $name);

        // not category
        return 0;
    }


    public function getCategoryIdByOfferTitle($title)
    {
        $matches = [
            11  => ['奶粉', '婴儿配方奶粉', '爱他美'],
            12  => ['果汁泥', '果泥', '维生素D3', '辅食', '米糊', '维生素滴剂', '曲奇',
                '叶酸', '营养素', '童年时光', '钙尔奇'],
            13  => ['拉拉裤', '纸尿裤', '尿不湿', '成长裤', '湿巾', '柔湿巾', '尿布', '纸巾'],
            14  => ['香皂', '指甲钳', '指甲剪', '洗衣液', '洗衣皂', '护唇膏', '牙刷', 
                '洗脸盆', '宝宝金水', '护臀霜', '护臀膏', '防蚊', '驱蚊', '清洁', '蚊香', 
                '沐浴', '洗发', '修护膏', '防护霜', '洗澡粉', '洗涤剂', '精油', '护理套装'],
            15  => ['奶嘴', '奶瓶', '退热贴', '围兜', '套装食具', '咬胶', '牙胶', '脐贴', 
                '儿童杯', '磨牙棒', '吸管杯'],
            16  => ['睡袋', '花洒', '行李箱', '餐椅', '餐盘', '防护插排', '护肚脐围', 
                '肚兜', '旅游朋友颈枕'],
            17  => ['儿童文具', '机器人', '火车', '玩具', '爬行垫', '游戏围栏', '手表', 
                '梦想屋', '邦尼兔', '费雪', '澳贝','早教机', '画板', '电子琴', '小泰克', '柔韧手抓球'],
            18  => ['童车', '童床', '推车', '三轮车', '伞车', '婴儿床', '马桶', '坐便器'],
            19  => ['太阳镜', '速干裤', '篮球服', '儿童午餐包', '书包', '溜冰鞋', 
                '学步鞋', '童鞋' ],
            20  => ['安全座椅', 'Britax'],
            21  => ['婴儿背带', '乳头霜', 'Mavala Stop', '防溢乳垫', 'Bio-Oil', '吸乳器', 
                '吸奶器', '暖奶器', '腰凳', '背带', '托腹带', '母乳储存', '储奶', '护理霜'],
            22  => ['翻翻书', '宝宝营养餐'],
            23  => ['肉', '月子茶', '亚麻籽油', '大叶红茶'],
            10  => ['促销活动'],
        ];

        foreach ($matches as $k => $v) {
            foreach ($v as $keyword) {
                if (strpos($title, $keyword)!==false) {
                    return $k;
                }
            }
        }

        return null;
    }


}