# Resmi PHP Apache imajını kullanıyoruz
FROM php:8.2-apache

# Gerekli sistem paketlerini ve PHP eklentilerini kuruyoruz
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libpng-dev \
    zip \
    unzip \
    git \
    && docker-php-ext-install pdo pdo_pgsql pgsql gd

# Apache mod_rewrite'ı aktif ediyoruz (Laravel için kritik)
RUN a2enmod rewrite

# Proje dosyalarını kopyalıyoruz
COPY . /var/www/html

# Apache'nin document root'unu public klasörüne yönlendiriyoruz
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# İzinleri ayarlıyoruz
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Port ayarı
EXPOSE 80
