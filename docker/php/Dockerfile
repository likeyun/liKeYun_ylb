FROM php:7.4-fpm-alpine

# 基础依赖与构建工具
RUN apk add --no-cache \
    freetype-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    libzip-dev \
    oniguruma-dev \
    icu-dev \
    bash \
    git \
    curl \
    tzdata

# 安装 PHP 扩展（pdo_mysql、mysqli、mbstring、gd、zip、exif、intl）
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install -j"$(nproc)" pdo_mysql mysqli mbstring gd zip exif intl

# 设置时区
ENV TZ=Asia/Shanghai
RUN cp /usr/share/zoneinfo/Asia/Shanghai /etc/localtime \
 && echo "Asia/Shanghai" > /etc/timezone

# 复制入口脚本
COPY entrypoint.sh /usr/local/bin/app-entrypoint.sh
RUN chmod +x /usr/local/bin/app-entrypoint.sh

WORKDIR /var/www/html

ENTRYPOINT ["/usr/local/bin/app-entrypoint.sh"] 