<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AuthUsers extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change()
    {
        $table = $this->table('auth_users', ['id' => false, 'primary_key' => ['user']]);
        $table->addColumn('user', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('hash', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('created_at', 'datetime', ['null' => true])
            ->addColumn('updated_at', 'datetime', ['null' => true])
            ->addColumn('deleted_at', 'datetime', ['null' => true])
            ->addColumn('deactivated_at', 'datetime', ['null' => true])
            ->addIndex(['user'], ['unique' => true])
            ->create();
    }
}
