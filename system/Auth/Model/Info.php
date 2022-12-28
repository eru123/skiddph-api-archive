<?php

namespace Api\Auth\Model;

use Api\Lib\Date;
use Api\Lib\Arr;
use Auth;
use Api\Database\Model;
use Api\Database\Helper;

class Info
{
    const TB = 'auth_users_info';

    // public static function info(array |int $user_ids = null)
    // {
    //     $orm = Auth::db()->table(self::TB, 'a');
    //     $infos = $orm->select('a.id', 'id')
    //         ->select('b.parent_id', 'parent')
    //         ->select('a.user_id', 'user_id')
    //         ->select('a.name', 'name')
    //         ->select('a.value', 'value')
    //         ->select('b.name', 'sub_name')
    //         ->select('b.value', 'sub_value')
    //         ->leftJoin(self::TB, 'b')
    //         ->on('a.id = b.parent_id')
    //         ->where([
    //             'a.user_id' => is_array($user_ids) ? [
    //                 'IN' => $user_ids
    //             ] : $user_ids
    //         ])
    //         ->and()
    //         ->where([
    //             'a.parent_id' => [
    //                 'IS' => 'NULL'
    //             ]
    //         ])
    //         ->order('a.id', 'ASC')
    //         ->order('a.updated_at', 'DESC')
    //         ->readMany()
    //         ->arr();

    //     $users = [];
    //     foreach ($infos as $info) {
    //         $user_id = $info['user_id'];
    //         if (!isset($users[$user_id])) {
    //             $users[$user_id] = [];
    //         }
    //         $users[$user_id][] = $info;
    //     }

    //     unset($infos);
    //     foreach ($users as $user_id => $user) {
    //         $tmp_user = [];

    //         // sort by parent_id
    //         usort($user, function ($a, $b) {
    //             return $a['parent'] <=> $b['parent'];
    //         });

    //         foreach ($user as $info) {
    //             $name = $info['name'];
    //             $value = $info['value'];
    //             $parent = $info['parent'];
    //             $sub_name = $info['sub_name'];
    //             $sub_value = $info['sub_value'];
    //             if (is_null($parent)) {
    //                 $tmp_user[$name] = Helper::jsonDecode($value);
    //             } else {
    //                 if (!isset($tmp_user[$name])) {
    //                     $tmp_user[$name] = [];
    //                 }
    //                 $tmp_user[$name][$sub_name] = Helper::jsonDecode($sub_value);
    //             }
    //         }

    //         $users[$user_id] = $tmp_user;
    //     }

    //     return is_array($user_ids) ? $users : @$users[$user_ids];
    // }

    public static function info(array |int $user_ids = null)
    {
        $orm = Auth::db()->table(self::TB, 'a');
        $infos = $orm->select('a.id', 'id')
            ->select('b.parent_id', 'parent')
            ->select('a.user_id', 'user_id')
            ->select('a.name', 'name')
            ->select('a.value', 'value')
            ->select('b.name', 'sub_name')
            ->select('b.value', 'sub_value')
            ->leftJoin(self::TB, 'b')
            ->on('a.id = b.parent_id')
            ->where([
                'a.user_id' => is_array($user_ids) ? [
                    'IN' => $user_ids
                ] : $user_ids
            ])
            ->and()
            ->where([
                'a.parent_id' => 0
            ])
            ->order('a.id', 'ASC')
            ->order('a.updated_at', 'DESC')
            ->readMany()
            ->arr();

        $users = [];
        foreach ($infos as $info) {
            $user_id = $info['user_id'];
            if (!isset($users[$user_id])) {
                $users[$user_id] = [];
            }
            $users[$user_id][] = $info;
        }
        // echo ">>", (json_encode($users, JSON_PRETTY_PRINT)), PHP_EOL;
        return is_array($user_ids) ? self::parse_users_info($users) : self::parse_user_info(@$users[$user_ids] ?? []);
    }

    public static function parse_users_info(array $users)
    {
        foreach ($users as $user_id => $user) {
            $users[$user_id] = self::parse_user_info($user);
        }
        return $users;
    }

    public static function parse_user_info(array $user)
    {
        $tmp_user = [];

        foreach ($user as $info) {
            $name = $info['name'];
            $value = $info['value'];
            $parent = $info['parent'];
            $sub_name = $info['sub_name'];
            $sub_value = $info['sub_value'];

            if (!isset($tmp_user[$name])) {
                $tmp_user[$name] = [
                    'attr' => [],
                    'data' => []
                ];
            }

            if (is_null($parent)) {
                $tmp_user[$name]['data'][] = Helper::jsonDecode($value);
            } else {
                $tmp_user[$name]['attr'][$sub_name] = Helper::jsonDecode($sub_value);
            }
        }

        if (empty($tmp_user)) {
            return null;
        }

        foreach ($tmp_user as $name => $data) {
            if (empty($data['attr']) && empty($data['data'])) {
                $tmp_user[$name] = null;
                continue;
            }

            if (!empty($data['attr'])) {
                $tmp_user[$name] = $data['attr'];
                continue;
            }

            if (!empty($data['data']) && count($data['data']) == 1) {
                $tmp_user[$name] = $data['data'][0];
                continue;
            }

            if (!empty($data['data'])) {
                $tmp_user[$name] = $data['data'];
                continue;
            }

            $tmp_user[$name] = null;
        }

        return $tmp_user;
    }

    public static function set(int $user_id, string $name, $value)
    {
        $orm = Auth::db();

        if (is_array($value)) {
            if (Arr::is_array($value)) {
                $affected = 0;
                foreach ($value as $v) {
                    $affected += $orm->table(self::TB)
                        ->data([
                            [
                                'user_id' => $user_id,
                                'name' => $name,
                                'value' => Helper::jsonEncode($v),
                                'updated_at' => Date::parse('now', 'datetime'),
                            ]
                        ])
                        ->upsert()
                        ->rowCount();
                }
                return $affected == count($value);
            } else {
                $parent = $orm->table(self::TB)
                    ->where([
                        'user_id' => $user_id
                    ])
                    ->and()
                    ->where([
                        'name' => $orm->quote($name),
                    ])
                    ->and()
                    ->where([
                        'parent_id' => 0
                    ])
                    ->readOne()
                    ->arr();

                if (empty($parent)) {
                    $parent_id = $orm->table(self::TB)
                        ->data([
                            [
                                'user_id' => $user_id,
                                'name' => $name,
                                'value' => '',
                                'created_at' => Date::parse('now', 'datetime'),
                                'updated_at' => Date::parse('now', 'datetime'),
                            ]
                        ])
                        ->insert()
                        ->lastInsertId();
                } else {
                    $parent_id = $parent['id'];
                }

                $orm->table(self::TB)
                    ->where([
                        'parent_id' => $parent_id
                    ])
                    ->delete();

                $affected = 0;

                foreach ($value as $sub_name => $sub_value) {
                    $affected += $orm->table(self::TB)
                        ->data([
                            [
                                'user_id' => $user_id,
                                'name' => $sub_name,
                                'value' => Helper::jsonEncode($sub_value),
                                'parent_id' => $parent_id,
                                'created_at' => Date::parse('now', 'datetime'),
                                'updated_at' => Date::parse('now', 'datetime'),
                            ]
                        ])
                        ->insert()
                        ->rowCount();
                }

                if ($affected > 0) {
                    $orm->table(self::TB)
                        ->where([
                            'id' => $parent_id
                        ])
                        ->data([
                            ['updated_at' => Date::parse('now', 'datetime')]
                        ])
                        ->update();
                }

                return $affected > 0;
            }
        }

        return $orm->table(self::TB)
            ->data([
                [
                    'user_id' => $user_id,
                    'name' => $name,
                    'value' => Helper::jsonEncode($value),
                    'updated_at' => Date::parse('now', 'datetime'),
                ]
            ])
            ->upsert()
            ->rowCount() > 0;
    }

    public static function find($where): int|null
    {
        $orm = Auth::db();

        foreach ($where as $name => $value) {
            if (is_array($value)) {
                continue;
            }

            $user = $orm->table(self::TB)
                ->select('user_id')
                ->where([
                    'name' => $orm->quote($name)
                ])
                ->and()
                ->where([
                    'value' => $orm->quote(Helper::jsonEncode($value))
                ])
                ->readOne()
                ->arr();

            if (!empty($user)) {
                return $user['user_id'];
            }
        }

        return null;
    }
}