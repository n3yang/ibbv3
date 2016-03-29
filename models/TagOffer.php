<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "tag_offer".
 *
 * @property string $tag_id
 * @property string $offer_id
 */
class TagOffer extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tag_offer';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['tag_id', 'offer_id'], 'required'],
            [['tag_id', 'offer_id'], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'tag_id' => 'Tag ID',
            'offer_id' => 'Offer ID',
        ];
    }
}
