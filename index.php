<?php

use Api\Auth\User;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/vendor/autoload.php';

use Api\Auth\Users;

Bootstrapper::init(__DIR__);

echo "START", PHP_EOL;


Users::create([
    'user' => 'admin',
    'pass' => 'pass',
    'profile' => [
        'email' => 'admin@localhost',
        'verified' => false,
    ],
    'age' => 22,
    'role' => 'SUPERADMIN'
]);

// Users::update(1, [
//     'user' => 'admin1',
//     'pass' => 'pass1',
//     // 'role' => 'SUPER|ADMIN',
//     'profile' => 'x',
// ]);

// $orm = Auth::db();

// try {
//     $orm->begin();

//     $user_id = $orm->table('auth_users')
//         ->data([
//             [
//                 'user' => 'admin',
//                 'hash' => password_hash('admin', PASSWORD_DEFAULT),
//                 'created_at' => date('Y-m-d H:i:s'),
//                 'updated_at' => date('Y-m-d H:i:s'),
//             ]
//         ])
//         ->insert()
//         ->lastInsertId();

//     $email_id = $orm->table('auth_users_info')
//         ->data([
//             ['user_id' => $user_id, 'name' => 'email', 'value' => 'admin@localhost', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
//         ])
//         ->insert()
//         ->lastInsertId();

//     $orm->table('auth_users_info')
//         ->data([
//             ['parent_id' => $email_id, 'user_id' => $user_id, 'name' => 'verified', 'value' => 'false', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
//         ])
//         ->insert();

//     $email_id = $orm->table('auth_users_info')
//         ->data([
//             ['user_id' => $user_id, 'name' => 'email', 'value' => 'admin1@localhost', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
//         ])
//         ->insert()
//         ->lastInsertId();

//     $orm->table('auth_users_info')
//         ->data([
//             ['parent_id' => $email_id, 'user_id' => $user_id, 'name' => 'verified', 'value' => 'false', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
//         ])
//         ->insert();

//     $orm->table('auth_users_role')
//         ->data([
//             ['user_id' => $user_id, 'role' => 'SUPERADMIN', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
//             ['user_id' => $user_id, 'role' => 'ADMIN', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
//         ])
//         ->insert();

//     $orm->commit();
// } catch (Exception $e) {
//     echo $e->getMessage(), PHP_EOL;
//     $orm->rollback();
// } catch (PDOException $e) {
//     echo $e->getMessage(), PHP_EOL;
//     $orm->rollback();
// }

echo PHP_EOL, "END", PHP_EOL;
