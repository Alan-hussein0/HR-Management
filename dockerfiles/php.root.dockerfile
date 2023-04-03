FROM php:8-fpm-alpine

RUN mkdir -p /var/www/html

WORKDIR /var/www/html

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

RUN sed -i "s/user = www-data/user = root/g" /usr/local/etc/php-fpm.d/www.conf
RUN sed -i "s/group = www-data/group = root/g" /usr/local/etc/php-fpm.d/www.conf
RUN echo "php_admin_flag[log_errors] = on" >> /usr/local/etc/php-fpm.d/www.conf

# RUN apk update && \
#     apk add \
#     libzip-dev \
#     libjpeg62-turbo-dev \
#     libpng-dev
# RUN apk add --no-cache libpng-dev zlib-dev libzip-dev \
# && docker-php-ext-configure zip --with-libzip \
# && docker-php-ext-install zip

# RUN docker-php-ext-install gd
# RUN apk add --no-cache zlib zlib-dev && apk del zlib-dev

# RUN apk add --no-cache libpng libpng-dev && docker-php-ext-install gd && apk del libpng-dev
RUN apk add --no-cache \
  freetype \
  libpng \
  libzip-dev \
  zlib-dev \
  libwebp-dev \
  libjpeg-turbo \
  freetype-dev \
  libpng-dev \
  libpng \
  # libjpeg62-turbo-dev \
#   icu \
  libjpeg-turbo-dev \
  gmp \
  gmp-dev

RUN docker-php-ext-configure gd --enable-gd \
  --with-freetype \
  --with-jpeg --with-webp && \
    docker-php-ext-install gd && \
    docker-php-ext-install zip
RUN apk add --no-cache mysql-client 

# RUN apk add --no-cache --virtual build-essentials \
#     icu-dev icu-libs zlib-dev g++ make automake autoconf libzip-dev \
#     libpng-dev libwebp-dev libjpeg-turbo-dev freetype-dev && \
#     docker-php-ext-configure gd --enable-gd --with-freetype --with-jpeg --with-webp && \
#     docker-php-ext-install gd && \
#     docker-php-ext-install mysqli && \
#     # docker-php-ext-install pdo_mysql && \
#     docker-php-ext-install intl && \
#     docker-php-ext-install opcache && \
#     docker-php-ext-install exif && \
#     docker-php-ext-install zip 
    # apk del build-essentials && rm -rf /usr/src/php*
# RUN docker-php-ext-configure gd \
#   --with-gd \
#   --with-jpeg-dir \
#   --with-png-dir \
#   --with-zlib-dir

RUN docker-php-ext-install pdo pdo_mysql


RUN mkdir -p /usr/src/php/ext/redis \
    && curl -L https://github.com/phpredis/phpredis/archive/5.3.4.tar.gz | tar xvz -C /usr/src/php/ext/redis --strip 1 \
    && echo 'redis' >> /usr/src/php-available-exts \
    && docker-php-ext-install redis
    
USER root

CMD ["php-fpm", "-y", "/usr/local/etc/php-fpm.conf", "-R"]
