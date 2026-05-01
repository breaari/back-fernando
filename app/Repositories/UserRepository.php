<?php

namespace App\Repositories;

class UserRepository extends BaseRepository
{
    protected $table = 'users';

    public function findByEmail($email)
    {
        return $this->findOneBy('email', $email);
    }

    public function findByRealEstate($realEstateId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE real_estate_id = ?";
        return $this->query($sql, [$realEstateId])->fetchAll();
    }

    public function activeUsers($limit = null, $offset = 0)
    {
        $sql = "SELECT * FROM {$this->table} WHERE active = 1";
        if ($limit) {
            $sql .= " LIMIT $limit OFFSET $offset";
        }
        return $this->query($sql)->fetchAll();
    }

    public function findWithRealEstate($id)
    {
        $sql = "SELECT u.*, r.name as real_estate_name, r.email as real_estate_email, r.phone as real_estate_phone
                FROM {$this->table} u
                LEFT JOIN real_estates r ON u.real_estate_id = r.id
                WHERE u.id = ?";
        return $this->query($sql, [$id])->fetch();
    }
}
