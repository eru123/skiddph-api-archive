<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class EmailTemplates extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('email_templates');
        $table->addColumn('name', 'string', ['limit' => 255])
            ->addColumn('subject', 'string', ['limit' => 255])
            ->addColumn('body', 'text')
            ->addColumn('created_at', 'datetime')
            ->addColumn('updated_at', 'datetime')
            ->create();
    }
}
