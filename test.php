<?php

require_once __DIR__ . '/vendor/autoload.php';

use SkiddPH\Core\Bootstrapper;
use SkiddPH\Helper\Date;
use SkiddPH\Model\UserEmail;
use SkiddPH\Model\UserInfo;
use SkiddPH\Model\UserRole;
use SkiddPH\Model\User;

Bootstrapper::init(__DIR__);

// var_dump(User::insert([
//     'user' => 'admin',
//     'hash' => password_hash('admin', PASSWORD_BCRYPT),
//     'created_at' => Date::parse('now', 'datetime'),
//     'updated_at' => Date::parse('now', 'datetime'),
// ]));

// var_dump(UserInfo::upsertFor(1, [
//     'fname' => 'Yeoli',
//     'lname' => 'Goa',
//     'mname' => 'Kino',
// ]));

// var_dump(UserEmail::insert([
//     'email' => 'yeoligoakino@gmail.com',
//     'user_id' => 1,
//     'verified' => true,
//     'created_at' => Date::parse('now', 'datetime'),
//     'updated_at' => Date::parse('now', 'datetime'),
// ]));

// var_dump(UserRole::upsertFor(1, [
//     'admin',
//     'superadmin'
// ]));

print_r(User::details(1));

// var_dump(UserEmail::inUse('yeoligoakino@gmail.com'));