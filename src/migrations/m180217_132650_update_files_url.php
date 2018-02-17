<?php

namespace bulldozer\files\migrations;

use yii\db\Migration;

/**
 * Class m180217_132650_update_files_url
 */
class m180217_132650_update_files_url extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $files = (new \yii\db\Query())
            ->select(['id', 'file_path', 'origin_file_path'])
            ->from(['{{%files}}'])
            ->all();

        foreach ($files as $file) {
            $this->update('{{%files}}', [
                'file_path' => substr($file['file_path'], strlen('/uploads'), strlen($file['file_path'])),
                'origin_file_path' => substr($file['origin_file_path'], strlen('/uploads'), strlen($file['origin_file_path'])),
            ], [
                'id' => $file['id']
            ]);
        }

        $files = (new \yii\db\Query())
            ->select(['id', 'file_path'])
            ->from(['{{%resized_images}}'])
            ->all();

        foreach ($files as $file) {
            $this->update('{{%resized_images}}', [
                'file_path' => substr($file['file_path'], strlen('/uploads'), strlen($file['file_path'])),
            ], [
                'id' => $file['id']
            ]);
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $files = (new \yii\db\Query())
            ->select(['id', 'file_path', 'origin_file_path'])
            ->from(['{{%files}}'])
            ->all();

        foreach ($files as $file) {
            $this->update('{{%files}}', [
                'file_path' => '/uploads' . $file['file_path'],
                'origin_file_path' => '/uploads' . $file['origin_file_path'],
            ], [
                'id' => $file['id']
            ]);
        }

        $files = (new \yii\db\Query())
            ->select(['id', 'file_path'])
            ->from(['{{%resized_images}}'])
            ->all();

        foreach ($files as $file) {
            $this->update('{{%resized_images}}', [
                'file_path' => '/uploads' . $file['file_path'],
            ], [
                'id' => $file['id']
            ]);
        }
    }
}
