<?php

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
class LinkController extends Controller
{

	public function actionUpdate()
	{
		$links = Link::findAll();
		// 查找所有的链接
		// 重新计算，更新数据
		// 查找相关的offer，更新
		// 查找相关的文章，更新
	}
}