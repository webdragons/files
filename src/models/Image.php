<?php

namespace bulldozer\files\models;

use bulldozer\App;
use Imanee\Exception\ImageNotFoundException;
use Imanee\ImageResource\ImagickResource;
use Imanee\Imanee;
use InvalidArgumentException;

/**
 * Class Image
 * @package common\models
 *
 * @property ResizedImage[] $resizedImages
 */
class Image extends File
{
    public function getResizedImages()
    {
        return $this->hasMany(ResizedImage::className(), ['image_id' => 'id']);
    }

    /**
     * @param int $width
     * @param int $height
     * @param bool $crop
     * @param bool $useOrigin
     * @return string
     */
    public function getThumbnail(int $width, int $height, bool $crop = false, bool $useOrigin = true)
    {
        try {
            $file_info = pathinfo($this->file_path);
            $new_file_name = $file_info['filename'] . '-' . $width . 'x' . $height . '.' . $file_info['extension'];

            if (!file_exists(App::getAlias('@frontend/web' . $file_info['dirname'] . '/' . $new_file_name))) {
                $imanee = new Imanee(
                    App::getAlias('@frontend/web' . ($useOrigin ? $this->origin_file_path : $this->file_path)),
                    new ImagickResource()
                );
                $imanee->thumbnail($width, $height, $crop)
                    ->write(App::getAlias('@frontend/web' . $file_info['dirname'] . '/' . $new_file_name));

                $resizedImage = new ResizedImage([
                    'image_id' => $this->id,
                    'width' => $width,
                    'height' => $height,
                    'file_path' => $file_info['dirname'] . '/' . $new_file_name
                ]);
                $resizedImage->save();
            }

            return $file_info['dirname'] . '/' . $new_file_name;
        } catch (ImageNotFoundException $e) {
            App::error($e->getMessage(), 'images');
            return $this->file_path;
        }
    }

    /**
     * @param string $watermark_path
     * @param int $position
     * @param int $transparency
     */
    public function setWatermark(string $watermark_path, int $position, int $transparency)
    {
        $transparency = 100 - $transparency;

        if ($transparency > 100 || $transparency < 0) {
            throw new InvalidArgumentException('transparency must be less 100 and more 0');
        }

        if ($this->origin_file_path) {
            copy(App::getAlias('@frontend/web' . $this->origin_file_path),
                App::getAlias('@frontend/web' . $this->file_path));

            $imanee = new Imanee(App::getAlias('@frontend/web' . $this->file_path), new ImagickResource());
            $imanee->watermark($watermark_path, $position, $transparency)
                ->write(App::getAlias('@frontend/web' . $this->file_path));

            foreach ($this->resizedImages as $resizedImage) {
                $resizedImage->delete();
            }
        }
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getWatermark()
    {
        return $this->hasOne(Watermark::className(), ['image_id' => 'id']);
    }

    /**
     * @inheritdoc
     */
    public function afterDelete()
    {
        parent::afterDelete();

        foreach ($this->resizedImages as $resizedImage) {
            $resizedImage->delete();
        }
    }
}