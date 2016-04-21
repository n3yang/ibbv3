<?php

namespace app\models;

use Yii;
use yii\base\Event;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\behaviors\TimestampBehavior;
use yii\data\ActiveDataProvider;

/**
 * This is the model class for table "link".
 *
 * @property string $id
 * @property string $name
 * @property string $url
 * @property string $slug
 * @property integer $click
 * @property string $created_at
 */
class Link extends \yii\db\ActiveRecord
{


    public function __construct()
    {
        // parent::__construct();
        Event::on(Link::className(), Link::EVENT_BEFORE_INSERT, function($event){
            $this->slug = empty($this->slug) ? self::generateSlug($this->url) : $this->slug;
        });
        Event::on(Link::className(), Link::EVENT_BEFORE_UPDATE, function($event){
            $this->slug = empty($this->slug) ? self::generateSlug($this->url) : $this->slug;
        });
    }
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'link';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['url'], 'required'],
            [['url'], 'string'],
            [['click'], 'integer'],
            [['url'], 'url'],
            [['created_at'], 'safe'],
            [['name', 'slug'], 'string', 'max' => 200]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => '名称',
            'url' => 'URL地址',
            'slug' => '短地址',
            'click' => '点击次数',
            'created_at' => '创建时间',
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'value' => new Expression('NOW()'),
            ],
        ];
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
        $query = Link::find();

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
            'click' => $this->click,
            'created_at' => $this->created_at,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'url', $this->url])
            ->andFilterWhere(['like', 'slug', $this->slug]);

        return $dataProvider;
    }



    /**
     * generate Slug by url
     * 
     * @param  string $url URL or any strings
     * @return string      short strings
     */
    public static function generateSlug($url)
    {
        $a = md5($url, 1);
        $s = '0123456789abcdefghijklmnopqrstuvwxyz';
        $d = '';
        for ($f = 0;  $f < 8;  $f++){
            $g = ord( $a[ $f ] );
            $d .= $s[ ( $g ^ ord( $a[ $f + 8 ] ) ) - $g & 0x1F ];
        }
        return $d;
    }
    
    /**
     * find link by slug
     * 
     * @param  string $slug 
     * @return static|null
     */
    public function findBySlug($slug)
    {
        return static::findOne(['slug' => $slug]);
    }

    public static function getSiteShortUrl($url)
    {
        $slug = self::generateSlug($url);
        return '/link/goto/' . $slug;
    }
}
