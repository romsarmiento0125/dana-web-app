<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table         = 'users';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['username', 'password_hash', 'created_at'];
    protected $useTimestamps = false;

    /**
     * Find a user by their username (case-insensitive via the DB collation).
     */
    public function findByUsername(string $username): ?array
    {
        return $this->where('username', $username)->first();
    }
}
