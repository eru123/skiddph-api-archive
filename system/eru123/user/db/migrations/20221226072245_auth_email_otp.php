<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AuthEmailOtp extends AbstractMigration
{
    public function up()
    {
        $this->query("CREATE TABLE `auth_email_otp` (
            `id` INT NOT NULL AUTO_INCREMENT,
            `user_id` INT NOT NULL,
            `email` VARCHAR(255) NOT NULL,
            `code` VARCHAR(255) NOT NULL,
            `hash` VARCHAR(255) NOT NULL,
            `callback` VARCHAR(255) NULL,
            `used` TINYINT(1) NOT NULL DEFAULT 0,
            `created_at` DATETIME NULL,
            `updated_at` DATETIME NULL,
            `deleted_at` DATETIME NULL,
            PRIMARY KEY (`id`)
        )");
    }

    public function down()
    {
        $this->query("DROP TABLE `auth_email_otp`");
    }
}