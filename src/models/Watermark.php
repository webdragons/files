<?php

namespace bulldozer\files\models;

use bulldozer\db\ActiveRecord;

/**
 * This is the model class for table "{{%watermarks}}".
 *
 * @property integer $id
 * @property integer $image_id
 */
class Watermark extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName(): string
    {
        return '{{%watermarks}}';
    }
}
