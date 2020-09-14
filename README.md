# ClassPlan

Scheduling utility for the University of Oklahoma.

## Setup

The following database urls can also be set as apache env variables - see the example apache config below.

##### Development
Create a file named ```.env.local``` with your connection information. See ```.env``` for
more details. eg:

    DATABASE_URL=mysql://root:root@127.0.0.1:3306/classplan?serverVersion=5.7
    CLASSNAV_DATABASE_URL=null|mysql://USER:PASS@HOST:PORT/DB?serverVersion=5.7

CLASSNAV can be set to null if you do not have access to the ODS.

##### Production
Create a file named ```.env.local``` with your staging credentials. eg:

    DATABASE_URL=mysql://USER:PASS@HOST:PORT/DB?serverVersion=5.7
    CLASSNAV_DATABASE_URL=mysql://USER:PASS@HOST:PORT/DB?serverVersion=5.7

#### Initialization
Next run composer and the setup commands from the project's root folder:

    composer install -oa
    php bin/console classplan:setup

Next run the import. If you're developing locally you can use the exported datastores
found in ```datastores/Classes.csv```, or if you're on cassapps you can pull from ods directly.

    php bin/console classplan:import --env=dev --source=(book|ods) -n --no-debug

##### Production
Consider running the following in a production setting:

    php bin/console doctrine:ensure-production-settings

#### Example Apache Config
Don't forget any relevant hostfile entries, and to change the environment variables.
APP_ENV should be either dev or prod.

    <VirtualHost *:80>
       ServerName   classplan.ou.test
       ServerAlias  www.classplan.ou.test
    
       ServerAdmin  USER_EMAIL
       SetEnv       APP_ENV "dev"
       SetEnv       APP_SECRET <app-secret-id>
       SetEnv       DATABASE_URL "mysql://db_user:db_pass@host:3306/db_name"
       SetEnv       CLASSNAV_DATABASE_URL "mysql://db_user:db_pass@host:3306/db_name"
       
       DocumentRoot /home/developer/development/personal/classplan/platform/public
       DirectoryIndex /index.php
    
       <Directory "/home/developer/development/personal/classplan/platform/public">
          Options        MultiViews FollowSymLinks
          AllowOverride  All
          # 2.2 config:
          # Order          allow,deny
          # Allow          from all
          # 2.4 config
          Require all granted
          FallbackResource /index.php
       </Directory>
    
    </VirtualHost>

#### CASAPPS Deployment

    cd /var/www/html
    git clone git@github.com:ashinpaugh/classplan-platform.git classplan
    docker exec -it web composer i -oa -d /var/www/html/classplan
    docker exec -it web php /var/www/html/classplan/bin/console classplan:setup --reset
    docker exec -it web php /var/www/html/classplan/bin/console classplan:import --source=ods --no-debug -n --env=dev
    
