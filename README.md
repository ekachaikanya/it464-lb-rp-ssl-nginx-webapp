# 🔒 Nginx Advanced Demo — SSL/TLS + Stateful/Stateless + Redis

> Stateful vs Stateless · SSL/TLS Termination · Redis Sessions · Load Balancer · Reverse Proxy

## 🏗 Architecture

```
:80   → nginx-lb  → 301 HTTPS redirect
:443  → nginx-lb  → SSL Terminate → app1, app2, app3 (least_conn)
:8080 → nginx-rp  → 301 HTTPS redirect  
:8443 → nginx-rp  → SSL Terminate → app1 (reverse proxy)

app1/2/3 = Nginx web server + PHP-FPM + Redis extension
redis    = Shared session store (PHPREDIS_SESSION:*)
```

## 📁 Files

```
nginx-advanced/
├── docker-compose.yml
├── ssl/
│   ├── generate-certs.sh   ← สร้าง self-signed cert
│   ├── server.crt          ← certificate (pre-generated)
│   └── server.key          ← private key
├── nginx-lb/nginx.conf     ← LB + SSL + HSTS + 80→443
├── nginx-rp/nginx.conf     ← RP + SSL :8443
└── app/
    ├── Dockerfile           ← php:8.2-fpm + nginx + supervisor + redis ext
    ├── nginx.conf
    ├── supervisord.conf
    └── www/
        ├── index.php        ← หน้าหลัก + Redis status
        └── session-demo.php ← ทดสอบ Redis shared sessions
```

## 🚀 Quick Start

```bash
# (Optional) Regenerate SSL cert
bash cd ssl
bash generate-certs.sh

# Build & Start
cd ..
docker compose up --build -d
```

## 🧪 Tests

### HTTPS Load Balancer
```bash
curl -sk https://localhost | grep -o 'Web Server [0-9]'
for i in {1..6}; do curl -sk https://localhost | grep -o 'Web Server [0-9]'; done
```

### HTTP → HTTPS Redirect
```bash
curl -I http://localhost
# Expected: 301 Location: https://localhost/
```

### HTTPS Reverse Proxy
```bash
curl -skI https://localhost:8443 | grep X-Served-Via
# Expected: X-Served-Via: nginx-rp
```

### SSL Certificate Info
```bash
echo | openssl s_client -connect localhost:443 2>/dev/null | openssl x509 -noout -subject -dates
```

### Redis Shared Session
```bash
# Open in browser: https://localhost/session-demo.php
# Press F5 multiple times — count should increase continuously
# even though requests go to different servers

# CLI test:
curl -sk -c /tmp/c.txt https://localhost/session-demo.php | grep -o 'Visit [0-9]*'
for i in {1..5}; do
  curl -sk -b /tmp/c.txt -c /tmp/c.txt https://localhost/session-demo.php | grep "count-num"
done
```

### View Redis Data
```bash
docker exec redis-sessions redis-cli KEYS "PHPREDIS_SESSION:*"
docker exec redis-sessions redis-cli GET "PHPREDIS_SESSION:<session_id>"
```

## 🔧 Change LB Algorithm

Edit `nginx-lb/nginx.conf`, then:
```bash
docker exec nginx-lb nginx -t
docker exec nginx-lb nginx -s reload
```

## 🛑 Stop
```bash
docker compose down
```
