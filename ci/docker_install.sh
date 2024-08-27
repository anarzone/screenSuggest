#!/bin/zsh

# We need to install dependencies only for Docker
[[ ! -e /.dockerenv ]] && exit 0

set -xe

apt-get update \
&& apt-get install -y \
&& apt-get autoremove -y \
&& docker-php-ext-install mysqli pdo pdo_mysql \
&& apt-get install curl -y \
&& apt-get install git -y\
&& apt-get install zip -y\
&& curl -sS https://get.symfony.com/cli/installer | bash \
&& curl -sS https://getcomposer.org/installer | php
#!/bin/bash

# We need to install dependencies only for Docker
[[ ! -e /.dockerenv ]] && exit 0
apt-get update -qq \
&& apt-get install -qq git \
  # Setup SSH deploy keys
&& 'which ssh-agent || ( apt-get install -qq openssh-client )' \
&& eval $(ssh-agent -s) \
&& ssh-add <(echo "$SSH_PRIVATE_KEY") \
&& mkdir -p ~/.ssh \
&& '[[ -f /.dockerenv ]] && echo -e "Host *\n\tStrictHostKeyChecking no\n\n" > ~/.ssh/config'
#php_test_run.sh
php bin/console cache:clear --env=test
php bin/console doctrine:database:drop --force --env=test
php bin/console doctrine:database:create --env=test
php bin/console doctrine:schema:update --force --env=test
php bin/phpunit
