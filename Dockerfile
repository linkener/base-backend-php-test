FROM php:7.3-fpm-alpine

WORKDIR /code

# install php extensions
RUN apk add --no-cache $PHPIZE_DEPS \
    # xdebug
    && pecl install xdebug-2.7.2 && docker-php-ext-enable xdebug \
    # pdo-mysql
    && docker-php-ext-install pdo_mysql \
    && rm -rf /var/cache/apk/*

# install composer
RUN curl --silent --fail --location --retry 3 --output /tmp/installer.php --url https://getcomposer.org/installer \
 && curl --silent --fail --location --retry 3 --output /tmp/installer.sig --url https://composer.github.io/installer.sig \
 && php -r " \
    \$signature = file_get_contents('/tmp/installer.sig'); \
    \$hash = hash('sha384', file_get_contents('/tmp/installer.php')); \
    if (!hash_equals(\$signature, \$hash)) { \
      unlink('/tmp/installer.php'); \
      unlink('/tmp/installer.sig'); \
      echo 'Integrity check failed, installer is either corrupt or worse.' . PHP_EOL; \
      exit(1); \
    }" \
 && php /tmp/installer.php --no-ansi --install-dir=/usr/bin --filename=composer \
 && composer --ansi --version --no-interaction \
 && rm -f /tmp/installer.php /tmp/installer.sig \
 && find /tmp -type d -exec chmod -v 1777 {} +

EXPOSE 9000
