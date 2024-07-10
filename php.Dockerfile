FROM php:8.2-apache
#ENV PHP_MEMORY_LIMIT=512M 
RUN cp /etc/apache2/mods-available/rewrite.load /etc/apache2/mods-enabled/ && \
    cp /etc/apache2/mods-available/headers.load /etc/apache2/mods-enabled/
RUN cd /usr/local/etc/php/conf.d/ && \
    echo 'memory_limit = 512M' >> /usr/local/etc/php/conf.d/docker-php-ram-limit.ini && \
    echo 'upload_max_filesize = 128M' >> /usr/local/etc/php/conf.d/docker-php-upload-limit.ini