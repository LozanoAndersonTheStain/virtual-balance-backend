# Imagen base PHP con Apache
FROM php:8.2-apache

# Instalar extensiones de PHP necesarias
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Habilitar mod_rewrite de Apache
RUN a2enmod rewrite

# Configurar DocumentRoot
RUN sed -i 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf

# Establecer directorio de trabajo
WORKDIR /var/www/html

# Copiar archivos del proyecto
COPY . /var/www/html/

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Instalar dependencias
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Crear directorio de logs con permisos
RUN mkdir -p logs && chmod -R 755 logs

# Configurar permisos
RUN chown -R www-data:www-data /var/www/html

# Exponer puerto 80
EXPOSE 80

# Comando de inicio
CMD ["apache2-foreground"]
