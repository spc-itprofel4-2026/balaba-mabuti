<?php

namespace App\Libraries;

use RuntimeException;

/**
 * Viber Bot API client (https://developers.viber.com/docs/api/rest-bot/).
 *
 * When no bot token is configured the service runs in demo mode: every
 * message is accepted and reported as "simulated" so the workflow can be
 * demonstrated before a bot is created.
 */
class ViberService
{
    private const API = 'https://chatapi.viber.com/pa';

    private string $token;
    private string $sender;
    private int $timeout;

    public function __construct()
    {
        $this->token   = trim((string) env('viber.botToken', ''));
        $this->sender  = (string) env('viber.senderName', 'Org Announcements');
        $this->timeout = 20;
    }

    public function isConfigured(): bool
    {
        return $this->token !== '';
    }

    public function senderName(): string
    {
        return $this->sender;
    }

    /**
     * Verifies the token against Viber (or reports demo mode).
     *
     * @return array{ok: bool, demo: bool, name: string, message: string}
     */
    public function accountInfo(): array
    {
        if (! $this->isConfigured()) {
            return [
                'ok'      => true,
                'demo'    => true,
                'name'    => $this->sender,
                'message' => 'Demo mode - no Viber bot token configured.',
            ];
        }

        $result = $this->post('get_account_info', []);

        return [
            'ok'      => ($result['status'] ?? 1) === 0,
            'demo'    => false,
            'name'    => (string) ($result['name'] ?? $this->sender),
            'message' => (string) ($result['status_message'] ?? ''),
        ];
    }

    /**
     * @return array{ok: bool, demo: bool, status: string, message: string}
     */
    public function sendText(string $receiver, string $text): array
    {
        if (! $this->isConfigured()) {
            return [
                'ok'      => true,
                'demo'    => true,
                'status'  => 'sent',
                'message' => 'Simulated delivery (demo mode).',
            ];
        }

        $result = $this->post('send_message', [
            'receiver'       => $receiver,
            'min_api_version' => 1,
            'sender'         => ['name' => $this->sender, 'avatar' => ''],
            'type'           => 'text',
            'text'           => $text,
        ]);

        $ok = ($result['status'] ?? 1) === 0;

        return [
            'ok'      => $ok,
            'demo'    => false,
            'status'  => $ok ? 'sent' : 'failed',
            'message' => (string) ($result['status_message'] ?? ($ok ? 'Delivered.' : 'Rejected by Viber.')),
        ];
    }

    /**
     * Sends one message to every subscribed member.
     *
     * @param  list<object|array<string, mixed>> $members
     * @return array{sent: int, failed: int, demo: bool, results: list<array<string, string>>}
     */
    public function broadcast(string $text, array $members): array
    {
        $summary = ['sent' => 0, 'failed' => 0, 'demo' => ! $this->isConfigured(), 'results' => []];

        foreach ($members as $member) {
            $member   = (array) $member;
            $receiver = (string) ($member['viber_id'] ?? '');
            $name     = (string) ($member['name'] ?? 'Unknown');

            if ($receiver === '') {
                continue;
            }

            $result   = $this->sendText($receiver, $text);
            $summary['sent'] += $result['ok'] ? 1 : 0;
            $summary['failed'] += $result['ok'] ? 0 : 1;
            $summary['results'][] = [
                'viber_id' => $receiver,
                'name'     => $name,
                'status'   => $result['status'],
                'message'  => $result['message'],
            ];
        }

        return $summary;
    }

    /**
     * Points Viber at our webhook so member subscriptions arrive automatically.
     *
     * @return array{ok: bool, message: string}
     */
    public function registerWebhook(string $url, array $events = []): array
    {
        if (! $this->isConfigured()) {
            return ['ok' => false, 'message' => 'No Viber bot token configured.'];
        }

        $events = $events !== [] ? $events : ['delivered', 'failed', 'subscribed', 'unsubscribed', 'conversation_started'];
        $result = $this->post('set_webhook', ['url' => $url, 'event_types' => $events]);

        $ok = ($result['status'] ?? 1) === 0;

        return [
            'ok'      => $ok,
            'message' => (string) ($result['status_message'] ?? ''),
        ];
    }

    /**
     * Viber signs every webhook payload with HMAC-SHA256 of the raw body
     * keyed by the bot token.
     */
    public function verifySignature(string $rawBody, ?string $signature): bool
    {
        if (! $this->isConfigured() || $signature === null || $signature === '') {
            return false;
        }

        $expected = hash_hmac('sha256', $rawBody, $this->token);

        return hash_equals($expected, $signature);
    }

    /**
     * @return array<string, mixed>
     */
    private function post(string $method, array $payload): array
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => self::API . '/' . $method,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => (string) json_encode($payload),
            CURLOPT_HTTPHEADER     => [
                'X-Viber-Auth-Token: ' . $this->token,
                'Content-Type: application/json',
            ],
            CURLOPT_TIMEOUT        => $this->timeout,
            CURLOPT_CONNECTTIMEOUT => 8,
        ]);

        $response = curl_exec($ch);
        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new RuntimeException('Viber API unreachable: ' . $error);
        }

        curl_close($ch);

        $decoded = json_decode((string) $response, true);

        return is_array($decoded) ? $decoded : [];
    }
}
