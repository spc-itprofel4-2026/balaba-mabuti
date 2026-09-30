<?php

namespace App\Controllers;

use App\Models\ActivityLogModel;
use App\Models\ViberMemberModel;

class Members extends BaseController
{
    public function index()
    {
        $model = new ViberMemberModel();

        $rows = $model->orderBy('created_at', 'DESC')->findAll();

        return $this->page('members/index', [
            'pageTitle'   => 'Viber members',
            'rows'        => $rows,
            'subscribed'  => count(array_filter($rows, static fn ($r) => $r->status === 'subscribed')),
            'total'       => count($rows),
        ]);
    }

    public function store()
    {
        $rules = [
            'viber_id' => 'required|max_length[64]',
            'name'     => 'required|max_length[120]',
            'phone'    => 'permit_empty|max_length[32]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->with('error', 'Please provide a Viber ID and a name.')->withInput();
        }

        $model  = new ViberMemberModel();
        $viberId = trim((string) $this->request->getPost('viber_id'));

        if ($model->where('viber_id', $viberId)->countAllResults() > 0) {
            return redirect()->back()->with('error', 'That Viber ID is already on the list.')->withInput();
        }

        $model->insert([
            'viber_id'      => $viberId,
            'name'          => trim((string) $this->request->getPost('name')),
            'phone'         => trim((string) $this->request->getPost('phone')) ?: null,
            'status'        => 'subscribed',
            'subscribed_at' => date('Y-m-d H:i:s'),
        ]);

        (new ActivityLogModel())->record('member_added', $viberId, 'viber', 'ok', session()->get('officerId'));

        return redirect()->back()->with('success', 'Member added.');
    }

    public function toggle(int $id)
    {
        $model = new ViberMemberModel();
        $row   = $model->find($id);

        if (! $row) {
            return redirect()->back()->with('error', 'Member not found.');
        }

        $status = $row->status === 'subscribed' ? 'unsubscribed' : 'subscribed';
        $model->update($id, [
            'status'        => $status,
            'subscribed_at' => $status === 'subscribed' ? date('Y-m-d H:i:s') : $row->subscribed_at,
        ]);

        (new ActivityLogModel())->record('member_' . $status, $row->viber_id, 'viber', 'ok', session()->get('officerId'));

        return redirect()->back()->with('success', $row->name . ' is now ' . $status . '.');
    }

    public function destroy(int $id)
    {
        $model = new ViberMemberModel();
        $row   = $model->find($id);

        if ($row) {
            $model->delete($id);
            (new ActivityLogModel())->record('member_deleted', $row->viber_id, 'viber', 'ok', session()->get('officerId'));
        }

        return redirect()->back()->with('success', 'Member removed.');
    }
}
