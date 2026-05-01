<?php

namespace App\Core;

class Response
{
    private static $statusCodes = [
        200 => 'OK',
        201 => 'Created',
        204 => 'No Content',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        403 => 'Forbidden',
        404 => 'Not Found',
        422 => 'Unprocessable Entity',
        500 => 'Internal Server Error',
    ];

    public static function success($data = [], $message = 'Success', $statusCode = 200)
    {
        return self::json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $statusCode);
    }

    public static function error($message = 'Error', $statusCode = 400, $errors = [])
    {
        return self::json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], $statusCode);
    }

    public static function created($data = [], $message = 'Created successfully')
    {
        return self::json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], 201);
    }

    public static function notFound($message = 'Resource not found')
    {
        return self::json([
            'success' => false,
            'message' => $message,
        ], 404);
    }

    public static function unauthorized($message = 'Unauthorized')
    {
        return self::json([
            'success' => false,
            'message' => $message,
        ], 401);
    }

    public static function forbidden($message = 'Forbidden')
    {
        return self::json([
            'success' => false,
            'message' => $message,
        ], 403);
    }

    public static function validation($errors)
    {
        return self::json([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $errors,
        ], 422);
    }

    private static function json($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}
