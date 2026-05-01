<?php

namespace App\Repositories;

class InquiryRepository extends BaseRepository
{
    protected $table = 'inquiries';

    public function findByProperty($propertyId)
    {
        return $this->findBy('property_id', $propertyId);
    }

    public function getWithProperty($id)
    {
        $sql = "SELECT i.*, p.title as property_title FROM {$this->table} i
                LEFT JOIN properties p ON i.property_id = p.id
                WHERE i.id = ?";
        return $this->query($sql, [$id])->fetch();
    }

    public function getByDateRange($startDate, $endDate)
    {
        $sql = "SELECT * FROM {$this->table} WHERE created_at BETWEEN ? AND ?
                ORDER BY created_at DESC";
        return $this->query($sql, [$startDate, $endDate])->fetchAll();
    }

    public function findAll($filters = [])
    {
        $sql = "SELECT i.*, p.title as property_title FROM {$this->table} i
                LEFT JOIN properties p ON i.property_id = p.id
                WHERE 1=1";
        $params = [];

        // Filtro por tipo
        if (isset($filters['has_property'])) {
            if ($filters['has_property']) {
                $sql .= " AND i.property_id IS NOT NULL";
            } else {
                $sql .= " AND i.property_id IS NULL";
            }
        }

        // Filtro por rango de fechas
        if (isset($filters['start_date']) && isset($filters['end_date'])) {
            $sql .= " AND DATE(i.created_at) BETWEEN ? AND ?";
            $params[] = $filters['start_date'];
            $params[] = $filters['end_date'];
        } elseif (isset($filters['start_date'])) {
            $sql .= " AND DATE(i.created_at) >= ?";
            $params[] = $filters['start_date'];
        } elseif (isset($filters['end_date'])) {
            $sql .= " AND DATE(i.created_at) <= ?";
            $params[] = $filters['end_date'];
        }

        $sql .= " ORDER BY i.created_at DESC";

        return $this->query($sql, $params)->fetchAll();
    }
}
