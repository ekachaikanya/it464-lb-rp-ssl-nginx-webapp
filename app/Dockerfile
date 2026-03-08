# ═══════════════════════════════════════════════════════════════
# PHP-FPM + Nginx Web Server + Redis Extension
# Supervisor จัดการ nginx และ php-fpm processes
# ═══════════════════════════════════════════════════════════════
FROM php:8.2-fpm-alpine

# ── ติดตั้ง nginx, supervisor และ PHP Redis extension ──────────
RUN apk add --no-cache nginx supervisor autoconf gcc g++ make linux-headers \
 && pecl install redis \
 && docker-php-ext-enable redis \
 && apk del autoconf gcc g++ make linux-headers \
 && rm -rf /tmp/pear

# ── Nginx web server config ────────────────────────────────────
COPY nginx.conf /etc/nginx/http.d/default.conf

# ── Supervisor: run nginx + php-fpm together ──────────────────
COPY supervisord.conf /etc/supervisord.conf

# ── PHP application files ──────────────────────────────────────
COPY www/ /var/www/html/

# ── Permissions ────────────────────────────────────────────────
RUN chown -R www-data:www-data /var/www/html \
 && mkdir -p /run/nginx /var/log/supervisor

EXPOSE 80

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
