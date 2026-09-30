<?php

namespace App\Libraries;

use RuntimeException;

/**
 * Cloud system of record for announcements.
 *
 * Talks to the Supabase project's PostgREST endpoint with the service_role
 * key (server-side only, never exposed to the browser). Table setup goes
 * through the Supabase Management API using the personal access token.
 */
class SupabaseService
{
    private string $url;
    private string $ref;
    private string $serviceKey;
    private string $accessToken;
    private int $timeout;

    public function __construct()
    {
        $this->url         = rtrim((string) env('supabase.url', ''), '/');
        $this->ref         = (string) env('supabase.projectRef', '');
        $this->serviceKey  = (string) env('supabase.serviceRoleKey', '');
        $this->accessToken = (string) env('supabase.accessToken', '');
        $this->timeout     = 15;
    }

    public function isConfigured(): bool
    {
        return $this->url !== ''
            && $this->serviceKey !== ''
            && stripos($this->serviceKey, 'REPLACE_') === false;
    }

    /**
     * Lightweight connectivity probe used by the dashboard.
     */
    public function health(): bool
    {
        if (! $this->isConfigured()) {
            return false;
        }

        try {
            $this->rest('GET', 'announcements', ['select' => 'id', 'limit' => '1']);

            return true;
        } catch (RuntimeException $e) {
            return false;
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function announcements(int $limit = 50): array
    {
        $rows = $this->rest('GET', 'announcements', [
            'select' => '*',
            'order'  => 'created_at.desc',
            'limit'  => (string) $limit,
        ]);

        return is_array($rows) ? $rows : [];
    }

    public function announcement(int $id): ?array
    {
        $rows = $this->rest('GET', 'announcements', [
            'select' => '*',
            'id'     => 'eq.' . $id,
            'limit'  => '1',
        ]);

        return isset($rows[0]) && is_array($rows[0]) ? $rows[0] : null;
    }

    public function createAnnouncement(array $data): array
    {
        $rows = $this->rest('POST', 'announcements', [], $data);

        if (! isset($rows[0]) || ! is_array($rows[0])) {
            throw new RuntimeException('Supabase did not return the created announcement.');
        }

        return $rows[0];
    }

    public function updateAnnouncement(int $id, array $data): ?array
    {
        $rows = $this->rest('PATCH', 'announcements', ['id' => 'eq.' . $id], $data);

        return isset($rows[0]) && is_array($rows[0]) ? $rows[0] : null;
    }

    public function deleteAnnouncement(int $id): void
    {
        $this->rest('DELETE', 'announcements', ['id' => 'eq.' . $id]);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function deliveries(int $announcementId): array
    {
        $rows = $this->rest('GET', 'announcement_deliveries', [
            'select'          => '*',
            'announcement_id' => 'eq.' . $announcementId,
            'order'           => 'created_at.desc',
            'limit'           => '1000',
        ]);

        return is_array($rows) ? $rows : [];
    }

    /**
     * @param list<array<string, mixed>> $rows
     */
    public function addDeliveries(array $rows): void
    {
        if ($rows === []) {
            return;
        }

        $this->rest('POST', 'announcement_deliveries', [], count($rows) === 1 ? $rows[0] : $rows);
    }

    /**
     * Dashboard counters, computed client-side over the two tables.
     *
     * @return array<string, int>
     */
    public function stats(): array
    {
        $announcements = $this->rest('GET', 'announcements', [
            'select' => 'status',
            'limit'  => '5000',
        ]);
        $deliveries    = $this->rest('GET', 'announcement_deliveries', [
            'select' => 'status',
            'limit'  => '5000',
        ]);

        $stats = [
            'total'            => 0,
            'sent'             => 0,
            'draft'            => 0,
            'failed'           => 0,
            'deliveries_ok'    => 0,
            'deliveries_failed' => 0,
        ];

        foreach ((array) $announcements as $row) {
            $stats['total']++;
            $status            = (string) ($row['status'] ?? '');
            $stats[$status]    = ($stats[$status] ?? 0) + 1;
        }

        foreach ((array) $deliveries as $row) {
            if (($row['status'] ?? '') === 'sent') {
                $stats['deliveries_ok']++;
            } else {
                $stats['deliveries_failed']++;
            }
        }

        return $stats;
    }

    // ------------------------------------------------------------------
    // User profiles (fullstack auth: system of record lives in Supabase)
    // ------------------------------------------------------------------

    /**
     * @return list<array<string, mixed>>
     */
    public function profiles(int $limit = 100): array
    {
        $rows = $this->rest('GET', 'user_profiles', [
            'select' => 'id,name,username,email,phone,role,active,last_login,created_at',
            'order'  => 'created_at.asc',
            'limit'  => (string) $limit,
        ]);

        return is_array($rows) ? $rows : [];
    }

    public function profileByUsername(string $username): ?array
    {
        $rows = $this->rest('GET', 'user_profiles', [
            'select'  => '*',
            'username' => 'eq.' . $username,
            'limit'   => '1',
        ]);

        return isset($rows[0]) && is_array($rows[0]) ? $rows[0] : null;
    }

    public function profileByPhone(string $phone): ?array
    {
        $rows = $this->rest('GET', 'user_profiles', [
            'select' => '*',
            'phone'  => 'eq.' . $phone,
            'limit'  => '1',
        ]);

        return isset($rows[0]) && is_array($rows[0]) ? $rows[0] : null;
    }

    public function profileById(int $id): ?array
    {
        $rows = $this->rest('GET', 'user_profiles', [
            'select' => '*',
            'id'     => 'eq.' . $id,
            'limit'  => '1',
        ]);

        return isset($rows[0]) && is_array($rows[0]) ? $rows[0] : null;
    }

    public function createProfile(array $data): array
    {
        $rows = $this->rest('POST', 'user_profiles', [], $data);

        if (! isset($rows[0]) || ! is_array($rows[0])) {
            throw new RuntimeException('Supabase did not return the created profile.');
        }

        return $rows[0];
    }

    public function updateProfile(int $id, array $data): ?array
    {
        $rows = $this->rest('PATCH', 'user_profiles', ['id' => 'eq.' . $id], $data);

        return isset($rows[0]) && is_array($rows[0]) ? $rows[0] : null;
    }

    public function usernameTaken(string $username): bool
    {
        try {
            return $this->profileByUsername($username) !== null;
        } catch (RuntimeException $e) {
            return false;
        }
    }

    public function phoneTaken(string $phone): bool
    {
        if ($phone === '') {
            return false;
        }

        try {
            return $this->profileByPhone($phone) !== null;
        } catch (RuntimeException $e) {
            return false;
        }
    }

    public function deleteProfile(int $id): void
    {
        $this->rest('DELETE', 'user_profiles', ['id' => 'eq.' . $id]);
    }

    /**
     * Runs raw SQL through the Supabase Management API (schema setup only).
     *
     * @return list<array<string, mixed>>
     */    public function runSql(string $sql): array
    {
        if ($this->ref === '' || $this->accessToken === '') {
            throw new RuntimeException('Supabase project reference or access token is missing.');
        }

        $url    = 'https://api.supabase.com/v1/projects/' . $this->ref . '/database/query';
        $status = 0;
        $body   = $this->http($url, 'POST', [
            'Authorization: Bearer ' . $this->accessToken,
            'Content-Type: application/json',
        ], (string) json_encode(['query' => $sql]), $status);

        if ($status < 200 || $status >= 300) {
            throw new RuntimeException('Supabase SQL endpoint returned HTTP ' . $status . ': ' . $this->excerpt($body));
        }

        $decoded = json_decode($body, true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * @param array<string, mixed> $query  PostgREST query string parameters
     * @return list<mixed>|array<string, mixed>
     */
    private function rest(string $method, string $resource, array $query = [], ?array $body = null): array
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('Supabase is not configured. Check supabase.* values in .env.');
        }

        $url = $this->url . '/rest/v1/' . ltrim($resource, '/');
        if ($query !== []) {
            $url .= '?' . http_build_query($query);
        }

        $headers = [
            'apikey: ' . $this->serviceKey,
            'Authorization: Bearer ' . $this->serviceKey,
            'Content-Type: application/json',
        ];

        if ($method === 'POST' || $method === 'PATCH') {
            $headers[] = 'Prefer: return=representation';
        }

        $payload = $body === null ? null : (string) json_encode($body);
        $status  = 0;
        $raw     = $this->http($url, $method, $headers, $payload, $status);

        if ($status < 200 || $status >= 300) {
            throw new RuntimeException('Supabase ' . $method . ' ' . $resource . ' failed (HTTP ' . $status . '): ' . $this->excerpt($raw));
        }

        if (trim($raw) === '') {
            return [];
        }

        $decoded = json_decode($raw, true);

        if (! is_array($decoded)) {
            throw new RuntimeException('Supabase returned an unexpected response.');
        }

        return $decoded;
    }

    private function http(string $url, string $method, array $headers, ?string $payload, int &$status): string
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => true,
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_TIMEOUT        => $this->timeout,
            CURLOPT_CONNECTTIMEOUT => 8,
        ]);

        if ($payload !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        }

        $response = curl_exec($ch);
        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new RuntimeException('Network error talking to Supabase: ' . $error);
        }

        $status     = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $headerSize = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        curl_close($ch);

        // With CURLOPT_HEADER the headers are part of $response; strip them off.
        return (string) substr((string) $response, $headerSize);
    }

    private function excerpt(string $body): string
    {
        $body = trim(strip_tags($body));

        return mb_strlen($body) > 300 ? mb_substr($body, 0, 300) . '...' : $body;
    }
}
