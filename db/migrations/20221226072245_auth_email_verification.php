<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AuthEmailVerification extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('auth_email_verification');
        $table->addColumn('user_id', 'integer', ['null' => false])
            ->addColumn('updated_at', 'datetime', ['null' => false])
            ->addColumn('token', 'string', ['null' => false])
            ->create();
    }
}