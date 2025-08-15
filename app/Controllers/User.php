<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UserModel;
use CodeIgniter\HTTP\ResponseInterface;

class User extends BaseController
{
    public function index()
    {
        $userModel = new UserModel();
        $users = $userModel->findAll();

        return $this->response->setJSON($users)->setStatusCode(200);
    }

    public function show($id)
    {
        $userModel = new UserModel();
        $user = $userModel->find($id);

        if (!$user) {
            return $this->response->setJSON(['error' => 'User not found'])->setStatusCode(404);
        }

        return $this->response->setJSON($user)->setStatusCode(200);
    }

    public function create()
    {
        $userModel = new UserModel();
        $data = $this->request->getPost();

        if (!$this->validate($userModel->getValidationRules())) {
            return $this->response->setJSON(['errors' => $this->validator->getErrors()])->setStatusCode(422);
        }

        $data['password_hash'] = password_hash($data['password_hash'], PASSWORD_DEFAULT);
        $userId = $userModel->insert($data);

        return $this->response->setJSON(['message' => 'User created', 'user_id' => $userId])->setStatusCode(201);
    }

    public function update($id)
    {
        $userModel = new UserModel();
        $data = $this->request->getPost();


        // Get all rules from model
        $allRules = $userModel->getValidationRules();
        $rules = [];

        foreach ($data as $field => $value) {
            if (isset($allRules[$field])) {
                $rules[$field] = $allRules[$field];
            }
        }

        // Validate only updated fields
        if (!empty($rules) && !$this->validate($rules)) {
            return $this->response
                ->setJSON(['errors' => $this->validator->getErrors()])
                ->setStatusCode(422);
        }

        // Hash password if it's being updated
        if (isset($data['password_hash'])) {
            $data['password_hash'] = password_hash($data['password_hash'], PASSWORD_DEFAULT);
        }

        // Attempt update
        if (!$userModel->update($id, $data)) {
            return $this->response
                ->setJSON(['error' => 'User not found or not updated'])
                ->setStatusCode(404);
        }

        return $this->response
            ->setJSON(['message' => 'User updated'])
            ->setStatusCode(200);
    }

    public function delete($id)
    {
        $userModel = new UserModel();

        if (!$userModel->delete($id)) {
            return $this->response->setJSON(['error' => 'User not found'])->setStatusCode(404);
        }

        return $this->response->setJSON(['message' => 'User deleted'])->setStatusCode(200);
    }
}
