<?php

namespace bulldozer\files\models;

use bulldozer\App;
use bulldozer\db\ActiveRecord;
use bulldozer\files\enums\FileTypesEnum;
use bulldozer\users\models\User;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
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
    public function behaviors(): array
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
    public static function tableName(): string
    {
        return '{{%files}}';
    }

    /**
     * @return ActiveQuery
     */
    public function getCreator(): ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'creator_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getUpdater(): ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'updater_id']);
    }

    /**
     * @return string
     */
    public function getFullUrl(): string
    {
        $url = urlencode($this->getWebFilePath());
        $url = str_replace('%2F', '/', $url);
        $url = Url::home(true) . ltrim($url, '/');

        return $url;
    }

    /**
     * @param UploadedFile $file
     * @return bool
     * @throws \yii\base\Exception
     */
    public function upload(UploadedFile $file): bool
    {
        if (strpos($file->type, "image") !== false) {
            $this->type = FileTypesEnum::TYPE_IMAGE;
            $outDir = '/images/' . substr(md5(time()), 0, 2) . '/' . substr(md5(time() + 1), 0, 2) . '/';
        } else {
            $this->type = FileTypesEnum::TYPE_OTHER;
            $outDir = '/files/' . substr(md5(time()), 0, 2) . '/' . substr(md5(time() + 1), 0, 2) . '/';
        }

        $outFileName = Inflector::slug($file->baseName) . '.' . $file->extension;
        $outOriginalFileName = substr(md5(rand()), 0, 5) . '-' . md5(rand()) . '.' . $file->extension;

        FileHelper::createDirectory(App::getAlias("@uploads") . $outDir);

        if ($file->saveAs(App::getAlias("@uploads") . $outDir . $outFileName)) {
            $this->file_path = $outDir . $outFileName;

            if ($this->type == FileTypesEnum::TYPE_IMAGE) {
                copy(App::getAlias("@uploads") . $outDir . $outFileName,
                    App::getAlias("@uploads") . $outDir . $outOriginalFileName);
                $this->origin_file_path = $outDir . $outOriginalFileName;
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
    public function afterDelete(): void
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

    /**
     * @return string
     */
    public function getWebFilePath(): string
    {
        $uploadsDirParts = explode('/', App::getAlias('@uploads'));
        $uploadsDir = end($uploadsDirParts);

        return '/' . $uploadsDir . '/' . $this->file_path;
    }
}
