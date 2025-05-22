FROM  traccar/traccar:6.1-alpine as build

RUN apk add --no-cache curl nano bash openssl

ENV DOCKERIZE_VERSION v0.6.1
RUN curl -Lk https://github.com/jwilder/dockerize/releases/download/$DOCKERIZE_VERSION/dockerize-linux-amd64-$DOCKERIZE_VERSION.tar.gz -o /tmp/dockerize.tar.gz \
    && echo '1fa29cd41a5854fd5423e242f3ea9737a50a8c3bcf852c9e62b9eb02c6ccd370  /tmp/dockerize.tar.gz' | sha256sum -c \
    && tar -C /usr/local/bin -xzvf /tmp/dockerize.tar.gz \
    && rm /tmp/dockerize.tar.gz

RUN curl -L https://github.com/Droplr/aws-env/raw/v0.4/bin/aws-env-linux-amd64 -o /usr/bin/aws-env \
  && chmod +x /usr/bin/aws-env

COPY traccar.xml.tpl /opt/traccar/conf/traccar.xml.tpl
COPY entrypoint.sh /usr/local/bin/entrypoint.sh
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

COPY boot.sh /usr/local/bin/boot.sh
CMD ["bash", "/usr/local/bin/boot.sh"]

