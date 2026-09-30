<?php
/**
 * @var list<array<string,mixed>> $profiles
 * @var bool  $cloudUp
 * @var int   $total
 * @var bool  $isAdmin
 */
$isAdmin = $isAdmin ?? (session()->get('officerRole') === 'admin');
?>
<div class="actions" style="margin-bottom:18px">
  <?php if ($isAdmin): ?>
    <a class="btn" href="<?= site_url('register') ?>">+ New officer account</a>
  <?php endif; ?>
  <span class="badge <?= $cloudUp ? 'sent' : 'failed' ?>">
    Supabase user_profiles · <?= $cloudUp ? 'connected' : 'offline (local mirror)' ?>
  </span>
  <span class="muted"><?= (int) $total ?> account(s)</span>
</div>

<?php if (! $isAdmin): ?>
  <div class="alert info">
    <span>i</span>
    <span>You are signed in as an <b>officer</b>: this list is read-only.
    Account creation, role changes and deletions are restricted to <b>admin</b> accounts.</span>
  </div>
<?php endif; ?>

<div class="card">
  <h2>Accounts &amp; roles</h2>
  <?php if ($profiles === []): ?>
    <div class="empty">No accounts yet.<br><br>
      <?php if ($isAdmin): ?><a class="btn" href="<?= site_url('register') ?>">Register the first officer</a><?php endif; ?>
    </div>
  <?php else: ?>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Name</th><th>Username</th><th>Mobile / contact</th>
            <th>Role</th><th>Status</th><th class="hide-xs">Last sign-in</th>
            <?php if ($isAdmin): ?><th>Actions</th><?php endif; ?>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($profiles as $p): ?>
            <?php
              $id     = (int) ($p['id'] ?? 0);
              $you    = $id === (int) session()->get('officerId');
              $active = ! empty($p['active']);
              $role   = (string) ($p['role'] ?? 'officer');
            ?>
            <tr>
              <td>
                <strong><?= esc($p['name'] ?? '') ?></strong>
                <?php if ($you): ?><span class="badge draft">you</span><?php endif; ?>
              </td>
              <td data-label="Username"><code class="k"><?= esc($p['username'] ?? '') ?></code></td>
              <td data-label="Mobile / contact">
                <?php if (! empty($p['phone'])): ?>
                  <code class="k"><?= esc($p['phone']) ?></code>
                  <span class="hint" style="margin:0">sign-in number</span>
                <?php else: ?>
                  <span class="muted">no number</span>
                <?php endif; ?>
                <div class="hint" style="margin:0"><?= esc($p['email'] ?? '') ?></div>
              </td>
              <td data-label="Role"><span class="badge <?= esc($role) ?>"><?= esc($role) ?></span></td>
              <td data-label="Status">
                <span class="badge <?= $active ? 'subscribed' : 'failed' ?>">
                  <?= $active ? 'active' : 'disabled' ?>
                </span>
              </td>
              <td class="hide-xs muted" data-label="Last sign-in"><?= esc($p['last_login'] ?? 'never') ?></td>
              <?php if ($isAdmin): ?>
                <td class="is-actions">
                  <div class="row-actions">
                    <a class="btn soft sm" href="<?= site_url('profiles/' . $id) ?>">Edit</a>
                    <form method="post" action="<?= site_url('profiles/' . $id . '/toggle') ?>">
                      <button class="btn ghost sm" type="submit"><?= $active ? 'Disable' : 'Enable' ?></button>
                    </form>
                    <form method="post" action="<?= site_url('profiles/' . $id . '/delete') ?>"
                          onsubmit="return confirm('Delete <?= esc((string) ($p['username'] ?? '')) ?>? This cannot be undone.');">
                      <button class="btn danger sm" type="submit">Delete</button>
                    </form>
                  </div>
                </td>
              <?php endif; ?>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<div class="card">
  <h2>How accounts are stored</h2>
  <table>
    <tr><td>System of record</td><td>Supabase table <code class="k">public.user_profiles</code> (RLS blocks public reads)</td></tr>
    <tr><td>Sign-in ID</td><td>Mobile number in E.164 form, e.g. <code class="k">+639171234567</code> (set at registration, like Viber)</td></tr>
    <tr><td>Offline mirror</td><td>SQLite <code class="k">officers</code> table (auto-synced on sign-in)</td></tr>
    <tr><td>Password storage</td><td>bcrypt <code class="k">password_hash()</code> — never plaintext</td></tr>
    <tr><td>Roles</td><td><code class="k">admin</code> (full control) and <code class="k">officer</code> (broadcast only)</td></tr>
    <tr><td>Enforcement</td><td><code class="k">App\Filters\AdminFilter</code> on every write route</td></tr>
  </table>
</div>
