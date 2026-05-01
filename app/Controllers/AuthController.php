<?php

namespace App\Controllers;

use App\Core\{Request, Response, Auth, Validator};
use App\Repositories\UserRepository;
use Exception;

class AuthController
{
    private $userRepository;

    public function __construct()
    {
        $this->userRepository = new UserRepository();
    }

    public function register()
    {
        $request = new Request();
        $data = $request->all();

        // Validar
        if (!Validator::validate($data, [
            'name' => 'required|string|max:150',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'real_estate_id' => 'required|numeric',
        ])) {
            Response::validation(Validator::errors());
        }

        try {
            $userData = [
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => password_hash($data['password'], PASSWORD_BCRYPT),
                'real_estate_id' => $data['real_estate_id'],
                'role' => $data['role'] ?? 'agent',
            ];

            $userId = $this->userRepository->create($userData);
            $user = $this->userRepository->find($userId);

            $token = Auth::generateToken($user['id'], $user['email'], $user['role']);

            Response::created([
                'user' => $user,
                'token' => $token,
            ], 'User registered successfully');
        } catch (Exception $e) {
            Response::error('Registration failed: ' . $e->getMessage(), 500);
        }
    }

    public function login()
    {
        $request = new Request();
        $data = $request->all();

        if (!Validator::validate($data, [
            'email' => 'required|email',
            'password' => 'required',
        ])) {
            Response::validation(Validator::errors());
        }

        try {
            $user = $this->userRepository->findByEmail($data['email']);

            if (!$user || !password_verify($data['password'], $user['password'])) {
                Response::unauthorized('Invalid email or password');
            }

            if (!$user['active']) {
                Response::forbidden('User account is disabled');
            }

            $token = Auth::generateToken($user['id'], $user['email'], $user['role']);

            // No devolver la contraseña
            unset($user['password']);

            Response::success([
                'user' => $user,
                'token' => $token,
            ], 'Login successful');
        } catch (Exception $e) {
            Response::error('Login failed: ' . $e->getMessage(), 500);
        }
    }

    public function me()
    {
        $user = Auth::authenticate();
        $userData = $this->userRepository->findWithRealEstate($user['userId']);

        if (!$userData) {
            Response::notFound('User not found');
        }

        unset($userData['password']);
        Response::success($userData, 'User data');
    }

    public function logout()
    {
        // El logout se maneja en el frontend eliminando el token
        Response::success([], 'Logged out successfully');
    }
}
