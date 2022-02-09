Additional MYSQL query
SET GLOBAL sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));

If you are using docker
run docker exec -it pmeis-service-app /bin/bash

Inside the container run:
    composer install
    php bin/console doctrine:migration:migrate
    mkdir -p config/jwt
    openssl genpkey -out config/jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096
    openssl pkey -in config/jwt/private.pem -out config/jwt/public.pem -pubout
    Note: passphrase is in .env JWT_PASSPHRASE
    chmod 775 -R config/jwt/
