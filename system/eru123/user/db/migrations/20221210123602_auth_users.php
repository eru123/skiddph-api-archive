<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AuthUsers extends AbstractMigration
{
    public function up()
    {   
        $this->query("CREATE TABLE `auth_users` (
            `id` INT NOT NULL AUTO_INCREMENT,
            `user` VARCHAR(255) NOT NULL,
            `hash` VARCHAR(255) NOT NULL,
            `last_user` VARCHAR(255) NULL,
            `last_hash` VARCHAR(255) NULL,
            `created_at` DATETIME NULL,
            `updated_at` DATETIME NULL,
            `status` VARCHAR(255) NOT NULL DEFAULT 'active',
            `fname` VARCHAR(255) NULL,
            `lname` VARCHAR(255) NULL,
            `mname` VARCHAR(255) NULL,
            `extra` JSON NULL,
            `roles` JSON NULL,
            PRIMARY KEY (`id`)
        )");
    }

    public function down()
    {
        $this->query("DROP TABLE `auth_users`");
    }
}
