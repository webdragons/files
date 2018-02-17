<?php

namespace bulldozer\files\models;

use bulldozer\App;
use Imanee\Exception\ImageNotFoundException;
use Imanee\ImageResource\ImagickResource;
use Imanee\Imanee;
use InvalidArgumentException;
use yii\db\ActiveQuery;
use yii\helpers\Url;

/**
 * Class Image
 * @package common\models
 *
 * @property ResizedImage[] $resizedImages
 */
class Image extends File
{
    /**
     * @return ActiveQuery
     */
    public function getResizedImages(): ActiveQuery
    {
        return $this->hasMany(ResizedImage::class, ['image_id' => 'id']);
    }

    /**
     * @param int $width
     * @param int $height
     * @param bool $crop
     * @param bool $useOrigin
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public function getThumbnail(int $width, int $height, bool $crop = false, bool $useOrigin = true): string
    {
        try {
            $file_info = pathinfo($this->file_path);
            $new_file_name = $file_info['filename'] . '-' . $width . 'x' . $height . '.' . $file_info['extension'];

            if (!file_exists(App::getAlias('@uploads/' . $file_info['dirname'] . '/' . $new_file_name))) {
                $imanee = new Imanee(
                    App::getAlias('@uploads' . ($useOrigin ? $this->origin_file_path : $this->file_path)),
                    new ImagickResource()
                );
                $imanee->thumbnail($width, $height, $crop)
                    ->write(App::getAlias('@uploads/' . $file_info['dirname'] . '/' . $new_file_name));

                /** @var ResizedImage $resizedImage */
                $resizedImage = App::createObject([
                    'class' => ResizedImage::class,
                    'image_id' => $this->id,
                    'width' => $width,
                    'height' => $height,
                    'file_path' => $file_info['dirname'] . '/' . $new_file_name
                ]);
                $resizedImage->save();
            }

            $uploadsDirParts = explode('/', App::getAlias('@uploads'));
            $uploadsDir = end($uploadsDirParts);

            return '/' . $uploadsDir . '/' . $file_info['dirname'] . '/' . $new_file_name;
        } catch (ImageNotFoundException $e) {
            App::error($e->getMessage(), 'images');

            return $this->file_path;
        }
    }

    /**
     * @param int $width
     * @param int $height
     * @param bool $crop
     * @param bool $useOrigin
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public function getFullThumbnail(int $width, int $height, bool $crop = false, bool $useOrigin = true): string
    {
        $url = urlencode($this->getThumbnail($width, $height, $crop, $useOrigin));
        $url = str_replace('%2F', '/', $url);
        $url = ltrim(preg_replace('#/{2,}#', '/', $url), '/');
        $url = Url::to($url, true);

        return $url;
    }

    /**
     * @param string $watermark_path
     * @param int $position
     * @param int $transparency
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function setWatermark(string $watermark_path, int $position, int $transparency)
    {
        $transparency = 100 - $transparency;

        if ($transparency > 100 || $transparency < 0) {
            throw new InvalidArgumentException('transparency must be less 100 and more 0');
        }

        if ($this->origin_file_path) {
            copy(App::getAlias('@uploads' . $this->origin_file_path),
                App::getAlias('@uploads' . $this->file_path));

            $imanee = new Imanee(App::getAlias('@uploads' . $this->file_path), new ImagickResource());
            $imanee->watermark($watermark_path, $position, $transparency)
                ->write(App::getAlias('@uploads' . $this->file_path));

            foreach ($this->resizedImages as $resizedImage) {
                $resizedImage->delete();
            }
        }
    }

    /**
     * @return ActiveQuery
     */
    public function getWatermark(): ActiveQuery
    {
        return $this->hasOne(Watermark::class, ['image_id' => 'id']);
    }

    /**
     * @inheritdoc
     */
    public function afterDelete(): void
    {
        parent::afterDelete();

        foreach ($this->resizedImages as $resizedImage) {
            $resizedImage->delete();
        }
    }
}