<?php

namespace App\Repositories;

class PropertyImageRepository extends BaseRepository
{
    protected $table = 'property_images';

    public function findByProperty($propertyId)
    {
        return $this->findBy('property_id', $propertyId);
    }

    public function getCoverImage($propertyId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE property_id = ? AND is_cover = 1 LIMIT 1";
        return $this->query($sql, [$propertyId])->fetch();
    }

    public function getOrderedByPosition($propertyId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE property_id = ? ORDER BY position ASC";
        return $this->query($sql, [$propertyId])->fetchAll();
    }
}
