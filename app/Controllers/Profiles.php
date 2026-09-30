<?php

namespace App\Controllers;

use App\Libraries\AuthService;
use App\Models\ActivityLogModel;
use RuntimeException;

/**
 * Officer account management.
 *
 * The list is visible to any signed-in officer (read only);
 * every write action lives behind the `admin` route filter
 * (App\Filters\AdminFilter), i.e. role === 'admin'.
 */
class Profiles extends BaseController
{
    public function index()
    {
        $profiles = service('auth')->profiles();
        $cloudUp  = service('supabase')->health();

        return $this->page('profiles/index', [
            'pageTitle' => 'Officers & roles',
            'profiles'  => $profiles,
            'cloudUp'   => $cloudUp,
            'isAdmin'   => $this->isAdmin(),
            'total'     => count($profiles),
        ]);
    }

    // ------------------------------------------------------------------
    // Admin only (see Config\Routes.php -> filter 'admin')
    // ------------------------------------------------------------------

    public function edit(int $id)
    {
        $profile = $this->findProfile($id);
        if ($profile === null) {
            return redirect()->to('/profiles')->with('error', 'Account not found.');
        }

        return $this->page('profiles/edit', [
            'pageTitle' => 'Edit officer',
            'profile'   => $profile,
            'roles'     => AuthService::assignableRoles(),
            'isAdmin'   => $this->isAdmin(),
        ]);
    }

    public function update(int $id)
    {
        $profile = $this->findProfile($id);
        if ($profile === null) {
            return redirect()->to('/profiles')->with('error', 'Account not found.');
        }

        $rules = [
            'name'     => 'required|min_length[2]|max_length[120]',
            'username' => 'required|min_length[3]|max_length[60]|regex_match[/\A[a-z0-9_.-]+\z/i]',
            'email'    => 'permit_empty|valid_email|max_length[160]',
            'phone'    => 'permit_empty|max_length[32]',
            'role'     => 'required|in_list[admin,officer]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', implode(' ', $this->validator->getErrors()));
        }

        $username = trim((string) $this->request->getPost('username'));

        // Uniqueness check against every other account.
        try {
            $existing = service('supabase')->profileByUsername($username);
            if ($existing !== null && (int) $existing['id'] !== $id) {
                return redirect()->back()->withInput()->with('error', 'Username "' . $username . '" is already in use.');
            }
        } catch (RuntimeException $e) {
            // Cloud unreachable: fall back to the local mirror check.
            $local = (new \App\Models\OfficerModel())->where('username', $username)->first();
            if ($local && (int) $local->id !== $id) {
                return redirect()->back()->withInput()->with('error', 'Username "' . $username . '" is already in use.');
            }
        }

        $newRole   = (string) $this->request->getPost('role');

        // Canonical E.164 number; it is a sign-in identifier, so keep it unique.
        // A blank field keeps the existing number (never wipe details by accident).
        $postedPhone = trim((string) $this->request->getPost('phone'));
        $phone       = $postedPhone === ''
            ? \App\Libraries\AuthService::normalizePhone((string) ($profile['phone'] ?? ''))
            : \App\Libraries\AuthService::normalizePhone($postedPhone);

        $phoneProblem = $postedPhone === '' ? null : \App\Libraries\AuthService::phoneError($phone);
        if ($phoneProblem !== null) {
            return redirect()->back()->withInput()->with('error', $phoneProblem);
        }
        if ($phone !== '') {
            try {
                $existingPhone = service('supabase')->profileByPhone($phone);
                if ($existingPhone !== null && (int) $existingPhone['id'] !== $id) {
                    return redirect()->back()->withInput()->with('error', 'Mobile number ' . $phone . ' is already used by another account.');
                }
            } catch (RuntimeException $e) {
                // Cloud unreachable: skip the uniqueness check rather than lock the admin out.
            }
        }

        $isSelf    = $this->isSelf((string) ($profile['username'] ?? ''), $id);
        $data      = [
            'name'      => trim((string) $this->request->getPost('name')),
            'username'  => $username,
            'email'     => trim((string) $this->request->getPost('email')) ?: null,
            'phone'     => $phone ?: null,
            'role'      => $newRole,
            'updated_at' => date('c'),
        ];

        // An admin must not strip their own admin role (lockout guard).
        if ($isSelf && $newRole !== 'admin') {
            return redirect()->back()->withInput()->with('error', 'You cannot remove your own admin role.');
        }

        $password = (string) $this->request->getPost('password');
        if ($password !== '') {
            if (mb_strlen($password) < 6) {
                return redirect()->back()->withInput()->with('error', 'New password must be at least 6 characters.');
            }
            $data['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
        }

        try {
            $updated = service('supabase')->updateProfile($id, $data);
            $merged  = array_merge($profile, $updated ?? []);
            service('auth')->syncLocal($merged);

            if ($isSelf) {
                session()->set([
                    'officerName' => (string) $data['name'],
                    'officerRole' => (string) $newRole,
                ]);
            }

            $this->log('account_updated', 'id=' . $id . ' username=' . $username . ' role=' . $newRole);
            $this->flashSession($isSelf);

            $flash = $password !== ''
                ? 'Account "' . $username . '" updated (password reset).'
                : 'Account "' . $username . '" updated.';

            return redirect()->to('/profiles')->with('success', $flash);
        } catch (RuntimeException $e) {
            // Offline fallback: update the local mirror only.
            $this->updateLocal($id, $data);
            $this->log('account_updated_local', 'id=' . $id . ' offline', 'system', 'failed');

            return redirect()->to('/profiles')->with('success', 'Account updated in the local mirror (Supabase unreachable).');
        }
    }

    public function toggle(int $id)
    {
        $profile = $this->findProfile($id);
        if ($profile === null) {
            return redirect()->to('/profiles')->with('error', 'Account not found.');
        }

        $username = (string) ($profile['username'] ?? '');
        if ($this->isSelf($username)) {
            return redirect()->to('/profiles')->with('error', 'You cannot disable your own account.');
        }

        $active = ! (bool) ($profile['active'] ?? true);

        try {
            service('supabase')->updateProfile($id, ['active' => $active, 'updated_at' => date('c')]);
        } catch (RuntimeException $e) {
            $this->updateLocal($id, ['active' => $active]);
        }

        service('auth')->syncLocal(array_merge($profile, ['active' => $active]));
        $this->log($active ? 'account_enabled' : 'account_disabled', 'id=' . $id . ' username=' . $username);

        return redirect()->to('/profiles')->with('success', '"' . $username . '" is now ' . ($active ? 'active' : 'disabled') . '.');
    }

    public function destroy(int $id)
    {
        $profile = $this->findProfile($id);
        if ($profile === null) {
            return redirect()->to('/profiles')->with('error', 'Account not found.');
        }

        $username = (string) ($profile['username'] ?? '');
        if ($this->isSelf($username)) {
            return redirect()->to('/profiles')->with('error', 'You cannot delete your own account.');
        }

        try {
            service('supabase')->deleteProfile($id);
        } catch (RuntimeException $e) {
            // Continue: local cleanup still matters.
        }

        service('auth')->deleteLocalByUsername($username);
        $this->log('account_deleted', 'id=' . $id . ' username=' . $username, 'system', 'failed');

        return redirect()->to('/profiles')->with('success', 'Account "' . $username . '" deleted.');
    }

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    private function isAdmin(): bool
    {
        return (string) session()->get('officerRole') === 'admin';
    }

    /**
     * True when the target account is the operator's own account.
     * Matches on username because local and Supabase ids can differ.
     */
    private function isSelf(string $username, int $id = 0): bool
    {
        $sessionUser = (string) session()->get('officerUsername', '');

        if ($sessionUser !== '' && $username !== '' && $sessionUser === $username) {
            return true;
        }

        return $id > 0 && $id === (int) session()->get('officerId', 0);
    }

    /** @return array<string, mixed>|null */
    private function findProfile(int $id): ?array
    {
        try {
            $profile = service('supabase')->profileById($id);
            if ($profile !== null) {
                return $profile;
            }
        } catch (RuntimeException $e) {
            // Fall through to local lookup.
        }

        foreach (service('auth')->profiles() as $row) {
            if ((int) ($row['id'] ?? 0) === $id) {
                return $row;
            }
        }

        return null;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function updateLocal(int $id, array $data): void
    {
        try {
            $model = new \App\Models\OfficerModel();
            $row   = $model->find($id);
            if ($row) {
                $payload = array_intersect_key($data, array_flip(['name', 'username', 'role', 'active', 'password_hash']));
                if (array_key_exists('active', $payload)) {
                    $payload['active'] = $payload['active'] ? 1 : 0;
                }
                $model->update($id, $payload);
            }
        } catch (RuntimeException $e) {
            // Best effort.
        }
    }

    private function log(string $action, string $detail, string $channel = 'system', string $status = 'ok'): void
    {
        (new ActivityLogModel())->record(
            $action,
            $detail,
            $channel,
            $status,
            (int) (session()->get('officerId') ?: 0) ?: null
        );
    }

    /**
     * If the signed-in officer changed their own username, rotate the
     * session copy so the "you" marker keeps working.
     */
    private function flashSession(bool $isSelf): void
    {
        if ($isSelf) {
            session()->set(['officerUsername' => trim((string) $this->request->getPost('username'))]);
        }
    }
}
