<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

final class AuthEmailVerification extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('auth_email_verification');
        $table->addColumn('user_id', 'integer', ['null' => false])
            ->addColumn('updated_at', 'datetime', ['null' => false])
            ->addColumn('token', 'text', ['null' => false, 'limit' => MysqlAdapter::TEXT_LONG])
            ->create();
    }
}