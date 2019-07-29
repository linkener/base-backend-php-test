FROM php:7.3-fpm-alpine

WORKDIR /code

# php extensions
RUN apk add --no-cache $PHPIZE_DEPS \
    # xdebug
    && pecl install xdebug-2.7.2 && docker-php-ext-enable xdebug \
    # pdo-mysql
    && docker-php-ext-install pdo_mysql

# install composer
ENV COMPOSER_INSTALLER_URL=https://getcomposer.org/installer
ENV COMPOSER_VERSION=1.8.6
ENV COMPOSER_INSTALLER_HASH=48e3236262b34d30969dca3c37281b3b4bbe3221bda826ac6a9a62d6444cdb0dcd0615698a5cbe587c3f0fe57a54d8f5

RUN curl --silent --fail --location --retry 3 --output /tmp/installer.php --url ${COMPOSER_INSTALLER_URL} \
 && php -r " \
    \$signature = '${COMPOSER_INSTALLER_HASH}'; \
    \$hash = hash('sha384', file_get_contents('/tmp/installer.php')); \
    if (!hash_equals(\$signature, \$hash)) { \
      unlink('/tmp/installer.php'); \
      echo 'Integrity check failed, installer is either corrupt or worse.' . PHP_EOL; \
      exit(1); \
    }" \
 && php /tmp/installer.php --no-ansi --install-dir=/usr/bin --filename=composer --version=${COMPOSER_VERSION} \
 && composer --ansi --version --no-interaction \
 && rm -f /tmp/installer.php \
 && find /tmp -type d -exec chmod -v 1777 {} +

CMD ["php-fpm", "-F"]
EXPOSE 9000
