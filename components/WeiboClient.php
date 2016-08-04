<?php

namespace app\components;

use yii;
use yii\base\Object;

class WeiboClient extends Object
{

    public $appKey;
    public $appSecret;
    public $accessToken;
    public $rateLimit;
    /**
     * 
     * @var SaeTClientV2
     */
    protected $client;
    const CACHE_RATE_LIMIT_KEY = 'weibo:api:ratelimit';

    public function __construct()
    {
        $this->appKey = Yii::$app->params['weibo']['appKey'];
        $this->appSecret = Yii::$app->params['weibo']['appSecret'];
        $this->accessToken = Yii::$app->params['weibo']['accessToken'];
        $this->client = new \SaeTClientV2($this->appKey, $this->appSecret, $this->accessToken);
        $this->setRateLimit(1, 1200);
    }

    /**
     * 发表图片微博
     *
     * 发表图片微博消息。目前上传图片大小限制为<5M。 
     * <br />注意：lat和long参数需配合使用，用于标记发表微博消息时所在的地理位置，只有用户设置中geo_enabled=true时候地理位置信息才有效。
     * <br />对应API：{@link http://open.weibo.com/wiki/2/statuses/upload statuses/upload}
     * 
     * @access public
     * @param string $status 要更新的微博信息。信息内容不超过140个汉字, 为空返回400错误。
     * @param string $picPath 要发布的图片路径, 支持url。[只支持png/jpg/gif三种格式, 增加格式请修改get_image_mime方法]
     * @param float $lat 纬度，发表当前微博所在的地理位置，有效范围 -90.0到+90.0, +表示北纬。可选。
     * @param float $long 可选参数，经度。有效范围-180.0到+180.0, +表示东经。可选。
     * @param int $visible    微博的可见性，0：所有人能看，1：仅自己可见，2：密友可见，3：指定分组可见，默认为0
     * @return bool true/false
     */
    public function upload( $status, $picPath = null, $lat = null, $long = null, $visible=0 )
    {
        $runTimes = Yii::$app->cache->get(self::CACHE_RATE_LIMIT_KEY);
        if ($runTimes >= $this->rateLimit[0]) {
            return false;
        }

        if (empty($picPath)) {
            $rs = $this->client->update($status, $lat, $long, null, $visible);
        } else {
            $rs = $this->client->upload($status, $picPath, $lat, $long, $visible);
        }

        if ($this->client->oauth->http_code == 200) {
            Yii::$app->cache->set(self::CACHE_RATE_LIMIT_KEY, $runTimes + 1, $this->rateLimit[1]);
            return true;
        } else {
            Yii::warning('Fail to upload weibo.'
                . ' HTTP code: ' . var_export($this->client->oauth->http_code, true)
                . ' HTTP info: ' . var_export($this->client->oauth->http_info, true)
                . ' response: ' . var_export($rs, true)
            );
            return false;
        }
    }

    /**
     * 头条文章
     * @param  string $title   文章标题，限定32个中英文字符以内
     * @param  string $content 正文内容，限制20000个中英文字符内，需要urlencode
     * @param  string $conver  文章封面图片地址url
     * @param  string $status  与其绑定短微博内容，限制120个中英文字符内
     * @param  string $summary 文章导语
     * @return [type]          [description]
     */
    public function uploadArticle($title, $content, $conver, $status, $summary = null)
    {
        $params = [
            'title'     => $title,
            'content'   => $content,
            'conver'    => $conver,
            'summary'   => $summary,
            'text'      => $status,
        ];
        $rs = $this->client->oauth->post( 'proxy/article/publish', $params );
        
    }

    /**
     * 设置频率限制
     * @param int $times   次数
     * @param int $seconds 单位时间：每XXX秒
     */
    public function setRateLimit($times, $seconds)
    {
        return $this->rateLimit = [$times, $seconds];
    }

    private function test()
    {
        // $sae = new \SaeTOAuthV2(Yii::$app->params['weibo']['appKey'], Yii::$app->params['weibo']['appSecret']);
        // 获取授权地址
        // echo $sae->getAuthorizeURL('http://ibaobr.com/user/oauth-weibo');
        // 访问返回结果地址，获取code
        // 根据code获取accessToken
        // $rs = $sae->getAccessToken('code', ['code'=>'97d1de8607cdfea041ba69a01c1d8602', 'redirect_uri'=>'http://ibaobr.com/user/oauth-weibo']);
        // 根据accessToken调用API
        // $stc = new \SaeTClientV2(Yii::$app->params['weibo']['appKey'], Yii::$app->params['weibo']['appSecret'], Yii::$app->params['weibo']['accessToken']);
        // $stc->set_debug(true);
        // $rs = $stc->upload('HABA 无添加润泽柔肤水 G露 180ml￥124', 'http://res.ibaobr.com/uploads/2016/07/1bpgo74mg7u.jpg');
        // var_dump($rs);
        // $rs = $stc->update('无添加润泽柔肤水 1231231  G露 180ml￥124');
        // $rs = $stc->public_timeline();
    }
}