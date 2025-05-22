FROM 296258661150.dkr.ecr.ap-southeast-2.amazonaws.com/openresty:1.21.4.3-5-alpine

RUN apk update && \
    apk add --no-cache curl nano bash openssl expat

SHELL ["/bin/bash", "-o", "pipefail", "-c"]

ENV DOCKERIZE_VERSION v0.7.0
RUN curl -Lk https://github.com/jwilder/dockerize/releases/download/$DOCKERIZE_VERSION/dockerize-linux-amd64-$DOCKERIZE_VERSION.tar.gz -o /tmp/dockerize.tar.gz \
    && tar -C /usr/local/bin -xzvf /tmp/dockerize.tar.gz \
    && rm /tmp/dockerize.tar.gz

COPY .infra/api-users/nginx/ /etc/nginx/
COPY .infra/api-users/nginx/boot.sh /usr/local/bin/boot.sh

WORKDIR /srv
COPY ./web/ /srv/web/

CMD ["bash", "/usr/local/bin/boot.sh"]