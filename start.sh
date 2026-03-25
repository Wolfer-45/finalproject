#!/bin/bash
set -e

MYSQL_DATA=/home/runner/mysql-data
MYSQL_RUN=/home/runner/mysql-run
MYSQL_SOCK=$MYSQL_RUN/mysql.sock

mkdir -p "$MYSQL_DATA" "$MYSQL_RUN"

# Initialize MySQL data directory if not already done
if [ ! -f "$MYSQL_DATA/ibdata1" ]; then
  echo "Initializing MySQL data directory..."
  mysqld --initialize-insecure --user=runner --datadir="$MYSQL_DATA" 2>&1
fi

# Clean up stale socket/pid files
rm -f "$MYSQL_SOCK" "$MYSQL_SOCK.lock" "$MYSQL_RUN/mysql.pid"

# Start MySQL in foreground (background it ourselves)
mysqld \
  --user=runner \
  --datadir="$MYSQL_DATA" \
  --socket="$MYSQL_SOCK" \
  --pid-file="$MYSQL_RUN/mysql.pid" \
  --port=3306 \
  --mysqlx=OFF \
  --log-error="$MYSQL_DATA/error.log" &

MYSQL_PID=$!

# Wait for MySQL to be ready
echo "Waiting for MySQL to start..."
for i in $(seq 1 30); do
  if mysql -u root --socket="$MYSQL_SOCK" -e "SELECT 1;" > /dev/null 2>&1; then
    echo "MySQL is ready."
    break
  fi
  sleep 1
done

# Import schema if database doesn't exist yet
DB_EXISTS=$(mysql -u root --socket="$MYSQL_SOCK" -e "SHOW DATABASES LIKE 'wanderwise_db';" 2>/dev/null | grep -c "wanderwise_db" || true)
if [ "$DB_EXISTS" -eq 0 ]; then
  echo "Creating database and importing schema..."
  mysql -u root --socket="$MYSQL_SOCK" < /home/runner/workspace/database.sql
  echo "Database schema imported."
fi

# Start PHP built-in server on port 5000
echo "Starting PHP server on port 5000..."
php -S 0.0.0.0:5000 -t /home/runner/workspace &

PHP_PID=$!

# Wait for any process to exit
wait $MYSQL_PID $PHP_PID
