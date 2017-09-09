Files
===============
Files module for Bulldozer CMF.

Установка
------------
Подключить в composer:
```
composer require bulldozer/files "*"
```

Добавить в backend\config\main.php:
```
return [
    'modules' => [
        'files' => [
            'class' => 'bulldozer\files\Module',
        ],
    ],
]
```

Добавить в console\config\main.php:
```
return [
    ...

    'controllerMap' => [
        'migrate' => [
            'class' => 'yii\console\controllers\MigrateController',
            'migrationNamespaces' => [
                'bulldozer\files\migrations',
            ],
        ],
    ],

    ...
]
```