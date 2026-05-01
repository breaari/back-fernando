<?php

namespace App\Controllers;

use App\Core\{Request, Response, Validator};
use App\Repositories\InquiryRepository;
use Exception;

class ContactController
{
    private $inquiryRepository;

    public function __construct()
    {
        $this->inquiryRepository = new InquiryRepository();
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

        // Validar con Validator
        if (!Validator::validate($data, [
            'name' => 'required|string|min:2|max:150',
            'email' => 'required|email|max:255',
            'message' => 'required|string|min:10|max:1000',
        ])) {
            Response::validation(Validator::errors());
            return;
        }

        try {
            // Guardar en la tabla inquiries sin property_id
            $inquiryData = [
                'property_id' => null,
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'message' => $data['message'],
            ];

            $inquiryId = $this->inquiryRepository->create($inquiryData);
            $inquiry = $this->inquiryRepository->find($inquiryId);

            Response::created($inquiry, 'Contact message received successfully');
        } catch (Exception $e) {
            error_log('Error processing contact: ' . $e->getMessage());
            Response::error('Error processing contact. Please try again.', 500);
        }
    }
}
