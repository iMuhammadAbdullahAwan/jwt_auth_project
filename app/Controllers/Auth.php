<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UserModel;
use CodeIgniter\I18n\Time;
use App\Models\RefreshTokenModel;


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
        $email    = $this->request->getPost('email');
        $password = $this->request->getPost('password_hash');

        if (!$email || !$password) {
            return $this->response->setJSON(['error' => 'Email and password required'])->setStatusCode(422);
        }

        $user = $usersModel->where('email', $email)->first();
        if (!$user || !password_verify($password, $user['password_hash'])) {
            return $this->response->setJSON(['error' => 'Invalid credentials'])->setStatusCode(401);
        }

        // Create access token
        $payload = [
            'sub' => $user['id'],
            'exp' => time() + 900, // 15 min
        ];
        $accessToken = $this->generateJWT($payload, getenv('JWT_SECRET'));

        // Create refresh token
        $refreshToken = bin2hex(random_bytes(40));
        $expiresAt = Time::now()->addDays(7);

        $refreshTokenModel = new RefreshTokenModel();
        $refreshTokenModel->insert([
            'user_id'    => $user['id'],
            'token'      => $refreshToken,
            'expires_at' => $expiresAt->toDateTimeString()
        ]);

        return $this->response->setJSON([
            'access_token'  => $accessToken,
            'expires_in'    => 900,
            'refresh_token' => $refreshToken,
            'refresh_expires_in' => 604800 // 7 days
        ]);
    }

    public function refreshToken()
    {
        $refreshToken = $this->request->getPost('refresh_token');
        if (!$refreshToken) {
            return $this->response->setJSON(['error' => 'Refresh token required'])->setStatusCode(422);
        }

        $refreshTokenModel = new RefreshTokenModel();
        $row = $refreshTokenModel
            ->where('token', $refreshToken)
            ->where('expires_at >=', Time::now()->toDateTimeString())
            ->first();

        if (!$row) {
            return $this->response->setJSON(['error' => 'Invalid or expired refresh token'])->setStatusCode(401);
        }

        $usersModel = new UserModel();
        $user = $usersModel->find($row['user_id']);

        // Generate new access token
        $payload = [
            'sub' => $user['id'],
            'exp' => time() + 900,
        ];
        $newAccessToken = $this->generateJWT($payload, getenv('JWT_SECRET'));

        return $this->response->setJSON([
            'access_token' => $newAccessToken,
            'expires_in'   => 900
        ]);
    }

    public function logout()
    {
        $refreshToken = $this->request->getPost('refresh_token');
        $refreshTokenModel = new RefreshTokenModel();
        $refreshTokenModel->where('token', $refreshToken)->delete();

        return $this->response->setJSON(['message' => 'Logged out successfully']);
    }

    // --- JWT Helpers ---
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
}
