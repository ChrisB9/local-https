FROM neilpang/acme.sh

ARG PHP_VERSION=8.0
ARG DOCKER_GEN_VERSION=0.7.7

ENV PHP_VERSION $PHP_VERSION
ENV DOCKER_GEN_VERSION $DOCKER_GEN_VERSION

ADD https://packages.whatwedo.ch/php-alpine.rsa.pub /etc/apk/keys/php-alpine.rsa.pub

RUN apk --update-cache add ca-certificates && \
    echo "https://packages.whatwedo.ch/php-alpine/v3.12/php-8.0" >> /etc/apk/repositories


RUN apk add --update-cache \
    	php8 \
    	php8-curl \
    	php8-phar \
    	php8-openssl \
    	php8-iconv \
    	curl \
    	docker && \
    ln -s /usr/bin/php8 /usr/bin/php && \
	ln -s /usr/bin/php-cgi8 /usr/bin/php-cgi && \
	ln -s /usr/sbin/php-fpm8 /usr/sbin/php-fpm && \
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer && \
    wget "https://github.com/nginx-proxy/docker-gen/releases/download/$DOCKER_GEN_VERSION/docker-gen-linux-amd64-$DOCKER_GEN_VERSION.tar.gz" && \
    tar xvzf "docker-gen-linux-amd64-$DOCKER_GEN_VERSION.tar.gz" && \
    mv docker-gen /usr/local/bin/docker-gen && \
    rm -rf /var/cache/apk/*

COPY src /app/src
COPY scripts /app/scripts
COPY templates /app/templates
COPY composer.json /app/
COPY composer.lock /app/

WORKDIR /app

RUN composer install --no-dev -n

ENTRYPOINT ["php", "/app/scripts/entrypoint.php"]

CMD ["docker-gen --watch --interval 3600 --wait 15s --notify-output --notify '/app/scripts/notify.php' templates/data.tmpl var/data.json"]


