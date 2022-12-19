<?php

namespace Api\Auth\Model;

use Auth;
use Api\Database\Model;
use Api\Lib\Arr;

class Info extends Model
{
    const TB = 'auth_users_info';
    public function __construct()
    {
        parent::__construct(Auth::db(), self::TB);
    }

    public static function userInfo(array|int $user_ids = null)
    {
        $orm = Auth::db();
        $infos = $orm->select('a.id', 'id')
            ->table(self::TB, 'a')
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
                'a.parent_id' => [
                    'IS' => 'NULL'
                ]
            ])
            ->order('a.id', 'ASC')
            ->order('a.updated_at', 'DESC')
            ->readMany()
            ->arr();

        $parents = [];
        $res = [];
        print_r($infos);
        foreach ($infos as $info) {
            $id = $info['id'];
            var_dump($info['parent']);
            if ($info['parent'] == NULL) {
                echo $info['user_id'] . ' - ' . $info['name'] . ' - ' . $info['value'], PHP_EOL;
                $res[$info['user_id']][$info['name']] = $info['value'];
                $parents[$id] = $info['name'];
                unset($infos[$id]);
            } else {
                echo ">>".$info['user_id'] . ' - ' . $parents[$info['parent']] . ' - ' . $info['sub_name'] . ' - ' . $info['sub_value'], PHP_EOL;
                // $res[$info['user_id']][$parents[$info['parent']]][$info['sub_name']] = $info['sub_value'];
                // unset($infos[$id]);
            }
        }
        // foreach ($infos as $id => $info) {
        //     $res[$info['user_id']][$parents[$info['parent']]][$info['sub_name']] = $info['sub_value'];
        // }
        // print_r($infos);
        return $res;
    }
}
