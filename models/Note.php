<?php

namespace app\models;

use Yii;
use yii\db\Expression;
use yii\behaviors\TimestampBehavior;
use yii\helpers\Url;

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
 * @property string $fetched_title
 * @property string $created_at
 * @property string $updated_at
 * @property integer $status
 */
class Note extends \yii\db\ActiveRecord
{

    const STATUS_PUBLISHED = 1;
    const STATUS_DRAFT = 2;
    const STATUS_DELETED = 3;

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
            [['user_id', 'category_id', 'title', 'content', 'status'], 'required'],
            [['user_id', 'category_id', 'status'], 'integer'],
            [['title', 'content', 'excerpt', 'fetched_title'], 'string'],
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
            'fetched_title' => '原始标题',
            'created_at' => '添加时间',
            'updated_at' => '修改时间',
            'status' => '状态',
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
        return $this->hasMany(Tag::className(), ['id' => 'tag_id'])->viaTable('tag_note', ['note_id' => 'id']);
    }

    public function getCategory()
    {
        return $this->hasOne(Category::className(), ['id' => 'category_id']);
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    public function getCoverUrl()
    {
        if (Url::isRelative($this->cover)) {
            return File::getImageUrlByPath($this->cover);
        } else {
            return $this->cover;
        }
    }

    public function getFormatedContent()
    {
        return static::formatContent($this->content);
    }
    public static function formatContent($content)
    {
        if (empty($content)) {
            return $content;
        }
        if (!preg_match_all("/(\[sitebox+[^\]]*\])/", $content, $m)) {
            return $content;
        }
        $template = Yii::$app->view->renderFile('@app/views/note/sitebox.php');
        foreach ($m[1] as $sitebox) {
            $formated = $template;
            if (preg_match_all("/([link|cover|title|site|price]+)=\"([^\"]+)?\"/", $sitebox, $matches, PREG_SET_ORDER)) {
                foreach ($matches as $params) {
                    $value = empty($params[2]) ? '' : $params[2];
                    $formated = str_replace('{$'.$params[1].'}', $value, $formated);
                }
            }
            $content = str_replace($sitebox, $formated, $content);
        }

        return $content;
    }


    public static function getStatusLabel($status = '')
    {
        $labels = [
            self::STATUS_PUBLISHED  => '已发布',
            self::STATUS_DRAFT      => '草稿',
            self::STATUS_DELETED    => '已删除',
        ];

        return $status == '' ? $labels : $labels[$status];
    }

    public function findSimilar($limit = 4)
    {
        return $this->find()
            ->where([
                'status'        => Note::STATUS_PUBLISHED,
                'category_id'   => $this->category_id,
            ])
            ->orderBy('note.id DESC')
            ->limit($limit)
            ->all();
    }
}
