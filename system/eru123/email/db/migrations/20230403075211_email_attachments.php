<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class EmailAttachments extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('email_attachments');
        $table->addColumn('email_id', 'integer', ['null' => false])
            ->addColumn('path', 'text', ['null' => false])
            ->create();
    }
}
