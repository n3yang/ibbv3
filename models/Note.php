<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "note".
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $category_id
 * @property string $title
 * @property string $content
 * @property string $excerpt
 * @property string $cover
 * @property string $keyword
 * @property string $fetched_from
 * @property string $created_at
 * @property string $updated_at
 * @property integer $status
 */
class Note extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'note';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'category_id', 'title', 'content', 'created_at', 'updated_at', 'status'], 'required'],
            [['user_id', 'category_id', 'status'], 'integer'],
            [['title', 'content', 'excerpt'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['cover', 'keyword', 'fetched_from'], 'string', 'max' => 200],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => '用户',
            'category_id' => '分类',
            'title' => '标题',
            'content' => '内容',
            'excerpt' => '摘要',
            'cover' => '封面图',
            'keyword' => '关键词',
            'fetched_from' => '采集地址',
            'created_at' => '添加时间',
            'updated_at' => '修改时间',
            'status' => '状态',
        ];
    }
}
