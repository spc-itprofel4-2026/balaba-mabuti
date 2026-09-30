<?php

namespace App\Controllers;

use App\Models\ActivityLogModel;
use App\Models\ViberMemberModel;

/**
 * Viber webhook receiver.
 *
 * Registers member subscriptions/unsubscriptions automatically so the
 * broadcast audience stays in sync without manual entry.
 * Docs: https://developers.viber.com/docs/api/rest-bot/#webhooks
 */
class Webhook extends BaseController
{
    public function index()
    {
        $raw   = (string) $this->request->getBody();
        $sig   = $this->request->getHeaderLine('X-Viber-Content-Signature');
        $viber = service('viber');

        // With a bot token configured the signature is mandatory; without one
        // (demo mode) we still accept the payload but log that it was unverified.
        $verified = $viber->isConfigured() ? $viber->verifySignature($raw, $sig) : false;

        if ($viber->isConfigured() && ! $verified) {
            (new ActivityLogModel())->record('webhook_rejected', 'signature mismatch', 'viber', 'failed');

            return $this->response->setStatusCode(403)->setJSON(['status' => 3, 'status_message' => 'Invalid signature']);
        }

        $payload = json_decode($raw, true);
        if (! is_array($payload)) {
            return $this->response->setStatusCode(400)->setJSON(['status' => 2, 'status_message' => 'Invalid payload']);
        }

        $event  = (string) ($payload['event'] ?? '');
        $user   = (array) ($payload['user'] ?? []);
        $member = new ViberMemberModel();

        switch ($event) {
            case 'subscribed':
                $this->upsert($member, $user);
                (new ActivityLogModel())->record('webhook_subscribed', (string) ($user['id'] ?? ''), 'viber', 'ok');
                break;

            case 'unsubscribed':
                $viberId = (string) ($payload['user']['id'] ?? $user['id'] ?? '');
                if ($viberId !== '') {
                    $existing = $member->where('viber_id', $viberId)->first();
                    if ($existing) {
                        $member->update((int) $existing->id, ['status' => 'unsubscribed']);
                    }
                }
                (new ActivityLogModel())->record('webhook_unsubscribed', $viberId, 'viber', 'ok');
                break;

            case 'conversation_started':
                // A member messaged the bot: make sure they are on the list.
                if (($user['id'] ?? '') !== '') {
                    $this->upsert($member, $user);
                }
                break;

            case 'delivered':
            case 'failed':
                (new ActivityLogModel())->record('webhook_' . $event, (string) ($payload['message_token'] ?? ''), 'viber', $event === 'failed' ? 'failed' : 'ok');
                break;

            default:
                // Unknown/ignored event: acknowledge so Viber does not retry.
                break;
        }

        return $this->response->setJSON(['status' => 0, 'status_message' => 'ok']);
    }

    /**
     * @param array<string, mixed> $user
     */
    private function upsert(ViberMemberModel $member, array $user): void
    {
        $viberId = (string) ($user['id'] ?? '');
        if ($viberId === '') {
            return;
        }

        $data = [
            'viber_id'      => $viberId,
            'name'          => (string) ($user['name'] ?? 'Unknown'),
            'phone'         => isset($user['phone']) && $user['phone'] !== '' ? (string) $user['phone'] : null,
            'avatar'        => (string) ($user['avatar'] ?? ''),
            'status'        => 'subscribed',
            'subscribed_at' => date('Y-m-d H:i:s'),
        ];

        $existing = $member->where('viber_id', $viberId)->first();

        if ($existing) {
            $member->update((int) $existing->id, $data);
        } else {
            $member->insert($data);
        }
    }
}
