#!/bin/sh
set -e

UPLOAD_MAX_FILESIZE=${UPLOAD_MAX_FILESIZE:-10M}
POST_MAX_SIZE=${POST_MAX_SIZE:-40M}
MAX_EXECUTION_TIME=${MAX_EXECUTION_TIME:-150}

sed -i "s/^upload_max_filesize = .*/upload_max_filesize = $UPLOAD_MAX_FILESIZE/" "$PHP_INI_DIR/php.ini"
sed -i "s/^post_max_size = .*/post_max_size = $POST_MAX_SIZE/" "$PHP_INI_DIR/php.ini"
sed -i "s/^max_execution_time = .*/max_execution_time = $MAX_EXECUTION_TIME/" "$PHP_INI_DIR/php.ini"

mkdir -p _notes
chown -R www-data:www-data _notes

exec docker-php-entrypoint "$@"
