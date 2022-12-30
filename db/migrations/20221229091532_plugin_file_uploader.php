<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class PluginFileUploader extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('plugin_file_uploader');
        $table->addColumn('user_id', 'integer', ['null' => false])
            ->addColumn('name', 'string', ['null' => false])
            ->addColumn('path', 'string', ['null' => true])
            ->addColumn('mime', 'string', ['null' => false])
            ->addColumn('size', 'integer', ['null' => false])
            ->addColumn('hash', 'string', ['null' => false])
            ->addColumn('connector', 'string', ['null' => false])
            ->addColumn('date', 'timestamp', ['null' => false, 'default' => 'CURRENT_TIMESTAMP'])
            ->create();
    }
}
