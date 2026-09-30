<?php
/**
 * @var array<string,mixed> $profile
 * @var list<string>        $roles
 */
$isAdminRole = (string) ($profile['role'] ?? 'officer') === 'admin';
$active      = (bool) ($profile['active'] ?? true);
?>
<div class="actions" style="margin-bottom:18px">
  <a class="btn ghost" href="<?= site_url('profiles') ?>">← Back to officers</a>
  <span class="badge <?= $isAdminRole ? 'admin' : 'officer' ?>"><?= esc($profile['role'] ?? 'officer') ?></span>
  <span class="badge <?= $active ? 'active' : 'disabled' ?>"><?= $active ? 'active' : 'disabled' ?></span>
</div>

<form method="post" action="<?= site_url('profiles/' . (int) $profile['id'] . '/update') ?>" autocomplete="off">
  <div class="card">
    <h2>Account details</h2>

    <div class="grid g2">
      <div class="field">
        <label for="name">Full name</label>
        <input type="text" id="name" name="name" value="<?= esc((string) ($profile['name'] ?? '')) ?>" required maxlength="120">
      </div>
      <div class="field">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" value="<?= esc((string) ($profile['username'] ?? '')) ?>" required maxlength="60" pattern="[A-Za-z0-9_.\-]+">
        <div class="hint">Letters, numbers, dot, dash, underscore. Used to sign in.</div>
      </div>
      <div class="field">
        <label for="email">Email <span class="muted">(optional)</span></label>
        <input type="email" id="email" name="email" value="<?= esc((string) ($profile['email'] ?? '')) ?>" maxlength="160">
      </div>
      <div class="field">
        <label for="phone">Mobile number <span class="muted">(sign-in ID)</span></label>
        <input type="tel" id="phone" name="phone" value="<?= esc((string) ($profile['phone'] ?? '')) ?>"
               maxlength="32" inputmode="tel" placeholder="09171234567">
      </div>
    </div>
  </div>

  <div class="card">
    <h2>Role &amp; permissions</h2>
    <div class="field">
      <label for="role">Role</label>
      <select id="role" name="role">
        <?php foreach ($roles as $role): ?>
          <option value="<?= esc($role) ?>" <?= ($profile['role'] ?? 'officer') === $role ? 'selected' : '' ?>>
            <?= $role === 'admin' ? 'admin — manage accounts, roles and all content' : 'officer — announcements &amp; members only' ?>
          </option>
        <?php endforeach; ?>
      </select>
      <div class="hint">
        <b>admin</b> can create, edit, disable and delete officer accounts and open Administration.
        <b>officer</b> can broadcast announcements and manage Viber members, but cannot touch accounts.
      </div>
    </div>

    <div class="field" style="margin-bottom:6px">
      <label for="password">Reset password <span class="muted">(leave blank to keep current)</span></label>
      <input type="password" id="password" name="password" value="" minlength="6" placeholder="New password">
      <div class="hint">Stored as bcrypt <code class="k">password_hash()</code> — never plain text.</div>
    </div>
  </div>

  <div class="actions">
    <button class="btn" type="submit">Save changes</button>
    <a class="btn ghost" href="<?= site_url('profiles') ?>">Cancel</a>
  </div>
</form>
