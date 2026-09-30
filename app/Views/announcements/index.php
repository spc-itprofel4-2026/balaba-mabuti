<?php
/**
 * @var list<array<string,mixed>> $rows
 * @var string|null $error
 * @var bool $supabaseUp
 */
?>
<div class="actions" style="margin-bottom:18px">
  <a class="btn" href="<?= site_url('announcements/create') ?>">+ New announcement</a>
  <span class="muted">Stored in Supabase · read live from the cloud</span>
</div>

<?php if ($error): ?>
  <div class="alert err"><strong>Could not load announcements.</strong> <?= esc($error) ?></div>
<?php endif; ?>

<div class="card">
  <h2>All announcements (<?= count($rows) ?>)</h2>

  <?php if ($rows === [] && ! $error): ?>
    <div class="empty">
      No announcements yet.<br><br>
      <a class="btn" href="<?= site_url('announcements/create') ?>">Create the first one</a>
    </div>
  <?php else: ?>
    <div class="table-wrap"><table>
      <thead>
        <tr>
          <th>Title</th><th>Category</th><th>Status</th>
          <th class="hide-xs">Audience</th><th class="hide-xs">Delivered</th><th class="hide-xs">Created</th><th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $row): ?>
          <?php $id = (int) ($row['id'] ?? 0); ?>
          <tr>
            <td>
              <a href="<?= site_url('announcements/' . $id) ?>"><strong><?= esc($row['title'] ?? '') ?></strong></a>
              <div class="hint">#<?= $id ?> · <?= esc(mb_substr((string) ($row['body'] ?? ''), 0, 80)) ?><?= mb_strlen((string) ($row['body'] ?? '')) > 80 ? '…' : '' ?></div>
            </td>
            <td data-label="Category"><span class="badge <?= esc($row['category'] ?? 'general') ?>"><?= esc($row['category'] ?? 'general') ?></span></td>
            <td data-label="Status"><span class="badge <?= esc($row['status'] ?? 'draft') ?>"><?= esc($row['status'] ?? 'draft') ?></span></td>
            <td class="hide-xs" data-label="Audience"><?= (int) ($row['recipient_count'] ?? 0) ?></td>
            <td class="hide-xs" data-label="Delivered"><?= (int) ($row['delivered_count'] ?? 0) ?></td>
            <td class="muted hide-xs" data-label="Created"><?= esc(substr((string) ($row['created_at'] ?? ''), 0, 16)) ?></td>
            <td class="is-actions"><a class="btn ghost sm" href="<?= site_url('announcements/' . $id) ?>">Open</a></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table></div>
  <?php endif; ?>
</div>
