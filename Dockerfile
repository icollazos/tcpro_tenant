FROM php:8.2.11-fpm-alpine3.18

# Instalar Apache y las dependencias necesarias
RUN apk add --no-cache apache2 apache2-proxy fcgi postgresql-client postgresql-dev && \
    docker-php-ext-install pdo pdo_pgsql pgsql

# Crear carpeta de sesiones con permisos adecuados
RUN mkdir -p /var/www/html/sessions && chmod 777 /var/www/html/sessions

# Copiar la configuración personalizada de PHP
COPY ./config/php.ini /usr/local/etc/php/conf.d/custom.ini

# Copiar el código fuente de la aplicación
COPY . /var/www/html/

# Ajustar DocumentRoot para que apunte a /var/www/html
RUN sed -i 's!/var/www/localhost/htdocs!/var/www/html!g' /etc/apache2/httpd.conf

# Habilitar módulos necesarios en Apache (rewrite, proxy, proxy_fcgi)
RUN echo 'LoadModule rewrite_module modules/mod_rewrite.so' >> /etc/apache2/httpd.conf && \
    echo 'LoadModule proxy_module modules/mod_proxy.so' >> /etc/apache2/httpd.conf && \
    echo 'LoadModule proxy_fcgi_module modules/mod_proxy_fcgi.so' >> /etc/apache2/httpd.conf

# Configurar Apache para enviar las peticiones PHP a PHP-FPM
RUN echo 'ProxyPassMatch "^/(.*\.php)$" "fcgi://127.0.0.1:9000/var/www/html/$1"' > /etc/apache2/conf.d/fpm.conf

# Permitir el uso de .htaccess (AllowOverride)
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/httpd.conf

# Establecer el índice predeterminado
RUN echo "DirectoryIndex index.php index.html" >> /etc/apache2/conf.d/directoryindex.conf

# Exponer el puerto 80
EXPOSE 80

# Iniciar PHP-FPM y Apache en primer plano
CMD ["sh", "-c", "php-fpm & httpd -DFOREGROUND"]
