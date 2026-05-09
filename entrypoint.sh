#!/bin/bash

# Veritabanı tablolarını kontrol et ve migrate et
php artisan migrate --force

# Apache'yi başlat
apache2-foreground
