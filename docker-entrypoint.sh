#!/bin/bash
set -e

# Railway sets the PORT env variable dynamically.
# Apache defaults to 80 — we need to match whatever Railway assigns.
PORT="${PORT:-80}"

sed -i "s/Listen 80/Listen $PORT/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost *:$PORT>/" /etc/apache2/sites-enabled/000-default.conf

exec apache2-foreground