<?php

namespace App\Controllers;

use App\Core\Response;
use App\Core\Database;
use App\Repositories\{AmenityRepository, TagRepository, LocationRepository};

class CatalogController
{
    private $amenityRepository;
    private $tagRepository;
    private $locationRepository;

    public function __construct()
    {
        $this->amenityRepository = new AmenityRepository();
        $this->tagRepository = new TagRepository();
        $this->locationRepository = new LocationRepository();
    }

    public function getAmenities()
    {
        try {
            $amenities = $this->amenityRepository->active();
            Response::success(['amenities' => $amenities]);
        } catch (\Exception $e) {
            Response::error('Error fetching amenities: ' . $e->getMessage(), 500);
        }
    }

    public function getTags()
    {
        try {
            $tags = $this->tagRepository->active();
            Response::success(['tags' => $tags]);
        } catch (\Exception $e) {
            Response::error('Error fetching tags: ' . $e->getMessage(), 500);
        }
    }

    public function getProvinces()
    {
        try {
            $provinces = $this->locationRepository->getProvinces();
            Response::success(['provinces' => $provinces]);
        } catch (\Exception $e) {
            Response::error('Error fetching provinces: ' . $e->getMessage(), 500);
        }
    }

    public function getCities($province)
    {
        try {
            $cities = $this->locationRepository->getCitiesByProvince($province);
            Response::success(['cities' => $cities]);
        } catch (\Exception $e) {
            Response::error('Error fetching cities: ' . $e->getMessage(), 500);
        }
    }

    public function getNeighborhoods($city)
    {
        try {
            $neighborhoods = $this->locationRepository->getNeighborhoodsByCity($city);
            Response::success(['neighborhoods' => $neighborhoods]);
        } catch (\Exception $e) {
            Response::error('Error fetching neighborhoods: ' . $e->getMessage(), 500);
        }
    }

    public function getPropertyTypes()
    {
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare('SELECT * FROM property_types WHERE active = 1');
            $stmt->execute();
            $types = $stmt->fetchAll();
            Response::success(['property_types' => $types]);
        } catch (\Exception $e) {
            Response::error('Error fetching property types: ' . $e->getMessage(), 500);
        }
    }

    public function getOperationTypes()
    {
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare('SELECT * FROM operation_types WHERE active = 1');
            $stmt->execute();
            $types = $stmt->fetchAll();
            Response::success(['operation_types' => $types]);
        } catch (\Exception $e) {
            Response::error('Error fetching operation types: ' . $e->getMessage(), 500);
        }
    }

    public function getMarketStatuses()
    {
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare('SELECT * FROM property_market_statuses WHERE active = 1');
            $stmt->execute();
            $statuses = $stmt->fetchAll();
            Response::success(['market_statuses' => $statuses]);
        } catch (\Exception $e) {
            Response::error('Error fetching market statuses: ' . $e->getMessage(), 500);
        }
    }
}
