<?php

namespace app\models;

use Yii;
use yii\base\Event;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\behaviors\TimestampBehavior;
use yii\data\ActiveDataProvider;
use app\components\JosClient;

/**
 * This is the model class for table "link".
 *
 * @property string $id
 * @property string $name
 * @property string $url
 * @property string $slug
 * @property string $hard
 * @property integer $click
 * @property string $created_at
 */
class Link extends \yii\db\ActiveRecord
{

    const SCENARIO_SEARCH = 'search';

    const REDIRECT_SLUG_PREFIX = '/link/goto';

    public function __construct()
    {
        // parent::__construct();
        Event::on(Link::className(), Link::EVENT_BEFORE_INSERT, function($event){
            $this->slug = $this->slug ?: self::generateSlug($this->url);
            $this->click = $this->click ?: 0;
        });
        Event::on(Link::className(), Link::EVENT_BEFORE_UPDATE, function($event){
            $this->slug = $this->slug ?: self::generateSlug($this->url);
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
            [['url'], 'required', 'on'=> self::SCENARIO_DEFAULT],
            [['url'], 'string'],
            [['click'], 'integer'],
            [['url'], 'url', 'on'=> self::SCENARIO_DEFAULT],
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
            'hard' => '强制跳转地址',
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

    public function scenarios()
    {
        return [
            self::SCENARIO_DEFAULT => array_keys(self::attributeLabels()),
            self::SCENARIO_SEARCH => ['id', 'name', 'slug', 'url', 'hard', 'click'],
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
            'sort'  => ['defaultOrder'=> ['id' => SORT_DESC]],
        ]);

        $this->scenario = self::SCENARIO_SEARCH;

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
            ->andFilterWhere(['like', 'hard', $this->hard])
            ->andFilterWhere(['like', 'slug', $this->slug]);

        return $dataProvider;
    }



    /**
     * generate Slug by url, pass url scheme
     * 
     * @param  string $url URL or any strings
     * @return string      short strings
     */
    public static function generateSlug($url)
    {
        if (preg_match('/^(.*):\/\/(.*)$/', $url, $matches)) {
            $url = $matches[2];
        }
        $salt = 'ibbv3';
        $str = substr( md5( $salt . $url ), 0, 12 );
        return gmp_strval( gmp_init( $str, 16 ), 62 );
    }

    
    /**
     * find link by slug
     * 
     * @param  string $slug 
     * @return static|null
     */
    public static function findOneBySlug($slug)
    {
        /*
        return static::getDb()->cache(function ($db) use ($slug){
            return static::findOne(['slug' => $slug]);
        });
        */
        $key = __CLASS__ . __METHOD__ . $slug;
        
        // get from caching
        $link = Yii::$app->cache->get($key);
        if ($link) {
            return $link;
        }

        // set caching
        $link = static::findOne(['slug' => $slug]);
        Yii::$app->cache->set($key, $link, 3600);

        return $link;
    }

    /**
     * convert a normal URL to short relative URL
     * 
     * @param  string $url normal URL
     * @return string      short relative URL
     */
    public static function getSiteShortUrl($url)
    {
        $slug = self::generateSlug($url);
        return self::REDIRECT_SLUG_PREFIX . '/' . $slug;
    }



    /**
     * 将商品连接（B2C连接）转换为CPS平台的连接
     * 
     * @param  string $url B2C连接
     * @return string      CPS连接
     */
    public static function replaceToCps($url)
    {
        // get from caching
        $cacheKey = __METHOD__ . '_KEY_' . md5($url);
        $cpsUrl = Yii::$app->cache->get($cacheKey);
        if ($cpsUrl) {
            return $cpsUrl;
        }

        // amazon.
        if (strpos($url, 'amazon.')) {
            $info = parse_url($url);
            parse_str($info['query'], $params);
            if (strpos($url, 'amazon.cn')) {
                $tag = 'ibaobr-23';
            } else if (strpos($url, 'amazon.co.jp')) {
                $tag = 'ibaobr0d-22';
            } else if (strpos($url, 'amazon.com')) {
                $tag = 'ibaobr-20';
            } else if (strpos($url, 'amazon.de')) {
                $tag = 'ibaobr0a-21';
            } else if (strpos($url, 'amazon.co.uk')) {
                $tag = 'ibaobr03-21';
            } else if (strpos($url, 'amazon.es')) {
                $tag = 'ibaobr0c-21';
            } else if (strpos($url, 'amazon.fr')) {
                $tag = 'ibaobr07-21';
            } else if (strpos($url, 'amazon.it')) {
                $tag = 'ibaobr0b3-21';
            }
            $params['t'] = $params['tag'] = $tag;
            $cpsUrl = $info['scheme'] . '://' . $info['host'] . $info['path'] . '?' . http_build_query($params);

        } else if (strpos($url, 'jd.com') || strpos($url, 'jd.hk')) {
            $jos = new JosClient;
            $cpsUrl = $jos->getPromotionUrl($url);
            if (!$cpsUrl) {
                $cpsUrl = $url;
            }
        } else {
            // 检测商品所属商城，并转换对应的CPS平台的连接
            $matches = [
                'kaola.com'             =>  '1737'  ,
                // 'yixun.com'             =>  '337'   ,
                'm.yhd.com'             =>  '516'   ,
                'yhd.com'               =>  '58'    ,
                'm.dangdang.com'        =>  '468'   ,
                'dangdang.com'          =>  '64'    ,
                'm.gou.com'             =>  '1602'  ,
                'gou.com'               =>  '756'   ,
                'm.muyingzhijia.com'    =>  '897'   ,
                'muyingzhijia.com'      =>  '114'   ,
                'supumall.com'          =>  '927'   ,
                'supuy.com'             =>  '927'   ,
                'lamall.com'            =>  '1731'  ,
                'miyabaobei.com'        =>  '930'   ,
                'm.mia.com'             =>  '1770'  ,
                'mia.com'               =>  '930'   ,
                // 'ymatou.com'            =>  '1419'  ,
                'suning.com'            =>  '84'    ,
                'm.suning.com'          =>  '501'   ,
                'm.gome.com.cn'         =>  '618'   ,
                'gome.com.cn'           =>  '236'   ,
                'womai.com'             =>  '334'   ,
                'moximoxi.net'          =>  '1728'  ,
                'xiji.com'              =>  '1752'  ,
                'ikjtao.com'            =>  '723'   ,
                'kjt.com'               =>  '1884'  ,
                'jgb.cn'                =>  '1872'  ,
                '111.com.cn'            =>  '256'   ,
                'fengqu.com'            =>  '2058'  ,
                'm.fengqu.com'          =>  '2307'  ,
                'haituncun.com'         =>  '2862'  ,
            ];
            foreach ($matches as $k => $v) {
                if (strpos($url, $k)) {
                    $aid = $v;
                    break;
                }
            }
            if (empty($aid)) {
                $cpsUrl = $url;
            } else {
                $cpsUrl = 'http://c.duomai.com/track.php?site_id=149193&aid=' . $aid . '&euid=&t=' . urlencode($url);
            }
        }
        // caching
        Yii::$app->cache->set($cacheKey, $cpsUrl, 3600);

        return $cpsUrl;
    }
}
