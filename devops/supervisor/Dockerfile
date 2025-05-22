FROM php:8.3.2-fpm
# If it crashes with one above then try below:
# FROM php:7.2-fpm-stretch

# PHP
RUN apt-get update && apt-get install -y \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        libonig-dev \
        libmcrypt-dev \
        libpq-dev \
        netcat-traditional \
        libxml2-dev \
        libc-client-dev \
        libkrb5-dev \
        zlib1g-dev \
        libzip-dev \
        unzip \
    && docker-php-ext-install -j$(nproc) iconv \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-configure imap --with-kerberos --with-imap-ssl \
    && docker-php-ext-install -j$(nproc) gd \
    && docker-php-ext-install zip \
    && docker-php-ext-install exif \
        bcmath \
        gd \
        mbstring \
        opcache \
        soap \
        pdo pdo_pgsql \
        sockets \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && docker-php-ext-enable exif \
    && rm -r /var/lib/apt/lists/*

RUN curl -o /tmp/composer-setup.php https://getcomposer.org/installer \
&& curl -o /tmp/composer-setup.sig https://composer.github.io/installer.sig \
&& php -r "if (hash('SHA384', file_get_contents('/tmp/composer-setup.php')) !== trim(file_get_contents('/tmp/composer-setup.sig'))) { unlink('/tmp/composer-setup.php'); echo 'Invalid installer' . PHP_EOL; exit(1); }" \
&& php /tmp/composer-setup.php --no-ansi --install-dir=/usr/local/bin --filename=composer --snapshot \
&& rm -f /tmp/composer-setup.*

RUN yes | pecl install apcu xdebug-beta \
        && echo "extension=$(find /usr/local/lib/php/extensions/ -name apcu.so)" > /usr/local/etc/php/conf.d/apcu.ini \
        && echo "apc.enable_cli=1" > /usr/local/etc/php/conf.d/apcu.ini \
        && echo "zend_extension=$(find /usr/local/lib/php/extensions/ -name xdebug.so)" > /usr/local/etc/php/conf.d/xdebug.ini \
        && echo "xdebug.remote_enable=on" >> /usr/local/etc/php/conf.d/xdebug.ini \
        && echo "xdebug.remote_autostart=on" >> /usr/local/etc/php/conf.d/xdebug.ini \
        && echo "xdebug.remote_connect_back=on" >> /usr/local/etc/php/conf.d/xdebug.ini

RUN apt-get update
RUN apt-get upgrade -y
RUN apt-get update
RUN apt-get install nano  -y
RUN apt-get update && \
    apt-get install -y git
RUN apt-get install supervisor -y

RUN apt-get update
RUN apt-get install wget
RUN apt install wget xfonts-75dpi -y
RUN apt-get install wkhtmltopdf -y

WORKDIR /srv
COPY boot.sh                /usr/local/bin/boot.sh
CMD ["bash", "/usr/local/bin/boot.sh"]

RUN yes | echo "memory_limit = 256M" >> /usr/local/etc/php/conf.d/memory_limit.ini
RUN yes | echo "upload_max_filesize = 20M" >> /usr/local/etc/php/conf.d/memory_limit.ini
RUN yes | echo "max_execution_time = 180" >> /usr/local/etc/php/conf.d/memory_limit.ini

COPY ./config/www.conf /usr/local/etc/php-fpm.d/www.conf
COPY ./supervisor/prod.conf /etc/supervisor/conf.d/prod.conf
COPY ./ /srv/
