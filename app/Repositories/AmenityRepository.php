<?php

namespace App\Repositories;

class AmenityRepository extends BaseRepository
{
    protected $table = 'amenities';

    public function active()
    {
        return $this->findBy('active', 1);
    }

    public function findByProperty($propertyId)
    {
        $sql = "SELECT a.* FROM amenities a
                JOIN property_amenities pa ON a.id = pa.amenity_id
                WHERE pa.property_id = ?";
        return $this->query($sql, [$propertyId])->fetchAll();
    }

    public function addToProperty($propertyId, $amenityId)
    {
        $sql = "INSERT INTO property_amenities (property_id, amenity_id) VALUES (?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$propertyId, $amenityId]);
    }

    public function removeFromProperty($propertyId, $amenityId)
    {
        $sql = "DELETE FROM property_amenities WHERE property_id = ? AND amenity_id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$propertyId, $amenityId]);
    }
}
