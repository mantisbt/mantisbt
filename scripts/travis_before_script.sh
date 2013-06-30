#!/bin/sh

# create database
if [ $DB = 'mysql' ]; then
	mysql -e 'create database bugtracker;'
	DB_USER='root'
elif [ $DB = 'pgsql' ]; then
	psql -c 'CREATE DATABASE bugtracker;' -U postgres
	psql -c "ALTER USER postgres SET bytea_output = 'escape';" -U postgres
	DB_USER='postgres'
fi

# start embedded server
php -S localhost:8000 &
sleep 5

# trigger installation
curl --data "install=2&hostname=localhost&db_username=${DB_USER}&db_type=${DB}&db_password=&database_name=bugtracker&admin_username=${DB_USER}&admin_password=" http://localhost:8000/admin/install.php

# create the first project
if [ $DB = 'mysql' ]; then
	mysql -e "INSERT INTO mantis_project_table(name, inherit_global) VALUES('First project', 1)" bugtracker
elif [ $DB = 'pgsql' ]; then
	psql -c "INSERT INTO mantis_project_table(name, inherit_global, description) VALUES('First project', 1, '')" -d bugtracker -U postgres
fi

# enable SOAP tests
echo "<?php \$GLOBALS['MANTIS_TESTSUITE_SOAP_ENABLED'] = true;  \$GLOBALS['MANTIS_TESTSUITE_SOAP_HOST'] = 'http://localhost:8000/api/soap/mantisconnect.php?wsdl';?>" > ./tests/bootstrap.php