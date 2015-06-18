https://github.com/ideatosrl/vagrant-php-template<br>
https://github.com/symfony/symfony-installer/


# Prima installazione

`git clone https://Fuminshou@bitbucket.org/Fuminshou/vagrun.git`<br>
`composer install`


# Creare il file .phar

Installare http://box-project.org/<br>
`curl -LSs https://box-project.github.io/box2/installer.php | php`<br>
`chmod a+x box.phar`<br>
`sudo mv box.phar /usr/local/bin/box`<br>
`box build -v`


# File da configurare:

- vagrantconfig.dist.yml
    - rinominarlo in vagrantconfig.yml
    - scegliere la config per i field (opzionali):
        - ram
        - cpus
        - ipaddress
        - name
- provisioning/ideato.database.mysql/vars/main.yml
    - dati configurazione db
- provisioning/ideato.webserver/vars/main.yml
    - virtual host
    - application root dir
    - server alias (attualmente non presente, mandare PR)
- Vagrantfile
    - box
    - synced_folder
    - adattare path vagrantconfig.yml
    - adattare path ansible