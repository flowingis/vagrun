https://github.com/ideatosrl/vagrant-php-template
https://github.com/symfony/symfony-installer/

File da configurare:
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

        
