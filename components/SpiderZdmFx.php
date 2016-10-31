<?php

namespace app\components;

use yii;
use app\components\SpiderZdm;
use app\models\Offer;

/**
 * Spider for zdm_fx
 */
class SpiderZdmFx extends SpiderZdm
{

    protected $syncCacheKey = 'SPIDER_ZDM_FX_SYNC_STATE';

    public $fetchListUrl = 'aHR0cHM6Ly9hcGkuc216ZG0uY29tL3YxL2ZheGlhbi9hcnRpY2xlcw==';
    public $fetchArticleUrl = 'aHR0cHM6Ly9hcGkuc216ZG0uY29tL3YxL2ZheGlhbi9hcnRpY2xlcy8=';

    public $fromSite = Offer::SITE_ZDM_FX;


    public static $ignoredArticleIds = [];



    public function setIgnoredArticleIds($ids)
    {
        $this->ignoredArticleIds = $ids;
    }

    public static function isValidArticle($article)
    {
        $zdm = new SpiderZdm();
        $rs = $zdm->getLastSync();
        $list = $rs['article_list'];
        foreach ($list as $passed) {
            if ($article['article_title'] == $passed['article_title']) {
                Yii::info('Find repeated article: ' . $a['article_id'] . ', ' . $a['article_title']);
                return false;
            }
        }
        return parent::isValidArticle($article);
    }
}