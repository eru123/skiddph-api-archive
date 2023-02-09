<?php

namespace SkiddPH\Model;

use PDO;
use SkiddPH\Helper\Date;
use SkiddPH\Plugin\DB\DB;
use SkiddPH\Plugin\DB\Model;
use SkiddPH\Plugin\DB\Row;
use SkiddPH\Plugin\Auth\Password;

class User extends Model
{
    protected $table = 'auth_users';

    public function set__hash($password)
    {
        return Password::hash($password);
    }

    public function verifyPassword($password, Row $row)
    {
        return Password::verify($password, $row->hash);
    }

    public function strip($row)
    {
        $data = $row->array();
        unset($data['hash']);
        unset($data['last_user']);
        unset($data['last_hash']);
        return new Row($this, $data);
    }

    public function insert__created_at()
    {
        return Date::parse('now', 'datetime');
    }

    public function insert__updated_at()
    {
        return Date::parse('now', 'datetime');
    }

    public function update__updated_at()
    {
        return Date::parse('now', 'datetime');
    }

    protected function f__details(int $id)
    {
        $query = DB::raw("SELECT auth_users.*, 
                (SELECT GROUP_CONCAT(NAME, '=', value) FROM auth_users_info WHERE auth_users_info.user_id = auth_users.id) AS info, 
                (SELECT GROUP_CONCAT(role) FROM auth_users_role WHERE auth_users_role.user_id = auth_users.id) AS roles,
                (SELECT GROUP_CONCAT(email) FROM auth_users_email WHERE auth_users_email.user_id = auth_users.id AND verified = 1) AS emails,
                (SELECT GROUP_CONCAT(email) FROM auth_users_email WHERE auth_users_email.user_id = auth_users.id AND verified = 0) AS pending_emails
            FROM
                auth_users;
            WHERE
                auth_users.id = ?
        ", [$id]);

        $stmt = $this->pdo()->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        $info = [];
        if (!empty($row['info'])) {
            $rgx = '/(?<name>[^=,]+)=(?<value>[^,]+)/';
            preg_match_all($rgx, $row['info'], $matches, PREG_SET_ORDER, 0);
            foreach ($matches as $match) {
                $info[$match['name']] = json_decode($match['value']);
            }
        }

        $row = array_merge($row, $info);
        $row['roles'] = !empty($row['roles']) ? explode(',', $row['roles']) : [];
        $row['emails'] = !empty($row['emails']) ? explode(',', $row['emails']) : [];
        $row['pending_emails'] = !empty($row['pending_emails']) ? explode(',', $row['pending_emails']) : [];

        unset($row['info']);
        unset($row['hash']);
        unset($row['last_user']);
        unset($row['last_hash']);
        unset($row['created_at']);
        unset($row['updated_at']);
        return $row;
    }
}