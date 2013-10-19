#!/bin/bash

# Global variables initialization
MANTIS_BOOTSTRAP=tests/bootstrap.php


# create database
if [ $DB = 'mysql' ]; then
	mysql -e 'create database bugtracker;'
	DB_USER='root'
elif [ $DB = 'pgsql' ]; then
	psql -c 'CREATE DATABASE bugtracker;' -U postgres
	psql -c "ALTER USER postgres SET bytea_output = 'escape';" -U postgres
	DB_USER='postgres'
fi

if [ $TRAVIS_PHP_VERSION = '5.3' ]; then
	# install Apache as PHP 5.3 does not come with an embedded web server
	sudo apt-get update -qq
	sudo apt-get install -qq apache2 libapache2-mod-php5 php5-mysql php5-pgsql

	cat <<-EOF | sudo tee /etc/apache2/sites-available/default >/dev/null
		<VirtualHost *:80>
		    DocumentRoot $PWD
		    <Directory />
		        Options FollowSymLinks
		        AllowOverride All
		    </Directory>
		    <Directory $PWD>
		        Options Indexes FollowSymLinks MultiViews
		        AllowOverride All
		        Order allow,deny
		        allow from all
		    </Directory>
		</VirtualHost>
		EOF

	sudo service apache2 restart

	# needed to allow web server to create config_inc.php
	chmod 777 .
else
	# use PHP's embedded server
	# get path of PHP as the path is not in $PATH for sudo
	myphp=$(which php)
	# sudo needed for port 80
	sudo $myphp -S localhost:80 &
fi

#  wait until server is up
sleep 10

# trigger installation
curl --data "install=2&hostname=localhost&db_username=${DB_USER}&db_type=${DB}&db_password=&database_name=bugtracker&admin_username=${DB_USER}&admin_password=" http://localhost/admin/install.php

echo " \$g_crypto_master_salt='1234567890abcdef'; " | sudo tee -a config_inc.php


# create the first project
if [ $DB = 'mysql' ]; then
	mysql -e "INSERT INTO mantis_project_table(name, inherit_global) VALUES('First project', 1)" bugtracker
elif [ $DB = 'pgsql' ]; then
	psql -c "INSERT INTO mantis_project_table(name, inherit_global, description) VALUES('First project', 1, '')" -d bugtracker -U postgres
fi


# enable SOAP tests
cat <<-EOF >> $MANTIS_BOOTSTRAP
	<?php
		\$GLOBALS['MANTIS_TESTSUITE_SOAP_ENABLED'] = true;
		\$GLOBALS['MANTIS_TESTSUITE_SOAP_HOST'] = 'http://localhost/api/soap/mantisconnect.php?wsdl';
	EOF
