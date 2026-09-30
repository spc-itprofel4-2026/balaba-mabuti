<?php
/**
 * @var array<string,mixed>|null $row
 * @var list<array<string,mixed>> $deliveries
 * @var string|null $error
 */
?>
<?php if ($error): ?>
  <div class="alert err"><?= esc($error) ?></div>
  <a class="btn ghost" href="<?= site_url('announcements') ?>">← Back to announcements</a>
<?php else: ?>
  <?php $id = (int) $row['id']; ?>
  <div class="actions" style="margin-bottom:18px">
    <a class="btn ghost" href="<?= site_url('announcements') ?>">← All announcements</a>
    <span style="flex:1"></span>
    <?= form_open('/announcements/' . $id . '/delete') ?>
      <button class="btn danger" type="submit"
              onclick="return confirm('Delete announcement #<?= $id ?>?')">Delete</button>
    <?= form_close() ?>
    <?= form_open('/announcements/' . $id . '/send') ?>
      <button class="btn" type="submit"
              onclick="return confirm('Broadcast this announcement to all subscribed Viber members?')">
        📢 Send to Viber now
      </button>
    <?= form_close() ?>
  </div>

  <div class="grid g2">
    <div class="card">
      <h2><?= esc($row['title']) ?></h2>
      <p style="white-space:pre-wrap;font-size:15px;line-height:1.65"><?= esc($row['body']) ?></p>

      <table style="margin-top:8px">
        <tr><td>Status</td><td><span class="badge <?= esc($row['status']) ?>"><?= esc($row['status']) ?></span></td></tr>
        <tr><td>Category</td><td><span class="badge <?= esc($row['category']) ?>"><?= esc($row['category']) ?></span> <?= $row['priority'] === 'high' ? '<span class="badge high">high priority</span>' : '' ?></td></tr>
        <tr><td>Audience</td><td><?= (int) ($row['recipient_count'] ?? 0) ?> member(s)</td></tr>
        <tr><td>Delivered</td><td><?= (int) ($row['delivered_count'] ?? 0) ?></td></tr>
        <tr><td>Sent by</td><td><?= esc($row['sent_by'] ?? '—') ?></td></tr>
        <tr><td>Sent at</td><td><?= esc($row['sent_at'] ?? '—') ?></td></tr>
        <tr><td>Created</td><td><?= esc($row['created_at'] ?? '') ?></td></tr>
      </table></div>
    </div>

    <div class="card">
      <h2>Delivery report</h2>
      <?php if ($deliveries === []): ?>
        <div class="empty">Not sent yet, or no delivery rows recorded.</div>
      <?php else: ?>
        <div class="table-wrap"><table>
          <thead><tr><th>Member</th><th>Viber ID</th><th>Status</th><th>Response</th></tr></thead>
          <tbody>
            <?php foreach ($deliveries as $d): ?>
              <tr>
                <td><?= esc($d['member_name'] ?? '') ?></td>
                <td class="muted" data-label="Viber ID"><?= esc($d['viber_id'] ?? '') ?></td>
                <td data-label="Status"><span class="badge <?= esc($d['status'] ?? '') === 'sent' ? 'sent' : 'failed' ?>"><?= esc($d['status'] ?? '') ?></span></td>
                <td class="hint" data-label="Response"><?= esc($d['response'] ?? '') ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table></div>
      <?php endif; ?>
    </div>
  </div>
<?php endif; ?>
