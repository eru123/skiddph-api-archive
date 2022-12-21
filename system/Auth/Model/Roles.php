<?php

namespace Api\Auth\Model;

use Auth;
use Api\Database\Model;

class Roles extends Model
{
    const TB = 'auth_users_role';
    public function __construct()
    {
        parent::__construct(Auth::db(), self::TB);
    }

    public static function set(int $user_id, $role)
    {
        $roles = [];
        if (is_array($role)) {
            $roles = $role;
        } else {
            $delims = ['|', ',', ' '];
            foreach ($delims as $delim) {
                if (strpos($role, $delim) !== false) {
                    $roles = explode($delim, $role);
                    break;
                }
            }
        }
        $roles = array_map(function ($role) use ($user_id) {
            $role = trim($role);
            $role = strtoupper($role);
            return [
                'user_id' => $user_id,
                'role' => $role
            ];
        }, $roles);

        $orm = Auth::db();

        $orm->table(self::TB)
            ->where([
                'user_id' => $user_id
            ])
            ->delete();

        return $orm->table(self::TB)
            ->data($roles)
            ->insert()
            ->rowCount() > 0;
    }

    public static function roles(int $user_id)
    {
        $roles = Auth::db()->table(self::TB)
            ->select('role')
            ->where([
                'user_id' => $user_id
            ])
            ->readMany()
            ->arr();

        return array_map(function ($role) {
            return $role['role'];
        }, $roles);
    }

    public static function add(int $user_id, $role)
    {
        $roles = [];
        if (is_array($role)) {
            $roles = $role;
        } else {
            $delims = ['|', ',', ' '];
            foreach ($delims as $delim) {
                if (strpos($role, $delim) !== false) {
                    $roles = explode($delim, $role);
                    break;
                }
            }
        }
        $roles = array_map(function ($role) use ($user_id) {
            $role = trim($role);
            $role = strtoupper($role);
            return [
                'user_id' => $user_id,
                'role' => $role
            ];
        }, $roles);

        return Auth::db()->table(self::TB)
            ->data($roles)
            ->upsert()
            ->rowCount() > 0;
    }

    public static function has(int $user_id, $roles)
    {
        if (is_array($roles)) {
            $roles = array_map(function ($role) {
                $role = trim($role);
                $role = strtoupper($role);
                return $role;
            }, $roles);
        } else {
            $delims = ['|', ',', ' '];
            foreach ($delims as $delim) {
                if (strpos($roles, $delim) !== false) {
                    $roles = explode($delim, $roles);
                    break;
                }
            }
        }
        
        $res = Auth::db()->table(self::TB)
            ->select('role')
            ->where([
                'user_id' => $user_id,
            ])
            ->and()
            ->where([
                'role' => [
                    'IN' => $roles
                ]
            ])
            ->readOne()
            ->arr();

        return !empty($res);
    }
}