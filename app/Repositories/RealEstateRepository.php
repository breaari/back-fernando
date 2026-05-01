<?php

namespace App\Repositories;

class RealEstateRepository extends BaseRepository
{
    protected $table = 'real_estates';

    public function active()
    {
        return $this->findBy('active', 1);
    }

    public function getWithUsers($id)
    {
        $sql = "SELECT re.*, COUNT(u.id) as total_users FROM {$this->table} re
                LEFT JOIN users u ON re.id = u.real_estate_id
                WHERE re.id = ?
                GROUP BY re.id";
        return $this->query($sql, [$id])->fetch();
    }
}
