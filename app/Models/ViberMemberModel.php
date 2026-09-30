<?php

namespace App\Models;

use CodeIgniter\Model;

class ViberMemberModel extends Model
{
    protected $table            = 'viber_members';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $dateFormat       = 'datetime';
    protected $allowedFields      = [
        'id', 'viber_id', 'name', 'phone', 'avatar', 'status', 'subscribed_at', 'created_at',
    ];
    protected $validationRules = [
        'viber_id' => 'required|max_length[64]',
        'name'     => 'required|max_length[120]',
    ];

    /**
     * Members that should receive a broadcast.
     *
     * @return list<object>
     */
    public function subscribedMembers(): array
    {
        return $this->where('status', 'subscribed')->findAll();
    }
}

