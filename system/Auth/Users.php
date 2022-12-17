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
                    $orm->table(ModelRoles::TB)
                        ->data(
                            self::injectDefaultsMany(
                                $create_up_date_default,
                                Arr::from($roles)->map(function ($role) use ($user_id) {
                                    return ['user_id' => $user_id, 'role' => $role];
                                })->arr()
                            )
                        )
                        ->insert();
                }

                $single_info = [];
                $multi_info = [];

                foreach ($data as $key => $value) {
                    if (is_array($value)) {
                        $multi_info[$key] = $value;
                    } else {
                        $single_info[$key] = $value;
                    }
                }

                if (count($single_info) > 0) {
                    $orm->table(ModelInfo::TB)
                        ->data(
                            self::injectDefaultsMany(
                                $create_up_date_default,
                                Arr::from($single_info)->map(function ($value, $key) use ($user_id) {
                                    return ['user_id' => $user_id, 'name' => $key, 'value' => $value];
                                })->arr()
                            )
                        )
                        ->insert();
                }

                if (count($multi_info) > 0) {
                    foreach ($multi_info as $key => $values) {
                        $info_id = $orm->table(ModelInfo::TB)
                            ->data([
                                self::injectDefaults(
                                    $create_up_date_default,
                                    ['user_id' => $user_id, 'name' => $key, 'value' => null]
                                )
                            ])
                            ->insert()
                            ->lastInsertId();

                        $orm->table(ModelInfo::TB)
                            ->data(
                                self::injectDefaultsMany(
                                    $create_up_date_default,
                                    Arr::from($values)->map(function ($value, $attr) use ($info_id, $user_id) {
                                        return ['parent_id' => $info_id, 'user_id' => $user_id, 'name' => $attr, 'value' => $value];
                                    })->arr()
                                )
                            )
                            ->insert();
                    }
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
        $create_default = [
            'created_at' => $date,
            'updated_at' => $date,
        ];

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
                    echo "user_data: " . print_r($user_data, true), PHP_EOL;
                    echo "sql: ", print_r($orm->getLastQuery(), true), PHP_EOL;
                    echo "affected rows: ", print_r($orm->rowCount(), true), PHP_EOL;
                }

                $user_cols = $orm->table(ModelUsers::TB)
                    ->columns();
                $data = Arr::from($item)->omit($user_cols)->arr();
                $roles = self::rolesFromData($data);

                if (count($roles) > 0) {
                    $orm->table(ModelRoles::TB)
                        ->where(['user_id' => $user_id])
                        ->delete();

                    $orm->table(ModelRoles::TB)
                        ->data(
                            self::injectDefaultsMany(
                                $create_default,
                                Arr::from($roles)->map(function ($role) use ($user_id) {
                                    return ['user_id' => $user_id, 'role' => $role];
                                })->arr()
                            )
                        )
                        ->insert();
                }

                $single_info = [];
                $multi_info = [];

                foreach ($data as $key => $value) {
                    if (is_array($value)) {
                        $multi_info[$key] = $value;
                    } else {
                        $single_info[$key] = $value;
                    }
                }

                echo 'count($single_info): ', count($single_info), ">> ", print_r($single_info, true), PHP_EOL;

                if (count($single_info) > 0) {
                    foreach ($single_info as $key => $value) {
                        echo "key: ", $key, " value: ", $value, " user_id: ", $user_id, PHP_EOL;
                        $info_id = $orm->table(ModelInfo::TB)
                            ->where([
                                'user_id' => $user_id,
                                'name' => $key,
                            ])
                            ->and()
                            ->where($orm->f('ISNULL(?)', 'parent_id')->query())
                            ->select('id')
                            ->readOne();
                        // $info_id = $orm->table(ModelInfo::TB)
                        //     ->where([
                        //         'user_id' => $user_id,
                        //         // 'parent_id' => null,
                        //         // 'name' => $key,
                        //     ])
                        //     // ->select('id')
                        //     ->readMany();
                        echo "info_id: ", print_r($info_id, true), PHP_EOL;
                        // if ($info_id) {
                        //     $orm->table(ModelInfo::TB)
                        //         ->data(self::injectDefaults($update_default, ['value' => $value]))
                        //         ->where(['id' => $info_id])
                        //         ->update();
                        // } else {
                        //     $orm->table(ModelInfo::TB)
                        //         ->data(
                        //             self::injectDefaults(
                        //                 $update_default,
                        //                 ['user_id' => $user_id, 'name' => $key, 'value' => $value]
                        //             )
                        //         )
                        //         ->insert();
                        // }
                    }
                }

                // if (count($multi_info) > 0) {
                //     foreach ($multi_info as $key => $values) {
                //         $info_id = $orm->table(ModelInfo::TB)
                //             ->where([
                //                 'user_id' => $user_id,
                //                 'parent_id' => null,
                //                 'name' => $key,
                //             ])
                //             ->select('id')
                //             ->readOne();

                //         echo print_r($info_id, true), PHP_EOL;

                //         if ($info_id) {
                //             $orm->table(ModelInfo::TB)
                //                 ->where(['parent_id' => $info_id])
                //                 ->delete();

                //             $orm->table(ModelInfo::TB)
                //                 ->data(
                //                     self::injectDefaultsMany(
                //                         $update_default,
                //                         Arr::from($values)->map(function ($value, $attr) use ($info_id, $user_id) {
                //                             return ['parent_id' => $info_id, 'user_id' => $user_id, 'name' => $attr, 'value' => $value];
                //                         })->arr()
                //                     )
                //                 )
                //                 ->insert();
                //         } else {
                //             $info_id = $orm->table(ModelInfo::TB)
                //                 ->data([
                //                     self::injectDefaults(
                //                         $update_default,
                //                         ['user_id' => $user_id, 'name' => $key, 'value' => null]
                //                     )
                //                 ])
                //                 ->insert()
                //                 ->lastInsertId();

                //             $orm->table(ModelInfo::TB)
                //                 ->data(
                //                     self::injectDefaultsMany(
                //                         $update_default,
                //                         Arr::from($values)->map(function ($value, $attr) use ($info_id, $user_id) {
                //                             return ['parent_id' => $info_id, 'user_id' => $user_id, 'name' => $attr, 'value' => $value];
                //                         })->arr()
                //                     )
                //                 )
                //                 ->insert();
                //         }
                //     }
                // }

                $orm->commit();
            } catch (Exception $e) {
                $orm->rollBack();
                self::$last_error = $e->getMessage();
                return false;
            }
        }

        return true;
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
