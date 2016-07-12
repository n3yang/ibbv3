<?php

namespace app\components;

use yii;
use yii\base\Object;
use yii\httpclient\Client;

class JosClient extends Object
{

    public $serverUrl = 'https://api.jd.com/routerjson';

    public $version = "2.0";
    public $format = "json";

    public $appKey;
    public $appSecret;
    public $accessToken;
    public $refreshToken;


    public function __construct()
    {
        $this->appKey = Yii::$app->params['jos']['appKey'];
        $this->appSecret = Yii::$app->params['jos']['appSecret'];
        $this->accessToken = Yii::$app->params['jos']['accessToken'];
        $this->refreshToken = Yii::$app->params['jos']['refreshToken'];
    }

    public function getPromotionUrl($url)
    {
        $params = [
            'promotionType' => 7,
            'materialId'    => $url,
            'unionId'       => Yii::$app->params['jos']['unionId'],
            'webId'         => Yii::$app->params['jos']['webId'],
            'channel'       => 'PC', // 推广渠道 PC：pc推广，WL：无线推广 
        ];

        $rs = $this->execute('jingdong.service.promotion.getcode', $params);

        return $rs['resultCode']=='0' ? $rs['url'] : null;
    }

    public function refreshToken()
    {
        $url = 'https://oauth.jd.com/oauth/token';
        $params = [
            'grant_type'    => 'refresh_token',
            'client_id'     => $this->appKey,
            'client_secret' => $this->appSecret,
            'refresh_token' => $this->refreshToken,
        ];
        $content = file_get_contents($url . '?' . http_build_query($params));
        $rs = json_decode($content, true);

        return $rs['code'] == '0' ? true : false;
    }

    protected function execute($method, $params = [])
    {
        // 组装系统参数
        $sysParams['app_key'] = $this->appKey;
        $sysParams['v'] = $this->version;
        $sysParams['method'] = $method;
        $sysParams['timestamp'] = date("Y-m-d H:i:s");
        $sysParams['access_token'] = $this->accessToken;

        // 获取业务参数
        $sysParams['360buy_param_json'] = json_encode($params);

        // 签名
        $sysParams['sign'] = $this->generateSign($sysParams);

        // 系统参数放入GET请求串
        // $requestUrl = $this->serverUrl . "?" . http_build_query($sysParams);
        // echo file_get_contents($requestUrl);

        $client = new Client();
        $response = $client->get($this->serverUrl, $sysParams)->send();

        // HTTP 状态错误
        if (!$response->getIsOk()) {
            Yii::error('jos http response is fault. ' . __METHOD__);
            Yii::error('jos http header: ' . var_export($response->getHeaders(), 1));
            Yii::error('jos http content: ' . var_export($response->getData(), 1));

            return false;
        }

        $data = $response->getData();
        $dataKey = str_replace('.', '_', $method) . '_responce';
        $queryResult = json_decode($data[$dataKey]['queryjs_result'], true);

        // 返回错误码
        if ($queryResult['resultCode']!='0') {
            Yii::error('jos response code is fault. ' . __METHOD__);
            Yii::error('jos response content: ' . var_export($response->getData(), 1));

            return false;
        }

        return $queryResult;
    }

    protected function generateSign($params)
    {
        ksort($params);
        $stringToBeSigned = $this->appSecret;
        foreach ($params as $k => $v)
        {
            if("@" != substr($v, 0, 1))
            {
                $stringToBeSigned .= "$k$v";
            }
        }
        unset($k, $v);
        $stringToBeSigned .= $this->appSecret;
        
        return strtoupper(md5($stringToBeSigned));
    }
}