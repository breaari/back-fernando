<?php

namespace App\Controllers;

use App\Core\{Request, Response, Auth, Validator};
use App\Repositories\PropertyImageRepository;
use Exception;

class PropertyImageController
{
    private $imageRepository;

    public function __construct()
    {
        $this->imageRepository = new PropertyImageRepository();
    }

    public function upload($id)
    {
        Auth::authenticate();
        $request = new Request();
        $files = $request->getFiles();

        if (!isset($files['image'])) {
            Response::error('No image file provided', 400);
        }

        try {
            $uploadDir = 'uploads/properties/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $file = $files['image'];
            $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

            if (!in_array($file['type'], $allowedTypes)) {
                Response::error('Invalid image type. Allowed: JPEG, PNG, WebP, GIF', 400);
            }

            if ($file['size'] > 5 * 1024 * 1024) { // 5MB
                Response::error('Image size must not exceed 5MB', 400);
            }

            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = uniqid('prop_' . $id . '_') . '.' . $extension;
            $filepath = $uploadDir . $filename;

            if (!move_uploaded_file($file['tmp_name'], $filepath)) {
                Response::error('Failed to upload image', 500);
            }

            $imageData = [
                'property_id' => $id,
                'image_url' => '/' . $filepath,
                'is_cover' => $request->get('is_cover', 0),
                'position' => $request->get('position', 0),
            ];

            $imageId = $this->imageRepository->create($imageData);
            $image = $this->imageRepository->find($imageId);
            
            if (!$image) {
                // Si no se puede recuperar, devolveremos al menos los datos que guardamos
                $image = array_merge(['id' => $imageId], $imageData);
            }

            Response::created($image, 'Image uploaded successfully');
        } catch (Exception $e) {
            Response::error('Error uploading image: ' . $e->getMessage(), 500);
        }
    }

    public function setCover($id, $imageId)
    {
        Auth::authenticate();

        try {
            // Remover cover de todas las imágenes de esta propiedad
            $this->imageRepository->query(
                "UPDATE property_images SET is_cover = 0 WHERE property_id = ?",
                [$id]
            );

            // Establecer como cover la imagen especificada
            $this->imageRepository->update($imageId, ['is_cover' => 1]);

            $image = $this->imageRepository->find($imageId);
            Response::success($image, 'Cover image set successfully');
        } catch (Exception $e) {
            Response::error('Error setting cover image: ' . $e->getMessage(), 500);
        }
    }

    public function delete($id, $imageId)
    {
        Auth::authenticate();

        try {
            $image = $this->imageRepository->find($imageId);

            if (!$image || $image['property_id'] != $id) {
                Response::notFound('Image not found');
            }

            // Eliminar archivo físico
            if (file_exists('.' . $image['image_url'])) {
                unlink('.' . $image['image_url']);
            }

            $this->imageRepository->delete($imageId);
            Response::success([], 'Image deleted successfully');
        } catch (Exception $e) {
            Response::error('Error deleting image: ' . $e->getMessage(), 500);
        }
    }

    public function reorder()
    {
        Auth::authenticate();
        $request = new Request();
        $data = $request->all();

        if (!isset($data['images']) || !is_array($data['images'])) {
            Response::error('images array is required', 400);
        }

        try {
            foreach ($data['images'] as $position => $imageId) {
                $this->imageRepository->update($imageId, ['position' => $position]);
            }

            Response::success([], 'Images reordered successfully');
        } catch (Exception $e) {
            Response::error('Error reordering images: ' . $e->getMessage(), 500);
        }
    }
}
