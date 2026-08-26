#!/bin/sh
set -e
cd "$(dirname "$0")/.."
SITE_ROOT="$(pwd)"
PHP83=/opt/homebrew/opt/php@8.3
NGINX=/opt/homebrew/bin/nginx
RUN_DIR="$SITE_ROOT/.local/run"

mkdir -p "$RUN_DIR"

if [ -x /opt/homebrew/opt/mysql@8.0/bin/mysql ]; then
  MYSQL=/opt/homebrew/opt/mysql@8.0/bin/mysql
  MYSQLADMIN=/opt/homebrew/opt/mysql@8.0/bin/mysqladmin
else
  MYSQL=$(command -v mysql)
  MYSQLADMIN=$(command -v mysqladmin)
fi

if "$MYSQLADMIN" --protocol=SOCKET ping --silent 2>/dev/null; then
  if [ ! -f "$SITE_ROOT/bitrix/php_interface/dbconn.local.php" ]; then
    "$SITE_ROOT/scripts/apply-local-db-config.sh"
  fi
  TABLES=$("$MYSQL" -h 127.0.0.1 -u proktolog_local -pproktolog_local -N -e \
    "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='proktolog_su_db'" 2>/dev/null || echo 0)
  if [ "$TABLES" -lt 50 ]; then
    if [ -f "$RUN_DIR/mysql-import.pid" ] && kill -0 "$(cat "$RUN_DIR/mysql-import.pid")" 2>/dev/null; then
      echo "MySQL import in progress — tail -f $RUN_DIR/mysql-import.log"
    else
      echo "WARN: DB empty ($TABLES tables). Run: ./scripts/setup-local-db.sh --background"
    fi
  else
    echo "Local MySQL: proktolog_su_db ($TABLES tables)"
  fi
else
  echo "WARN: MySQL not running"
fi

if [ -f "$SITE_ROOT/bitrix/html_pages/.enabled" ]; then
  mv "$SITE_ROOT/bitrix/html_pages/.enabled" \
    "$SITE_ROOT/bitrix/html_pages/.enabled.local-off" 2>/dev/null || true
fi

"$SITE_ROOT/scripts/stop-dev.sh" 2>/dev/null || true
sleep 1

USER_NAME="$(whoami)"
USER_GROUP="$(id -gn)"

sed "s|SITE_ROOT|$SITE_ROOT|g; s|RUN_DIR|$RUN_DIR|g" \
  "$SITE_ROOT/.local/nginx/nginx.conf" > "$RUN_DIR/nginx.conf"
sed "s|RUN_DIR|$RUN_DIR|g" \
  "$SITE_ROOT/.local/php/fpm.conf" > "$RUN_DIR/fpm.conf"
sed "s|USER_NAME|$USER_NAME|g; s|USER_GROUP|$USER_GROUP|g" \
  "$SITE_ROOT/.local/php/pools.conf" > "$RUN_DIR/pools.conf"
cp "$SITE_ROOT/.local/php/php.ini" "$RUN_DIR/php.ini"

export PHPRC="$RUN_DIR/php.ini"

"$PHP83/sbin/php-fpm" -y "$RUN_DIR/fpm.conf" &
FPM_PID=$!
sleep 1

if ! kill -0 "$FPM_PID" 2>/dev/null; then
  echo "php-fpm failed — see $RUN_DIR/php-fpm.log"
  exit 1
fi

"$NGINX" -c "$RUN_DIR/nginx.conf"
sleep 1

HTTP=$(curl -sS -o /tmp/proktolog-check.html -w '%{http_code}' --max-time 60 http://127.0.0.1:8102/ || echo 000)

echo "soft http://127.0.0.1:8102/ → HTTP $HTTP"
echo "php-fpm :9102 (ondemand, max 2 workers, 512M)"
echo "stop: ./scripts/stop-dev.sh"

if [ "$HTTP" = "000" ]; then
  echo "WARN: site not responding — check $RUN_DIR/nginx-error.log"
  tail -10 "$RUN_DIR/nginx-error.log" 2>/dev/null || true
  exit 1
fi
