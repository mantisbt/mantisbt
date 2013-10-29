#!/bin/bash -e
# -----------------------------------------------------------------------------
# MantisBT Travis-CI before script
# -----------------------------------------------------------------------------

# Global variables initialization
HOSTNAME=localhost
MANTIS_DB_NAME=bugtracker
MANTIS_BOOTSTRAP=tests/bootstrap.php

SQL_CREATE_DB="CREATE DATABASE $MANTIS_DB_NAME;"
SQL_CREATE_PROJECT="INSERT INTO mantis_project_table
	(name, inherit_global, description)
	VALUES
	('Test Project',1,'Travis-CI Test Project');"


# -----------------------------------------------------------------------------

function step () {
	echo "-----------------------------------------------------------------------------"
	echo $1
	echo
}

# -----------------------------------------------------------------------------
step "Create database $MANTIS_DB_NAME"

case $DB in

	mysql)
		DB_USER='root'
		DB_PASSWORD=''
		DB_CMD='mysql -e'
		DB_CMD_SCHEMA="$MANTIS_DB_NAME"

		$DB_CMD "$SQL_CREATE_DB"
		;;

	pgsql)
		DB_USER='postgres'
		DB_PASSWORD=''
		DB_CMD="psql -U $DB_USER -c"
		DB_CMD_SCHEMA="-d $MANTIS_DB_NAME"

		$DB_CMD "$SQL_CREATE_DB"
		$DB_CMD "ALTER USER $DB_USER SET bytea_output = 'escape';"
		;;
esac


# -----------------------------------------------------------------------------
step "Web server setup"

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
	sudo $myphp -S $HOSTNAME:80 &
fi

#  wait until server is up
sleep 10


# -----------------------------------------------------------------------------
step "MantisBT Installation"

# Define parameters for MantisBT installer
declare -A query=(
	[install]=2
	[db_type]=$DB
	[hostname]=$HOSTNAME
	[database_name]=$MANTIS_DB_NAME
	[db_username]=$DB_USER
	[db_password]=$DB_PASSWORD
	[admin_username]=$DB_USER
	[admin_password]=$DB_PASSWORD
)

# Build http query string
unset query_string
for param in "${!query[@]}"
do
	value=${query[$param]}
	query_string="${query_string}&${param}=${value}"
done

# trigger installation
curl --data "${query_string:1}" http://$HOSTNAME/admin/install.php


# -----------------------------------------------------------------------------
step "Post-installation steps"

echo "Creating project"
$DB_CMD "$SQL_CREATE_PROJECT" $DB_CMD_SCHEMA


# enable SOAP tests
echo "Creating PHPUnit Bootstrap file"
cat <<-EOF >> $MANTIS_BOOTSTRAP
	<?php
		\$GLOBALS['MANTIS_TESTSUITE_SOAP_ENABLED'] = true;
		\$GLOBALS['MANTIS_TESTSUITE_SOAP_HOST'] = 'http://$HOSTNAME/api/soap/mantisconnect.php?wsdl';
	EOF

step "Before-script execution completed successfully"
