<?php

namespace bulldozer\files\enums;

use yii2mod\enum\helpers\BaseEnum;

/**
 * Class FileTypesEnum
 * @package bulldozer\files\enums
 */
class FileTypesEnum extends BaseEnum
{
    const TYPE_IMAGE       = 1;
    const TYPE_OTHER       = 15;

    /**
     * @var array
     */
    public static $list = [
        self::TYPE_IMAGE => 'Изображение',
        self::TYPE_OTHER => 'Другое',
    ];
}