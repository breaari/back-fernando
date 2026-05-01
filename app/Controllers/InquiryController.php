<?php

namespace App\Controllers;

use App\Core\{Request, Response, Validator};
use App\Repositories\InquiryRepository;
use Exception;

class InquiryController
{
    private $inquiryRepository;

    public function __construct()
    {
        $this->inquiryRepository = new InquiryRepository();
    }

    public function getAll()
    {
        $request = new Request();
        $filters = [];

        // Filtro por tipo (property_id indica consulta sobre propiedad, null es contacto general)
        if (isset($_GET['type'])) {
            if ($_GET['type'] === 'property') {
                $filters['has_property'] = true;
            } elseif ($_GET['type'] === 'contact') {
                $filters['has_property'] = false;
            }
        }

        // Filtro por rango de fechas
        if (isset($_GET['start_date'])) {
            $filters['start_date'] = $_GET['start_date'];
        }
        if (isset($_GET['end_date'])) {
            $filters['end_date'] = $_GET['end_date'];
        }

        try {
            $inquiries = $this->inquiryRepository->findAll($filters);
            Response::success(['inquiries' => $inquiries, 'total' => count($inquiries)]);
        } catch (Exception $e) {
            Response::error('Error fetching inquiries: ' . $e->getMessage(), 500);
        }
    }

    public function create()
    {
        $request = new Request();
        $data = $request->all();

        // Sanitizar inputs
        $data['name'] = isset($data['name']) ? trim(strip_tags($data['name'])) : '';
        $data['email'] = isset($data['email']) ? trim(strtolower($data['email'])) : '';
        $data['phone'] = isset($data['phone']) ? trim(strip_tags($data['phone'])) : null;
        $data['message'] = isset($data['message']) ? trim(strip_tags($data['message'])) : '';

        if (!Validator::validate($data, [
            'name' => 'required|string|min:2|max:150',
            'email' => 'required|email|max:255',
            'message' => 'required|string|min:10|max:1000',
        ])) {
            Response::validation(Validator::errors());
            return;
        }

        try {
            $inquiryData = [
                'property_id' => isset($data['property_id']) && is_numeric($data['property_id']) ? (int)$data['property_id'] : null,
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'message' => $data['message'],
            ];

            $inquiryId = $this->inquiryRepository->create($inquiryData);
            $inquiry = $this->inquiryRepository->find($inquiryId);

            // Aquí puedes enviar un email de notificación
            // sendEmailNotification($inquiry);

            Response::created($inquiry, 'Inquiry created successfully');
        } catch (Exception $e) {
            error_log('Error creating inquiry: ' . $e->getMessage());
            Response::error('Error creating inquiry. Please try again.', 500);
        }
    }

    public function getByProperty($propertyId)
    {
        try {
            $inquiries = $this->inquiryRepository->findByProperty($propertyId);
            Response::success(['inquiries' => $inquiries]);
        } catch (Exception $e) {
            Response::error('Error fetching inquiries: ' . $e->getMessage(), 500);
        }
    }

    public function show($id)
    {
        try {
            $inquiry = $this->inquiryRepository->getWithProperty($id);

            if (!$inquiry) {
                Response::notFound('Inquiry not found');
            }

            Response::success($inquiry);
        } catch (Exception $e) {
            Response::error('Error fetching inquiry: ' . $e->getMessage(), 500);
        }
    }

    public function delete($id)
    {
        try {
            $inquiry = $this->inquiryRepository->find($id);

            if (!$inquiry) {
                Response::notFound('Inquiry not found');
            }

            $this->inquiryRepository->delete($id);
            Response::success([], 'Inquiry deleted successfully');
        } catch (Exception $e) {
            Response::error('Error deleting inquiry: ' . $e->getMessage(), 500);
        }
    }
}
