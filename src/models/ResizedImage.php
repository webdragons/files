<?php

namespace bulldozer\files\models;

use bulldozer\App;

/**
 * This is the model class for table "{{%resized_images}}".
 *
 * @property integer $id
 * @property integer $image_id
 * @property integer $width
 * @property integer $height
 * @property string $file_path
 */
class ResizedImage extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%resized_images}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['image_id'], 'required'],
            [['image_id', 'width', 'height'], 'integer'],
            [['file_path'], 'string', 'max' => 700],
        ];
    }

    /**
     * @inheritdoc
     */
    public function afterDelete()
    {
        parent::afterDelete();

        $file_name = App::getAlias('@frontend') . '/web' . $this->file_path;

        if (file_exists($file_name) && !is_dir($file_name)) {
            unlink($file_name);
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'image_id' => 'Image ID',
            'width' => 'Width',
            'height' => 'Height',
            'file_path' => 'File Path',
        ];
    }
}
