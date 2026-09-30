<?php

namespace App\Controllers;

use App\Libraries\SupabaseService;
use App\Libraries\ViberService;
use App\Models\ActivityLogModel;
use App\Models\ViberMemberModel;
use RuntimeException;

class Dashboard extends BaseController
{
    public function index()
    {
        $supabase = service('supabase');
        $viber    = service('viber');

        $stats = [
            'total' => 0, 'sent' => 0, 'draft' => 0, 'failed' => 0,
            'deliveries_ok' => 0, 'deliveries_failed' => 0,
        ];
        $cloudError = null;

        try {
            $stats = $supabase->stats();
        } catch (RuntimeException $e) {
            $cloudError = $e->getMessage();
        }

        $memberModel = new ViberMemberModel();
        $members     = [
            'total'        => $memberModel->countAllResults(),
            'subscribed'   => $memberModel->where('status', 'subscribed')->countAllResults(),
            'unsubscribed' => $memberModel->where('status', 'unsubscribed')->countAllResults(),
        ];

        $logs = (new ActivityLogModel())->orderBy('id', 'DESC')->limit(10)->findAll();

        $viberInfo = null;
        if ($viber->isConfigured()) {
            try {
                $viberInfo = $viber->accountInfo();
            } catch (RuntimeException $e) {
                $viberInfo = ['ok' => false, 'demo' => false, 'message' => $e->getMessage()];
            }
        }

        return $this->page('dashboard', [
            'pageTitle'  => 'Dashboard',
            'stats'      => $stats,
            'members'    => $members,
            'logs'       => $logs,
            'cloudUp'    => $cloudError === null && $supabase->isConfigured(),
            'cloudError' => $cloudError,
            'viber'      => [
                'configured' => $viber->isConfigured(),
                'info'       => $viberInfo,
            ],
        ]);
    }
}
