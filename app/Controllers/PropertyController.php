<?php

namespace App\Controllers;

use App\Core\{Request, Response, Auth, Validator};
use App\Repositories\{PropertyRepository, PropertyImageRepository, AmenityRepository, TagRepository, LocationRepository};
use Exception;

class PropertyController
{
    private $propertyRepository;
    private $imageRepository;
    private $amenityRepository;
    private $tagRepository;
    private $locationRepository;

    public function __construct()
    {
        $this->propertyRepository = new PropertyRepository();
        $this->imageRepository = new PropertyImageRepository();
        $this->amenityRepository = new AmenityRepository();
        $this->tagRepository = new TagRepository();
        $this->locationRepository = new LocationRepository();
    }

    public function index()
    {
        $request = new Request();
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 15);
        $propertyTypeId = $request->get('property_type_id');
        $operationTypeId = $request->get('operation_type_id');
        $q = $request->get('q');

        try {
            $paginated = $this->propertyRepository->paginate($limit, $page, [
                'property_type_id' => $propertyTypeId,
                'operation_type_id' => $operationTypeId,
                'q' => $q
            ]);
            
            // Add images to each property
            foreach ($paginated['data'] as &$property) {
                $property['images'] = $this->imageRepository->getOrderedByPosition($property['id']);
            }
            
            Response::success($paginated);
        } catch (Exception $e) {
            Response::error('Error fetching properties: ' . $e->getMessage(), 500);
        }
    }

    public function show($id)
    {
        try {
            $property = $this->propertyRepository->findWithDetails($id);

            if (!$property) {
                Response::notFound('Property not found');
            }

            // Agregar imágenes
            $property['images'] = $this->imageRepository->getOrderedByPosition($id);
            // Agregar amenidades
            $property['amenities'] = $this->amenityRepository->findByProperty($id);
            // Agregar tags
            $property['tags'] = $this->tagRepository->findByProperty($id);

            Response::success($property);
        } catch (Exception $e) {
            Response::error('Error fetching property: ' . $e->getMessage(), 500);
        }
    }

    public function create()
    {
        Auth::authenticate(); // Requiere autenticación
        $request = new Request();
        $data = $request->all();

        $this->resolveLocationId($data);

        if (!Validator::validate($data, [
            'title' => 'required|string|max:255',
            'price' => 'required|numeric',
            'currency' => 'required|in:ARS,USD',
            'location_id' => 'required|numeric',
            'property_type_id' => 'required|numeric',
            'operation_type_id' => 'required|numeric',
        ])) {
            Response::validation(Validator::errors());
        }

        try {
            $user = Auth::authenticate();
            
            $propertyData = [
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'price' => $data['price'],
                'currency' => $data['currency'],
                'location_id' => $data['location_id'],
                'street' => $data['street'] ?? null,
                'street_number' => $data['street_number'] ?? null,
                'floor' => $data['floor'] ?? null,
                'apartment' => $data['apartment'] ?? null,
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
                'video_url' => $data['video_url'] ?? null,
                'rooms' => $data['rooms'] ?? null,
                'bedrooms' => $data['bedrooms'] ?? null,
                'bathrooms' => $data['bathrooms'] ?? null,
                'garages' => $data['garages'] ?? null,
                'total_floors' => $data['total_floors'] ?? null,
                'surface_total' => $data['surface_total'] ?? null,
                'surface_covered' => $data['surface_covered'] ?? null,
                'surface_semi_covered' => $data['surface_semi_covered'] ?? null,
                'surface_uncovered' => $data['surface_uncovered'] ?? null,
                'expenses_amount' => $data['expenses_amount'] ?? null,
                'expenses_currency' => $data['expenses_currency'] ?? null,
                'is_new' => $data['is_new'] ?? 0,
                'antiquity_years' => $data['antiquity_years'] ?? null,
                'is_featured' => $data['is_featured'] ?? 0,
                'status' => 'draft',
                'property_type_id' => $data['property_type_id'],
                'operation_type_id' => $data['operation_type_id'],
                'user_id' => $user['userId'],
                'market_status_id' => $data['market_status_id'] ?? 1,
                'private_notes' => $data['private_notes'] ?? null,
            ];

            $propertyId = $this->propertyRepository->create($propertyData);
            $property = $this->propertyRepository->findWithDetails($propertyId);

            Response::created($property, 'Property created successfully');
        } catch (Exception $e) {
            Response::error('Error creating property: ' . $e->getMessage(), 500);
        }
    }

    public function update($id)
    {
        Auth::authenticate();
        $request = new Request();
        $data = $request->all();

        $this->resolveLocationId($data);

        try {
            $property = $this->propertyRepository->find($id);
            if (!$property) {
                Response::notFound('Property not found');
            }

            $user = Auth::authenticate();
            if ($property['user_id'] != $user['userId'] && !Auth::isAdmin($user)) {
                Response::forbidden('You cannot edit this property');
            }

            $allowedFields = [
                'title', 'description', 'price', 'currency', 'location_id',
                'street', 'street_number', 'floor', 'apartment', 'latitude', 'longitude',
                'video_url',
                'rooms', 'bedrooms', 'bathrooms', 'garages', 'total_floors',
                'surface_total', 'surface_covered', 'surface_semi_covered', 'surface_uncovered',
                'expenses_amount', 'expenses_currency', 'is_new', 'antiquity_years',
                'is_featured', 'status', 'market_status_id', 'private_notes',
            ];

            $updateData = [];
            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    $updateData[$field] = $data[$field];
                }
            }

            if (!empty($updateData)) {
                $this->propertyRepository->update($id, $updateData);
            }

            $property = $this->propertyRepository->findWithDetails($id);
            Response::success($property, 'Property updated successfully');
        } catch (Exception $e) {
            Response::error('Error updating property: ' . $e->getMessage(), 500);
        }
    }

    public function delete($id)
    {
        Auth::authenticate();
        try {
            $property = $this->propertyRepository->find($id);
            if (!$property) {
                Response::notFound('Property not found');
            }

            $user = Auth::authenticate();
            if ($property['user_id'] != $user['userId'] && !Auth::isAdmin($user)) {
                Response::forbidden('You cannot delete this property');
            }

            $this->propertyRepository->delete($id);
            Response::success([], 'Property deleted successfully');
        } catch (Exception $e) {
            Response::error('Error deleting property: ' . $e->getMessage(), 500);
        }
    }

    public function search()
    {
        $request = new Request();
        $filters = $request->all();

        try {
            $properties = $this->propertyRepository->search($filters);
            
            // Add images to each property
            foreach ($properties as &$property) {
                $property['images'] = $this->imageRepository->getOrderedByPosition($property['id']);
            }
            
            Response::success(['data' => $properties]);
        } catch (Exception $e) {
            Response::error('Error searching properties: ' . $e->getMessage(), 500);
        }
    }

    public function featured()
    {
        try {
            $properties = $this->propertyRepository->featured();
            
            // Add images to each property
            foreach ($properties as &$property) {
                $property['images'] = $this->imageRepository->getOrderedByPosition($property['id']);
            }
            
            Response::success(['data' => $properties]);
        } catch (Exception $e) {
            Response::error('Error fetching featured properties: ' . $e->getMessage(), 500);
        }
    }

    public function myProperties()
    {
        Auth::authenticate();
        $request = new Request();
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 15);

        try {
            $user = Auth::authenticate();
            $properties = $this->propertyRepository->findByUser($user['userId'], $limit, ($page - 1) * $limit);
            Response::success(['properties' => $properties]);
        } catch (Exception $e) {
            Response::error('Error fetching your properties: ' . $e->getMessage(), 500);
        }
    }

    private function resolveLocationId(&$data)
    {
        $country = $data['country'] ?? null;
        $province = $data['state'] ?? ($data['province'] ?? null);
        $city = $data['city'] ?? null;
        $neighborhood = $data['neighborhood'] ?? null;

        if (!$country && !$province && !$city && !$neighborhood) {
            // No location fields provided, keep existing location_id if any
            return;
        }

        $locationId = $this->locationRepository->findOrCreate($country, $province, $city, $neighborhood);
        if ($locationId) {
            $data['location_id'] = $locationId;
        }
    }

    public function addAmenity($propertyId)
    {
        Auth::authenticate();
        $request = new Request();
        $data = $request->all();

        if (!isset($data['amenity_id'])) {
            Response::error('amenity_id is required', 400);
        }

        try {
            $user = Auth::authenticate();
            $property = $this->propertyRepository->find($propertyId);

            if (!$property) {
                Response::notFound('Property not found');
            }

            if ($property['user_id'] != $user['userId'] && !Auth::isAdmin($user)) {
                Response::forbidden('You cannot edit this property');
            }

            $this->amenityRepository->addToProperty($propertyId, $data['amenity_id']);
            Response::success([], 'Amenity added to property');
        } catch (Exception $e) {
            Response::error('Error adding amenity: ' . $e->getMessage(), 500);
        }
    }

    public function removeAmenity($propertyId)
    {
        Auth::authenticate();
        $request = new Request();
        $data = $request->all();

        if (!isset($data['amenity_id'])) {
            Response::error('amenity_id is required', 400);
        }

        try {
            $user = Auth::authenticate();
            $property = $this->propertyRepository->find($propertyId);

            if (!$property) {
                Response::notFound('Property not found');
            }

            if ($property['user_id'] != $user['userId'] && !Auth::isAdmin($user)) {
                Response::forbidden('You cannot edit this property');
            }

            $this->amenityRepository->removeFromProperty($propertyId, $data['amenity_id']);
            Response::success([], 'Amenity removed from property');
        } catch (Exception $e) {
            Response::error('Error removing amenity: ' . $e->getMessage(), 500);
        }
    }

    public function addTag($propertyId)
    {
        Auth::authenticate();
        $request = new Request();
        $data = $request->all();

        if (!isset($data['tag_id'])) {
            Response::error('tag_id is required', 400);
        }

        try {
            $user = Auth::authenticate();
            $property = $this->propertyRepository->find($propertyId);

            if (!$property) {
                Response::notFound('Property not found');
            }

            if ($property['user_id'] != $user['userId'] && !Auth::isAdmin($user)) {
                Response::forbidden('You cannot edit this property');
            }

            $this->tagRepository->addToProperty($propertyId, $data['tag_id']);
            Response::success([], 'Tag added to property');
        } catch (Exception $e) {
            Response::error('Error adding tag: ' . $e->getMessage(), 500);
        }
    }

    public function removeTag($propertyId)
    {
        Auth::authenticate();
        $request = new Request();
        $data = $request->all();

        if (!isset($data['tag_id'])) {
            Response::error('tag_id is required', 400);
        }

        try {
            $user = Auth::authenticate();
            $property = $this->propertyRepository->find($propertyId);

            if (!$property) {
                Response::notFound('Property not found');
            }

            if ($property['user_id'] != $user['userId'] && !Auth::isAdmin($user)) {
                Response::forbidden('You cannot edit this property');
            }

            $this->tagRepository->removeFromProperty($propertyId, $data['tag_id']);
            Response::success([], 'Tag removed from property');
        } catch (Exception $e) {
            Response::error('Error removing tag: ' . $e->getMessage(), 500);
        }
    }
}
