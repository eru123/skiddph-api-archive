<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AuthUsersEmail extends AbstractMigration
{
    public function up()
    {
        $this->query("CREATE TABLE `auth_users_email` (
            `id` INT NOT NULL AUTO_INCREMENT,
            `user_id` INT NOT NULL,
            `email` VARCHAR(255) NOT NULL,
            `is_default` TINYINT(1) NOT NULL DEFAULT 0,
            `verified` TINYINT(1) NOT NULL DEFAULT 0,
            `created_at` DATETIME NULL,
            `updated_at` DATETIME NULL,
            `deleted_at` DATETIME NULL,
            PRIMARY KEY (`id`)
        )");
    }

    public function down()
    {
        $this->query("DROP TABLE `auth_users_email`");
    }
}