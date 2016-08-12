<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "tag_note".
 *
 * @property string $tag_id
 * @property string $note_id
 */
class TagNote extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tag_note';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['tag_id', 'note_id'], 'required'],
            [['tag_id', 'note_id'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'tag_id' => 'Tag ID',
            'note_id' => 'Note ID',
        ];
    }
}
