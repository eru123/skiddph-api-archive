<?php

require_once __DIR__ . '/vendor/autoload.php';

use SkiddPH\Core\Bootstrapper;
use SkiddPH\Model\User;
use SkiddPH\Plugin\DB\DB;

Bootstrapper::init(__DIR__);

// $user = User::first('user', 'admin');
// if ($user) {
//     echo json_encode($user->strip()->array(), JSON_PRETTY_PRINT) . PHP_EOL;
//     $user->delete();
// }

// $user = User::first('user', 'admin123');
// if ($user) {
//     echo json_encode($user->strip()->array(), JSON_PRETTY_PRINT) . PHP_EOL;
//     $user->delete();
// }



// for ($i = 0; $i < 1000; $i++) {
//     try {
//         $user = User::create();

//         $user->user = 'admin_' . $i;
//         $user->hash = 'pass';
//         $user->save();
//     } catch (Exception $e) {
//         echo $e->getMessage() . PHP_EOL;
//     }
// }

$users = User::get();
// $effected = $users->update([
//     'status' => 'active'
// ]);

// echo 'Effected: ' . $effected . PHP_EOL;

// // foreach ($users as $user) {
// //     echo 'X >> ' . json_encode($user->strip()->array(), JSON_PRETTY_PRINT) . PHP_EOL;
// // }


var_dump($users->count());
var_dump($users->delete());

// $alt = User::first('user', 'admin123');
// if ($alt) {
//     $alt->delete();
// }
// sleep(1);
// $user->user = 'admin123';
// $user->updated_at = DB::raw('NOW()');
// $user->update();