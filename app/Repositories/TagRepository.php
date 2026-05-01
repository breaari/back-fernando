<?php

namespace App\Repositories;

class TagRepository extends BaseRepository
{
    protected $table = 'tags';

    public function active()
    {
        return $this->findBy('active', 1);
    }

    public function findByProperty($propertyId)
    {
        $sql = "SELECT t.* FROM tags t
                JOIN property_tags pt ON t.id = pt.tag_id
                WHERE pt.property_id = ?";
        return $this->query($sql, [$propertyId])->fetchAll();
    }

    public function addToProperty($propertyId, $tagId)
    {
        $sql = "INSERT INTO property_tags (property_id, tag_id) VALUES (?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$propertyId, $tagId]);
    }

    public function removeFromProperty($propertyId, $tagId)
    {
        $sql = "DELETE FROM property_tags WHERE property_id = ? AND tag_id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$propertyId, $tagId]);
    }
}
