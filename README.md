# Vagrun

Vagrun is a command-line tool that helps you to start and configure a PHP Vagrant machine from scratch. 
Vagrun is based on [vagrant-php-template](https://github.com/ideatosrl/vagrant-php-template) project.

[![Build Status](https://travis-ci.org/ideatosrl/vagrun.svg?branch=master)](https://travis-ci.org/ideatosrl/vagrun)

# First install

`git clone https://github.com/ideatosrl/vagrun.git`

`composer install`


# How to create .phar file

Install http://box-project.org/

`curl -LSs https://box-project.github.io/box2/installer.php | php`

`chmod a+x box.phar`

`sudo mv box.phar /usr/local/bin/box`

`box build -v`
