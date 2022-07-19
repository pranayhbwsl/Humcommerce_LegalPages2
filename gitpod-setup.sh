#!/bin/bash
DIR="/workspace/magento2gitpod/app/code/Humcommerce/LegalPages"
if [ ! -d "$DIR" ]; then
    #removing module files from root
    folders=(.github Block Controller etc Helper Model Observer Setup Test view )
    files=(.gitignore composer.json LICENSE README.md registration.php)

    for files in "${folders[@]}"; do
        rm -r "$files"
    done
    for file in "${files[@]}"; do
        rm "$file"
    done

    #magento installation
    sudo composer selfupdate --2;
    cd /workspace/magento2gitpod &&
    composer config -g -a http-basic.repo.magento.com 64229a8ef905329a184da4f174597d25 a0df0bec06011c7f1e8ea8833ca7661e &&
    composer create-project --no-interaction --no-progress --repository-url=https://repo.magento.com/ magento/project-community-edition=2.3.5 magento2
    cd magento2 && cp -avr .* /workspace/magento2gitpod;
    cd /workspace/magento2gitpod && rm -r -f magento2;
    mysql -e 'create database magento2;';

    url=$(gp url | awk -F"//" {'print $2'}) && url+="/" && url="https://8002-"$url;

    #starting Redis and ElasticSearch services
    redis-server &
    $ES_HOME79/bin/elasticsearch -d -p $ES_HOME79/pid -Ediscovery.type=single-node &
    sleep 15;

    mysql -u root -padmin@12345 -e 'create database magento2;' &&
    url=$(gp url | awk -F"//" {'print $2'}) && url+="/" && url="https://8002-"$url;cd /workspace/magento2gitpod && composer install -n && php bin/magento setup:install --db-name='magento2' --db-user='root' --db-password='admin@12345' --base-url=$url --backend-frontname='admin' --admin-user='admin' --admin-password='admin@12345' --admin-email='pranay.chahare@hbwsl.com' --admin-firstname='Admin' --admin-lastname='Admin' --use-rewrites='1' --use-secure='1' --base-url-secure=$url --use-secure-admin='1' --language='en_US' --db-host='127.0.0.1' --cleanup-database --timezone='America/New_York' --currency='USD' --session-save='redis'

    n98-magerun2 module:disable Magento_Csp &&
    n98-magerun2 module:disable Magento_TwoFactorAuth &&
    n98-magerun2 setup:upgrade &&

    yes | php bin/magento setup:config:set --session-save=redis --session-save-redis-host=127.0.0.1 --session-save-redis-log-level=3 --session-save-redis-db=0 --session-save-redis-port=6379 &&
    yes | php bin/magento setup:config:set --cache-backend=redis --cache-backend-redis-server=127.0.0.1 --cache-backend-redis-db=1 &&
    yes | php bin/magento setup:config:set --page-cache=redis --page-cache-redis-server=127.0.0.1 --page-cache-redis-db=2

    php bin/magento config:set web/cookie/cookie_path "/" --lock-config &&
    php bin/magento config:set web/cookie/cookie_domain ".gitpod.io" --lock-config &&

    n98-magerun2 cache:clean &&
    n98-magerun2 cache:flush &&
    redis-cli flushall

    echo $url
    #clonning module in the app/code/Humcommerce
    ORIGIN_VALUE=$(git config --get remote.origin.url)

    cd /workspace/magento2gitpod/app && mkdir -p code/Humcommerce && cd code/Humcommerce && git clone $ORIGIN_VALUE && mv magento2gitpod LegalPages

    cd /workspace/magento2gitpod
    rm -rf .git

    php bin/magento indexer:reindex
    php bin/magento setup:upgrade
    php bin/magento setup:static-content:deploy -f
    php bin/magento cache:flush
else
    redis-server &
    $ES_HOME79/bin/elasticsearch -d -p $ES_HOME79/pid -Ediscovery.type=single-node &
    sleep 15;

    mysql -u root -padmin@12345 -e 'create database magento2;'
fi
