#!/bin/bash
# ═══════════════════════════════════════════════════════════════
# สร้าง Self-Signed SSL Certificate สำหรับ development/demo
# ═══════════════════════════════════════════════════════════════

set -e

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
SSL_DIR="$SCRIPT_DIR"

echo "🔑 Generating Self-Signed SSL Certificate..."
echo "   Output: $SSL_DIR/server.crt + server.key"
echo ""

openssl req -x509 \
  -nodes \
  -days 365 \
  -newkey rsa:2048 \
  -keyout "$SSL_DIR/server.key" \
  -out    "$SSL_DIR/server.crt" \
  -subj   "/C=TH/ST=Bangkok/L=Bangkok/O=DevOps-Demo/OU=Workshop/CN=localhost" \
  -addext "subjectAltName=DNS:localhost,IP:127.0.0.1"

echo ""
echo "✅ Certificate Info:"
openssl x509 -in "$SSL_DIR/server.crt" -noout \
  -subject -issuer -dates \
  -ext subjectAltName 2>/dev/null || \
openssl x509 -in "$SSL_DIR/server.crt" -noout -subject -dates

echo ""
echo "📁 Files created:"
ls -lh "$SSL_DIR/server.crt" "$SSL_DIR/server.key"

echo ""
echo "🔍 To inspect certificate:"
echo "   openssl x509 -in ssl/server.crt -text -noout"
echo "   openssl rsa  -in ssl/server.key -check"
echo ""
echo "🌐 Test SSL connection after docker compose up:"
echo "   echo | openssl s_client -connect localhost:443 2>/dev/null | openssl x509 -noout -subject"
