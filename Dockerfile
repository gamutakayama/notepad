FROM php:8.4-apache

RUN a2enmod headers rewrite && \
  mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

COPY . ./

RUN mv entrypoint.sh /usr/local/bin/notepad-entrypoint && \
  chmod +x /usr/local/bin/notepad-entrypoint

ENTRYPOINT [ "notepad-entrypoint" ]
CMD [ "apache2-foreground" ]
