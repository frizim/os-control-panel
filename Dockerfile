# --------------------------- BUILD ---------------------------
FROM node:22-alpine AS build-frontend
COPY js ./js
COPY scss ./scss
COPY package.json package-lock.json webpack.config.mjs ./
RUN ls -al
RUN npm update && npm audit fix && npm install
RUN npm run build

FROM composer:lts AS build-backend
COPY app ./app
COPY composer.json composer.lock .
RUN composer install

# ------------- INSTALL AND CONFIGURE PHP & NGINX -------------
FROM nginx:mainline-alpine-slim AS runtime
LABEL org.opencontainers.image.authors="frizim.com"
WORKDIR /app
RUN apk add curl dcron libcap \
    php84-curl php84-fpm php84-mbstring php84-pdo_mysql php84-pecl-apcu php84-session php84-xml && \
    
    apk cache clean && \
    mkdir -p /app/data /run/nginx /run/php /var/cache/nginx /var/log/php84 /var/lib/php/sessions && \
    chown -R nginx:nginx /app/data /etc/nginx /run/nginx /run/php /var/cache/nginx /var/log/php84 /var/lib/php && \
    rm /etc/nginx/conf.d/* && rm /etc/crontabs/root && \
    touch /etc/crontabs/nginx && chown nginx:nginx /app /etc/crontabs/nginx /usr/sbin/crond && \
# This lets us run crond without root
    setcap cap_setgid=ep /usr/sbin/crond
COPY --chown=nginx:nginx --chmod=700 deploy/nginx /etc/nginx

COPY --chown=root:nginx --chmod=550 deploy/php.ini /etc/php84/php.ini
RUN sed -iE 's/^listen =.*/listen = \/run\/php\/php-fpm.sock/' /etc/php84/php-fpm.d/www.conf && \
    chattr +i /etc/php84/php.ini && chattr +i /etc/php84/php-fpm.d/www.conf && \
    echo $'#!/bin/sh \n\
      if [ -f "config.ini" ]; then \n\
        exit \n\
      fi \n\
      \n\
      export CRON_KEY="${CRON_KEY:-$(tr -dc A-Za-z0-9 </dev/urandom | head -c 32; echo)}" \n\
      export GRID_URL="${GRID_URL:-http://${DOMAIN}:8002}" \n\
      export TOS_URL="${TOS_URL:-https://${DOMAIN}/tos.html}" \n\
      export SMTP_HOST="${SMTP_HOST:-${DOMAIN}}" \n\
      export SMTP_SENDER="${SMTP_SENDER:-noreply@${DOMAIN}}" \n\
      export SMTP_SENDER_DISPLAY="${SMTP_SENDER_DISPLAY:-${GRID_NAME} Support}" \n\
      export RESTCONSOLE_HOST="${RESTCONSOLE_HOST:-${DOMAIN}}" \n\
      \n\
      echo "${CRON_KEY}" > /tmp/cronkey \n\
      envsubst < ./config.ini.template > ./config.ini' > /docker-entrypoint.d/80-mcp-config.sh && \
    echo $'#!/bin/sh \n\
      CRON_KEY="$(cat /tmp/cronkey)" \n\
      rm /tmp/cronkey \n\
      echo "* * * * * curl \'127.0.0.1:8080/index.php?api=runCron&key=$CRON_KEY\'" > /etc/crontabs/nginx' > /docker-entrypoint.d/85-crontab.sh && \
    echo $'#!/bin/sh \n\
      /usr/sbin/php-fpm84 --daemonize -d date.timezone=${TZ}' > /docker-entrypoint.d/90-php.sh && \
    echo $'#!/bin/sh \n\
      crond -l 4 -L /dev/stdout -P -c /etc/crontabs' > /docker-entrypoint.d/95-crond.sh && \
    chown root:nginx /docker-entrypoint.d/* && chmod 750 /docker-entrypoint.d/*

# ------------- COPY CONFIG TEMPLATE & APP FILES --------------
COPY deploy/config.ini.template .
COPY --chown=root:nginx --chmod=550 templates ./templates
COPY --chown=root:nginx --chmod=550 --from=build-frontend public ./public
COPY --chown=root:nginx --chmod=550 public/index.php ./public/index.php
COPY --chown=root:nginx --chmod=550 --from=build-backend app/app ./app
COPY --chown=root:nginx --chmod=550 --from=build-backend app/vendor ./vendor

# -------------- DEFAULT ENVIRONMENT VARIABLES ----------------
ENV TZ="Etc/UTC"

ENV SMTP_PORT="465"
ENV RESTCONSOLE_PORT="9001"

ENV PASSWORD_MIN_LENGTH="8"
ENV DEFAULT_AVATAR_NAME="Example"
ENV DEFAULT_AVATAR_UUID="00000000-0000-0000-0000-000000000000"

EXPOSE 8080
USER nginx:nginx
