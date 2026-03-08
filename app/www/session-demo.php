<?php
// ═══════════════════════════════════════════════════════════
// Redis Shared Session Demo
// กด F5 ซ้ำๆ — visit_count ต้องเพิ่ม แม้ไปต่าง server
// ═══════════════════════════════════════════════════════════

$redisHost   = getenv('REDIS_HOST') ?: 'redis';
$serverName  = getenv('SERVER_NAME') ?: 'Unknown';
$serverColor = getenv('SERVER_COLOR') ?: '#22d3a5';
$serverEmoji = getenv('SERVER_EMOJI') ?: '⚡';

// ── ใช้ Redis เก็บ session ─────────────────────────────────
ini_set('session.save_handler', 'redis');
ini_set('session.save_path',    "tcp://{$redisHost}:6379");
ini_set('session.gc_maxlifetime', 3600);

session_start();

// ── นับ visits ─────────────────────────────────────────────
if (!isset($_SESSION['visit_count'])) {
    $_SESSION['visit_count']  = 0;
    $_SESSION['first_server'] = $serverName;
    $_SESSION['started_at']   = date('H:i:s');
}

$_SESSION['visit_count']++;
$_SESSION['last_server'] = $serverName;
$_SESSION['last_visit']  = date('H:i:s');
$_SESSION['servers_seen'][] = $serverName;

// Keep only last 10 servers
$_SESSION['servers_seen'] = array_slice(array_unique($_SESSION['servers_seen'] ?? []), 0, 10);

$count       = $_SESSION['visit_count'];
$firstServer = $_SESSION['first_server'];
$startedAt   = $_SESSION['started_at'];
$serversSeen = implode(', ', $_SESSION['servers_seen']);
$sessId      = substr(session_id(), 0, 12) . '...'; // show partial for UI

// ── Check session in Redis ──────────────────────────────────
$redisInfo = '';
try {
    $r = new Redis();
    $r->connect($redisHost, 6379, 1);
    $key = 'PHPREDIS_SESSION:' . session_id();
    $ttl = $r->ttl($key);
    $redisInfo = "TTL: {$ttl}s";
    $r->close();
} catch (Exception $e) {
    $redisInfo = 'Error: ' . $e->getMessage();
}

// ── Reset session ───────────────────────────────────────────
if (isset($_GET['reset'])) {
    session_destroy();
    header('Location: /session-demo.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Redis Session Demo</title>
  <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;700&display=swap" rel="stylesheet">
  <style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body {
      font-family: 'IBM Plex Mono', monospace;
      background: #050810; color: #c8d8f0;
      min-height: 100vh; display: flex; align-items: center;
      justify-content: center; padding: 20px;
    }
    .card {
      background: #090d1a; border: 2px solid #ff3d6b;
      border-radius: 18px; padding: 40px 44px;
      max-width: 580px; width: 100%;
      box-shadow: 0 0 60px rgba(255,61,107,.1);
    }
    h1 { color: #ff3d6b; font-size: 1.5rem; margin-bottom: 6px; }
    .sub { color: #4a6090; font-size: .8rem; margin-bottom: 28px; }
    .count-box {
      background: rgba(255,61,107,.06); border: 2px solid rgba(255,61,107,.3);
      border-radius: 14px; padding: 28px; text-align: center; margin-bottom: 20px;
    }
    .count-num { font-size: 4rem; font-weight: 700; color: #ff3d6b; line-height: 1; }
    .count-label { color: #4a6090; font-size: .8rem; margin-top: 6px; }
    .server-now {
      background: rgba(<?= ltrim($serverColor, '#') ?>, .06);
      border: 1px solid <?= htmlspecialchars($serverColor) ?>44;
      color: <?= htmlspecialchars($serverColor) ?>;
      padding: 10px 16px; border-radius: 8px; margin-bottom: 20px;
      font-size: .85rem; text-align: center;
    }
    .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 20px; }
    .cell { background: #0c1628; border: 1px solid #131c35; border-radius: 10px; padding: 12px; }
    .lbl { color: #2d4060; font-size: .62rem; letter-spacing: 1px; text-transform: uppercase; margin-bottom: 4px; }
    .val { font-size: .8rem; word-break: break-all; }
    .servers-seen { grid-column: 1/-1; }
    .key-box {
      background: rgba(255,61,107,.04); border: 1px solid rgba(255,61,107,.2);
      border-radius: 8px; padding: 12px; margin-bottom: 16px;
    }
    .key-lbl { color: rgba(255,61,107,.5); font-size: .65rem; letter-spacing: 1px; text-transform: uppercase; margin-bottom: 6px; }
    .key-val { color: #ff3d6b; font-size: .8rem; }
    .hint {
      background: rgba(0,255,157,.04); border: 1px solid rgba(0,255,157,.15);
      border-radius: 8px; padding: 14px; font-size: .78rem;
      color: #4a6090; line-height: 1.7; margin-bottom: 16px;
    }
    .hint strong { color: #00ff9d; }
    .btns { display: flex; gap: 10px; }
    .btn {
      flex: 1; padding: 10px;
      border-radius: 8px; font-family: 'IBM Plex Mono', monospace;
      font-size: .78rem; cursor: pointer; text-decoration: none;
      text-align: center; display: block;
      transition: opacity .2s;
    }
    .btn:hover { opacity: .8; }
    .btn-reload { background: rgba(0,229,255,.1); border: 1px solid rgba(0,229,255,.3); color: #00e5ff; }
    .btn-reset  { background: rgba(255,61,107,.1); border: 1px solid rgba(255,61,107,.3); color: #ff3d6b; }
  </style>
</head>
<body>
<div class="card">
  <h1>🗄️ Redis Shared Session</h1>
  <p class="sub">กด Reload ซ้ำๆ — count ต้องเพิ่ม แม้ request จะไปหา server คนละตัว</p>

  <div class="count-box">
    <div class="count-num"><?= $count ?></div>
    <div class="count-label">Total Visits (shared session)</div>
  </div>

  <div class="server-now">
    <?= htmlspecialchars($serverEmoji) ?> This request served by: <strong><?= htmlspecialchars($serverName) ?></strong>
  </div>

  <div class="key-box">
    <div class="key-lbl">Redis Key</div>
    <div class="key-val">PHPREDIS_SESSION:<?= htmlspecialchars($sessId) ?></div>
    <div style="color:#2d4060;font-size:.7rem;margin-top:4px"><?= htmlspecialchars($redisInfo) ?></div>
  </div>

  <div class="grid">
    <div class="cell">
      <div class="lbl">First Server</div>
      <div class="val"><?= htmlspecialchars($firstServer) ?></div>
    </div>
    <div class="cell">
      <div class="lbl">Session Started</div>
      <div class="val"><?= htmlspecialchars($startedAt) ?></div>
    </div>
    <div class="cell servers-seen">
      <div class="lbl">Servers Seen This Session</div>
      <div class="val"><?= htmlspecialchars($serversSeen) ?></div>
    </div>
  </div>

  <div class="hint">
    <strong>✅ ถ้า count เพิ่มต่อเนื่อง</strong> แม้ไปต่าง server = Redis shared session ทำงาน!<br>
    <strong>❌ ถ้า count reset เป็น 1 ทุกครั้ง</strong> = ใช้ local session (ไม่ได้ใช้ Redis)<br>
    ดูด้วย: <code>docker exec redis-sessions redis-cli KEYS "PHPREDIS*"</code>
  </div>

  <div class="btns">
    <a href="/session-demo.php" class="btn btn-reload">🔄 Reload (F5)</a>
    <a href="/session-demo.php?reset=1" class="btn btn-reset">🗑️ Reset Session</a>
  </div>
</div>
</body>
</html>
