FROM php:7.3-cli

ARG DEBIAN_FRONTEND=noninteractive

RUN version=$(php -r "echo PHP_MAJOR_VERSION.PHP_MINOR_VERSION;") \
    && mkdir -p /tmp/blackfire \
    && curl -A "Docker" -L -s https://blackfire.io/api/v1/releases/probe/php/linux/amd64/$version  | tar zxp -C /tmp/blackfire \
    && curl -A "Docker" -L -s https://blackfire.io/api/v1/releases/client/linux_static/amd64       | tar zxp -C /tmp/blackfire \
    && mv /tmp/blackfire/blackfire /usr/bin/blackfire \
    && mv /tmp/blackfire/blackfire-*.so $(php -r "echo ini_get('extension_dir');")/blackfire.so \
    && printf "extension=blackfire.so\nblackfire.agent_socket=tcp://blackfire:8707\n" > /usr/local/etc/php/conf.d/blackfire.ini \
    && rm -Rf /tmp/blackfire \
    && apt-get clean; rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/*
