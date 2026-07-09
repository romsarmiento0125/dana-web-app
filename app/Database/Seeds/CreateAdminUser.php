<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Creates an initial admin user for Dana AI.
 *
 * Run with:  php spark db:seed CreateAdminUser
 *
 * Default credentials ─ change the password before going to production!
 *   Username : admin
 *   Password : changeme123
 */
class CreateAdminUser extends Seeder
{
    public function run(): void
    {
        $data = [
            'username'      => 'admin',
            'password_hash' => password_hash('changeme123', PASSWORD_BCRYPT),
            'role'          => 'admin',
            'created_at'    => date('Y-m-d H:i:s'),
        ];

        // Avoid duplicate inserts when seeder is run more than once
        $exists = $this->db->table('users')
                           ->where('username', $data['username'])
                           ->get()
                           ->getNumRows();

        if ($exists === 0) {
            $this->db->table('users')->insert($data);
            echo "Admin user created. Username: admin / Password: changeme123\n";
        } else {
            $this->db->table('users')
                ->where('username', $data['username'])
                ->update(['role' => 'admin']);
            echo "Admin user already exists — ensured role is admin.\n";
        }
    }
}
