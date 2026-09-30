<?php
/**
 * Shared application layout — Viber-inspired palette.
 *
 * Responsive rules:
 *  - >= 980px : persistent sidebar + wide content grid
 *  - < 980px  : off-canvas sidebar with hamburger toggle + backdrop
 *
 * @var string $content    rendered view body
 * @var string $pageTitle
 */
$active = trim(str_replace('\\', '/', (string) uri_string()), '/');
$active = preg_replace('#^index\.php/#', '', $active) ?? '';
$isAdmin = (string) session()->get('officerRole') === 'admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="theme-color" content="#7360F2">
<title><?= esc($pageTitle) ?> · Viber Announcements</title>
<style>
:root{
  /* --- Viber palette --- */
  --viber:#7360F2;
  --viber-dark:#5A4AD1;
  --viber-light:#9A8BFF;
  --viber-soft:#EEEBFF;
  --viber-tint:#F7F5FF;

  --bg:#F4F4FB;
  --card:#ffffff;
  --line:#ECECF6;
  --ink:#1B1B2F;
  --head:#12122A;
  --muted:#6F6F91;

  --ok:#16C784;
  --ok-soft:#E4FBF2;
  --bad:#FF5C5C;
  --bad-soft:#FFECEC;
  --warn:#FFB020;
  --warn-soft:#FFF5E1;
  --info:#3DA5FF;
  --info-soft:#E7F3FF;

  --radius:18px;
  --shadow:0 4px 18px rgba(27,27,47,.06);
  --shadow-lg:0 18px 44px rgba(27,27,47,.16);
}
*{box-sizing:border-box}
html,body{height:100%}
body{
  margin:0;background:var(--bg);color:var(--ink);
  font-family:"Segoe UI",system-ui,-apple-system,"Helvetica Neue",Arial,sans-serif;
  -webkit-font-smoothing:antialiased;
}
a{color:inherit;text-decoration:none}
button{font-family:inherit}
:focus-visible{outline:3px solid rgba(115,96,242,.45);outline-offset:2px;border-radius:10px}
body.lock{overflow:hidden}

/* ---------- mobile top bar ---------- */
.mobilebar{
  display:none;position:sticky;top:0;z-index:40;
  align-items:center;gap:12px;padding:10px 14px;
  background:rgba(255,255,255,.94);backdrop-filter:blur(10px);
  border-bottom:1px solid var(--line);
}
.burger{
  width:42px;height:42px;border:1px solid var(--line);background:#fff;border-radius:13px;
  display:flex;flex-direction:column;align-items:center;justify-content:center;gap:4px;
  cursor:pointer;flex:0 0 auto;
}
.burger span{display:block;width:18px;height:2px;background:var(--ink);border-radius:2px}
.mobilebar .brand{font-weight:700;font-size:15px;display:flex;align-items:center;gap:9px;margin:0;color:var(--head)}
.mobilebar .brand .dot{width:30px;height:30px;border-radius:10px;font-size:15px}

/* ---------- shell ---------- */
.shell{display:flex;min-height:100vh}
.backdrop{
  display:none;position:fixed;inset:0;background:rgba(18,18,42,.5);
  backdrop-filter:blur(2px);z-index:45;opacity:0;transition:opacity .2s ease;
}
.backdrop.show{display:block;opacity:1}

/* ---------- sidebar ---------- */
.sidebar{
  width:254px;flex:0 0 254px;background:var(--card);
  border-right:1px solid var(--line);
  padding:20px 14px 16px;display:flex;flex-direction:column;gap:4px;
  position:sticky;top:0;height:100vh;z-index:50;
}
.brand{display:flex;align-items:center;gap:11px;font-weight:800;font-size:16.5px;color:var(--head);margin-bottom:22px;padding:0 6px}
.brand .dot{
  width:38px;height:38px;border-radius:13px;flex:0 0 auto;
  background:linear-gradient(135deg,var(--viber),var(--viber-light));
  display:flex;align-items:center;justify-content:center;
  color:#fff;font-weight:800;font-size:17px;
  box-shadow:0 8px 18px rgba(115,96,242,.38);
}
.brand small{display:block;font-weight:500;font-size:11px;color:var(--muted);letter-spacing:.02em}
.nav-label{font-size:10.5px;letter-spacing:.14em;text-transform:uppercase;color:#A2A2C0;margin:14px 12px 7px;font-weight:700}
.sidebar a.nav{
  display:flex;align-items:center;gap:11px;
  padding:11px 13px;border-radius:13px;font-size:14.5px;color:#4A4A6A;font-weight:600;
  transition:background .15s ease,color .15s ease;
}
.sidebar a.nav:hover{background:var(--viber-tint);color:var(--viber-dark)}
.sidebar a.nav.active{background:var(--viber-soft);color:var(--viber-dark)}
.sidebar a.nav.active .nav-ico{background:var(--viber);color:#fff}
.nav-ico{
  width:26px;height:26px;border-radius:9px;background:#F0F0F8;color:#6A6A90;
  display:flex;align-items:center;justify-content:center;font-size:13px;flex:0 0 auto;
  transition:background .15s ease,color .15s ease;
}
.nav-pill{
  margin-left:auto;background:var(--viber);color:#fff;font-size:10px;font-weight:800;
  padding:2px 7px;border-radius:999px;letter-spacing:.06em;text-transform:uppercase;
}
.side-user{
  margin-top:auto;background:var(--viber-tint);border:1px solid var(--viber-soft);
  border-radius:16px;padding:12px;display:flex;align-items:center;gap:10px;
}
.side-user .avatar{
  width:38px;height:38px;border-radius:50%;flex:0 0 auto;
  background:linear-gradient(135deg,var(--viber),var(--viber-light));
  color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:14px;
}
.side-user .who{min-width:0;line-height:1.25}
.side-user .who b{display:block;font-size:13.5px;color:var(--head);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.side-user .who span{font-size:11.5px;color:var(--muted)}
.side-foot{font-size:11.5px;line-height:1.6;color:#9A9AB8;padding:12px 6px 0;border-top:1px solid var(--line);margin-top:12px}
.side-actions{display:flex;gap:8px;margin-top:10px;padding:0 2px}
.side-actions .btn{flex:1}

/* ---------- main ---------- */
.main{flex:1;min-width:0;display:flex;flex-direction:column}
.topbar{
  position:sticky;top:0;z-index:30;
  background:rgba(255,255,255,.92);backdrop-filter:blur(12px);
  border-bottom:1px solid var(--line);
  padding:14px 28px;display:flex;align-items:center;justify-content:space-between;gap:16px;
}
.topbar h1{margin:0;font-size:19px;letter-spacing:-.015em;color:var(--head)}
.topbar .crumb{font-size:12.5px;color:var(--muted);margin-top:2px}
.user{display:flex;align-items:center;gap:12px;font-size:14px;min-width:0}
.user .who{text-align:right;line-height:1.25;min-width:0}
.user .who b{display:block;font-weight:700;color:var(--head);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:200px}
.role-tag{
  display:inline-block;font-size:10.5px;font-weight:800;letter-spacing:.06em;text-transform:uppercase;
  padding:2px 8px;border-radius:999px;background:var(--viber-soft);color:var(--viber-dark);
}
.role-tag.admin{background:linear-gradient(135deg,var(--viber),var(--viber-light));color:#fff}
.avatar{
  width:38px;height:38px;border-radius:50%;flex:0 0 auto;
  background:linear-gradient(135deg,var(--viber),var(--viber-light));
  color:#fff;display:flex;align-items:center;justify-content:center;
  font-weight:700;font-size:14px;box-shadow:0 6px 14px rgba(115,96,242,.32);
}
.content{padding:26px 28px 46px;max-width:1200px;width:100%;margin:0 auto;flex:1}

/* ---------- surfaces ---------- */
.card{
  background:var(--card);border:1px solid var(--line);border-radius:var(--radius);
  padding:20px 22px;margin-bottom:20px;box-shadow:var(--shadow);
}
.card h2{margin:0 0 14px;font-size:15.5px;letter-spacing:-.01em;color:var(--head);display:flex;align-items:center;gap:9px}
.card h2::before{content:"";width:5px;height:17px;border-radius:4px;background:linear-gradient(180deg,var(--viber),var(--viber-light))}
.grid{display:grid;gap:16px}
.g4{grid-template-columns:repeat(auto-fit,minmax(180px,1fr))}
.g2{grid-template-columns:repeat(auto-fit,minmax(330px,1fr))}

.stat{
  background:var(--card);border:1px solid var(--line);border-radius:var(--radius);
  padding:16px 18px;box-shadow:var(--shadow);position:relative;overflow:hidden;
}
.stat::after{
  content:"";position:absolute;right:-30px;top:-30px;width:96px;height:96px;border-radius:50%;
  background:radial-gradient(circle,rgba(115,96,242,.16),transparent 70%);
}
.stat .label{font-size:11.5px;color:var(--muted);text-transform:uppercase;letter-spacing:.08em;font-weight:700}
.stat .value{font-size:32px;font-weight:800;margin-top:6px;letter-spacing:-.025em;color:var(--head);position:relative;z-index:1}
.stat .sub{font-size:12.5px;color:var(--muted);margin-top:3px;position:relative;z-index:1}

/* ---------- buttons ---------- */
.btn{
  display:inline-flex;align-items:center;justify-content:center;gap:8px;
  background:linear-gradient(135deg,var(--viber),var(--viber-light));
  color:#fff;border:0;border-radius:13px;padding:11px 17px;
  font-size:14.5px;font-weight:700;cursor:pointer;min-height:42px;
  box-shadow:0 8px 18px rgba(115,96,242,.3);
  transition:transform .14s ease,box-shadow .14s ease,filter .14s ease;
}
.btn:hover{transform:translateY(-1px);box-shadow:0 12px 24px rgba(115,96,242,.38);filter:brightness(1.05)}
.btn:active{transform:translateY(0)}
.btn.ghost{background:#fff;color:#4A4A6A;box-shadow:none;border:1px solid var(--line);font-weight:600}
.btn.ghost:hover{background:var(--viber-tint);border-color:var(--viber-soft);color:var(--viber-dark);box-shadow:none}
.btn.soft{background:var(--viber-soft);color:var(--viber-dark);box-shadow:none}
.btn.soft:hover{background:#E2DDFF;box-shadow:none}
.btn.danger{background:var(--bad-soft);color:#D63030;box-shadow:none;border:1px solid #FFD5D5}
.btn.danger:hover{background:#FFDCDC}
.btn.sm{padding:7px 12px;min-height:34px;font-size:13px;border-radius:11px}
.actions{display:flex;gap:10px;align-items:center;flex-wrap:wrap}

/* ---------- alerts / text ---------- */
.alert{border-radius:14px;padding:13px 16px;margin-bottom:18px;font-size:14.5px;border:1px solid;display:flex;gap:10px;align-items:flex-start;font-weight:500}
.alert.ok{background:var(--ok-soft);border-color:#BDF3E0;color:#0B7A52}
.alert.err{background:var(--bad-soft);border-color:#FFCFCF;color:#B02525}
.alert.info{background:var(--info-soft);border-color:#C9E6FF;color:#155E9E}
.muted{color:var(--muted);font-size:13.5px}
.hint{font-size:12.5px;color:var(--muted);margin-top:6px;line-height:1.55}
.empty{text-align:center;padding:34px 14px;color:var(--muted);line-height:1.7}
code.k{background:var(--viber-soft);color:var(--viber-dark);padding:2px 8px;border-radius:8px;font-size:12.5px;font-weight:600}

/* ---------- tables ---------- */
.table-wrap{overflow-x:auto;-webkit-overflow-scrolling:touch;margin:0 -6px;padding:0 6px;max-width:100%}
table{width:100%;border-collapse:collapse;font-size:14.5px}
.table-wrap table{min-width:560px}
th{
  text-align:left;font-size:11.5px;text-transform:uppercase;letter-spacing:.07em;color:var(--muted);
  padding:11px 12px;border-bottom:2px solid var(--line);white-space:nowrap;background:#FBFBFF;
}
td{padding:13px 12px;border-bottom:1px solid #F4F4FA;vertical-align:middle;overflow-wrap:anywhere}
tbody tr{transition:background .12s ease}
tbody tr:hover td{background:var(--viber-tint)}
tbody tr:last-child td{border-bottom:0}

/* ---------- badges ---------- */
.badge{display:inline-block;padding:4px 11px;border-radius:999px;font-size:12px;font-weight:700;letter-spacing:.02em;white-space:nowrap}
.badge.sent,.badge.subscribed,.badge.active{background:var(--ok-soft);color:#0B7A52}
.badge.draft{background:var(--viber-soft);color:var(--viber-dark)}
.badge.failed,.badge.urgent,.badge.high,.badge.disabled{background:var(--bad-soft);color:#D63030}
.badge.unsubscribed{background:#F1F1F7;color:#7A7A9C}
.badge.event{background:var(--warn-soft);color:#9A6400}
.badge.reminder{background:var(--info-soft);color:#155E9E}
.badge.general{background:#F1F1F7;color:#5A5A7C}
.badge.admin{background:linear-gradient(135deg,var(--viber),var(--viber-light));color:#fff}
.badge.officer{background:var(--viber-soft);color:var(--viber-dark)}

/* ---------- forms ---------- */
label{display:block;font-size:13.5px;font-weight:700;margin-bottom:7px;color:#3A3A5C}
input[type=text],input[type=password],input[type=email],textarea,select{
  width:100%;padding:12px 14px;border:1.5px solid #E4E4F0;border-radius:13px;
  font-size:15px;font-family:inherit;background:#fff;color:var(--ink);
  transition:border-color .14s ease,box-shadow .14s ease;
}
input:focus,textarea:focus,select:focus{
  outline:none;border-color:var(--viber);box-shadow:0 0 0 4px rgba(115,96,242,.15);
}
textarea{min-height:160px;resize:vertical;line-height:1.6}
.field{margin-bottom:18px}
select{
  appearance:none;padding-right:40px;
  background-image:url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%236f6f91' stroke-width='2.5' stroke-linecap='round'><path d='M6 9l6 6 6-6'/></svg>");
  background-repeat:no-repeat;background-position:right 14px center;
}

/* ---------- activity log ---------- */
.log{padding:0;margin:0;list-style:none}
.log li{font-size:13.5px;padding:11px 0;border-bottom:1px dashed var(--line);display:flex;gap:9px;align-items:flex-start;flex-wrap:wrap}
.log li:last-child{border-bottom:0}
.pill-ok{color:#0B7A52;font-weight:700}
.pill-bad{color:#D63030;font-weight:700}
.row-actions{display:flex;gap:7px;flex-wrap:wrap;align-items:center}

/* =========================================================
   RESPONSIVE
   ========================================================= */
@media (max-width: 980px){
  .mobilebar{display:flex}
  .sidebar{
    position:fixed;left:0;top:0;bottom:0;height:100dvh;
    transform:translateX(-104%);transition:transform .24s cubic-bezier(.4,0,.2,1);
    box-shadow:none;overflow-y:auto;
  }
  .sidebar.open{transform:translateX(0);box-shadow:var(--shadow-lg)}
  .topbar{padding:12px 16px;top:61px}
  .content{padding:20px 16px 40px}
  .card{padding:18px 16px}
  .g2{grid-template-columns:1fr}
}
@media (max-width: 640px){
  .topbar{flex-wrap:wrap;gap:10px}
  .topbar h1{font-size:17px}
  .topbar .crumb{display:none}
  .topbar-logout{display:none}      /* sign-out lives in the mobile bar + menu */
  .mobilebar .brand{font-size:14px;min-width:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
  .user .who b{max-width:110px;font-size:13px}
  .stat .value{font-size:27px}
  .g4{grid-template-columns:repeat(auto-fit,minmax(140px,1fr))}
  .actions{width:100%}
  .actions .btn{flex:1 1 auto}
  .content{padding:16px 13px 36px}

  /* Key/value tables inside a card stack label-above-value (no squeezed columns) */
  .card > table,
  .card > table tbody,
  .card > table tr,
  .card > table td{display:block;width:100%}
  .card > table tr{padding:11px 0;border-bottom:1px dashed var(--line)}
  .card > table tr:last-child{border-bottom:0;padding-bottom:2px}
  .card > table td{border:0;padding:0}
  .card > table td:first-child{font-weight:700;color:var(--head);font-size:13px;margin-bottom:3px}

  /* Data tables become stacked cards — no horizontal scrolling, nothing hidden */
  .table-wrap{overflow-x:visible;margin:0;padding:0}
  .table-wrap table,
  .table-wrap tbody{display:block;width:100%;min-width:0}
  .table-wrap thead{display:none}
  .table-wrap tbody tr{
    display:block;background:#fff;border:1px solid var(--line);border-radius:15px;
    padding:12px 14px;margin-bottom:12px;box-shadow:0 3px 10px rgba(27,27,47,.05);
  }
  .table-wrap tbody tr:last-child{margin-bottom:0}
  .table-wrap td{
    display:flex;align-items:center;justify-content:space-between;gap:12px;
    border:0;padding:7px 0;text-align:right;
  }
  .table-wrap td::before{
    content:attr(data-label);flex:0 0 auto;
    font-size:11px;letter-spacing:.07em;text-transform:uppercase;
    color:var(--muted);font-weight:700;text-align:left;
  }
  .table-wrap td:empty{display:none}
  .table-wrap td:first-child{
    display:block;text-align:left;font-size:15.5px;font-weight:700;color:var(--head);
    border-bottom:1px dashed var(--line);padding:0 0 9px;margin-bottom:3px;
  }
  .table-wrap td:first-child::before{display:none}
  .table-wrap td.is-actions{display:flex;flex-wrap:wrap;gap:8px;width:100%;padding-top:11px;justify-content:stretch}
  .table-wrap td.is-actions::before{display:none}
  .table-wrap td.is-actions > *{flex:1 1 90px}
  .table-wrap td.is-actions .row-actions{width:100%;gap:8px;flex-direction:row;justify-content:stretch}
  .table-wrap td.is-actions .btn,
  .table-wrap td.is-actions form .btn{width:100%;flex:1 1 auto;min-height:38px}
  .table-wrap td.is-actions form{display:flex}
  .table-wrap .hint{text-align:left}
}
@media (max-width: 400px){
  .mobilebar .brand{font-size:13px}
  .mobilebar .brand .dot{width:26px;height:26px;border-radius:9px}
  .topbar h1{font-size:16px}
}
</style>
</head>
<body>

<header class="mobilebar">
  <button class="burger" type="button" data-menu aria-label="Open menu" aria-controls="sidebar">
    <span></span><span></span><span></span>
  </button>
  <div class="brand"><span class="dot">V</span> Viber Broadcast</div>
  <?= form_open('/logout', ['class' => 'mobilebar-logout', 'style' => 'margin-left:auto']) ?>
    <button class="btn ghost sm" type="submit">Sign out</button>
  <?= form_close() ?>
</header>

<div class="shell">
  <div class="backdrop" id="backdrop" data-menu></div>

  <aside class="sidebar" id="sidebar">
    <div class="brand">
      <span class="dot">V</span>
      <span>Viber Broadcast<small>Announcement System</small></span>
    </div>

    <div class="nav-label">Workspace</div>
    <a class="nav <?= str_starts_with($active, 'dashboard') ? 'active' : '' ?>" href="<?= site_url('dashboard') ?>">
      <span class="nav-ico">▦</span> Dashboard
    </a>
    <a class="nav <?= str_starts_with($active, 'announcements') ? 'active' : '' ?>" href="<?= site_url('announcements') ?>">
      <span class="nav-ico">✉</span> Announcements
    </a>
    <a class="nav <?= str_starts_with($active, 'members') ? 'active' : '' ?>" href="<?= site_url('members') ?>">
      <span class="nav-ico">☰</span> Members
    </a>

    <?php if ($isAdmin): ?>
      <div class="nav-label">Administration</div>
      <a class="nav <?= str_starts_with($active, 'profiles') ? 'active' : '' ?>" href="<?= site_url('profiles') ?>">
        <span class="nav-ico">☺</span> Officers
        <span class="nav-pill">admin</span>
      </a>
    <?php endif; ?>

    <div class="side-user">
      <div class="avatar"><?= esc(strtoupper(mb_substr(session()->get('officerName') ?? 'O', 0, 1))) ?></div>
      <div class="who">
        <b><?= esc(session()->get('officerName') ?? '') ?></b>
        <span><?= esc(session()->get('officerRole') ?? '') ?></span>
      </div>
    </div>

    <div class="side-actions">
      <?= form_open('/logout', ['style' => 'display:flex;flex:1']) ?>
        <button class="btn ghost sm" type="submit" style="width:100%">Sign out</button>
      <?= form_close() ?>
    </div>

    <div class="side-foot">
      IT PROF EL 4 · Week 6<br>
      Balaba &amp; Mabuti · BS-IT 80107
    </div>
  </aside>

  <div class="main">
    <div class="topbar">
      <div>
        <h1><?= esc($pageTitle) ?></h1>
        <div class="crumb"><?= esc(parse_url(site_url(), PHP_URL_HOST)) ?> · Viber Community broadcast channel</div>
      </div>
      <div class="user">
        <div class="who">
          <b><?= esc(session()->get('officerName') ?? '') ?></b>
          <span class="role-tag <?= $isAdmin ? 'admin' : '' ?>"><?= esc(session()->get('officerRole') ?? '') ?></span>
        </div>
        <div class="avatar"><?= esc(strtoupper(mb_substr(session()->get('officerName') ?? 'O', 0, 1))) ?></div>
        <?= form_open('/logout', ['class' => 'topbar-logout']) ?>
          <button class="btn ghost sm" type="submit">Sign out</button>
        <?= form_close() ?>
      </div>
    </div>

    <div class="content">
      <?php if ($success = session()->getFlashdata('success')): ?>
        <div class="alert ok"><span>✔</span><span><?= esc($success) ?></span></div>
      <?php endif; ?>
      <?php if ($error = session()->getFlashdata('error')): ?>
        <div class="alert err"><span>✖</span><span><?= esc($error) ?></span></div>
      <?php endif; ?>

      <?= $content ?>
    </div>
  </div>
</div>

<script>
(function () {
  var sidebar = document.getElementById('sidebar');
  var backdrop = document.getElementById('backdrop');

  function setOpen(open) {
    sidebar.classList.toggle('open', open);
    backdrop.classList.toggle('show', open);
    document.body.classList.toggle('lock', open);
  }

  document.querySelectorAll('[data-menu]').forEach(function (el) {
    el.addEventListener('click', function (e) {
      e.preventDefault();
      setOpen(!sidebar.classList.contains('open'));
    });
  });

  sidebar.querySelectorAll('a').forEach(function (a) {
    a.addEventListener('click', function () { setOpen(false); });
  });

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') { setOpen(false); }
  });

  window.addEventListener('resize', function () {
    if (window.innerWidth > 980) { setOpen(false); }
  });
})();
</script>
</body>
</html>
