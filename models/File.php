<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "file".
 *
 * @property string $id
 * @property string $name
 * @property string $path
 * @property string $mime
 * @property integer $size
 * @property string $md5
 * @property integer $user_id
 * @property string $created_at
 */
class File extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'file';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['path'], 'required'],
            [['size', 'user_id'], 'integer'],
            [['created_at'], 'safe'],
            [['name', 'path', 'mime'], 'string', 'max' => 200],
            [['md5'], 'string', 'max' => 50]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'PK',
            'name' => '名称',
            'path' => '路径',
            'mime' => 'MIME类型',
            'size' => '文件大小',
            'md5' => 'md5 hash',
            'user_id' => 'User ID',
            'created_at' => '创建时间',
        ];
    }
}
