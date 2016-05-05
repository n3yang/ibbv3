<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "tag".
 *
 * @property string $id
 * @property string $name
 * @property string $slug
 * @property string $created_at
 * @property string $updated_at
 */
class Tag extends \yii\db\ActiveRecord
{

/*
insert into tag values
    ('11','奶粉牛奶','naifenniunai', NOW(), NOW()),
    ('12','营养辅食','yingyangfushi', NOW(), NOW()),
    ('13','尿裤湿巾','niaokushijin', NOW(), NOW()),
    ('14','洗护用品','xihuyongpin', NOW(), NOW()),
    ('15','喂养用品','weiyangyongpin', NOW(), NOW()),
    ('16','家纺服饰','jiafangfushi', NOW(), NOW()),
    ('17','玩具乐器','wanjuyueqi', NOW(), NOW()),
    ('18','童车童床','tongchetongchuang', NOW(), NOW()),
    ('19','童装童鞋','tongzhuangtongxie', NOW(), NOW()),
    ('20','安全座椅','anquanzuoyi', NOW(), NOW()),
    ('21','妈妈用品','mamayongpin', NOW(), NOW());
*/
    static $reservedTag = [
        '11'  => ['name' => '奶粉牛奶', 'slug' => 'naifenniunai'],
        '12'  => ['name' => '营养辅食', 'slug' => 'yingyangfushi'],
        '13'  => ['name' => '尿裤湿巾', 'slug' => 'niaokushijin'],
        '14'  => ['name' => '洗护用品', 'slug' => 'xihuyongpin'],
        '15'  => ['name' => '喂养用品', 'slug' => 'weiyangyongpin'],
        '16'  => ['name' => '家纺服饰', 'slug' => 'jiafangfushi'],
        '17'  => ['name' => '玩具乐器', 'slug' => 'wanjuyueqi'],
        '18'  => ['name' => '童车童床', 'slug' => 'tongchetongchuang'],
        '19'  => ['name' => '童装童鞋', 'slug' => 'tongzhuangtongxie'],
        '20'  => ['name' => '安全座椅', 'slug' => 'anquanzuoyi'],
        '21'  => ['name' => '妈妈用品', 'slug' => 'mamayongpin'],
        '22'  => ['name' => '图书影音', 'slug' => 'tushuyingyin'],
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tag';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'slug'], 'required'],
            [['created_at', 'updated_at'], 'safe'],
            [['name'], 'string', 'max' => 200],
            [['slug'], 'string', 'max' => 100]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'slug' => 'Slug',
            'created_at' => '添加时间',
            'updated_at' => '更新时间',
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
                // 'attributes' => [
                //     ActiveRecord::EVENT_BEFORE_INSERT => ['created_at', 'updated_at'],
                //     ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],
                // ],
                // if you're using datetime instead of UNIX timestamp:
                'value' => new Expression('NOW()'),
            ],
        ];
    }

    public function getOffers()
    {
        return $this->hasMany(Offer::className(), ['id' => 'offer_id'])->viaTable('tag_offer', ['tag_id' => 'id']);
    }

}
