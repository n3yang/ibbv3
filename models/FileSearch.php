<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\File;

/**
 * FileSearch represents the model behind the search form about `app\models\File`.
 */
class FileSearch extends File
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'size', 'user_id'], 'integer'],
            [['name', 'path', 'mime', 'md5', 'created_at'], 'safe'],
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
        $query = File::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'  => ['defaultOrder'=> ['id' => SORT_DESC]],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'size' => $this->size,
            'user_id' => $this->user_id,
            'created_at' => $this->created_at,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'path', $this->path])
            ->andFilterWhere(['like', 'mime', $this->mime])
            ->andFilterWhere(['like', 'md5', $this->md5]);

        return $dataProvider;
    }
}
