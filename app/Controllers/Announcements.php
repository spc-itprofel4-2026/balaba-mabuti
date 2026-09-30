<?php

namespace App\Controllers;

use App\Models\ActivityLogModel;
use App\Models\ViberMemberModel;
use RuntimeException;

class Announcements extends BaseController
{
    public function index()
    {
        $supabase = service('supabase');
        $rows     = [];
        $error    = null;

        try {
            $rows = $supabase->announcements(100);
        } catch (RuntimeException $e) {
            $error = $e->getMessage();
        }

        return $this->page('announcements/index', [
            'pageTitle'  => 'Announcements',
            'rows'       => $rows,
            'error'      => $error,
            'supabaseUp' => $supabase->isConfigured(),
        ]);
    }

    public function create()
    {
        return $this->page('announcements/create', [
            'pageTitle' => 'New announcement',
        ]);
    }

    public function store()
    {
        $rules = [
            'title'    => 'required|max_length[160]',
            'body'     => 'required|max_length[4000]',
            'category' => 'permit_empty|in_list[general,event,reminder,urgent]',
            'priority' => 'permit_empty|in_list[normal,high]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->with('error', 'Please fix the highlighted fields.')->withInput();
        }

        $officer = $this->currentOfficer();

        $payload = [
            'title'    => trim((string) $this->request->getPost('title')),
            'body'     => trim((string) $this->request->getPost('body')),
            'category' => (string) ($this->request->getPost('category') ?: 'general'),
            'priority' => (string) ($this->request->getPost('priority') ?: 'normal'),
            'status'   => 'draft',
            'sent_by'  => $officer->name ?? null,
        ];

        try {
            $row = service('supabase')->createAnnouncement($payload);
        } catch (RuntimeException $e) {
            (new ActivityLogModel())->record('announcement_create_failed', $e->getMessage(), 'supabase', 'failed', $officer->id ?? null);

            return redirect()->back()->with('error', 'Could not save to Supabase: ' . $e->getMessage())->withInput();
        }

        (new ActivityLogModel())->record(
            'announcement_created',
            $payload['title'],
            'supabase',
            'ok',
            $officer->id ?? null
        );

        return redirect()->to('/announcements/' . ($row['id'] ?? ''))
            ->with('success', 'Announcement saved as draft.');
    }

    public function show(int $id)
    {
        $supabase = service('supabase');
        $error    = null;
        $row      = null;
        $log      = [];

        try {
            $row = $supabase->announcement($id);
            if ($row) {
                $log = $supabase->deliveries($id);
            }
        } catch (RuntimeException $e) {
            $error = $e->getMessage();
        }

        if (! $row && $error === null) {
            $error = 'Announcement #' . $id . ' was not found.';
        }

        return $this->page('announcements/show', [
            'pageTitle' => 'Announcement #' . $id,
            'row'       => $row,
            'deliveries' => $log,
            'error'     => $error,
        ]);
    }

    public function send(int $id)
    {
        $officer   = $this->currentOfficer();
        $supabase  = service('supabase');
        $viber     = service('viber');

        try {
            $row = $supabase->announcement($id);
        } catch (RuntimeException $e) {
            return redirect()->back()->with('error', 'Could not reach Supabase: ' . $e->getMessage());
        }

        if (! $row) {
            return redirect()->back()->with('error', 'Announcement not found.');
        }

        $members = (new ViberMemberModel())->subscribedMembers();
        if ($members === []) {
            return redirect()->back()->with('error', 'No subscribed Viber members yet. Add members first.');
        }

        $message = '📢 ' . $row['title'] . "\n\n" . $row['body'] . "\n\n— sent by " . ($officer->name ?? 'Officer');

        try {
            $summary = $viber->broadcast($message, $members);
        } catch (RuntimeException $e) {
            (new ActivityLogModel())->record('announcement_send_failed', $e->getMessage(), 'viber', 'failed', $officer->id ?? null);

            return redirect()->back()->with('error', 'Viber API error: ' . $e->getMessage());
        }

        $status = $summary['sent'] > 0 ? 'sent' : 'failed';

        try {
            $supabase->updateAnnouncement($id, [
                'status'          => $status,
                'recipient_count' => count($summary['results']),
                'delivered_count' => $summary['sent'],
                'sent_at'         => date('c'),
                'sent_by'         => $officer->name ?? 'Officer',
            ]);

            $deliveryRows = [];
            foreach ($summary['results'] as $result) {
                $deliveryRows[] = [
                    'announcement_id' => $id,
                    'viber_id'        => $result['viber_id'],
                    'member_name'     => $result['name'],
                    'status'          => $result['status'],
                    'response'        => $result['message'],
                ];
            }
            $supabase->addDeliveries($deliveryRows);
        } catch (RuntimeException $e) {
            (new ActivityLogModel())->record('announcement_update_failed', $e->getMessage(), 'supabase', 'failed', $officer->id ?? null);
        }

        $mode = $summary['demo'] ? ' [demo mode - not really sent]' : '';
        (new ActivityLogModel())->record(
            'announcement_sent',
            '#' . $id . ' "' . $row['title'] . '" -> ' . $summary['sent'] . ' sent / ' . $summary['failed'] . ' failed' . $mode,
            'viber',
            $summary['failed'] === 0 ? 'ok' : 'partial',
            $officer->id ?? null
        );

        $flash = 'Broadcast complete: ' . $summary['sent'] . ' sent, ' . $summary['failed'] . ' failed.';
        if ($summary['demo']) {
            $flash .= ' (Demo mode - configure viber.botToken in .env for real delivery.)';
        }

        return redirect()->to('/announcements/' . $id)->with('success', $flash);
    }

    public function destroy(int $id)
    {
        $officer = $this->currentOfficer();

        try {
            service('supabase')->deleteAnnouncement($id);
        } catch (RuntimeException $e) {
            return redirect()->back()->with('error', 'Could not delete: ' . $e->getMessage());
        }

        (new ActivityLogModel())->record('announcement_deleted', '#' . $id, 'supabase', 'ok', $officer->id ?? null);

        return redirect()->to('/announcements')->with('success', 'Announcement #' . $id . ' deleted.');
    }
}
