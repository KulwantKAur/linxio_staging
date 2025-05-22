FROM 296258661150.dkr.ecr.ap-southeast-2.amazonaws.com/docker_php:centrifugo_v3 as build

RUN apk add --no-cache curl nano bash openssl zlib jq moreutils

ARG AWS_ENV_PATH
ARG JWT_PUBLIC_KEY
ARG CENTRIFUGO_ADMIN_PASSWORD
ARG CENTRIFUGO_ADMIN_SECRET
ARG CENTRIFUGO_API_KEY
ARG CENTRIFUGO_ALLOWED_ORIGINS
ARG CENTRIFUGO_ENGINE
ARG CENTRIFUGO_REDIS_ADDRESS

ENV DOCKERIZE_VERSION v0.7.0
ENV AWS_ENV_PATH $AWS_ENV_PATH
ENV JWT_PUBLIC_KEY $JWT_PUBLIC_KEY
ENV CENTRIFUGO_ADMIN_PASSWORD $CENTRIFUGO_ADMIN_PASSWORD
ENV CENTRIFUGO_ADMIN_SECRET $CENTRIFUGO_ADMIN_SECRET
ENV CENTRIFUGO_API_KEY $CENTRIFUGO_API_KEY
ENV CENTRIFUGO_ALLOWED_ORIGINS $CENTRIFUGO_ALLOWED_ORIGINS
ENV CENTRIFUGO_ENGINE $CENTRIFUGO_ENGINE
ENV CENTRIFUGO_REDIS_ADDRESS $CENTRIFUGO_REDIS_ADDRESS

RUN curl -L https://github.com/Droplr/aws-env/raw/v0.5/bin/aws-env-linux-amd64 -o /usr/bin/aws-env \
  && chmod +x /usr/bin/aws-env

COPY config.json.example /centrifugo/config.json

RUN jq --arg jwt "$JWT_PUBLIC_KEY" '.token_rsa_public_key |= gsub("JWT"; $jwt)' /centrifugo/config.json | sponge /centrifugo/config.json
RUN jq --arg pass "$CENTRIFUGO_ADMIN_PASSWORD" '.admin_password |= $pass' /centrifugo/config.json | sponge /centrifugo/config.json
RUN jq --arg secret "$CENTRIFUGO_ADMIN_SECRET" '.admin_secret |= $secret' /centrifugo/config.json | sponge /centrifugo/config.json
RUN jq --arg key "$CENTRIFUGO_API_KEY" '.api_key |= $key' /centrifugo/config.json | sponge /centrifugo/config.json
RUN jq --arg origins "$CENTRIFUGO_ALLOWED_ORIGINS" '.allowed_origins |= [$origins]' /centrifugo/config.json | sponge /centrifugo/config.json
RUN jq --arg engine "$CENTRIFUGO_ENGINE" '.engine |= $engine' /centrifugo/config.json | sponge /centrifugo/config.json
RUN jq --arg address "$CENTRIFUGO_REDIS_ADDRESS" '.redis_address |= $address' /centrifugo/config.json | sponge /centrifugo/config.json

COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
