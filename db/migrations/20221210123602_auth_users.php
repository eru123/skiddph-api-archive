<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AuthUsers extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('auth_users');
        $table->addColumn('user', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('hash', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('last_user', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('last_hash', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('status', 'string', ['limit' => 255, 'default' => 'active', 'null' => false])
            ->addColumn('extra', 'json', ['null' => true])
            ->addColumn('created_at', 'datetime', ['null' => true])
            ->addColumn('updated_at', 'datetime', ['null' => true])
            ->addColumn('deleted_at', 'datetime', ['null' => true])
            ->addIndex(['user'], ['unique' => true])
            ->addIndex(['status'])
            ->create();
    }
}
