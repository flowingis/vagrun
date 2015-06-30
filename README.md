# Vagrun

Vagrun is a command-line tool that helps you to start and configure a PHP Vagrant machine from scratch. 
Vagrun is based on [vagrant-php-template](https://github.com/ideatosrl/vagrant-php-template) project.

See [documentation](http://ideatosrl.github.io/vagrun/) for further information.

[![Build Status](https://travis-ci.org/ideatosrl/vagrun.svg?branch=master)](https://travis-ci.org/ideatosrl/vagrun)

## How to install

`curl http://ideatosrl.github.io/vagrun/vagrun.phar > vagrun.phar`

## Usage

### Initialization

`vagrun.phar init [--path=/your/path]`

### Configuration

`vagrun.phar config [--path=/your/path]`

### Erase installation

>**WARNING:** use this command at your own risk

>The following code will **delete** all the configuration files and the directory .vagrant

`vagrun.phar cleanup [--path=/your/path]`

If you are sure to delete vagrant configuration and directory you could use the option 
`--force` 
to erase the installation without being prompted for confirmation.


## Global installation

See [documentation](http://ideatosrl.github.io/vagrun/) for further information.

## Requirements

PHP 5.4.0 or above.

## Authors

- Simone '[dymissy](https://github.com/dymissy)' D'Amico sd@ideato.it
- Nicole '[Fuminshou](https://github.com/Fuminshou)' Bartolini nb@ideato.it
- [other contributors](https://github.com/ideatosrl/vagrun/graphs/contributors)

## License

Vagrun is licensed under the MIT License - see the LICENSE file for details.