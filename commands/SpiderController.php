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
use app\models\SpiderPyh;
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

        // $spider = new SpiderZdmFx;
        // $spider->syncArticle();
    }

    public function actionTest()
    {

    }

    public function actionSyncPyh()
    {
        $spider = new SpiderPyh;
        $spider->syncArticle();
    }

}
