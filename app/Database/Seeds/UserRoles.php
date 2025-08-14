<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserRoles extends Seeder
{
    public function run()
    {
        $user_roles = [
            [
                'role_name' => 'Admin',
            ],
            [
                'role_name' => 'Coach',
            ],
            [
                'role_name' => 'Athlete',
            ]
        ];

        $this->db->table('user_roles')->insertBatch($user_roles);
    }
}
