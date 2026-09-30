<?php
/**
 * @var list<object> $rows
 * @var int $subscribed
 * @var int $total
 */
?>
<div class="grid g4" style="margin-bottom:20px">
  <div class="stat">
    <div class="label">Subscribed</div>
    <div class="value" style="color:#15803d"><?= (int) $subscribed ?></div>
    <div class="sub">will receive broadcasts</div>
  </div>
  <div class="stat">
    <div class="label">Total on list</div>
    <div class="value"><?= (int) $total ?></div>
    <div class="sub">including unsubscribed</div>
  </div>
</div>

<div class="grid g2">
  <div class="card">
    <h2>Member list</h2>
    <?php if ($rows === []): ?>
      <div class="empty">
        No members yet.<br>
        <span class="hint">Add them manually below, or they appear automatically<br>once they message the Viber bot.</span>
      </div>
    <?php else: ?>
      <div class="table-wrap"><table>
        <thead><tr><th>Name</th><th>Viber ID</th><th>Status</th><th></th></tr></thead>
        <tbody>
          <?php foreach ($rows as $row): ?>
            <tr>
              <td>
                <strong><?= esc($row->name) ?></strong>
                <?php if ($row->phone): ?><div class="hint"><?= esc($row->phone) ?></div><?php endif; ?>
              </td>
              <td class="muted" data-label="Viber ID"><?= esc($row->viber_id) ?></td>
              <td data-label="Status"><span class="badge <?= esc($row->status) ?>"><?= esc($row->status) ?></span></td>
              <td class="is-actions">
                <?= form_open('/members/' . (int) $row->id . '/toggle') ?>
                  <button class="btn ghost sm" type="submit"><?= $row->status === 'subscribed' ? 'Unsubscribe' : 'Subscribe' ?></button>
                <?= form_close() ?>
                <?= form_open('/members/' . (int) $row->id . '/delete') ?>
                  <button class="btn danger sm" type="submit" onclick="return confirm('Remove this member?')">Remove</button>
                <?= form_close() ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table></div>
    <?php endif; ?>
  </div>

  <div class="card">
    <h2>Add a member manually</h2>
    <p class="muted" style="margin-top:-6px">
      Normally members are added automatically by the webhook when they message the bot.
      Use this for members who cannot or will not start the bot conversation.
    </p>

    <?= form_open('/members') ?>
      <div class="field">
        <label for="name">Display name</label>
        <input type="text" id="name" name="name" maxlength="120" required placeholder="Juan Dela Cruz" value="<?= esc(old('name')) ?>">
      </div>
      <div class="field">
        <label for="viber_id">Viber ID</label>
        <input type="text" id="viber_id" name="viber_id" maxlength="64" required placeholder="0123456789012345678" value="<?= esc(old('viber_id')) ?>">
        <div class="hint">The numeric ID Viber assigns to the account (visible in webhook payloads).</div>
      </div>
      <div class="field">
        <label for="phone">Phone (optional)</label>
        <input type="text" id="phone" name="phone" maxlength="32" placeholder="+63…" value="<?= esc(old('phone')) ?>">
      </div>
      <button class="btn" type="submit">Add member</button>
    <?= form_close() ?>
  </div>
</div>
