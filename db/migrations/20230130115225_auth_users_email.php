<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AuthUsersEmail extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('auth_users_email');
        $table->addColumn('user_id', 'integer', ['null' => false])
            ->addColumn('email', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('verified', 'boolean', ['default' => false, 'null' => false])
            ->addColumn('created_at', 'datetime', ['null' => true])
            ->addColumn('updated_at', 'datetime', ['null' => true])
            ->addColumn('deleted_at', 'datetime', ['null' => true])
            ->create();
    }
}