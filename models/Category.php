<?php

namespace app\models;

use Yii;
use yii\db\Expression;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "category".
 *
 * @property integer $id
 * @property string $name
 * @property integer $parent_id
 * @property integer $type
 * @property string $slug
 * @property string $created_at
 * @property string $updated_at
 */
class Category extends \yii\db\ActiveRecord
{

    /*
    insert into category values
        ('10','未分类',0,1,'none', NOW(), NOW()),
        ('11','奶粉牛奶',0,1,'naifenniunai', NOW(), NOW()),
        ('12','营养辅食',0,1,'yingyangfushi', NOW(), NOW()),
        ('13','尿裤湿巾',0,1,'niaokushijin', NOW(), NOW()),
        ('14','洗护用品',0,1,'xihuyongpin', NOW(), NOW()),
        ('15','喂养用品',0,1,'weiyangyongpin', NOW(), NOW()),
        ('16','家纺服饰',0,1,'jiafangfushi', NOW(), NOW()),
        ('17','玩具乐器',0,1,'wanjuyueqi', NOW(), NOW()),
        ('18','童车童床',0,1,'tongchejiaju', NOW(), NOW()),
        ('19','童装童鞋',0,1,'tongzhuangtongxie', NOW(), NOW()),
        ('20','安全座椅',0,1,'anquanzuoyi', NOW(), NOW()),
        ('21','妈妈用品',0,1,'mamayongpin', NOW(), NOW()),
        ('22','图书影音',0,1,'tushuyingyin', NOW(), NOW()),
        ('23','美食生鲜',0,1,'meishishengxian', NOW(), NOW());
    */
   
    const TYPE_OFFER = 1;
    const TYPE_ARTICLE = 2;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'category';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'slug'], 'required'],
            [['parent_id', 'type'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
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
            'parent_id' => '父级ID',
            'type' => '目录类别',
            'slug' => '短地址',
            'created_at' => '创建时间',
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
}
