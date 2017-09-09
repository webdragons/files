<?php

namespace bulldozer\files\migrations;

use yii\db\Migration;

/**
 * Handles the creation of table `files`.
 */
class m170518_182255_init extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%files}}', [
            'id' => $this->primaryKey(),
            'created_at' => $this->integer(11)->unsigned(),
            'updated_at' => $this->integer(11)->unsigned(),
            'creator_id' => $this->integer(11)->unsigned(),
            'updater_id' => $this->integer(11)->unsigned(),
            'type' => $this->smallInteger(2)->unsigned()->notNull(),
            'file_path' => $this->string(700),
            'origin_file_path' => $this->string(700),
        ], $tableOptions);

        $this->createTable('{{%resized_images}}', [
            'id' => $this->primaryKey(),
            'image_id' => $this->integer(11)->unsigned()->notNull(),
            'width' => $this->integer(5),
            'height' => $this->integer(5),
            'file_path' => $this->string(700),
        ], $tableOptions);

        $this->createTable('{{%watermarks}}', [
            'id' => $this->primaryKey(),
            'image_id' => $this->integer(11)->unsigned()->notNull(),
        ], $tableOptions);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('{{%files}}');
        $this->dropTable('{{%resized_images}}');
        $this->dropTable('{{%watermarks}}');
    }
}
