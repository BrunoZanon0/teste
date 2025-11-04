FROM php:8.2-apache

WORKDIR /var/www/html

# Instala dependências do sistema
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev

# Limpa cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Instala extensões PHP necessárias
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Habilita mod_rewrite do Apache
RUN a2enmod rewrite

# Copia arquivos da aplicação
COPY . .

# Torna o arquivo zanon executável
RUN chmod +x zanon

# Instala Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Instala dependências do projeto
RUN if [ -f "composer.json" ]; then composer install --no-dev --optimize-autoloader; fi

# Configura permissões
RUN chown -R www-data:www-data /var/www/html

# Configuração do Apache DIRETAMENTE no Dockerfile
RUN echo '<VirtualHost *:80>\n\
    ServerAdmin webmaster@localhost\n\
    DocumentRoot /var/www/html\n\
    \n\
    <Directory /var/www/html>\n\
        Options Indexes FollowSymLinks\n\
        AllowOverride All\n\
        Require all granted\n\
        \n\
        # Configurações específicas para API\n\
        RewriteEngine On\n\
        \n\
        # Redireciona todas as requisições para o index.php\n\
        RewriteCond %{REQUEST_FILENAME} !-f\n\
        RewriteCond %{REQUEST_FILENAME} !-d\n\
        RewriteRule ^ index.php [QSA,L]\n\
    </Directory>\n\
    \n\
    ErrorLog ${APACHE_LOG_DIR}/error.log\n\
    CustomLog ${APACHE_LOG_DIR}/access.log combined\n\
</VirtualHost>' > /etc/apache2/sites-available/000-default.conf

# Expõe porta
EXPOSE 80

CMD ["apache2-foreground"]