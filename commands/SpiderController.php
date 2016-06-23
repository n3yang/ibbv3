<?php
/**
 * 
 * 
 */

namespace app\commands;

use yii;
use yii\console\Controller;
use yii\helpers\Console;
use yii\helpers\ArrayHelper;
use PHPHtmlParser\Dom;
use app\models\File;
use app\models\Offer;
use app\models\Link;
use app\components\SpiderZdm;
use app\components\SpiderZdmFx;
use app\components\SpiderPyh;
use yii\httpclient\Client;

/**
 * 
 */
class SpiderController extends Controller
{
    /**
     * This command echoes what you have entered as the message.
     * @param string $message the message to be echoed.
     */
    public function actionIndex($message = 'hello world', $a='hi')
    {
        echo $message . "--" . $a . "\n";
    }

    public function actionHi($user = 'Tom')
    {
    	$this->stdout("Hi, ", Console::BG_BLUE);
    	$this->stdout("Tom", Console::BOLD);

    }

    public function actionSyncZdm($value='')
    {


        // $rs = file_get_contents('http://zhushou.huihui.cn/api/hui/latest.json');
        // $rs = file_get_contents('http://zhushou.huihui.cn/api/myzhushou/collection/list');
        // $spider = new SpiderZdm;
        // $rs = $spider->getHttpContent('http://www.mgpyh.com/api/v1/get_post/239730');
        // print_r(json_decode($rs, 1));
        // echo date('Y-m-d H:i:s', '1461433041');
        // echo date('Y-m-d H:i:s', '1461755059');
        // return;

        $spider = new SpiderZdm;
        $spider->syncArticle();
        // $list = $spider->fetchList('75, 93, 147', 6);
        // var_dump($list);
        // $article = $spider->fetchArticle(6089338);
        // $article = $spider->fetchArticle(6103406);
        // var_dump($article);

        // $f = $spider->addRemoteFile('http://eimg.smzdm.com/201604/16/5712393eab6aa9184.png');
        // var_dump($f);

        // $spider->syncArticle();
        // $file = File::getUrlById(15);
        // var_dump($file);
        // var_dump(Link::findOneBySlug('gska1cgf'));
        // $spider->replaceUrl('http://www.smzdm.com/gourl/5D8916EA9AA5AC64/AA_YH_75');

        $spider = new SpiderZdmFx;
        $spider->syncArticle();
    }

    public function actionTest()
    {

        $authkey = substr(crypt(md5(time()), "$6$"), 10, 64);
        // 46 baby
        // 86 food
        // 113 cloth
        // 145 book
        $query = [
            'keyword'   => '',
            'sortid'    => '46',
            'mallid'    => '',
            'page'      => '0',
        ];

        $client = new Client();
        $response = $client->createRequest()
            ->setUrl('http://localhost')
            ->setUrl('http://hmapp.liuzhu.com/api/product/SearchContent')
            ->setMethod('post')
            ->setFormat(Client::FORMAT_JSON)
            ->addHeaders(['timeToken' => time()])
            ->addHeaders(['version' => '18'])
            // ->addHeaders(['Custom-Auth-Key' => 'SbvlwB90+GRIm8WL8Nk+VMkh1Et9996hIo3Inj35SLfdR0MCJ+o7PcPecysmarm3'])
            ->addHeaders(['Custom-Auth-Key' => $authkey])
            ->addHeaders(['Accept-Language' => 'zh-Hans-CN;q=1,.en-CN;q=0.9'])
            ->addHeaders(['Accept-Encoding' => 'gzip,deflate'])
            ->addHeaders(['Content-Type' => 'application/json;charset=utf-8'])
            ->addHeaders(['imei' => ''])
            ->addHeaders(['deviceid' => '91133FC7-F009-6271-9031-BAA362DC0523'])
            ->addHeaders(['Custom-Auth-Name' => ''])
            ->addHeaders(['User-Agent' => 'Huim/3.4.1.(iPhone;.iOS.9.3.2;.Scale/2.00)'])
            ->addHeaders(['nettype' => 'WIFI'])
            ->addHeaders(['client' => 1])
            ->setData($query)
            ->send();
        if ($response->oK()) {
            $array = $response->getData();
            // var_dump($array['data'][0]['title']);
            // echo ArrayHelper::getValue($array['data'], 'title') . "\n";
            $rs = ArrayHelper::getColumn($array['data'], 'title', $keepKeys = true);
            var_dump($rs);
        }

        // /api/product/getcontent/?id=83851
    }


    public function actionUp()
    {
        $spider = new \app\components\SpiderBase;

        $ls = \app\models\Link::find()->where(['like', 'url', 'taobao'])->all();

        foreach ($ls as $l) {
            echo $l->url . '--->';
            $real = $spider->getRealUrl($l->url);
            if (!$real) {
                echo 'fail to get real' . PHP_EOL;
                continue;
            }
            echo $real . PHP_EOL;
            echo 'slug: ' . $l->slug . '-->';
            $l->url = $real;
            $l->slug = Link::generateSlug($real);
            echo $l->slug . PHP_EOL;
            if ($l->save()) {
                echo 'saved' . PHP_EOL;
                // echo SpiderPyh::getRealUrlFromTaobaoClick($l->url).PHP_EOL;
                $offers = Offer::find()->where(['link_slug' => $l->slug])->all();
                if ($offers) {
                    foreach($offers as $o) {
                        echo 'find offer: ' . $o->id;
                        $o->link_slug = $l->slug;
                        if ($o->save()) {
                            echo '...saved';
                        } else {
                            echo '...faild';
                        }
                    }
                } else {
                    echo 'offer is not found';
                }
            }
            echo PHP_EOL . PHP_EOL ;
        }

    }


    public function actionSyncPyh()
    {
        $spider = new SpiderPyh;
        $spider->syncArticle();
    }

}
