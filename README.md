# Vagrun

Vagrun is a command-line tool that helps you to start and configure a PHP Vagrant machine from scratch. 
Vagrun is based on [vagrant-php-template](https://github.com/ideatosrl/vagrant-php-template) project.

Please, take a look at [documentation](http://ideatosrl.github.io/vagrun/) for further information and examples.

[![Build Status](https://travis-ci.org/ideatosrl/vagrun.svg?branch=master)](https://travis-ci.org/ideatosrl/vagrun)

## Requirements

PHP 5.4.0 or above.

## Installation

`curl http://ideatosrl.github.io/vagrun/vagrun.phar > your/path/vagrun.phar`

## Global installation

`sudo mv your/path/vagrun.phar /usr/local/bin/vagrun`

## Usage

### Initialization

`vagrun init [--path=/your/path]`

### Configuration

`vagrun config [--path=/your/path]`

### Erase installation

>**WARNING:** use this command at your own risk

>The following code will **delete** all the configuration files and the directory .vagrant

`vagrun cleanup [--path=/your/path]`

If you are sure to delete vagrant configuration and `.vagrant` directory you could use the option 
`--force` to erase the installation without being prompted for confirmation.

## Authors

- Simone '[dymissy](https://github.com/dymissy)' D'Amico sd@ideato.it
- Nicole '[Fuminshou](https://github.com/Fuminshou)' Bartolini nb@ideato.it
- [other contributors](https://github.com/ideatosrl/vagrun/graphs/contributors)

## License

Vagrun is licensed under the MIT License - see the LICENSE file for details.