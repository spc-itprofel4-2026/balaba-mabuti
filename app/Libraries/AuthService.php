<?php

namespace App\Libraries;

use App\Models\OfficerModel;
use RuntimeException;

/**
 * Fullstack authentication.
 *
 * Supabase `user_profiles` is the system of record for accounts; the local
 * SQLite `officers` table is kept as a mirror so sign-in still works when the
 * cloud is unreachable (and so activity logs have a local owner to point at).
 */
class AuthService
{
    private SupabaseService $supabase;

    public function __construct(?SupabaseService $supabase = null)
    {
        $this->supabase = $supabase ?? service('supabase');
    }

    /**
     * Viber-style sign-in: the identifier may be a mobile number or a username.
     *
     * @return array<string, mixed>|null
     */
    public function attempt(string $username, string $password): ?array
    {
        $identifier = trim($username);
        $profile    = null;
        $cloudOk    = true;

        try {
            if (self::looksLikePhone($identifier)) {
                $phone  = self::normalizePhone($identifier);
                $profile = $phone === '' ? null : $this->supabase->profileByPhone($phone);
            }

            if ($profile === null) {
                $profile = $this->supabase->profileByUsername($identifier);
            }
        } catch (RuntimeException $e) {
            $cloudOk = false;
        }

        if ($cloudOk) {
            if ($profile === null
                || ! (bool) ($profile['active'] ?? true)
                || ! password_verify($password, (string) $profile['password_hash'])) {
                return null;
            }

            $this->touchCloud($profile);
            $this->mirrorLocal($profile);

            return $profile;
        }

        // Offline fallback: verify against the local mirror (by number or username).
        $model = new OfficerModel();
        $local = self::looksLikePhone($identifier)
            ? $model->where('phone', self::normalizePhone($identifier))->first()
            : null;
        $local = $local ?? $model->where('username', $identifier)->first();
        if (! $local || (int) $local->active !== 1 || ! password_verify($password, $local->password_hash)) {
            return null;
        }

        return [
            'id'          => (int) $local->id,
            'name'        => $local->name,
            'username'    => $local->username,
            'role'        => $local->role,
            'active'      => (bool) $local->active,
            'source'      => 'local',
        ];
    }

    /**
     * True when the sign-in box holds a mobile number instead of a username.
     */
    public static function looksLikePhone(string $value): bool
    {
        $value = trim($value);

        if ($value === '' || ! preg_match('/^[\d\s\-\.\(\+]+$/', $value)) {
            return false;
        }

        $digits = preg_replace('/\D+/', '', $value) ?? '';

        return strlen($digits) >= 7 && strlen($digits) <= 15;
    }

    /**
     * Normalises a number to E.164 (+<country><number>).
     *
     * Philippine local formats are converted automatically; other countries
     * must be typed with their country code (+1, +44, +61 …).
     *
     * 0917 123 4567   -> +639171234567
     * 639171234567    -> +639171234567
     * +1 202 555 0123 -> +12025550123
     */
    public static function normalizePhone(string $raw): string
    {
        $raw = trim($raw);

        if ($raw === '') {
            return '';
        }

        // Explicit international format: keep it, just strip separators.
        if ($raw[0] === '+') {
            return '+' . (preg_replace('/\D+/', '', $raw) ?? '');
        }

        $digits = preg_replace('/\D+/', '', $raw) ?? '';
        if ($digits === '') {
            return '';
        }

        // Philippine local formats only.
        if (preg_match('/^09\d{9}$/', $digits)) {
            return '+63' . substr($digits, 1);          // 09171234567
        }
        if (preg_match('/^63\d{10,11}$/', $digits)) {
            return '+' . $digits;                       // 639171234567
        }
        if (preg_match('/^9\d{9}$/', $digits)) {
            return '+63' . $digits;                     // 9171234567
        }

        // Anything else: assume an international number missing the "+".
        return '+' . $digits;
    }

    /**
     * E.164 correctness check: returns a human-readable problem or null.
     *
     * Standard: minimum 7 and maximum 15 digits after the "+" (ITU E.164),
     * plus specific rules for Philippine numbers.
     */
    public static function phoneError(string $phone): ?string
    {
        if ($phone === '') {
            return 'Mobile number is required.';
        }

        if (! preg_match('/^\+[1-9]\d*$/', $phone)) {
            return 'Use digits only, starting with + and your country code, e.g. +639171234567.';
        }

        $digits = substr($phone, 1);

        if ($digits[0] === '0') {
            return 'Drop the leading 0 and add your country code, e.g. +639171234567 (or +1…, +44…).';
        }
        if (strlen($digits) < 7) {
            return 'Number is too short — minimum is 7 digits (e.g. +639171234567).';
        }
        if (strlen($digits) > 15) {
            return 'Number is too long — maximum is 15 digits (ITU E.164 standard).';
        }
        if (str_starts_with($digits, '63') && ! preg_match('/^63(9\d{9}|[2-8]\d{7,8})$/', $digits)) {
            return 'Philippine numbers look like +639171234567 (mobile) or +63281234567 (landline).';
        }

        return null;
    }

    /**
     * Creates a new officer account in Supabase and mirrors it locally.
     *
     * @param array{name: string, username: string, email: ?string, phone: ?string, password: string} $data
     * @return array<string, mixed>
     */
    public function register(array $data): array
    {
        $username = trim($data['username']);
        $phone    = self::normalizePhone((string) ($data['phone'] ?? ''));

        if ($this->supabase->usernameTaken($username)) {
            throw new RuntimeException('That username is already taken.');
        }

        if ($phone !== '' && $this->supabase->phoneTaken($phone)) {
            throw new RuntimeException('That mobile number is already registered. Sign in instead.');
        }

        // Role is never taken from user input: registration always yields an officer.
        $profile = $this->supabase->createProfile([
            'name'          => trim($data['name']),
            'username'      => $username,
            'email'         => trim((string) ($data['email'] ?? '')) ?: null,
            'phone'         => $phone ?: null,
            'role'          => 'officer',
            'password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
            'active'        => true,
        ]);

        $this->mirrorLocal($profile);

        return $profile;
    }

    /**
     * Account list for the admin screen, cloud first with local fallback.
     *
     * @return list<array<string, mixed>>
     */
    public function profiles(): array
    {
        try {
            return $this->supabase->profiles(200);
        } catch (RuntimeException $e) {
            $rows = [];
            foreach ((new OfficerModel())->orderBy('created_at', 'ASC')->findAll() as $row) {
                $rows[] = [
                    'id'         => (int) $row->id,
                    'name'       => $row->name,
                    'username'   => $row->username,
                    'email'      => null,
                    'phone'      => $row->phone ?? null,
                    'role'       => $row->role,
                    'active'     => (bool) $row->active,
                    'last_login' => $row->last_login,
                    'created_at' => $row->created_at,
                    'source'     => 'local',
                ];
            }

            return $rows;
        }
    }

    /**
     * Keeps the local mirror in sync so offline sign-in keeps working.
     *
     * @param array<string, mixed> $profile
     */
    public function syncLocal(array $profile): void
    {
        $this->mirrorLocal($profile);
    }

    /**
     * Removes/locks the local mirror row after an admin action.
     */
    public function deleteLocalByUsername(string $username): void
    {
        try {
            $model = new OfficerModel();
            $row   = $model->where('username', $username)->first();
            if ($row) {
                $model->delete((int) $row->id);
            }
        } catch (RuntimeException $e) {
            // Best effort.
        }
    }

    /**
     * Roles an admin may assign.
     *
     * @return list<string>
     */
    public static function assignableRoles(): array
    {
        return ['admin', 'officer'];
    }

    /**
     * @param array<string, mixed> $profile
     */
    private function mirrorLocal(array $profile): void
    {
        try {
            $model = new OfficerModel();
            $row   = $model->where('username', (string) $profile['username'])->first();

            $payload = [
                'name'          => (string) $profile['name'],
                'phone'         => self::normalizePhone((string) ($profile['phone'] ?? '')) ?: null,
                'password_hash' => (string) $profile['password_hash'],
                'role'          => (string) ($profile['role'] ?? 'officer'),
                'active'        => ! empty($profile['active']) ? 1 : 0,
            ];

            if ($row) {
                $model->update((int) $row->id, $payload);
            } else {
                $model->insert($payload + ['username' => (string) $profile['username']]);
            }
        } catch (RuntimeException $e) {
            // Local mirror is best-effort; never block sign-in on it.
        }
    }

    /**
     * @param array<string, mixed> $profile
     */
    private function touchCloud(array $profile): void
    {
        try {
            $this->supabase->updateProfile((int) $profile['id'], [
                'last_login' => date('c'),
                'updated_at' => date('c'),
            ]);
        } catch (RuntimeException $e) {
            // Ignore: a failed last_login write must not block sign-in.
        }
    }
}
