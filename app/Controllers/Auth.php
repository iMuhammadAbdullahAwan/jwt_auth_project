<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UserModel;
use CodeIgniter\I18n\Time;
use App\Models\RefreshTokenModel;
use Config\Services;
use App\Models\PasswordResetModel;

class Auth extends BaseController
{
    public function register()
    {
        $usersModel = new UserModel();

        // Handle both JSON and form data
        $contentType = $this->request->getHeaderLine('Content-Type');

        if (strpos($contentType, 'application/json') !== false) {
            $postData = $this->request->getJSON(true); // true for associative array
        } else {
            $postData = $this->request->getPost();
        }

        // Add safety check
        if (!isset($postData['password_hash'])) {
            return $this->response
                ->setJSON(['error' => 'Password field is required'])
                ->setStatusCode(422);
        }

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

        // Send Welcome Email
        $email = Services::email();
        $email->setFrom(getenv('email.fromEmail'), getenv('email.fromName'));
        $email->setTo($postData['email']);
        $email->setSubject('🎉 Welcome to MyApp!');
        $email->setMessage(view('emails/welcome', [
            'first_name' => $postData['first_name']
        ]));
        $email->send();

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

        // Handle both JSON and form data
        $contentType = $this->request->getHeaderLine('Content-Type');

        if (strpos($contentType, 'application/json') !== false) {
            $postData = $this->request->getJSON(true); // true for associative array
        } else {
            $postData = $this->request->getPost();
        }

        // FIX: Use the parsed $postData instead of getPost()
        $email    = $postData['email'] ?? null;
        $password = $postData['password_hash'] ?? null;

        // dd($email);

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

        // Send Login Alert Email
        $emailService = \Config\Services::email();
        $emailService->setFrom(getenv('email.fromEmail'), getenv('email.fromName'));
        $emailService->setTo($user['email']);
        $emailService->setSubject('🔐 Login Alert');
        $emailService->setMessage(view('emails/login_alert', [
            'first_name' => $user['first_name'],
            'time'     => date('Y-m-d H:i:s'),
            'ip'       => $this->request->getIPAddress()
        ]));
        $emailService->send();


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

    public function forgotPassword()
    {
        $userModel = new UserModel();
        $resetModel = new PasswordResetModel();

        // Handle both JSON and form data
        $contentType = $this->request->getHeaderLine('Content-Type');
        if (strpos($contentType, 'application/json') !== false) {
            $data = $this->request->getJSON(true);
            $email = $data['email'] ?? null;
        } else {
            $email = $this->request->getVar('email');
        }

        if (!$email) {
            return $this->response->setJSON(['error' => 'Email is required'])->setStatusCode(422);
        }

        $user = $userModel->where('email', $email)->first();
        if (!$user) {
            return $this->response->setJSON(['error' => 'Email not found'])->setStatusCode(404);
        }

        // Clean up any existing tokens for this email
        $resetModel->where('email', $email)->delete();

        $token = bin2hex(random_bytes(32));
        $resetModel->insert([
            'email'      => $email,
            'token'      => $token,
            'expires_at' => date('Y-m-d H:i:s', strtotime('+1 hour'))
        ]);

        // Create reset link for frontend
        $resetLink = "http://localhost:5173/reset-password?token={$token}";

        $emailService = Services::email();
        $emailService->setFrom(getenv('email.fromEmail'), getenv('email.fromName'));
        $emailService->setTo($email);
        $emailService->setSubject('🔑 Reset your password');
        $emailService->setMessage(view('emails/password_reset', [
            'first_name' => $user['first_name'],
            'reset_link' => $resetLink,
            'token' => $token
        ]));
        $emailService->send();

        return $this->response->setJSON([
            'message' => 'Reset link sent to email successfully',
            'debug' => [
                'reset_link' => $resetLink,
                'token' => $token
            ]
        ]);
    }

    public function resetPassword()
    {
        $resetModel = new PasswordResetModel();
        $userModel  = new UserModel();

        // Handle both JSON and form data
        $contentType = $this->request->getHeaderLine('Content-Type');
        if (strpos($contentType, 'application/json') !== false) {
            $data = $this->request->getJSON(true);
            $token = $data['token'] ?? null;
            $newPassword = $data['password'] ?? $data['password_hash'] ?? null;
        } else {
            $token = $this->request->getVar('token');
            $newPassword = $this->request->getVar('password') ?? $this->request->getVar('password_hash');
        }

        // Validate required fields
        if (!$token || !$newPassword) {
            return $this->response->setJSON([
                'error' => 'Token and new password are required'
            ])->setStatusCode(422);
        }

        // Validate password strength
        if (strlen($newPassword) < 8) {
            return $this->response->setJSON([
                'error' => 'Password must be at least 8 characters long'
            ])->setStatusCode(422);
        }

        $reset = $resetModel
            ->where('token', $token)
            ->where('expires_at >=', date('Y-m-d H:i:s'))
            ->first();

        if (!$reset) {
            return $this->response->setJSON([
                'error' => 'Invalid or expired reset token'
            ])->setStatusCode(400);
        }

        // Get user for email notification
        $user = $userModel->where('email', $reset['email'])->first();

        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $userModel
            ->where('email', $reset['email'])
            ->set(['password_hash' => $hashedPassword])
            ->update();

        // Clean up used token
        $resetModel->where('token', $token)->delete();

        // Send password change confirmation email
        $emailService = Services::email();
        $emailService->setFrom(getenv('email.fromEmail'), getenv('email.fromName'));
        $emailService->setTo($reset['email']);
        $emailService->setSubject('🔐 Password Changed Successfully');
        $emailService->setMessage(view('emails/password_changed', [
            'first_name' => $user['first_name'] ?? 'User',
            'time' => date('Y-m-d H:i:s'),
            'ip' => $this->request->getIPAddress()
        ]));
        $emailService->send();

        return $this->response->setJSON([
            'message' => 'Password updated successfully'
        ]);
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
