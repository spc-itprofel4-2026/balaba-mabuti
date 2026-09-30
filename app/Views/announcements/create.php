<?php
/** @var array<string,mixed> $errors */
?>
<div class="card" style="max-width:760px">
  <h2>New announcement</h2>
  <p class="muted" style="margin-top:-6px">Saved as a draft in Supabase. You can send it to Viber afterwards.</p>

  <?php if (session()->has('errors')): ?>
    <div class="alert err">
      <ul style="margin:0;padding-left:18px">
        <?php foreach (session('errors') as $e): ?>
          <li><?= esc($e) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <?= form_open('/announcements') ?>
    <div class="field">
      <label for="title">Title</label>
      <input type="text" id="title" name="title" maxlength="160" required
             placeholder="e.g. General Assembly moved to Room 204"
             value="<?= esc(old('title')) ?>">
    </div>

    <div class="field">
      <label for="body">Message</label>
      <textarea id="body" name="body" maxlength="4000" required
                placeholder="Write the announcement exactly as members should read it in Viber."><?= esc(old('body')) ?></textarea>
      <div class="hint">Keep it short and scannable — this is what lands on every member's phone.</div>
    </div>

    <div class="grid g2">
      <div class="field">
        <label for="category">Category</label>
        <select id="category" name="category">
          <?php foreach (['general', 'event', 'reminder', 'urgent'] as $cat): ?>
            <option value="<?= $cat ?>" <?= old('category') === $cat ? 'selected' : '' ?>><?= ucfirst($cat) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="field">
        <label for="priority">Priority</label>
        <select id="priority" name="priority">
          <option value="normal" <?= old('priority') === 'normal' ? 'selected' : '' ?>>Normal</option>
          <option value="high" <?= old('priority') === 'high' ? 'selected' : '' ?>>High</option>
        </select>
      </div>
    </div>

    <div class="actions">
      <button class="btn" type="submit">Save draft</button>
      <a class="btn ghost" href="<?= site_url('announcements') ?>">Cancel</a>
    </div>
  <?= form_close() ?>
</div>
