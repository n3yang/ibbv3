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

    const SITE_ZDM = 1;
    const SITE_ZDM_FX = 2;


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
            [['thumb_file_id', 'site', 'b2c'], 'integer'],
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

    public function getTags()
    {
        return $this->hasMany(Tag::className(), ['id' => 'tag_id'])->viaTable('tag_offer', ['offer_id' => 'id']);
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

    public static function getB2cLabel($b2c='')
    {
        $labels = [
            self::B2C_JD            => '京东',
            self::B2C_TMALL         => '天猫',
            self::B2C_SUNING        => '苏宁',
            self::B2C_GOME          => '国美',
            self::B2C_MIYA          => '蜜牙',
            self::B2C_DANGDANG      => '当当',
            self::B2C_AMAZON_CN     => '亚马逊',
            self::B2C_AMAZON_BB     => '亚马逊海外购',
            self::B2C_YHD           => '一号店',
            self::B2C_TAOBAO_JHS    => '淘宝聚划算',
            self::B2C_TAOBAO        => '淘宝',
            self::B2C_1IYAOWANG     => '1药网',
        ];
        return $b2c == '' ? $labels : $labels[$b2c];
    }

    public static function getSiteLabel($site='')
    {
        $labels = [
            self::SITE_ZDM      => '值买',
            self::SITE_ZDMFX    => '值发现',
        ];
        return $site == '' ? $labels : $labels[$site];
    }
}
