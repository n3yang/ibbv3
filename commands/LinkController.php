<?php

namespace app\commands;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\console\Controller;
use app\models\Offer;
use app\models\Note;
use app\models\Link;
use app\components\SpiderZdm;
use app\components\SpiderPyh;

/**
 * 
 */
class LinkController extends Controller
{


    public function actionReplace($url = null, $site = 'zdm')
    {
        switch ($site) {
            case 'zdm':
                $spider = new SpiderZdm;
                break;
            case 'pyh':
                $spider = new SpiderPyh;
                break;
        }
        if ($spider instanceof \app\components\SpiderBase) {
            $real = $spider->getRealUrl($url);
            echo $real;
        }
    }

    // 查找所有的链接
    public function actionUpdate()
    {
        foreach (Link::find()->each(100) as $link) {
            // 重新计算，更新数据
            
            $newSlug = Link::generateSlug($link->url);
            if ($link->slug == $newSlug) {
                continue;
            }

            // var_dump($newScheme, $newSlug, $newUrl);
            $this->stdout('link ID: ' . $link->id .  ', slug: ' . $link->slug . ' -> ' . $newSlug . PHP_EOL);

            // 查找相关的offer，更新
            $offers = Offer::find()
                ->where(['like', 'content', $link->slug])
                ->all();
            foreach ($offers as $o) {
                $o->detachBehaviors();
                $this->stdout(' update offer: ' . $o->id);
                $o->content = str_replace($link->slug, $newSlug, $o->content) . '1';
                $o->save();
                $this->stdout(PHP_EOL);
            }

            // 查找相关的文章，更新
            $note = Note::find()
                ->where(['like', 'content', $link->slug])
                ->all();
            foreach ($note as $n) {
                $n->detachBehaviors();
                $this->stdout(' update note: ' . $n->id);
                $n->content = str_replace($link->slug, $newSlug, $n->content);
                $n->save();
                $this->stdout(PHP_EOL);
            }

            // 更新 link
            $link->slug = $newSlug;
            $link->save();
            $this->stdout('link ID: ' . $link->id . ', .. saved' . PHP_EOL);
        }
        
    }
}