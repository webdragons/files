<?php

namespace bulldozer\files\models;

/**
 * This is the model class for table "{{%watermarks}}".
 *
 * @property integer $id
 * @property integer $image_id
 */
class Watermark extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%watermarks}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['image_id'], 'required'],
            [['image_id'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'image_id' => 'Image ID',
        ];
    }
}
