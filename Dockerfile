FROM bitnami/php-fpm:8.0
COPY . /usr/src/smailer
WORKDIR /usr/src/smailer
CMD [ "php-fpm"]