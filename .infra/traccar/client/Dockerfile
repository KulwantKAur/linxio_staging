FROM mhart/alpine-node:6

# Create app directory
RUN mkdir -p /usr/src/app
WORKDIR /usr/src/app
RUN apk update && apk add bash

COPY boot.sh /usr/local/bin/boot.sh

EXPOSE 3000
CMD ["bash", "/usr/local/bin/boot.sh"]