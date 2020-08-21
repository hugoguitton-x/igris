## build
FROM node:latest AS build
RUN mkdir /usr/src/app
WORKDIR /usr/src/app
ENV PATH ./node_modules/.bin:$PATH
COPY package.json ./package.json
RUN npm install --silent
COPY . .
RUN npm run build

## production
FROM php:7.4-fpm

RUN docker-php-ext-install pdo_mysql

RUN pecl install apcu

RUN apt-get update && \
apt-get install -y \
libzip-dev libpq-dev libicu-dev cron

RUN docker-php-ext-install zip
RUN docker-php-ext-enable apcu
RUN docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql
RUN docker-php-ext-install pdo pdo_pgsql
RUN docker-php-ext-install intl

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php -r "if (hash_file('sha384', 'composer-setup.php') === 'e0012edf3e80b6978849f5eff0d4b4e4c79ff1609dd1e613307e16318854d24ae64f26d17af3ef0bf7cfb710ca74755a') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;" \
    && php composer-setup.php --filename=composer \
    && php -r "unlink('composer-setup.php');" \
    && mv composer /usr/local/bin/composer
    
ENV TZ=Europe/Paris
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

WORKDIR /usr/src/app

# UID dépendant de la machine (echo $UID)
COPY --chown=1001:1001 . /usr/src/app

WORKDIR /usr/src/app/public

COPY --from=build /usr/src/app/public/build .

RUN usermod -u 1001 www-data
RUN usermod -G staff www-data

RUN PATH=$PATH:/usr/src/apps/vendor/bin:bin

RUN chmod +x /usr/src/app/entrypoint.sh

ENTRYPOINT ["/usr/src/app/entrypoint.sh"]