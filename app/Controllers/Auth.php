<?php

namespace App\Controllers;

use App\Models\ActivityLogModel;
use RuntimeException;

class Auth extends BaseController
{
    private const MAX_ATTEMPTS = 5;
    private const LOCK_SECONDS = 60;

    public function index()
    {
        if (session()->get('officerId')) {
            return redirect()->to('/dashboard');
        }

        // Standalone page: no sidebar/topbar for signed-out visitors.
        return view('auth/login');
    }

    public function login()
    {
        $rules = [
            'username' => 'required|min_length[3]|max_length[60]',
            'password' => 'required|min_length[6]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->with('error', 'Mobile number/username and password are required.')->withInput();
        }

        // Simple lockout against password guessing.
        $lockUntil = (int) session()->get('lockUntil');
        if ($lockUntil > time()) {
            $wait = $lockUntil - time();

            return redirect()->back()->with('error', 'Too many failed attempts. Try again in ' . $wait . ' second(s).');
        }

        $username = trim((string) $this->request->getPost('username'));
        $password = (string) $this->request->getPost('password');

        try {
            $profile = service('auth')->attempt($username, $password);
        } catch (RuntimeException $e) {
            return redirect()->back()->with('error', 'Sign-in service unavailable: ' . $e->getMessage())->withInput();
        }

        if ($profile === null) {
            $attempts = (int) session()->get('loginAttempts') + 1;
            session()->set(['loginAttempts' => $attempts]);

            if ($attempts >= self::MAX_ATTEMPTS) {
                session()->set([
                    'loginAttempts' => 0,
                    'lockUntil'     => time() + self::LOCK_SECONDS,
                ]);

                return redirect()->back()->with('error', 'Too many failed attempts. Locked for ' . self::LOCK_SECONDS . ' seconds.');
            }

            (new ActivityLogModel())->record('login_failed', 'username=' . $username, 'auth', 'failed');

            return redirect()->back()->with('error', 'Invalid credentials.')->withInput();
        }

        session()->regenerate();
        session()->remove(['loginAttempts', 'lockUntil']);
        session()->set([
            'officerId'       => (int) $profile['id'],
            'officerName'     => (string) $profile['name'],
            'officerRole'     => (string) ($profile['role'] ?? 'officer'),
            'officerUsername' => (string) ($profile['username'] ?? $username),
        ]);

        (new ActivityLogModel())->record(
            'login',
            'username=' . $username . ' via ' . ($profile['source'] ?? 'supabase'),
            'auth',
            'ok',
            (int) $profile['id']
        );

        return redirect()->to('/dashboard')->with('success', 'Welcome back, ' . $profile['name'] . '.');
    }

    public function logout()
    {
        $officerId = session()->get('officerId');

        if ($officerId) {
            (new ActivityLogModel())->record('logout', null, 'auth', 'ok', (int) $officerId);
        }

        session()->destroy();

        return redirect()->to('/login')->with('success', 'You have been signed out.');
    }
}
