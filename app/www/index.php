<?php
$serverName  = getenv('SERVER_NAME')  ?: 'Unknown Server';
$serverColor = getenv('SERVER_COLOR') ?: '#22d3a5';
$serverEmoji = getenv('SERVER_EMOJI') ?: '⚡';
$redisHost   = getenv('REDIS_HOST')   ?: 'redis';

$hostname   = gethostname();
$phpVersion = PHP_VERSION;
$serverIp   = $_SERVER['SERVER_ADDR']          ?? 'N/A';
$clientIp   = $_SERVER['HTTP_X_REAL_IP']
           ?? $_SERVER['HTTP_X_FORWARDED_FOR']
           ?? $_SERVER['REMOTE_ADDR']           ?? 'N/A';
$proto      = $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? $_SERVER['REQUEST_SCHEME'] ?? 'http';
$proxyBy    = $_SERVER['HTTP_X_PROXY_BY']        ?? 'none';
$lbBy       = $_SERVER['HTTP_X_FORWARDED_BY']    ?? 'none';
$via        = ($proxyBy !== 'none') ? $proxyBy : (($lbBy !== 'none') ? $lbBy : 'direct');
$isHttps    = ($proto === 'https') ? '🔒 HTTPS' : '⚠ HTTP';
$timestamp  = date('Y-m-d H:i:s');

// Redis connection check
$redisStatus = 'N/A';
try {
    $r = new Redis();
    if ($r->connect($redisHost, 6379, 1)) {
        $r->ping();
        $redisStatus = '✅ Connected';
        $r->close();
    }
} catch (Exception $e) {
    $redisStatus = '❌ ' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($serverName) ?></title>
  <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;700&display=swap" rel="stylesheet">
  <style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body {
      font-family: 'IBM Plex Mono', monospace;
      background: #050810;
      color: #c8d8f0;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 20px;
    }
    body::before {
      content: '';
      position: fixed; inset: 0; pointer-events: none;
      background-image:
        linear-gradient(rgba(0,229,255,.02) 1px, transparent 1px),
        linear-gradient(90deg, rgba(0,229,255,.02) 1px, transparent 1px);
      background-size: 40px 40px;
    }
    .card {
      position: relative;
      background: #090d1a;
      border: 2px solid <?= htmlspecialchars($serverColor) ?>;
      border-radius: 18px;
      padding: 44px 48px;
      max-width: 540px; width: 100%;
      box-shadow: 0 0 80px <?= htmlspecialchars($serverColor) ?>1a;
      animation: pop .4s cubic-bezier(.16,1,.3,1);
      z-index: 1;
    }
    @keyframes pop { from { opacity:0; transform:scale(.9) translateY(20px); } to { opacity:1; transform:none; } }
    .card::before, .card::after {
      content: ''; position: absolute;
      width: 24px; height: 24px;
    }
    .card::before { top:-2px; left:-2px; border-top:3px solid <?= htmlspecialchars($serverColor) ?>; border-left:3px solid <?= htmlspecialchars($serverColor) ?>; border-radius:4px 0 0 0; }
    .card::after  { bottom:-2px; right:-2px; border-bottom:3px solid <?= htmlspecialchars($serverColor) ?>; border-right:3px solid <?= htmlspecialchars($serverColor) ?>; border-radius:0 0 4px 0; }
    .icon { font-size: 3rem; margin-bottom: 14px; }
    h1   { font-size: 1.7rem; font-weight: 700; color: <?= htmlspecialchars($serverColor) ?>; margin-bottom: 6px; }
    .badge {
      display: inline-flex; align-items: center; gap: 6px;
      background: <?= htmlspecialchars($serverColor) ?>18;
      border: 1px solid <?= htmlspecialchars($serverColor) ?>44;
      color: <?= htmlspecialchars($serverColor) ?>; padding: 4px 12px;
      border-radius: 999px; font-size: .72rem; margin-bottom: 8px;
    }
    .badge::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: <?= htmlspecialchars($serverColor) ?>; animation: blink 1.5s infinite; }
    .ssl-badge {
      display: inline-flex; align-items: center; gap: 6px;
      background: rgba(0,229,255,.06); border: 1px solid rgba(0,229,255,.25);
      color: #00e5ff; padding: 4px 12px; border-radius: 999px; font-size: .72rem;
      margin-bottom: 24px;
    }
    @keyframes blink { 0%,100%{opacity:1} 50%{opacity:.2} }
    .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 18px; }
    .cell { background: #0c1628; border: 1px solid #131c35; border-radius: 10px; padding: 12px; transition: border-color .2s; }
    .cell:hover { border-color: <?= htmlspecialchars($serverColor) ?>44; }
    .lbl { color: #2d4060; font-size: .65rem; letter-spacing: 1.5px; text-transform: uppercase; margin-bottom: 5px; }
    .val { font-size: .84rem; word-break: break-all; }
    .val.hi { color: <?= htmlspecialchars($serverColor) ?>; }
    .hr { border:none; border-top:1px solid #131c35; margin:16px 0; }
    .foot { color: #2d4060; font-size: .72rem; text-align: center; line-height: 1.7; }
    .hint { margin-top:14px; padding:10px; background:rgba(0,0,0,.2); border:1px dashed #131c35; border-radius:8px; color:#2d4060; font-size:.7rem; text-align:center; }
    .hint span { color: <?= htmlspecialchars($serverColor) ?>; }
  </style>
</head>
<body>
  <div class="card">
    <div class="icon"><?= htmlspecialchars($serverEmoji) ?></div>
    <h1><?= htmlspecialchars($serverName) ?></h1>
    <div class="badge">Nginx + PHP-FPM <?= PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION ?></div>
    <div class="ssl-badge"><?= $isHttps ?></div>
    <br>

    <div class="grid">
      <div class="cell">
        <div class="lbl">Hostname</div>
        <div class="val hi"><?= htmlspecialchars($hostname) ?></div>
      </div>
      <div class="cell">
        <div class="lbl">Server IP</div>
        <div class="val"><?= htmlspecialchars($serverIp) ?></div>
      </div>
      <div class="cell">
        <div class="lbl">Client IP</div>
        <div class="val"><?= htmlspecialchars($clientIp) ?></div>
      </div>
      <div class="cell">
        <div class="lbl">Protocol</div>
        <div class="val"><?= htmlspecialchars($proto) ?></div>
      </div>
      <div class="cell">
        <div class="lbl">Via (LB/RP)</div>
        <div class="val"><?= htmlspecialchars($via) ?></div>
      </div>
      <div class="cell">
        <div class="lbl">Redis</div>
        <div class="val" style="font-size:.75rem"><?= $redisStatus ?></div>
      </div>
    </div>

    <hr class="hr">
    <div class="foot">
      <?= $timestamp ?><br>
      <span style="color:<?= htmlspecialchars($serverColor) ?>"><?= htmlspecialchars($serverName) ?></span>
    </div>
    <div class="hint">
      🔒 <span>HTTPS</span> :443 (LB) &nbsp;|&nbsp; <span>HTTPS</span> :8443 (RP)<br>
      🔄 กด F5 — server จะสลับ (LB) หรือไม่สลับ (RP)
    </div>
  </div>
</body>
</html>
