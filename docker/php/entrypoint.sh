#!/usr/bin/env sh
set -e

APP_DIR=/var/www/html
CONSOLE_DIR="$APP_DIR/console"
UPLOAD_DIR="$CONSOLE_DIR/upload"

# 尝试设置目录权限（在 Windows 绑定卷可能无效，但不应阻塞）
chmod -R 777 "$CONSOLE_DIR" || true
# 最低限度保证上传目录可写
chmod -R 777 "$UPLOAD_DIR" || true

exec docker-php-entrypoint php-fpm 