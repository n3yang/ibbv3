<?php

namespace app\components;

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

}