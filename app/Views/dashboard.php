<?php
/**
 * @var array<string,int> $stats
 * @var array<string,int> $members
 * @var list<object>      $logs
 * @var bool              $cloudUp
 * @var string|null       $cloudError
 * @var array{configured:bool, info:array|null} $viber
 */
?>
<?php if (! $cloudUp): ?>
  <div class="alert err">
    <strong>Supabase unreachable.</strong>
    <?= $cloudError ? esc($cloudError) : 'The cloud system of record is not configured.' ?>
  </div>
<?php endif; ?>

<div class="grid g4">
  <div class="stat">
    <div class="label">Announcements</div>
    <div class="value"><?= (int) $stats['total'] ?></div>
    <div class="sub">stored in Supabase</div>
  </div>
  <div class="stat">
    <div class="label">Sent</div>
    <div class="value" style="color:#15803d"><?= (int) $stats['sent'] ?></div>
    <div class="sub">broadcast to members</div>
  </div>
  <div class="stat">
    <div class="label">Drafts</div>
    <div class="value" style="color:#4338ca"><?= (int) $stats['draft'] ?></div>
    <div class="sub">not yet delivered</div>
  </div>
  <div class="stat">
    <div class="label">Viber audience</div>
    <div class="value" style="color:#733eea"><?= (int) $members['subscribed'] ?></div>
    <div class="sub">subscribed of <?= (int) $members['total'] ?> total</div>
  </div>
</div>

<div class="grid g2" style="margin-top:20px">
  <div class="card">
    <h2>Integration status</h2>
    <table>
      <tr>
        <td>Supabase (announcement store)</td>
        <td><?= $cloudUp ? '<span class="badge sent">connected</span>' : '<span class="badge failed">offline</span>' ?></td>
      </tr>
      <tr>
        <td>Viber Bot API</td>
        <td>
          <?php if ($viber['configured']): ?>
            <?php if (! empty($viber['info']['ok'])): ?>
              <span class="badge sent">connected</span>
              <div class="hint">Bot: <?= esc($viber['info']['name'] ?? '') ?></div>
            <?php else: ?>
              <span class="badge failed">token rejected</span>
              <div class="hint"><?= esc($viber['info']['message'] ?? '') ?></div>
            <?php endif; ?>
          <?php else: ?>
            <span class="badge draft">demo mode</span>
            <div class="hint">Set <code class="k">viber.botToken</code> in .env to deliver real messages.</div>
          <?php endif; ?>
        </td>
      </tr>
      <tr>
        <td>Deliveries recorded</td>
        <td><span class="pill-ok"><?= (int) $stats['deliveries_ok'] ?></span> ok · <span class="pill-bad"><?= (int) $stats['deliveries_failed'] ?></span> failed</td>
      </tr>
      <tr>
        <td>Local database</td>
        <td><span class="badge sent">SQLite · writable/announcements.db</span></td>
      </tr>
    </table>
    <div class="actions" style="margin-top:16px">
      <a class="btn" href="<?= site_url('announcements/create') ?>">+ New announcement</a>
      <a class="btn ghost" href="<?= site_url('members') ?>">Manage members</a>
    </div>
  </div>

  <div class="card">
    <h2>Recent activity</h2>
    <?php if ($logs === []): ?>
      <div class="empty">No activity yet.</div>
    <?php else: ?>
      <ul class="log">
        <?php foreach ($logs as $log): ?>
          <li>
            <strong><?= esc($log->action) ?></strong>
            <span class="badge <?= $log->status === 'ok' ? 'sent' : ($log->status === 'failed' ? 'failed' : 'draft') ?>"><?= esc($log->status) ?></span>
            <span class="muted"><?= esc($log->channel) ?> · <?= esc($log->created_at ?? '') ?></span>
            <?php if (! empty($log->detail)): ?>
              <div class="hint"><?= esc($log->detail) ?></div>
            <?php endif; ?>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </div>
</div>
