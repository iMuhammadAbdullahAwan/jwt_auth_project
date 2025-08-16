<?php

namespace App\Models;

use CodeIgniter\Model;

class RefreshTokenModel extends Model
{
    protected $table      = 'refresh_tokens';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = false; // we manage expiry manually

    protected $allowedFields = [
        'user_id',
        'token',
        'expires_at',
    ];
}
