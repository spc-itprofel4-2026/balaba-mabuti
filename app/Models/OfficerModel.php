<?php

namespace App\Models;

use CodeIgniter\Model;

class OfficerModel extends Model
{
    protected $table            = 'officers';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $dateFormat       = 'datetime';
    protected $allowedFields      = [
        'id', 'name', 'username', 'phone', 'password_hash', 'role', 'active', 'last_login', 'created_at',
    ];
    protected $hidden   = ['password_hash'];
    protected $validationRules = [
        'username' => 'required|min_length[3]|max_length[60]|is_unique[officers.username,id,{id}]',
        'name'     => 'required|max_length[120]',
    ];
}

