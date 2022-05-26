#! /usr/bin/env bash
set -e

echo -e "\nUpdating packages"
apt-get update

echo -e "\nInstalling cURL"
apt-get install curl --assume-yes

echo -e "\nInstalling Node"
curl -fsSL https://deb.nodesource.com/setup_16.x | sudo -E bash -
sudo apt-get install -y nodejs

#echo -e "\nInstalling PHP"
#apt-get install --assume-yes software-properties-common
#LC_ALL=C.UTF-8 add-apt-repository --yes --update ppa:ondrej/php
#sudo add-apt-repository -y --remove ppa:mc3man/trusty-media
#sudo apt-key adv --keyserver hkp://keyserver.ubuntu.com:80 --recv-keys 6494C6D6997C215E
#sudo apt-get update
#sudo apt update
#sudo apt search php7
#sudo apt install php7.1
#apt install --assume-yes php7.1 php7.1-curl php7.1-xml php7.1-mbstring php7.1-mysql

echo -e "\nInstalling Dockerize"
curl --location --silent --show-error https://github.com/jwilder/dockerize/releases/download/v0.6.1/dockerize-linux-amd64-v0.6.1.tar.gz -o dockerize-linux.tar.gz
echo "1fa29cd41a5854fd5423e242f3ea9737a50a8c3bcf852c9e62b9eb02c6ccd370  dockerize-linux.tar.gz" | sha256sum --check --strict
tar -C /usr/local/bin -xzvf dockerize-linux.tar.gz
rm dockerize-linux.tar.gz

echo -e "\nInstalling Composer"
curl --silent --show-error https://getcomposer.org/installer -o composer-setup.php
php composer-setup.php --install-dir=/usr/local/bin --filename=composer php composer-setup.php --version=1.10.10
composer self-update
composer install
