<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddRoleToUsersTable extends Migration
{
    public function up(): void
    {
        if ($this->db->fieldExists('role', 'users')) {
            return;
        }

        $this->forge->addColumn('users', [
            'role' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'user',
                'null'       => false,
                'after'      => 'password_hash',
            ],
        ]);

        // Keep existing records safe; ensure admin account has admin role.
        $this->db->table('users')->set('role', 'user')->update();
        $this->db->table('users')->set('role', 'admin')->where('username', 'admin')->update();
    }

    public function down(): void
    {
        if ($this->db->fieldExists('role', 'users')) {
            $this->forge->dropColumn('users', 'role');
        }
    }
}
