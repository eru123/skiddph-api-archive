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

    public static function parse_roles($roles)
    {
        if (!is_array($roles)) {
            $delims = ['|', ',', ' '];
            foreach ($delims as $delim) {
                if (strpos($roles, $delim) !== false) {
                    $roles = explode($delim, $roles);
                    break;
                }
            }

            if (!is_array($roles)) {
                $roles = [$roles];
            }
        }

        $pre_res = array_map(function ($role) {
            $role = trim($role);
            $role = strtoupper($role);
            return $role;
        }, $roles);

        return array_filter($pre_res, function ($role) {
            return !empty($role);
        });
    }

    public static function set(int $user_id, $role)
    {
        $roles = self::parse_roles($role);
        $roles = array_map(function ($role) use ($user_id) {
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
            ->select('GROUP_CONCAT(ROLE)', 'roles')
            ->where([
                'user_id' => $user_id
            ])
            ->readOne()
            ->arr();

        if (empty($roles) || empty($roles['roles'])) {
            return [];
        }

        return explode(',', $roles['roles']);
    }

    public static function add(int $user_id, $role)
    {
        $roles = self::parse_roles($role);
        $current_roles = self::roles($user_id);

        $roles = array_filter($roles, function ($role) use ($current_roles) {
            return !in_array($role, $current_roles);
        });

        $roles = array_map(function ($role) use ($user_id) {
            return [
                'user_id' => $user_id,
                'role' => $role
            ];
        }, $roles);

        if (empty($roles)) {
            return false;
        }

        return Auth::db()->table(self::TB)
            ->data($roles)
            ->insert()
            ->rowCount() > 0;
    }

    public static function remove(int $user_id, $role)
    {
        $orm = Auth::db();
        $roles = self::parse_roles($role);
        $current_roles = self::roles($user_id);

        $roles = array_filter($roles, function ($role) use ($current_roles) {
            return in_array($role, $current_roles);
        });

        $roles = array_map(function ($role) use ($orm) {
            return $orm->quote($role);
        }, $roles);

        if (empty($roles)) {
            return false;
        }

        return $orm->table(self::TB)
            ->where([
                'user_id' => $user_id
            ])
            ->and()
            ->where([
                'role' => [
                    'IN' => $roles
                ]
            ])
            ->delete()
            ->rowCount() > 0;
    }

    public static function has(int $user_id, $roles)
    {
        $roles = self::parse_roles($roles);
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
