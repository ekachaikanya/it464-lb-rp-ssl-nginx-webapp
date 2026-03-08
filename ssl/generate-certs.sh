#!/bin/bash

echo "🔑 Generating Self-Signed SSL Certificate..."
echo "   Output: server.crt + server.key"
echo ""

openssl req -x509  -nodes   -days 365   -newkey rsa:2048   -keyout "server.key"   -out    "server.crt"   -subj   "/C=TH/ST=Bangkok/L=Bangkok/O=DevOps-Demo/OU=Workshop/CN=localhost"   -addext "subjectAltName=DNS:localhost,IP:127.0.0.1"

echo ""
echo "✅ Certificate Info:"
openssl x509 -in "server.crt" -noout   -subject -issuer -dates   -ext subjectAltName 2>/dev/null
openssl x509 -in "server.crt" -noout -subject -dates

echo ""
echo "📁 Files created:"
ls -lh "server.crt" "server.key"

echo ""
echo "🔍 To inspect certificate:"
echo "   openssl x509 -in server.crt -text -noout"
echo "   openssl rsa  -in server.key -check"
echo ""
echo "🌐 Test SSL connection after docker compose up:"
echo "   echo | openssl s_client -connect localhost:443 2>/dev/null | openssl x509 -noout -subject"
