<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AuthUsersInfo extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('auth_users_info');
        $table->addColumn('user_id', 'integer', ['null' => false])
            ->addColumn('name', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('value', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('created_at', 'datetime', ['null' => true])
            ->addColumn('updated_at', 'datetime', ['null' => true])
            ->addIndex(['user_id', 'name'], ['unique' => true])
            ->create();
    }
}