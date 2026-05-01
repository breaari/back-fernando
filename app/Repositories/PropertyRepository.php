<?php

namespace App\Repositories;

class PropertyRepository extends BaseRepository
{
    protected $table = 'properties';

    public function paginate($perPage = 15, $page = 1, $filters = [])
    {
        $offset = ($page - 1) * $perPage;
        $sql = "SELECT p.*, l.country, l.province, l.city, l.neighborhood
                FROM {$this->table} p
                LEFT JOIN locations l ON p.location_id = l.id
                WHERE p.status = 'published'";
        
        $params = [];
        
        if (isset($filters['property_type_id']) && !empty($filters['property_type_id'])) {
            $sql .= " AND p.property_type_id = ?";
            $params[] = $filters['property_type_id'];
        }
        
        if (isset($filters['operation_type_id']) && !empty($filters['operation_type_id'])) {
            $sql .= " AND p.operation_type_id = ?";
            $params[] = $filters['operation_type_id'];
        }
        
        if (isset($filters['q']) && !empty($filters['q'])) {
            $sql .= " AND (p.title LIKE ? OR p.description LIKE ? OR l.city LIKE ? OR l.neighborhood LIKE ?)";
            $q = "%{$filters['q']}%";
            $params[] = $q;
            $params[] = $q;
            $params[] = $q;
            $params[] = $q;
        }
        
        $sql .= " ORDER BY p.created_at DESC LIMIT {$perPage} OFFSET {$offset}";
        $items = $this->query($sql, $params)->fetchAll();
        
        $countSql = "SELECT COUNT(*) as count FROM {$this->table} p LEFT JOIN locations l ON p.location_id = l.id WHERE p.status = 'published'";
        if (isset($filters['property_type_id']) && !empty($filters['property_type_id'])) {
            $countSql .= " AND p.property_type_id = ?";
        }
        if (isset($filters['operation_type_id']) && !empty($filters['operation_type_id'])) {
            $countSql .= " AND p.operation_type_id = ?";
        }
        if (isset($filters['q']) && !empty($filters['q'])) {
            $countSql .= " AND (p.title LIKE ? OR p.description LIKE ? OR l.city LIKE ? OR l.neighborhood LIKE ?)";
        }
        
        $countParams = [];
        if (isset($filters['property_type_id']) && !empty($filters['property_type_id'])) {
            $countParams[] = $filters['property_type_id'];
        }
        if (isset($filters['operation_type_id']) && !empty($filters['operation_type_id'])) {
            $countParams[] = $filters['operation_type_id'];
        }
        if (isset($filters['q']) && !empty($filters['q'])) {
            $q = "%{$filters['q']}%";
            $countParams[] = $q;
            $countParams[] = $q;
            $countParams[] = $q;
            $countParams[] = $q;
        }
        
        $countResult = $this->query($countSql, $countParams)->fetch();
        $total = $countResult['count'] ?? 0;

        return [
            'data' => $items,
            'total' => $total,
            'per_page' => $perPage,
            'page' => $page,
            'last_page' => ceil($total / $perPage),
        ];
    }

    public function findWithDetails($id)
    {
        $sql = "SELECT 
                    p.*,
                    l.country, l.province, l.city, l.neighborhood,
                    pt.name as property_type_name,
                    ot.name as operation_type_name,
                    ms.name as market_status_name,
                    u.name as agent_name,
                    u.email as agent_email
                FROM {$this->table} p
                LEFT JOIN locations l ON p.location_id = l.id
                LEFT JOIN property_types pt ON p.property_type_id = pt.id
                LEFT JOIN operation_types ot ON p.operation_type_id = ot.id
                LEFT JOIN property_market_statuses ms ON p.market_status_id = ms.id
                LEFT JOIN users u ON p.user_id = u.id
                WHERE p.id = ?";
        return $this->query($sql, [$id])->fetch();
    }

    public function findByUser($userId, $limit = null, $offset = 0)
    {
        $sql = "SELECT * FROM {$this->table} WHERE user_id = ?";
        if ($limit) {
            $sql .= " LIMIT $limit OFFSET $offset";
        }
        return $this->query($sql, [$userId])->fetchAll();
    }

    public function published($limit = null, $offset = 0)
    {
        $sql = "SELECT * FROM {$this->table} WHERE status = 'published'";
        if ($limit) {
            $sql .= " LIMIT $limit OFFSET $offset";
        }
        return $this->query($sql)->fetchAll();
    }

    public function featured()
    {
        $sql = "SELECT p.*, l.country, l.province, l.city, l.neighborhood
                FROM {$this->table} p
                LEFT JOIN locations l ON p.location_id = l.id
                WHERE p.is_featured = 1 AND p.status = 'published' 
                ORDER BY p.created_at DESC
                LIMIT 10";
        return $this->query($sql)->fetchAll();
    }

    public function search($filters = [])
    {
        $sql = "SELECT p.* FROM {$this->table} p
                LEFT JOIN locations l ON p.location_id = l.id
                WHERE p.status = 'published' AND p.is_featured = 0";

        $params = [];

        if (isset($filters['property_type_id']) && !empty($filters['property_type_id'])) {
            $sql .= " AND p.property_type_id = ?";
            $params[] = $filters['property_type_id'];
        }

        if (isset($filters['operation_type_id']) && !empty($filters['operation_type_id'])) {
            $sql .= " AND p.operation_type_id = ?";
            $params[] = $filters['operation_type_id'];
        }

        if (isset($filters['city']) && !empty($filters['city'])) {
            $sql .= " AND l.city LIKE ?";
            $params[] = "%{$filters['city']}%";
        }

        if (isset($filters['min_price']) && !empty($filters['min_price'])) {
            $sql .= " AND p.price >= ?";
            $params[] = $filters['min_price'];
        }

        if (isset($filters['max_price']) && !empty($filters['max_price'])) {
            $sql .= " AND p.price <= ?";
            $params[] = $filters['max_price'];
        }

        if (isset($filters['bedrooms']) && !empty($filters['bedrooms'])) {
            $sql .= " AND p.bedrooms >= ?";
            $params[] = $filters['bedrooms'];
        }

        $sql .= " ORDER BY p.created_at DESC";

        if (isset($filters['limit'])) {
            $sql .= " LIMIT " . (int)$filters['limit'];
        }

        return $this->query($sql, $params)->fetchAll();
    }

    public function findByLocation($locationId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE location_id = ? AND status = 'published'";
        return $this->query($sql, [$locationId])->fetchAll();
    }

    public function getStatistics()
    {
        $sql = "SELECT 
                    COUNT(*) as total_properties,
                    SUM(CASE WHEN status = 'published' THEN 1 ELSE 0 END) as published,
                    SUM(CASE WHEN is_featured = 1 THEN 1 ELSE 0 END) as featured,
                    COUNT(DISTINCT user_id) as total_agents
                FROM {$this->table}";
        return $this->query($sql)->fetch();
    }
}
