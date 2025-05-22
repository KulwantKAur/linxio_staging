FROM public.ecr.aws/bitnami/golang:1.21.3 as metrics
ENV GOPROXY=proxy.golang.org

WORKDIR /srv

COPY .infra/api-users/metrics/go.mod .infra/api-users/metrics/go.sum ./
RUN go mod download

COPY .infra/api-users/metrics .
RUN CGO_ENABLED=0 GOOS=linux GOARCH=amd64 go build -ldflags="-w -s"

FROM public.ecr.aws/bitnami/golang:1.21.3 as lock
ENV GOPROXY=proxy.golang.org
WORKDIR /srv

COPY .infra/api-users/lock/go.mod .infra/api-users/lock/go.sum ./
RUN go mod download

COPY .infra/api-users/lock .
RUN CGO_ENABLED=0 GOOS=linux GOARCH=amd64 go build -ldflags="-w -s"

FROM php:8.3.2-fpm  as core
# If it crashes with one above then try below:
# FROM php:7.2-fpm-stretch

SHELL ["/bin/bash", "-o", "pipefail", "-c"]

# PHP
RUN apt-get update && apt-get install -y --no-install-recommends \
        libfreetype-dev \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        libwebp-dev \
        libmcrypt-dev \
        libpq-dev \
        netcat-traditional \
        libxml2-dev \
        libc-client-dev \
        libkrb5-dev \
        zlib1g-dev \
        libzip-dev \
        libonig-dev \
        curl \
        unzip \
        nano \
        git \
        jq \
        moreutils \ 
        wget xfonts-75dpi wkhtmltopdf \
        iputils-ping \
        dnsutils \
        less \
    && docker-php-ext-install "-j$(nproc)" iconv \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-configure imap --with-kerberos --with-imap-ssl \
    && docker-php-ext-install "-j$(nproc)" gd \
    && docker-php-ext-install zip \
    && docker-php-ext-install exif \
        bcmath \
        mbstring \
        opcache \
        soap \
        pdo pdo_pgsql \
        sockets \
    && pecl install redis-6.0.2 \
    && docker-php-ext-enable redis \
    && docker-php-ext-enable exif \
    && rm -r /var/lib/apt/lists/*

RUN pecl install apcu && docker-php-ext-enable apcu
#RUN pecl install xdebug-beta && docker-php-ext-enable xdebug

RUN curl "https://awscli.amazonaws.com/awscli-exe-linux-x86_64.zip" -o "awscliv2.zip" \
    && unzip awscliv2.zip \
    && ./aws/install

RUN curl -o /tmp/composer-setup.php https://getcomposer.org/installer \
    && curl -o /tmp/composer-setup.sig https://composer.github.io/installer.sig \
    && php -r "if (hash('SHA384', file_get_contents('/tmp/composer-setup.php')) !== trim(file_get_contents('/tmp/composer-setup.sig'))) { unlink('/tmp/composer-setup.php'); echo 'Invalid installer' . PHP_EOL; exit(1); }" \
    && php /tmp/composer-setup.php --no-ansi --install-dir=/usr/local/bin --filename=composer \
    && rm -f /tmp/composer-setup.*

ENV DOCKERIZE_VERSION v0.7.0
RUN curl -Lk https://github.com/jwilder/dockerize/releases/download/$DOCKERIZE_VERSION/dockerize-linux-amd64-$DOCKERIZE_VERSION.tar.gz -o /tmp/dockerize.tar.gz \
    && tar -C /usr/local/bin -xzvf /tmp/dockerize.tar.gz \
    && rm /tmp/dockerize.tar.gz

RUN curl -L https://github.com/Droplr/aws-env/raw/v0.5/bin/aws-env-linux-amd64 -o /usr/bin/aws-env \
  && chmod +x /usr/bin/aws-env

COPY --from=metrics /srv/metrics /usr/bin/php-fpm-metrics
COPY --from=lock /srv/lock /usr/bin/cron-lock

WORKDIR /srv

FROM core as vendor

COPY composer.* /srv/
RUN composer install --no-autoloader --no-scripts --no-progress --no-dev --no-interaction

FROM core
ARG fcm_project_id
ARG fcm_private_key_id
ARG fcm_private_key
ARG fcm_client_x509_cert_url
ARG fcm_client_email

ENV fcm_project_id $fcm_project_id
ENV fcm_private_key_id $fcm_private_key_id
ENV fcm_private_key $fcm_private_key
ENV fcm_client_x509_cert_url $fcm_client_x509_cert_url
ENV fcm_client_email $fcm_client_email

COPY --from=vendor /srv/vendor/ ./vendor/
COPY ./ /srv/

RUN composer install --optimize-autoloader --no-progress --no-dev --no-interaction

RUN cp app/config/parameters.yml.dist app/config/parameters.yml

COPY .infra/api-users/php-fpm/conf.d/zz-apcu.ini \
  .infra/api-users/php-fpm/conf.d/zz-memory-limit.ini \
  .infra/api-users/php-fpm/conf.d/zz-opcache.ini \
   /usr/local/etc/php/conf.d/

COPY .infra/api-users/php-fpm/php-fpm.d/www.conf.tpl /usr/local/etc/php-fpm.d/www.conf.tpl

COPY .infra/api-users/php-fpm/fcm-config/firebase.json app/config/firebase.json

RUN jq --arg id "$fcm_project_id" '.project_id |= gsub("fcm_project_id"; $id)' app/config/firebase.json | sponge app/config/firebase.json
RUN jq --arg key_id "$fcm_private_key_id" '.private_key_id |= $key_id' app/config/firebase.json | sponge app/config/firebase.json
RUN jq --arg private_key "$fcm_private_key" '.private_key |= $private_key' app/config/firebase.json | sponge app/config/firebase.json
RUN jq --arg cert_url "$fcm_client_x509_cert_url" '.client_x509_cert_url |= $cert_url' app/config/firebase.json | sponge app/config/firebase.json
RUN jq --arg email "$fcm_client_email" '.client_email |= $email' app/config/firebase.json | sponge app/config/firebase.json

COPY .infra/api-users/php-fpm/entrypoint.sh /usr/local/bin/entrypoint.sh
COPY .infra/api-users/php-fpm/critical.sh /usr/local/bin/critical.sh
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

COPY .infra/api-users/php-fpm/boot.sh /usr/local/bin/boot.sh
CMD ["bash", "/usr/local/bin/boot.sh"]