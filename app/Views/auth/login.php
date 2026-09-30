<?php
/**
 * Officer sign-in (standalone page, no sidebar).
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Sign in · Viber Announcements</title>
<style>
*{box-sizing:border-box}
body{margin:0;min-height:100vh;display:flex;align-items:center;justify-content:center;
     font-family:"Segoe UI",system-ui,sans-serif;
     background:radial-gradient(1000px 500px at 20% -10%,#3b1d8f 0%,transparent 60%),
                radial-gradient(800px 400px at 100% 100%,#0ea5e9 0%,transparent 55%),#0f172a}
.card{background:#fff;border-radius:18px;padding:34px;width:100%;max-width:400px;
      box-shadow:0 30px 70px rgba(0,0,0,.45)}
.brand{display:flex;align-items:center;gap:10px;font-weight:700;font-size:17px;margin-bottom:6px}
.dot{width:34px;height:34px;border-radius:10px;background:linear-gradient(135deg,#733eea,#00c6ff);
     display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700}
h1{font-size:20px;margin:16px 0 4px}
p.sub{margin:0 0 22px;color:#6b7a99;font-size:14px}
label{display:block;font-size:13px;font-weight:600;margin-bottom:6px;color:#3d4a63}
input{width:100%;padding:11px 13px;border:1px solid #d6dded;border-radius:10px;font-size:14px;margin-bottom:16px}
input:focus{outline:2px solid #c7b8ff;border-color:#733eea}
button{width:100%;padding:12px;border:0;border-radius:10px;background:#733eea;color:#fff;
       font-size:15px;font-weight:600;cursor:pointer}
button:hover{background:#7b5cff}
.alert{border-radius:10px;padding:11px 14px;font-size:14px;margin-bottom:16px}
.alert.err{background:#fef2f2;border:1px solid #fecaca;color:#991b1b}
.alert.ok{background:#ecfdf5;border:1px solid #bbf7d0;color:#166534}
.hint{margin-top:18px;font-size:12px;color:#8496b5;text-align:center;line-height:1.6}
</style>
</head>
<body>
<div class="card">
  <div class="brand"><span class="dot">V</span> Viber Broadcast System</div>
  <h1>Officer sign-in</h1>
  <p class="sub">Announcements &amp; reminders for all members</p>

  <?php if ($error = session()->getFlashdata('error')): ?>
    <div class="alert err"><?= esc($error) ?></div>
  <?php endif; ?>
  <?php if ($success = session()->getFlashdata('success')): ?>
    <div class="alert ok"><?= esc($success) ?></div>
  <?php endif; ?>

  <?= form_open('/login') ?>
    <label for="username">Mobile number or username</label>
    <input type="text" id="username" name="username" value="<?= esc(old('username')) ?>"
           placeholder="09171234567 or hermie" autocomplete="username" required autofocus>

    <label for="password">Password</label>
    <input type="password" id="password" name="password" autocomplete="current-password" required>

    <button type="submit">Sign in</button>
  <?= form_close() ?>

  <div class="switch" style="margin-top:16px;text-align:center;font-size:13.5px;color:#6b7a99">
    No account yet? <a href="<?= site_url('register') ?>" style="color:#733eea;font-weight:600">Create account</a>
  </div>

  <div class="hint">
    Sign in with your <b>mobile number</b> (Viber-style) or username.<br>
    Example: <code>+639171234567</code> · Password: <code>password123</code>
  </div>
</div>
</body>
</html>
