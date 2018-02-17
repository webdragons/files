<?php

namespace bulldozer\files\models;

use bulldozer\App;
use bulldozer\db\ActiveRecord;

/**
 * This is the model class for table "{{%resized_images}}".
 *
 * @property integer $id
 * @property integer $image_id
 * @property integer $width
 * @property integer $height
 * @property string $file_path
 */
class ResizedImage extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName(): string
    {
        return '{{%resized_images}}';
    }

    /**
     * @inheritdoc
     */
    public function afterDelete(): void
    {
        parent::afterDelete();

        $file_name = App::getAlias('@uploads' . $this->file_path);

        if (file_exists($file_name) && !is_dir($file_name)) {
            unlink($file_name);
        }
    }
}
