<?php

namespace SkiddPH\Plugin\Auth;

use Exception;
use SkiddPH\Helper\Arr;
use SkiddPH\Helper\Date;
use SkiddPH\Plugin\Auth\Model\Users as ModelUsers;
use SkiddPH\Plugin\Auth\Model\Info as ModelInfo;
use SkiddPH\Plugin\Auth\Model\Roles as ModelRoles;
use SkiddPH\Plugin\Database\QueryError;

class Users
{
    private static $last_error = null;

    static function create(array $idata): int
    {
        $orm = Auth::db();

        if (!Arr::is_array($idata)) {
            $idata = [$idata];
        }

        $date = Date::parse("now", 'datetime');
        $create_up_date_default = [
            'created_at' => $date,
            'updated_at' => $date,
        ];

        $user_id = null;

        foreach ($idata as $data) {
            try {
                $orm->begin();

                if (!empty($data['pass'])) {
                    $data['hash'] = Password::hash($data['pass']);
                    unset($data['pass']);
                }

                $user_id = $orm->table(ModelUsers::TB)
                    ->data(self::injectDefaultsMany($create_up_date_default, [$data]))
                    ->insert()
                    ->lastInsertId();

                $user_cols = $orm->table(ModelUsers::TB)
                    ->columns();
                $data = Arr::from($data)->omit($user_cols)->arr();
                $roles = self::rolesFromData($data);

                if ($user_id == 1) {
                    $roles[] = 'SUPERADMIN';
                }

                if (count($roles) > 0) {
                    ModelRoles::set($user_id, $roles);
                }

                foreach ($data as $key => $value) {
                    ModelInfo::set($user_id, $key, $value);
                }

                $orm->commit();
            } catch (Exception $e) {
                $orm->rollBack();
                self::$last_error = $e->getMessage();

                $codes = [
                    '23000' => 'User already exists.'
                ];

                if (isset($codes[$e->getCode()])) {
                    throw new QueryError($codes[$e->getCode()], 400, $e);
                }

                throw new QueryError($e->getMessage(), $e->getCode(), $e);
            }
        }
        return $user_id;
    }

    static function update($id, array $data): bool
    {
        $orm = Auth::db();

        if (!Arr::is_array($data)) {
            $data = [$data];
        }

        $date = Date::parse("now", 'datetime');

        $update_default = [
            'updated_at' => $date,
        ];

        foreach ($data as $item) {
            try {
                $orm->begin();
                $date = Date::parse("now", 'datetime');

                if (!empty($item['pass'])) {
                    $item['hash'] = Password::hash($item['pass']);
                    unset($item['pass']);
                }

                $user_id = $id;
                $update_user_fields = ['user', 'hash'];
                $user_data = Arr::from($item)->pick($update_user_fields)->arr();

                if (count($user_data) > 0) {
                    $orm->table(ModelUsers::TB)
                        ->where(['id' => $user_id])
                        ->data([self::injectDefaults($update_default, $user_data)])
                        ->update();
                }

                $user_cols = $orm->table(ModelUsers::TB)
                    ->columns();
                $data = Arr::from($item)->omit($user_cols)->arr();
                $roles = self::rolesFromData($data);

                if (count($roles) > 0) {
                    ModelRoles::set($user_id, $roles);
                }

                foreach ($data as $key => $value) {
                    ModelInfo::set($user_id, $key, $value);
                }

                $orm->commit();
            } catch (Exception $e) {
                $orm->rollBack();
                self::$last_error = $e->getMessage();
                return false;
            }
        }

        return true;
    }

    static function find($where, bool $filter = true, bool $info = true)
    {
        if (!is_array($where) && preg_match('/^[0-9]+$/', $where)) {
            $user = ModelUsers::user($where);
        } else if (is_array($where) && isset($where['id'])) {
            $user = ModelUsers::user($where['id']);
        } else if (is_array($where) && isset($where['user'])) {
            $user = ModelUsers::user($where['user'], "user");
        } else if (is_array($where)) {
            $user_id = ModelInfo::find($where);
            if (empty($user_id)) {
                return null;
            }
            $user = ModelUsers::user($user_id);
        }

        if (empty($user)) {
            return null;
        }

        if ($filter) {
            $user = array_filter($user, function ($key) {
                return !in_array($key, ['user', 'last_hash', 'last_user', 'hash', 'updated_at', 'status']);
            }, ARRAY_FILTER_USE_KEY);
        }

        if ($info) {
            $user['roles'] = ModelRoles::roles($user['id']);
            $user = array_merge($user, ModelInfo::info($user['id']) ?? []);
        }
        return $user;
    }

    static function changeUsername($user_id, $username)
    {
        $user = ModelUsers::user($user_id);
        if (empty($user)) {
            throw new QueryError("User not found.", 404);
        }

        $old_user = $user['user'];

        if ($old_user == $username) {
            throw new QueryError("Username already in use.", 400);
        }

        try {
            $orm = Auth::db();
            $orm->begin();

            ModelUsers::set(['last_user' => $orm->quote($old_user)], ['last_user' => '']);
            ModelUsers::set(['last_user' => $orm->quote($username)], ['last_user' => '']);

            if (
                !ModelUsers::set($user_id, [
                    'user' => $username,
                    'last_user' => $old_user,
                    'updated_at' => Date::parse("now", 'datetime')
                ])
            ) {
                throw new QueryError("Failed to change username.", 500);
            }

            $orm->commit();
        } catch (Exception $e) {
            $orm->rollBack();

            $codes = [
                '23000' => 'Username already in use.'
            ];

            if (isset($codes[$e->getCode()])) {
                throw new QueryError($codes[$e->getCode()], 400, $e);
            }

            throw new QueryError("Failed to change username", 500, $e);
        }

        return true;
    }

    static function changePassword($user_id, $password)
    {
        $user = ModelUsers::user($user_id);
        if (empty($user)) {
            throw new QueryError("User not found.", 404);
        }

        $old_hash = $user['hash'];
        $last_hash = $user['last_hash'];

        if (Password::verify($password, $last_hash)) {
            throw new QueryError("Cannot use old password.", 400);
        }

        if (Password::verify($password, $old_hash)) {
            throw new QueryError("Cannot use current password.", 400);
        }

        try {
            $orm = Auth::db();
            $orm->begin();

            if (
                !ModelUsers::set($user_id, [
                    'hash' => Password::hash($password),
                    'last_hash' => $old_hash,
                    'updated_at' => Date::parse("now", 'datetime')
                ])
            ) {
                throw new QueryError("Failed to change password.", 500);
            }

            $orm->commit();
        } catch (Exception $e) {
            $orm->rollBack();
            throw new QueryError("Failed to change password", 500, $e);
        }

        return true;
    }

    static function addRole($user_id, $roles)
    {
        if (empty($roles)) {
            throw new Exception("Invalid role.", 400);
        }

        $add = ModelRoles::add($user_id, $roles);

        if (!$add || $add === 0) {
            throw new Exception("Failed to add role.", 500);
        }

        return true;
    }

    static function removeRole($user_id, $roles)
    {
        if (empty($roles)) {
            throw new Exception("Invalid role.", 400);
        }

        $remove = ModelRoles::remove($user_id, $roles);

        if (!$remove || $remove === 0) {
            throw new Exception("Failed to remove role.", 500);
        }

        return true;
    }

    static function publicUser($user)
    {
        $user = array_filter($user, function ($key) {
            return in_array($key, ['id', 'fname', 'lname', 'email', 'user', 'roles']);
        }, ARRAY_FILTER_USE_KEY);

        return $user;
    }

    public static function lastError(): ?string
    {
        return self::$last_error;
    }

    private static function rolesFromData(array &$data): array
    {
        $roles = null;
        if (isset($data['roles'])) {
            $roles = $data['roles'];
            unset($data['roles']);
        } else if (isset($data['role'])) {
            $roles = $data['role'];
            unset($data['role']);
        } else {
            $roles = [];
        }

        if (is_array($roles)) {
            return $roles;
        }

        if (strpos($roles, '|') !== false) {
            return explode('|', $roles);
        } else if (strpos($roles, ',') !== false) {
            return explode(',', $roles);
        } else if (!empty($roles)) {
            return [$roles];
        } else {
            return [];
        }
    }

    private static function injectDefaultsMany(array $defaults, array $data): array
    {
        return Arr::from($data)->map(function ($item) use ($defaults) {
            return Arr::from($defaults)->merge($item)->arr();
        })->arr();
    }

    private static function injectDefaults(array $defaults, array $data): array
    {
        return Arr::from($defaults)->merge($data)->arr();
    }
}
