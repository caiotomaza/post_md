FROM php:8.2-fpm

# Instalar dependências do sistema e bibliotecas para o PostgreSQL
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libicu-dev \
    zip \
    unzip \
    libpq-dev

# Limpar cache do apt
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Instalar extensões do PHP necessárias para o Laravel e Postgres
RUN docker-php-ext-install pdo_pgsql mbstring exif pcntl bcmath gd intl

# Obter o Composer mais recente
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Definir diretório de trabalho padrão
WORKDIR /var/www/html
