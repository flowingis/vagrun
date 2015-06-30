# Prima installazione

`git clone https://github.com/ideatosrl/vagrun.git`

`composer install`


# Creare il file .phar

Installare http://box-project.org/

`curl -LSs https://box-project.github.io/box2/installer.php | php`

`chmod a+x box.phar`

`sudo mv box.phar /usr/local/bin/box`

`box build -v`
