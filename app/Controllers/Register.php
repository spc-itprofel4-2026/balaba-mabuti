<?php

namespace App\Controllers;

use App\Libraries\AuthService;
use App\Models\ActivityLogModel;
use RuntimeException;

class Register extends BaseController
{
    public function index()
    {
        if (session()->get('officerId')) {
            return redirect()->to('/dashboard');
        }

        return view('auth/register');
    }

    public function store()
    {
        $rules = [
            'name'            => 'required|max_length[120]',
            'username'        => 'required|min_length[3]|max_length[60]|regex_match[/^[A-Za-z0-9._-]+$/]',
            'email'           => 'required|valid_email|max_length[160]',
            'phone'           => 'required|max_length[40]|regex_match[/^\+?[\d\s\-\.\(\)]{1,40}$/]',
            'password'        => 'required|min_length[8]|max_length[72]',
            'password_confirm' => 'required|matches[password]',
        ];

        $messages = [
            'username.regex_match' => 'Username may only contain letters, numbers, dot, dash and underscore.',
            'phone.required'       => 'Mobile number is required — it is how you sign in, like Viber.',
            'phone.regex_match'    => 'Enter a valid mobile number, e.g. 09171234567 or +12025550123.',
            'password.matches'     => 'The password confirmation does not match.',
        ];

        if (! $this->validate($rules, $messages)) {
            return redirect()->back()->with('error', 'Please fix the highlighted fields.')->withInput();
        }

        // Canonical E.164 form is stored and used for sign-in lookups.
        $phone = AuthService::normalizePhone((string) $this->request->getPost('phone'));
        if (($problem = AuthService::phoneError($phone)) !== null) {
            return redirect()->back()->with('error', $problem)->withInput();
        }

        // Invitation control: only people holding the org's invite code may
        // create an officer account (keeps posting rights limited, see Part 2).
        $invite = trim((string) env('auth.inviteCode', ''));
        if ($invite !== '' && ! hash_equals($invite, trim((string) $this->request->getPost('invite_code')))) {
            return redirect()->back()->with('error', 'Invalid invite code. Ask an admin for the current code.')->withInput();
        }

        try {
            $profile = service('auth')->register([
                'name'     => (string) $this->request->getPost('name'),
                'username' => (string) $this->request->getPost('username'),
                'email'    => (string) $this->request->getPost('email'),
                'phone'    => $phone,
                'password' => (string) $this->request->getPost('password'),
            ]);
        } catch (RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage())->withInput();
        }

        (new ActivityLogModel())->record(
            'account_registered',
            'username=' . ($profile['username'] ?? ''),
            'supabase',
            'ok',
            (int) ($profile['id'] ?? 0)
        );

        // Sign the new officer in immediately.
        session()->regenerate();
        session()->set([
            'officerId'       => (int) $profile['id'],
            'officerName'     => (string) $profile['name'],
            'officerRole'     => (string) ($profile['role'] ?? 'officer'),
            'officerUsername' => (string) ($profile['username'] ?? ''),
        ]);

        return redirect()->to('/dashboard')->with('success', 'Account created. Welcome, ' . $profile['name'] . '!');
    }
}
