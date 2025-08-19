<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePasswordResets extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'auto_increment' => true],
            'email'      => ['type' => 'VARCHAR', 'constraint' => '255'],
            'token'      => ['type' => 'VARCHAR', 'constraint' => '64'],
            'expires_at' => ['type' => 'DATETIME']
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('password_resets');
    }


    public function down()
    {
        $this->forge->dropTable('password_resets');
    }
}
