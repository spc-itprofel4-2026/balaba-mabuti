<?php

namespace App\Models;

use CodeIgniter\Model;

class ActivityLogModel extends Model
{
    protected $table            = 'activity_logs';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $dateFormat       = 'datetime';
    protected $allowedFields      = [
        'id', 'officer_id', 'action', 'detail', 'channel', 'status', 'created_at',
    ];

    /**
     * @param int|null $officerId
     */
    public function record(string $action, ?string $detail = null, string $channel = 'system', string $status = 'ok', $officerId = null): void
    {
        $this->insert([
            'officer_id' => $officerId,
            'action'     => $action,
            'detail'     => $detail,
            'channel'    => $channel,
            'status'     => $status,
        ]);
    }
}

