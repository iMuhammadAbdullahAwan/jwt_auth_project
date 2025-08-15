<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;
use Config\Services;

class JwtFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $authHeader = $request->getHeaderLine('Authorization');

        if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return Services::response()
                ->setJSON(['error' => 'Authorization token required'])
                ->setStatusCode(401);
        }

        $token = $matches[1];
        $decoded = $this->decodeJWT($token, getenv('JWT_SECRET'));

        if (!$decoded || ($decoded['exp'] ?? 0) < time()) {
            return Services::response()
                ->setJSON(['error' => 'Invalid or expired token'])
                ->setStatusCode(401);
        }

        // Optionally attach user info to request
        $request->user = $decoded;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Nothing after
    }

    /**
     * Decode and verify JWT
     */
    private function decodeJWT($jwt, $secret)
    {
        $parts = explode('.', $jwt);
        if (count($parts) !== 3) {
            return null;
        }

        [$header64, $payload64, $signature] = $parts;

        $header  = json_decode(base64_decode($header64), true);
        $payload = json_decode(base64_decode($payload64), true);

        // Verify signature
        $validSig = hash_hmac('sha256', "$header64.$payload64", $secret, true);
        $validSig = rtrim(strtr(base64_encode($validSig), '+/', '-_'), '=');

        if (!hash_equals($validSig, $signature)) {
            return null;
        }

        return $payload;
    }
}
