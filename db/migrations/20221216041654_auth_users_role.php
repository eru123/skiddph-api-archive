<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AuthUsersRole extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('auth_users_role');
        $table->addColumn('user_id', 'integer', ['null' => false])
            ->addColumn('role', 'string', ['limit' => 255, 'null' => false])
            ->addIndex(['user_id', 'role'], ['unique' => true])
            ->create();
    }
}
