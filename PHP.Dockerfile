FROM php:8.2-fpm-alpine3.18

# Copy composer binary from official composer image
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN apk add --no-cache $PHPIZE_DEPS
RUN apk add --no-cache linux-headers
RUN pecl install xdebug 

RUN docker-php-ext-enable xdebug 
RUN docker-php-ext-install pdo pdo_mysql

# Set workdir to project so composer runs in correct path via docker exec
WORKDIR /websites/doko

# Configure maildev

RUN apk update
RUN apk add ssmtp

RUN echo "hostname=v.je" > /etc/ssmtp/ssmtp.conf
RUN echo "root=mailer@v.je>" >> /etc/ssmtp/ssmtp.conf
RUN echo "mailhub=maildev:1025" >> /etc/ssmtp/ssmtp.conf

RUN echo "sendmail_path=sendmail -i -t" >> /usr/local/etc/php/conf.d/php-sendmail.ini
RUN sed -i '/#!\/bin\/sh/aecho "$(hostname -i)\t$(hostname) $(hostname).localhost" >> /etc/hosts' /usr/local/bin/docker-php-entrypoint

RUN echo "post_max_size=5000M" > /usr/local/etc/php/conf.d/php-uploadsize.ini
RUN echo "upload_max_filesize=5000M" >> /usr/local/etc/php/conf.d/php-uploadsize.ini
RUN echo "short_open_tag=off" >> /usr/local/etc/php/conf.d/opentags.ini

# Faster container start & helpful tools
RUN apk add --no-cache bash curl git

# Default command stays php-fpm
