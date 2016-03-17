<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\offer;

/**
 * offerSearch represents the model behind the search form about `app\models\offer`.
 */
class offerSearch extends offer
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'thumb_file_id', 'site', 'b2c', 'created_at'], 'integer'],
            [['title', 'content', 'price', 'link_slug'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = offer::find()->orderBy('id DESC');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'thumb_file_id' => $this->thumb_file_id,
            'site' => $this->site,
            'b2c' => $this->b2c,
            'created_at' => $this->created_at,
        ]);

        $query->andFilterWhere(['like', 'title', $this->title])
            ->andFilterWhere(['like', 'content', $this->content])
            ->andFilterWhere(['like', 'price', $this->price])
            ->andFilterWhere(['like', 'link_slug', $this->link_slug]);

        return $dataProvider;
    }
}
