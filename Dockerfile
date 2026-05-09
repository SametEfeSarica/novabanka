FROM php:8.2-apache

# 1. Sistem paketlerini ve PostgreSQL sürücülerini kur
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libpng-dev \
    zip \
    unzip \
    git \
    && docker-php-ext-install pdo pdo_pgsql pgsql gd

# 2. Composer'ı ekle
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 3. Apache mod_rewrite aktif et
RUN a2enmod rewrite

# 4. Proje dosyalarını kopyala
COPY . /var/www/html

# 5. Çalışma dizini ve bağımlılıkların kurulumu
WORKDIR /var/www/html
RUN composer install --no-interaction --optimize-autoloader --no-dev

# 6. Apache konfigürasyonunu güncelle
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# 7. İzinleri en geniş kapsamda ayarla (Hata almamak için kritik)
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage \
    && chmod -R 775 /var/www/html/bootstrap/cache

# 8. Başlangıç komutu
# Önbelleği temizler, tabloları basar ve sunucuyu başlatır
CMD php artisan config:clear && php artisan migrate --force && apache2-foreground

EXPOSE 80
