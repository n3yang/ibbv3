<?php
/**
 * 
 * 
 */

namespace app\commands;
use yii;
use yii\console\Controller;
use yii\helpers\Console;
use PHPHtmlParser\Dom;
use app\models\File;
use app\models\Offer;
use app\models\SpiderZdm;
use app\models\SpiderZdmFx;
use app\models\Link;

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

        // /api/v1/get_more/?productid=I1&channel=App%20Store&osv=9.3.2&request_key=newest&requesttime=1463930416&os=iPhone%20OS&clientversion=1.1.2&platform=ios&imei=d0e7fd71117943e18a3c00c38bbd4a29&signature=b247687e18570ebb75d61a3e686017b2&appkey=pumpkin&page=1&resolution=375%2A667&device=iPhone8%2C1&access_token=
        // User-Agent: mgpyh/1.1.9 CFNetwork/758.4.3 Darwin/15.5
        $spider = new SpiderZdm();
        $html = $spider->getHttpContent('http://www.mgpyh.com/api/v1/get_more/?productid=I1&channel=App%20Store&osv=9.3.2&request_key=newest&requesttime=1463930416&os=iPhone%20OS&clientversion=1.1.2&platform=ios&imei=d0e7fd71117943e18a3c00c38bbd4a29&signature=b247687e18570ebb75d61a3e686017b2&appkey=pumpkin&page=1&resolution=375%2A667&device=iPhone8%2C1&access_token=');
        print_r( json_decode($html) );
        return;
        $spider = new SpiderZdm();
        $listUrl = 'http://www.mgpyh.com/post/zone/';
        $html = $spider->getHttpContent($listUrl);

        $dom = new Dom;
        $dom->load($html);

        $contents = $dom->find('.content-item');
        foreach ($contents as $content ){
            $timestamp = $content->getAttribute('data-timestamp');

            // TODO: add some cache testing
            $excerpt = $content->find('.post-thumb')->text();
            $link = 'http://www.mgpyh.com' . $content->find('.readmore')->getAttribute('href');


            // get article info
            $html = $spider->getHttpContent($link, '', [CURLOPT_REFERER=>$listUrl]);
            $rdom = new Dom;
            $rdom->load($html);
            echo $title = $rdom->find('.recommend .title')->text();

            break;
        }

    }

}
