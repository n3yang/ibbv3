<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\Pagination;

/**
 * ContactForm is the model behind the contact form.
 */
class FrontSearchForm extends Model
{

    const TYPE_NOTE = 'note';
    const TYPE_OFFER = 'offer';

    public $keyword;
    public $type = self::TYPE_OFFER;
    public $page = 1;

    private $limit = 20;
    private $offset = 0;
    private $total;

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['keyword'], 'required']
        ];
    }

    public function search()
    {
        if ($this->type == self::TYPE_NOTE) {
            $note = self::searchNote($this->keyword, $this->limit, $this->offset);

        }
        $pagination = new pagination([
            'totalCount' => $form->total,
            'defaultPageSize' => $form->limit,
        ]);

        return [
            'note' => self::searchNote($this->keyword, $this->limit, $this->offset),
            'offer' => self::searchOffer($this->keyword, $this->limit, $this->offset),
        ];
    }


    private static function buildNoteQuery($keyword)
    {
        return Note::find()
            ->where(['like', 'title', $keyword])
            ->andWhere(['=', 'status', Note::STATUS_PUBLISHED])
    }

    public static function searchNote($keyword, $limit = 20, $offset = 0)
    {
        return self::buildNoteQuery($keyword)
            ->limit($limit)
            ->offset($offset)
            ->orderBy(['id' => SORT_DESC])
            ->all();
    }

    public static function countNote($keyword)
    {
        return self::buildNoteQuery($keyword)->limit(-1)->offset(-1)->orderBy([])->count('*');
    }

    public static function searchOffer($keyword, $limit = 20, $offset = 0)
    {
        $cacheKey = __METHOD__ . $keyword . $limit . $offset;

        $ds = Yii::$app->cache->get($cacheKey);
        if ($ds) {
            return $ds;
        }

        $offer = Offer::find()
            ->where(['like', 'title', $keyword])
            ->andWhere(['=', 'status', Offer::STATUS_PUBLISHED])
            ->andWhere(['>', 'created_at', date('Y-m-d', time() - 31536000)]); // 31536000 = 3600 * 24 * 365

        $total = $offer->count();
        $model = $offer->limit($limit)->offset($offset)->all();

        $ds = [
            'model' => $model,
            'total' => $total,
        ];

        Yii::$app->cache->set($key, $ds, 600);

        return $ds;
    }

    public static function countOffer($keyword)
    {
        return Offer::find()
            ->where(['like', 'title', $keyword])
            ->andWhere(['=', 'status', Offer::STATUS_PUBLISHED])
            ->andWhere(['>', 'created_at', date('Y-m-d', time() - 31536000)]) // 31536000 = 3600 * 24 * 365
            ->count();
    }
}