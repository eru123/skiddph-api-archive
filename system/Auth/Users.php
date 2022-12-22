<?php

namespace Api\Auth;

use Exception;
use Auth;

use Api\Auth\Model\{
    Users as ModelUsers,
    Info as ModelInfo,
    Roles as ModelRoles
};

use Api\Lib\{
    Arr,
    Date
};

use Api\Database\QueryError;

class Users
{
    private static $last_error = null;

    static function create(array $idata): bool
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
                throw new QueryError($e->getMessage(), $e->getCode(), $e);
            }
        }

        return true;
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
                return !in_array($key, ['last_hash', 'last_user', 'hash', 'updated_at', 'status']);
            }, ARRAY_FILTER_USE_KEY);
        }

        if ($info) {
            $user['roles'] = ModelRoles::roles($user['id']);
            $user = array_merge($user, ModelInfo::info($user['id']) ?? []);
        }
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