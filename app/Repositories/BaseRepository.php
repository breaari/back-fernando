<?php

namespace App\Repositories;

use App\Core\Database;

abstract class BaseRepository
{
    protected $table;
    protected $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function all($limit = null, $offset = 0)
    {
        $query = "SELECT * FROM {$this->table}";
        if ($limit) {
            $query .= " LIMIT $limit OFFSET $offset";
        }
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function find($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create($data)
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $query = "INSERT INTO {$this->table} ($columns) VALUES ($placeholders)";

        $stmt = $this->db->prepare($query);
        $stmt->execute(array_values($data));

        return $this->db->lastInsertId();
    }

    public function update($id, $data)
    {
        $set = implode(', ', array_map(fn($key) => "$key = ?", array_keys($data)));
        $query = "UPDATE {$this->table} SET $set WHERE id = ?";

        $values = array_values($data);
        $values[] = $id;

        $stmt = $this->db->prepare($query);
        return $stmt->execute($values);
    }

    public function delete($id)
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function findBy($field, $value)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE $field = ?");
        $stmt->execute([$value]);
        return $stmt->fetchAll();
    }

    public function findOneBy($field, $value)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE $field = ? LIMIT 1");
        $stmt->execute([$value]);
        return $stmt->fetch();
    }

    public function count()
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM {$this->table}");
        $stmt->execute();
        return $stmt->fetch()['count'];
    }

    public function paginate($perPage = 15, $page = 1)
    {
        $offset = ($page - 1) * $perPage;
        $items = $this->all($perPage, $offset);
        $total = $this->count();

        return [
            'data' => $items,
            'total' => $total,
            'per_page' => $perPage,
            'page' => $page,
            'last_page' => ceil($total / $perPage),
        ];
    }

    protected function query($sql, $params = [])
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
}
