FROM wordpress:5.5.0-php7.2-apache

# Install SQLSRV plugin
RUN apt-get update && apt-get install -y gnupg2
RUN curl https://packages.microsoft.com/keys/microsoft.asc | apt-key add -
RUN curl https://packages.microsoft.com/config/debian/10/prod.list > /etc/apt/sources.list.d/mssql-release.list
RUN apt-get update
ENV ACCEPT_EULA=Y
RUN apt-get -y install msodbcsql17
RUN apt-get -y install unixodbc-dev
RUN pecl install sqlsrv
RUN pecl install pdo_sqlsrv
RUN printf "extension=sqlsrv.so\n; extension=pdo_sqlsrv.so;\n" > /usr/local/etc/php/php.ini

COPY wp-config.php /var/www/html/
COPY Exlog_freemius_mock.php /var/www/html/
#COPY external-login-settings.php /var/www/html/
