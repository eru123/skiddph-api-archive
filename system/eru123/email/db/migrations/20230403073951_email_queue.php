<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class EmailQueue extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('email_queues');
        $table->addColumn('to', 'text', ['null' => true]) // comma separated
            ->addColumn('cc', 'text', ['null' => true]) // comma separated
            ->addColumn('bcc', 'text', ['null' => true]) // comma separated
            ->addColumn('subject', 'text', ['null' => true])
            ->addColumn('body', 'text', ['null' => true])
            ->addColumn('priority', 'integer', ['null' => true]) // sends emails with higher priority first
            ->addColumn('for_approval', 'boolean', ['null' => true]) // if true, the email will be sent only after approval
            ->addColumn('approved_at', 'datetime', ['null' => true])
            ->addColumn('pending_at', 'datetime', ['null' => true])
            ->addColumn('delivered_at', 'datetime', ['null' => true])
            ->addColumn('failed_at', 'datetime', ['null' => true])
            ->addColumn('created_at', 'datetime', ['null' => true])
            ->addColumn('deleted_at', 'datetime', ['null' => true])
            ->create();
    }
}
