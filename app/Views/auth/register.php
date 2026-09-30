<?php
/**
 * Officer registration (standalone page, no sidebar).
 * Account is written to Supabase user_profiles and mirrored locally.
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="theme-color" content="#141b36">
<title>Create account · Viber Announcements</title>
<style>
*{box-sizing:border-box}
body{margin:0;min-height:100vh;min-height:100dvh;display:flex;align-items:center;justify-content:center;
     padding:22px;font-family:"Segoe UI",system-ui,-apple-system,sans-serif;
     background:radial-gradient(1000px 520px at 18% -10%,#4c1fd7 0%,transparent 60%),
                radial-gradient(820px 420px at 100% 100%,#0ea5e9 0%,transparent 55%),#0f172a}
.card{background:#fff;border-radius:20px;padding:34px;width:100%;max-width:460px;
      box-shadow:0 30px 70px rgba(0,0,0,.45)}
.brand{display:flex;align-items:center;gap:11px;font-weight:700;font-size:17px}
.dot{width:36px;height:36px;border-radius:11px;background:linear-gradient(135deg,#6d3df5,#0ea5e9);
     display:flex;align-items:center;justify-content:center;color:#fff;font-weight:800}
h1{font-size:21px;margin:18px 0 4px;letter-spacing:-.01em}
p.sub{margin:0 0 22px;color:#6b7a99;font-size:14px}
label{display:block;font-size:13.5px;font-weight:600;margin-bottom:6px;color:#33415c}
input{width:100%;padding:12px 14px;border:1px solid #d7deed;border-radius:12px;font-size:15px;
      margin-bottom:16px;font-family:inherit;transition:border-color .14s,box-shadow .14s}
input:focus{outline:none;border-color:#6d3df5;box-shadow:0 0 0 4px rgba(109,61,245,.14)}
.row{display:grid;grid-template-columns:1fr 1fr;gap:14px}
button{width:100%;padding:13px;border:0;border-radius:12px;background:linear-gradient(135deg,#6d3df5,#8b5cf6);
       color:#fff;font-size:15px;font-weight:600;cursor:pointer;box-shadow:0 10px 22px rgba(109,61,245,.32);
       transition:transform .14s,filter .14s}
button:hover{transform:translateY(-1px);filter:brightness(1.05)}
.alert{border-radius:12px;padding:12px 15px;font-size:14px;margin-bottom:16px}
.alert.err{background:#fef2f2;border:1px solid #fecaca;color:#7f1d1d}
.switch{margin-top:18px;text-align:center;font-size:13.5px;color:#6b7a99}
.switch a{color:#6d3df5;font-weight:600}
.hint{margin-top:14px;font-size:12px;color:#8496b5;text-align:center;line-height:1.6}
@media (max-width:480px){
  body{padding:14px}
  .card{padding:26px 20px;border-radius:16px}
  h1{font-size:19px}
  .row{grid-template-columns:1fr;gap:0}
}
</style>
</head>
<body>
<div class="card">
  <div class="brand"><span class="dot">V</span> Viber Broadcast System</div>
  <h1>Create officer account</h1>
  <p class="sub">Register with your mobile number — it is your sign-in ID, just like Viber.</p>

  <?php if ($error = session()->getFlashdata('error')): ?>
    <div class="alert err"><?= esc($error) ?></div>
  <?php endif; ?>

  <?php if (session()->has('errors')): ?>
    <div class="alert err">
      <ul style="margin:0;padding-left:18px">
        <?php foreach (session('errors') as $e): ?>
          <li><?= esc($e) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <?= form_open('/register') ?>
    <label for="name">Full name</label>
    <input type="text" id="name" name="name" maxlength="120" required autofocus
           placeholder="Juan Dela Cruz" value="<?= esc(old('name')) ?>">

    <label for="phone">Mobile number <span style="color:#6d3df5">(used to sign in)</span></label>
    <input type="tel" id="phone" name="phone" maxlength="40" required
           inputmode="tel" autocomplete="tel" placeholder="09171234567 or +12025550123"
           pattern="[+0-9 ()\-\.]{6,40}"
           value="<?= esc(old('phone')) ?>">
    <div class="hint" style="text-align:left;margin:-8px 0 16px">
      Saved as <code>+639171234567</code> — like Viber, this number is your sign-in ID.<br>
      Minimum 7 / maximum 15 digits. Other countries: add the country code (<code>+1</code>, <code>+44</code>, <code>+61</code>…).
    </div>

    <div class="row">
      <div>
        <label for="username">Username</label>
        <input type="text" id="username" name="username" minlength="3" maxlength="60" required
               placeholder="juan" value="<?= esc(old('username')) ?>">
      </div>
      <div>
        <label for="email">Email</label>
        <input type="email" id="email" name="email" maxlength="160" required
               placeholder="juan@org.local" value="<?= esc(old('email')) ?>">
      </div>
    </div>

    <label for="invite_code">Invite code</label>
    <input type="text" id="invite_code" name="invite_code" maxlength="64"
           placeholder="Provided by an admin" value="<?= esc(old('invite_code')) ?>">

    <div class="row">
      <div>
        <label for="password">Password</label>
        <input type="password" id="password" name="password" minlength="8" maxlength="72" required>
      </div>
      <div>
        <label for="password_confirm">Confirm password</label>
        <input type="password" id="password_confirm" name="password_confirm" minlength="8" maxlength="72" required>
      </div>
    </div>

    <button type="submit">Create account</button>
  <?= form_close() ?>

  <div class="switch">Already registered? <a href="<?= site_url('login') ?>">Sign in</a></div>
  <div class="hint">Passwords are bcrypt-hashed before they reach the database.<br>Minimum 8 characters.</div>
</div>
</body>
</html>
