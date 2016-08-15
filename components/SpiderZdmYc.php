<?php

namespace app\components;

use yii;
use yii\helpers\Url;
use PHPHtmlParser\Dom;
use PHPHtmlParser\Dom\TextNode;
use app\components\SpiderZdm;
use app\models\Note;

/**
 * Spider zdm yuanchuang
 */
class SpiderZdmYc extends SpiderZdm
{

    public $fetchArticleUrl = 'aHR0cHM6Ly9hcGkuc216ZG0uY29tL3YxL3l1YW5jaHVhbmcvYXJ0aWNsZXMv';

    public function fetchArticle($id)
    {
        Yii::info('Fetch yuanchuang article: ' . $id);

        $this->switchUserAgentToMobile();
         // build request data & get result
        $reqData = array(
            'f'             => 'iphone', 
            'filtervideo'   => 1,
            's'             => substr(uniqid().time(), 0, 19),
            'imgmode'       => 0
        );

        $rdata = $this->getHttpContent($url, $reqData);
        $rdata = json_decode($rdata, 1);

        // get error ? log it.
        if ($rdata['error_code'] != 0) {
            Yii::warning('Fail to fetch yuanchuang article, return: ' . var_export($rdata, true));
            return array();
        } else {
            $a = $rdata['data'];
            $newNote['fetched_from'] = $url . '?' . http_build_query($reqData);
        }

        $content = $this->parseContent($a['article_filter_content'], null, $a['article_title']);

        // insert into DB

    }

    public function parseContent($content = null, $articleTitle = null)
    {
        if (empty($content)) {
            return '';
        }
        Yii::info('Parsing content...');

        // remove javascript
        $content = preg_replace('/<head>(.*)<\/head>/is', '', $content);
        // remove not allowed tags
        $allowedTags = '<p><a><br /><span><h2><strong><b><img><div>';
        $detail = trim(strip_tags($content, $allowedTags));
        // replace some text
        $detail = str_replace('值友', '网友', $detail);
        $detail = str_replace('张大妈', '网站', $detail);
        // replace image url
        $detail = str_replace('_e600.jpg', '_e440.jpg', $detail);
        // echo $detail;

        // load dom
        $dom = new Dom;
        $dom->load($detail);

        // get all tag A (link), and replace it to my short link (cps link)
        $aTags = $dom->find('a');
        foreach ($aTags as $a) {
            $url = $a->getAttribute('href');
            echo 'found URL: ' . $url . PHP_EOL;
            if (empty($url) || strpos(strtolower($url), 'http') !== 0)  {
                continue;
            }
            if (strpos($url, 'zdm.com/p/')){
                $a->setAttribute('href', '#');
            } else {
                $title = strip_tags($a->innerHtml());
                if (empty($title)) {
                    $title = $articleTitle;
                }
                $myurl = self::replaceUrl($url, $title);
                $a->setAttribute('href', $myurl['shortUrl']);
            }
            echo 'replace to: ' . $myurl['shortUrl'] . PHP_EOL;
        }

        // get all tag img, and replace
        $imgTags = $dom->find('img');
        foreach ($imgTags as $i => $img) {
            $src = $img->getAttribute('src');
            echo 'found img: ' . $src . PHP_EOL;
            // remove emotion
            if (strpos($src, 'emotion')) {
                $img->delete();
                unset($img);
                continue;
            }
            // fetch and replace
            if (!Url::isRelative($src)) {
                if (strpos($src, '.360buyimg.com')) {
                    $src = str_replace('?from=net', '', $src);
                }
                $rs = $this->addRemoteFile($src, $articleTitle, [600, 400]);
                if (!empty($rs['url'])) {
                    $attributes = ['src1', 'src2', 'itemprop', 'class', '_size', 'data-title', 'title'];
                    foreach ($attributes as $attr) {
                        $img->removeAttribute($attr);
                    }
                    $img->setAttribute('src', $rs['url']);
                    $img->setAttribute('alt', $articleTitle);
                    echo 'replace to: ' . $rs['url'] . PHP_EOL;
                }
            }
        }

        // get sit info
        $wraps = $dom->find('.site_wrap');
        foreach ($wraps as $w) {
            $w->removeAttribute('class');
            $src = $w->find('img')[0]->getAttribute('src');
            $link = $w->find('.site_title')[0]->getAttribute('href');
            $title = $w->find('.site_title')[0]->text;
            $price = $w->find('.site_price .red')[0]->text;
            $site = $w->find('.site_price')[0]->text;
            echo 'found site: ' . $link . PHP_EOL;
            // remove old tags
            $w->find('.site_box')->delete();

            $i = "[sitebox link=\"$link\" cover=\"$src\" title=\"$title\" site=\"$site\" price=\"$price\"]";
            $text = new TextNode($i);
            $w->addChild($text);
            echo 'add site: ' . $i . PHP_EOL;
        }

        return $dom->outerHtml;
    }





}