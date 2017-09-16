<?php

namespace bulldozer\files\models;

use bulldozer\App;
use bulldozer\files\enums\FileTypesEnum;
use bulldozer\users\models\User;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\FileHelper;
use yii\helpers\Inflector;
use yii\helpers\Url;
use yii\web\UploadedFile;

/**
 * This is the model class for table "{{%files}}".
 *
 * @property integer $id
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $creator_id
 * @property integer $updater_id
 * @property integer $type
 * @property string $file_path
 * @property string $origin_file_path
 *
 * @property string $fullUrl
 */
class File extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public function behaviors() : array
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at', 'updated_at'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],
                ],
            ],
            [
                'class' => BlameableBehavior::class,
                'createdByAttribute' => 'creator_id',
                'updatedByAttribute' => 'updater_id',
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName() : string
    {
        return '{{%files}}';
    }

    /**
     * @inheritdoc
     */
    public function rules() : array
    {
        return [
            [['created_at', 'updated_at', 'creator_id', 'updater_id', 'type'], 'integer'],
            [['type'], 'required'],
            [['file_path', 'origin_file_path'], 'string', 'max' => 700],
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getCreator() : ActiveQuery
    {
        return $this->hasOne(User::className(), ['id' => 'creator_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getUpdater() : ActiveQuery
    {
        return $this->hasOne(User::className(), ['id' => 'updater_id']);
    }

    /**
     * @return string
     */
    public function getFullUrl() : string
    {
        $url = urlencode($this->file_path);
        $url = str_replace('%2F', '/', $url);
        $url = Url::home(true) . ltrim($url, '/');

        return $url;
    }

    /**
     * @param UploadedFile $file
     * @return bool
     */
    public function upload(UploadedFile $file) : bool
    {
        if (strpos($file->type, "image") !== false) {
            $this->type = FileTypesEnum::TYPE_IMAGE;
            $out_dir = '/images/'.substr(md5(time()), 0, 2).'/'.substr(md5(time()+1), 0, 2).'/';
        } else {
            $this->type = FileTypesEnum::TYPE_OTHER;
            $out_dir = '/files/'.substr(md5(time()), 0, 2).'/'.substr(md5(time()+1), 0, 2).'/';
        }

        $out_file_name = Inflector::slug($file->baseName) . '.' . $file->extension;
        $out_original_file_name = substr(md5(time()), 0, 5) . '-' . md5(time() + 1) . '.' . $file->extension;

        FileHelper::createDirectory(App::getAlias("@uploads") . $out_dir);

        if ($file->saveAs(App::getAlias("@uploads") . $out_dir . $out_file_name)) {
            $this->file_path = $out_dir . $out_file_name;

            if ($this->type == FileTypesEnum::TYPE_IMAGE) {
                copy(App::getAlias("@uploads") . $out_dir . $out_file_name,
                    App::getAlias("@uploads") . $out_dir . $out_original_file_name);
                $this->origin_file_path = $out_dir . $out_original_file_name;
            } else {
                $this->origin_file_path = $this->file_path;
            }

            return true;
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function afterDelete()
    {
        parent::afterDelete();

        $file_name = App::getAlias('@uploads') . $this->file_path;
        $origin_file_name = App::getAlias('@uploads') . $this->origin_file_path;

        if (file_exists($file_name) && !is_dir($file_name)) {
            unlink($file_name);
        }

        if (file_exists($origin_file_name) && !is_dir($origin_file_name)) {
            unlink($origin_file_name);
        }
    }
}
