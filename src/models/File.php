<?php

namespace bulldozer\files\models;

use bulldozer\App;
use bulldozer\files\enums\FileTypesEnum;
use bulldozer\users\models\User;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
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
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at', 'updated_at'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],
                ],
            ],
            [
                'class' => BlameableBehavior::className(),
                'createdByAttribute' => 'creator_id',
                'updatedByAttribute' => 'updater_id',
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%files}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_at', 'updated_at', 'creator_id', 'updater_id', 'type'], 'integer'],
            [['type'], 'required'],
            [['file_path', 'origin_file_path'], 'string', 'max' => 700],
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreator()
    {
        return $this->hasOne(User::className(), ['id' => 'creator_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUpdater()
    {
        return $this->hasOne(User::className(), ['id' => 'updater_id']);
    }

    /**
     * @return string
     */
    public function getFullUrl()
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
    public function upload(UploadedFile $file)
    {
        if (strpos($file->type, "image") !== false) {
            $this->type = FileTypesEnum::TYPE_IMAGE;
            $out_dir = '/uploads/images/'.substr(md5(time()), 0, 2).'/'.substr(md5(time()+1), 0, 2).'/';
        } else {
            $this->type = FileTypesEnum::TYPE_OTHER;
            $out_dir = '/uploads/files/'.substr(md5(time()), 0, 2).'/'.substr(md5(time()+1), 0, 2).'/';
        }

        $out_file_name = Inflector::slug($file->baseName) . '.' . $file->extension;
        $out_original_file_name = substr(md5(time()), 0, 5) . '-' . md5(time() + 1) . '.' . $file->extension;

        FileHelper::createDirectory(App::getAlias("@frontend") . '/web' . $out_dir);

        if ($file->saveAs(App::getAlias("@frontend") . '/web' . $out_dir . $out_file_name)) {
            $this->file_path = $out_dir . $out_file_name;

            if ($this->type == FileTypesEnum::TYPE_IMAGE) {
                copy(App::getAlias("@frontend") . '/web' . $out_dir . $out_file_name,
                    App::getAlias("@frontend") . '/web' . $out_dir . $out_original_file_name);
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

        $file_name = App::getAlias('@frontend') . '/web' . $this->file_path;
        $origin_file_name = App::getAlias('@frontend') . '/web' . $this->origin_file_path;

        if (file_exists($file_name) && !is_dir($file_name)) {
            unlink($file_name);
        }

        if (file_exists($origin_file_name) && !is_dir($origin_file_name)) {
            unlink($origin_file_name);
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'creator_id' => 'Creator ID',
            'updater_id' => 'Updater ID',
            'type' => 'Type',
            'file_path' => 'File Path',
        ];
    }
}
