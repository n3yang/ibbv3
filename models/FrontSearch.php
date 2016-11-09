<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\Pagination;

/**
 * 
 */
class FrontSearch extends Model
{

    public function search()
    {

    }

    /**
     * build note searching query 
     * @param  string $keyword input keyword
     * @return app\models\Note
     */
    private static function buildNoteQuery($keyword)
    {
        return Note::find()
            ->where(['like', 'title', $keyword])
            ->andWhere(['=', 'status', Note::STATUS_PUBLISHED]);
    }

    /**
     * searching database
     * @param  string  $keyword keyword
     * @param  integer $limit   limit
     * @param  integer $offset  offset
     * @return app\models\Note  
     */
    public static function searchNote($keyword, $limit = 20, $offset = 0)
    {
        return self::buildNoteQuery($keyword)
            ->limit($limit)
            ->offset($offset)
            ->orderBy(['id' => SORT_DESC])
            ->all();
    }

    /**
     * count the searching result
     * @param  string $keyword keyword
     * @return integer
     */
    public static function countNote($keyword)
    {
        return self::buildNoteQuery($keyword)->limit(-1)->offset(-1)->orderBy([])->count('*');
    }

    /**
     * build Offer searching query 
     * @param  string $keyword input keyword
     * @return app\models\Offer
     */
    private static function buildOfferQuery($keyword)
    {
        return Offer::find()
            ->where(['like', 'title', $keyword])
            ->andWhere(['=', 'status', Offer::STATUS_PUBLISHED])
            ->andWhere(['>', 'created_at', date('Y-m-d', time() - 31536000)]); // 31536000 = 3600 * 24 * 365
    }

    /**
     * searching database, caching 1200s
     * @param  string  $keyword keyword
     * @param  integer $limit   limit
     * @param  integer $offset  offset
     * @return app\models\Offer  
     */
    public static function searchOffer($keyword, $limit = 20, $offset = 0)
    {
        $ds = Offer::getDb()->cache(function ($db) {
            return self::buildOfferQuery()
                ->limit($limit)
                ->offset($offset)
                ->orderBy(['id' => SORT_DESC])
                ->all();
        }, 1200);

        return $ds;
    }

    /**
     * count the searching result
     * @param  string $keyword keyword
     * @return integer
     */
    public static function countOffer($keyword)
    {
        return self::buildOfferQuery($keyword)->limit(-1)->offset(-1)->orderBy([])->count('*');
    }

}