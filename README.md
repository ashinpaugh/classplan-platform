#ClassPlan

Scheduling utility for the University of Oklahoma.

##Setup

#####Development
Create a file named ```.env.local``` with your connection information. See ```.env``` for
more details. eg:

    DATABASE_URL=mysql://root:root@127.0.0.1:3306/classplan?serverVersion=5.7

#####Production
Create a file named ```.env.local``` with your staging credentials. eg:

    CLASSPLAN_DATABASE_URL=mysql://USER:PASS@HOST:PORT/DB?serverVersion=5.7
    CLASSNAV_DATABASE_URL=mysql://USER:PASS@HOST:PORT/DB?serverVersion=5.7

####Initialization
Next run composer and the setup commands from the project's root folder:

    composer install -oa
    php bin/console classplan:setup

Next run the import. If you're developing locally you can use the exported datastores
found in ```datastores/Classes.csv```, or if you're on cassapps you can pull from ods directly.

    php bin/console classplan:import --source=ods -b

####Example Apache Config
Don't forget any relevant hostfile entries!

    <VirtualHost *:80>
       ServerName   classplan.ou.test
       ServerAlias  www.classplan.ou.test
    
       ServerAdmin  USER_EMAIL
       SetEnv       APPLICATION_ENV "development"
       DocumentRoot /home/developer/development/personal/classplan/platform/public
    
       <Directory "/home/developer/development/personal/classplan/platform/public">
          DirectoryIndex index.php index.html
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
