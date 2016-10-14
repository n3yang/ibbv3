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
        $url = $this->fetchArticleUrl . $id;
        $rdata = $this->getHttpContent($url, $reqData);
        $rdata = json_decode($rdata, 1);
        print_r($rdata);

        // get error ? log it.
        if ($rdata['error_code'] != 0) {
            Yii::warning('Fail to fetch yuanchuang article, return: ' . var_export($rdata, true));
            return array();
        } else {
            $a = $rdata['data'];
            $newNote['fetched_from'] = $url . '?' . http_build_query($reqData);
        }

        // article cover image
        $src = str_replace('_c640.', '_d320.', $a['article_pic']);
        $cover = $this->addRemoteFile($src, $a['article_title']);
        $newNote['cover'] = $cover['path'];
        // parses content
        $content = $this->parseContent($a['article_filter_content'], $a['article_title']);
        $newNote['content'] = $content;
        // setup
        $newNote['user_id'] = 1;
        $newNote['category_id'] = 100;
        $newNote['title'] = $a['article_title'];
        $newNote['fetched_title'] = $a['article_title'];
        $newNote['fetched_author'] = $a['article_referrals'];
        $newNote['status'] = Note::STATUS_DRAFT;

        // insert into DB
        $note = new Note;
        $note->setAttributes($newNote, false);

        if (!$note->save()) {
            return 0;
        }

        return 1;
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
        $replacements = [
            '值友'       => '网友',
            '张大妈'     => '网站',
            '<span >'   => '<span>',
            '<p >'      => '<p>',
            '<strong >' => '<strong>',
            '<b >' => '<b>',
            '<div >' => '<div>',
        ];
        $detail = str_replace(array_keys($replacements), array_values($replacements), $detail);
        // replace image url
        $detail = str_replace('_e600.jpg', '_e440.jpg', $detail);
        // $detail = str_replace('_e600.jpg', '_a680.jpg', $detail);
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
            if (strpos($src, 'emotion') || strpos($src, 'wp-includes/images/smilies/')) {
                $img->delete();
                unset($img);
                echo 'img removed' . PHP_EOL;
                continue;
            }
            // fetch and replace
            if (!Url::isRelative($src)) {
                if (strpos($src, '?from=net')) {
                    $src = str_replace('?from=net', '', $src);
                }
                $rs = $this->addRemoteFile($src, $articleTitle, [1600, 1600]);
                if (!empty($rs['url'])) {
                    // it's image show
                    if (!empty($img->getAttribute('itemprop'))) {
                        $img->setAttribute('class', 'img-attach');
                    }
                    $imgTitle = $img->getAttribute('title');
                    if (empty($imgTitle)) {
                        $imgTitle = $articleTitle;
                    }
                    $attributes = ['src1', 'src2', 'itemprop', '_size', 'data-title', 'title'];
                    foreach ($attributes as $attr) {
                        $img->removeAttribute($attr);
                    }
                    $img->setAttribute('src', $rs['url']);
                    $img->setAttribute('alt', $imgTitle);
                    echo 'replace to: ' . $rs['url'] . PHP_EOL;
                }
            }
        }

        // get site info
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

            $i = PHP_EOL . "[sitebox link=\"$link\" cover=\"$src\" title=\"$title\" site=\"$site\" price=\"$price\"]" . PHP_EOL;
            $text = new TextNode($i);
            $w->addChild($text);
            echo 'add site: ' . $i . PHP_EOL;
        }

        // remove descriptions
        // $wraps = $dom->find('span.img_desc');
        // foreach ($wraps as $w) {
        //     $w->setText = '';
        // }

        $patterns = [
            '/(span class)="img_desc"/',
            '/^(<div class="details_box" id="details_box">\s)/',
            '/(<\/div>)$/'
        ];
        $replacements = [
            '$1="span-img-wrap"',
        ];
        $html = preg_replace($patterns, $replacements, $dom->outerHtml);

        return $html;
    }





}