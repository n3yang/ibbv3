<?php

namespace app\models;

use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "offer".
 *
 * @property string $id
 * @property string $title
 * @property string $content
 * @property string $excerpt
 * @property string $price
 * @property integer $thumb_file_id
 * @property string $link_slug
 * @property integer $site
 * @property integer $b2c
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $status
 */
class Offer extends \yii\db\ActiveRecord
{
    const STATUS_PUBLISHED = 1;
    const STATUS_DRAFT = 2;
    const STATUS_DELETED = 3;


    const B2C_JD = 1;
    const B2C_TMALL = 2;
    const B2C_SUNING = 3;
    const B2C_GOME = 4;
    const B2C_MIYA = 5;
    const B2C_DANGDANG = 6;
    const B2C_AMAZON_CN = 7;
    const B2C_AMAZON_BB = 8; //海外购
    const B2C_YHD = 9;
    const B2C_TAOBAO_JHS = 10;
    const B2C_TAOBAO = 11;
    const B2C_1IYAOWANG = 12;
    const B2C_MUYINGZHIJIA = 13;
    const B2C_AMAZON_JP = 14;
    const B2C_FENGQUHAITAO = 15;
    const B2C_KAOLA = 16;
    const B2C_HAITUNCUN = 17;
    const B2C_AMAZON_US = 18;
    const B2C_WOMAI = 19;
    const B2C_TMALL_CS = 20;
    const B2C_AMAZON_UK = 21;
    const B2C_SUPUY = 22;
    const B2C_TMALL_GJ = 23;
    const B2C_AMAZON_DE = 24;
    const B2C_AMAZON_FR = 25;
    const B2C_AMAZON_ES = 26;


    const SITE_ZDM = 1;
    const SITE_ZDM_FX = 2;
    const SITE_PYH = 3;


    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'offer';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title', 'content'], 'required'],
            [['title', 'content'], 'string'],
            [['thumb_file_id', 'site', 'b2c', 'category_id'], 'integer'],
            [['price', 'link_slug'], 'string', 'max' => 200],
            ['status', 'default', 'value' => self::STATUS_DRAFT],
            // ['tags', 'each', 'rule' => ['integer']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => '标题',
            'content' => '内容',
            'excerpt'   => '摘要',
            'price' => '价格',
            'thumb_file_id' => 'File ID',
            'link_slug' => '链接地址',
            'site' => '抓取网站',
            'b2c' => '商城',
            'fetched_from'  => 'From',
            'created_at' => '添加时间',
            'updated_at' => '修改时间',
            'status' => '状态',
            'tags' => '标签',
            'category_id' => '目录',
        ];
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                // 'attributes' => [
                //     ActiveRecord::EVENT_BEFORE_INSERT => ['created_at', 'updated_at'],
                //     ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],
                // ],
                // if you're using datetime instead of UNIX timestamp:
                'value' => new Expression('NOW()'),
            ],
        ];
    }

    public function findByTagId($tagId, $limit = 20, $offset = 0, $status = self::STATUS_PUBLISHED)
    {
        return static::find()
            ->select('offer.*')
            ->leftJoin('tag_offer', 'offer.id=tag_offer.offer_id')
            ->where(['offer.status'=>$status])
            ->andWhere(['tag_offer.tag_id'=>$tagId])
            ->orderBy('offer.id DESC')
            ->offset($offset)
            ->limit($limit)
            ->all();
    }

    public static function findHot()
    {
        /*
        return Offer::find()
            ->select('offer.*, link.click AS click')
            ->leftJoin('link', 'offer.link_slug=link.slug')
            ->where(['>', 'offer.created_at', date('Y-m-d 00:00:00')])
            ->andWhere(['offer.status'=>Offer::STATUS_PUBLISHED])
            ->orderBy('link.click DESC')
            ->limit(10)
            ->all();
        */
        $rs = self::getDb()->cache(function ($db) {
            return self::find()
                ->where(['status'=>Offer::STATUS_PUBLISHED])
                ->andWhere(['>', 'created_at', date('Y-m-d 00:00:00')])
                ->orderBy('click')
                ->limit(10)
                ->with('thumb')
                ->all();
        }, 300);

        return $rs;
    }

    public function getThumbUrl()
    {
        if (empty($this->thumb)) {
            return '';
        } else {
            return $this->thumb->getImageUrl();
        }
    }

    public function getThumb()
    {
        return $this->hasOne(File::className(), ['id'=>'thumb_file_id']);
    }

    public function getTags()
    {
        return $this->hasMany(Tag::className(), ['id' => 'tag_id'])->viaTable('tag_offer', ['offer_id' => 'id']);
    }

    public function getCategory()
    {
        return $this->hasOne(Category::className(), ['id'=>'category_id']);
    }

    public function getLinkSlugUrl()
    {
        return Link::REDIRECT_SLUG_PREFIX . '/' . $this->link_slug;
    }

    public static function getStatusLabel($status='')
    {
        $labels = [
            self::STATUS_PUBLISHED  => '已发布',
            self::STATUS_DRAFT      => '草稿',
            self::STATUS_DELETED    => '已删除',
        ];

        return $status == '' ? $labels : $labels[$status];
    }

    public function getB2cLabel()
    {
        $labels = $this->getB2cLabels();
        return $labels[$this->b2c] ?: '';
    }

    public static function getB2cLabels()
    {
        return [
            self::B2C_JD            => '京东',
            self::B2C_TMALL         => '天猫',
            self::B2C_SUNING        => '苏宁',
            self::B2C_GOME          => '国美',
            self::B2C_MIYA          => '蜜牙',
            self::B2C_DANGDANG      => '当当',
            self::B2C_AMAZON_CN     => '亚马逊',
            self::B2C_AMAZON_BB     => '亚马逊海外购',
            self::B2C_AMAZON_US     => '美国亚马逊',
            self::B2C_AMAZON_UK     => '英国亚马逊',
            self::B2C_AMAZON_JP     => '日本亚马逊',
            self::B2C_AMAZON_DE     => '德国亚马逊',
            self::B2C_AMAZON_FR     => '法国亚马逊',
            self::B2C_AMAZON_ES     => '西班牙亚马逊',
            self::B2C_YHD           => '一号店',
            self::B2C_TAOBAO_JHS    => '淘宝聚划算',
            self::B2C_TAOBAO        => '淘宝',
            self::B2C_1IYAOWANG     => '1药网',
            self::B2C_MUYINGZHIJIA  => '母婴之家',
            self::B2C_FENGQUHAITAO  => '丰趣海淘',
            self::B2C_KAOLA         => '考拉海淘',
            self::B2C_HAITUNCUN     => '海豚村',
            self::B2C_WOMAI         => '中粮我买网',
            self::B2C_TMALL_CS      => '天猫超市',
            self::B2C_TMALL_GJ      => '天猫国际',
            self::B2C_SUPUY         => '速普母婴',
        ];
    }

    public function getSiteLabel()
    {
        $labels = $this->getSiteLabels();
        return $labels[$this->site] ?: '';
    }
    public static function getSiteLabels($site='')
    {
        $labels = [
            self::SITE_ZDM      => '值买',
            self::SITE_ZDM_FX   => '值发现',
            self::SITE_PYH      => '买个便宜货',
        ];
        return $site == '' ? $labels : $labels[$site];
    }
}
