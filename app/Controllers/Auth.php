<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UserModel;

class Auth extends BaseController
{
    public function register()
    {
        $usersModel = new UserModel();
        $postData   = $this->request->getPost();

        // Apply model validation
        if (!$this->validate($usersModel->getValidationRules())) {
            return $this->response
                ->setJSON(['errors' => $this->validator->getErrors()])
                ->setStatusCode(422);
        }

        // Hash password securely
        $postData['password_hash'] = password_hash($postData['password_hash'], PASSWORD_DEFAULT);

        // Insert into DB
        $userId = $usersModel->insert($postData);

        return $this->response
            ->setJSON([
                'message' => 'User registered successfully',
                'user_id' => $userId
            ])
            ->setStatusCode(201);
    }

    public function login()
    {
        $usersModel = new UserModel();
        $email      = $this->request->getPost('email');
        $password   = $this->request->getPost('password_hash');

        if (!$email || !$password) {
            return $this->response->setJSON(['error' => 'Email and password are required'])->setStatusCode(422);
        }

        $user = $usersModel->where('email', $email)->first();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            return $this->response->setJSON(['error' => 'Invalid credentials'])->setStatusCode(401);
        }

        // Create JWT token manually
        $payload = [
            'sub'  => $user['id'],
            'name' => $user['first_name'] . ' ' . $user['last_name'],
            'role' => $user['role_id'],
            'iat'  => time(),
            'exp'  => time() + 900
        ];

        $jwt = $this->generateJWT($payload, getenv('JWT_SECRET'));

        return $this->response->setJSON([
            'access_token' => $jwt,
            'expires_in'   => 900
        ]);
    }

    private function generateJWT($payload, $secret)
    {
        $header = $this->base64UrlEncode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
        $payload = $this->base64UrlEncode(json_encode($payload));

        $signature = hash_hmac('sha256', "$header.$payload", $secret, true);
        $signature = $this->base64UrlEncode($signature);

        return "$header.$payload.$signature";
    }

    private function base64UrlEncode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    public function logout()
    {
        return $this->response->setJSON(['message' => 'Logged out successfully']);
    }

    public function refreshToken()
    {
        // This would require storing refresh tokens in DB and validating them
        return $this->response->setJSON(['error' => 'Not implemented'])->setStatusCode(501);
    }
}
