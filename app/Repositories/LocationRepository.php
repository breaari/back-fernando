<?php

namespace App\Repositories;

class LocationRepository extends BaseRepository
{
    protected $table = 'locations';

    public function findByDetails($country, $province, $city, $neighborhood)
    {
        $sql = "SELECT * FROM {$this->table}
                WHERE (country = ? OR (country IS NULL AND ? IS NULL))
                  AND (province = ? OR (province IS NULL AND ? IS NULL))
                  AND (city = ? OR (city IS NULL AND ? IS NULL))
                  AND (neighborhood = ? OR (neighborhood IS NULL AND ? IS NULL))
                LIMIT 1";

        return $this->query($sql, [
            $country, $country,
            $province, $province,
            $city, $city,
            $neighborhood, $neighborhood
        ])->fetch();
    }

    public function findOrCreate($country, $province, $city, $neighborhood)
    {
        $country = $country !== null ? trim($country) : null;
        $province = $province !== null ? trim($province) : null;
        $city = $city !== null ? trim($city) : null;
        $neighborhood = $neighborhood !== null ? trim($neighborhood) : null;

        $existing = $this->findByDetails($country, $province, $city, $neighborhood);
        if ($existing && isset($existing['id'])) {
            return $existing['id'];
        }

        return $this->create([
            'country' => $country,
            'province' => $province,
            'city' => $city,
            'neighborhood' => $neighborhood,
        ]);
    }

    public function findByCity($city)
    {
        return $this->findBy('city', $city);
    }

    public function findByProvince($province)
    {
        return $this->findBy('province', $province);
    }

    public function getProvinces()
    {
        $sql = "SELECT DISTINCT province as name FROM {$this->table} WHERE province IS NOT NULL ORDER BY province ASC";
        $results = $this->query($sql)->fetchAll();
        // Add id based on array index
        return array_map(function($item, $idx) {
            return ['id' => $idx + 1, 'name' => $item['name']];
        }, $results, array_keys($results));
    }

    public function getCitiesByProvince($province)
    {
        $sql = "SELECT DISTINCT city as name FROM {$this->table} WHERE province = ? AND city IS NOT NULL ORDER BY city ASC";
        $results = $this->query($sql, [$province])->fetchAll();
        return array_map(function($item, $idx) {
            return ['id' => $idx + 1, 'name' => $item['name']];
        }, $results, array_keys($results));
    }

    public function getNeighborhoodsByCity($city)
    {
        $sql = "SELECT DISTINCT neighborhood as name FROM {$this->table} WHERE city = ? AND neighborhood IS NOT NULL ORDER BY neighborhood ASC";
        $results = $this->query($sql, [$city])->fetchAll();
        return array_map(function($item, $idx) {
            return ['id' => $idx + 1, 'name' => $item['name']];
        }, $results, array_keys($results));
    }
}
