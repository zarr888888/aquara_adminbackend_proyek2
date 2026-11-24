#!/bin/sh
set -eu
case "${PORT:-}" in ''|*[!0-9]*) exit 1;; esac
[ "$PORT" -gt 0 ] && [ "$PORT" -le 65535 ] || exit 1
[ "$APP_ENV" = production ] && [ "$APP_DEBUG" = false ] && [ -n "${APP_KEY:-}" ] || exit 1
sed -i -E "s/^Listen [0-9]+$/Listen $PORT/" /etc/apache2/ports.conf
sed -i -E "s/<VirtualHost \*:[0-9]+>/<VirtualHost *:$PORT>/" /etc/apache2/sites-available/000-default.conf
exec supervisord -c /etc/samudera-supervisor.conf