<?php
/**
 * 
 * 
 */

namespace app\commands;
use yii;
use yii\console\Controller;
use yii\helpers\Console;
use app\models\File;
use app\models\Offer;
use app\models\SpiderZdm;

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
        $spider = new SpiderZdm;
        // $list = $spider->fetchList('75, 93, 147', 2);
        // var_dump($list);
        // $article = $spider->fetchArticle(6089338);
        // $article = $spider->fetchArticle(6100889);
        // var_dump($article);

        // $f = $spider->addRemoteFile('http://eimg.smzdm.com/201604/16/5712393eab6aa9184.png');
        // var_dump($f);

        $spider->syncArticle();
        // $file = File::getUrlById(15);
        // var_dump($file);
    }



}
