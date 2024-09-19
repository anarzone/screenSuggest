#!/bin/zsh

# We need to install dependencies only for Docker
[[ ! -e /.dockerenv ]] && exit 0

set -xe

apt-get update \
&& apt-get install -y \
&& apt-get autoremove -y \
&& docker-php-ext-install mysqli pdo pdo_mysql zip\
&& apt-get install curl -y \
&& apt-get install git -y\
&& apt-get install libzip-dev zip -y\
&& curl -sS https://get.symfony.com/cli/installer | bash \
&& curl -sS https://getcomposer.org/installer | php
#!/bin/bash