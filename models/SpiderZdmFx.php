<?php

namespace app\models;

use app\models\SpiderZdm;

/**
 * Spider for zdm_fx
 */
class SpiderZdmFx extends SpiderZdm
{

    protected $syncCacheKey = 'SPIDER_ZDM_FX_SYNC_STATE';

    public $fetchListUrl = 'https://api.smzdm.com/v1/faxian/articles';
    public $fetchArticleUrl = 'https://api.smzdm.com/v1/faxian/articles/';

    public $fromSite = Offer::SITE_ZDM_FX;

}