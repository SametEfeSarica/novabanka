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

# 6. Apache konfigürasyonunu (Public klasörü için) güncelle
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# 7. İzinleri ayarla
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# 8. KRİTİK NOKTA: Başlangıç komutunu değiştiriyoruz
# Önce migration yapar, başarılı olursa Apache'yi başlatır.
CMD php artisan migrate --force && apache2-foreground

EXPOSE 80
