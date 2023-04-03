<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class EmailLogs extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('email_logs');
        $table->addColumn('email_id', 'integer', ['null' => false])
            ->addColumn('message', 'text', ['null' => true])
            ->addColumn('created_at', 'datetime', ['null' => true])
            ->create();
    }
}
