FROM nginx:1.14

RUN echo "deb http://archive.debian.org/debian stretch main" > /etc/apt/sources.list
RUN apt-get update \
    && apt-get install --no-install-recommends --no-install-suggests -y \
        curl \
        nano

COPY . /etc/nginx/
COPY boot.sh /usr/local/bin/boot.sh

CMD ["bash", "/usr/local/bin/boot.sh"]
