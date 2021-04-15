#!/bin/bash -e
# -----------------------------------------------------------------------------
# MantisBT Travis-CI before script
# -----------------------------------------------------------------------------

# Nothing to do here for documentation builds
# Note: Publican is installed via .travis.yml (apt add-on)
if [[ -n $DOCBOOK ]]
then
	exit
fi

# -----------------------------------------------------------------------------
# Global variables initialization
#
HOSTNAME=localhost

# Port 80 requires use of 'sudo' to run the PHP built-in web server, which
# causes builds to fail due to a bug in Travis [1]so we use port 8080 instead.
# [1] https://github.com/travis-ci/travis-ci/issues/2235
PORT=8080
MANTIS_DB_NAME=bugtracker
MANTIS_BOOTSTRAP=tests/bootstrap.php
MANTIS_CONFIG=config/config_inc.php

TIMESTAMP=$(date "+%s")

SQL_CREATE_DB="CREATE DATABASE $MANTIS_DB_NAME;"
SQL_CREATE_PROJECT="INSERT INTO mantis_project_table
	(name, inherit_global, description)
	VALUES
	('Test Project',true,'Travis-CI Test Project');"
SQL_CREATE_VERSIONS="INSERT INTO mantis_project_version_table
	(project_id, version, description, released, obsolete, date_order)
	VALUES
	(1, '1.0.0', 'Obsolete version', true, true, $(($TIMESTAMP - 120))),
	(1, '1.1.0', 'Released version', true, false, $(($TIMESTAMP - 60))),
	(1, '2.0.0', 'Future version', false, false, $TIMESTAMP);"
SQL_CREATE_TAGS="INSERT INTO mantis_tag_table
	(user_id, name, description, date_created, date_updated)
	VALUES
	(0, 'modern-ui', '', $TIMESTAMP, $TIMESTAMP),
	(0, 'patch', '', $TIMESTAMP, $TIMESTAMP);"


# -----------------------------------------------------------------------------

function step () {
	echo "-----------------------------------------------------------------------------"
	echo $1
	echo
}

# -----------------------------------------------------------------------------
step "Travis Before Script initialization"

# PHP version specific setup
case $TRAVIS_PHP_VERSION in
	5.6)
		# Fix deprecated warning in PHP 5.6 builds:
		# "Automatically populating $HTTP_RAW_POST_DATA is deprecated [...]"
		# https://www.bram.us/2014/10/26/php-5-6-automatically-populating-http_raw_post_data-is-deprecated-and-will-be-removed-in-a-future-version/
		# https://bugs.php.net/bug.php?id=66763
		# Generate custom php.ini settings
		cat <<-EOF >mantis_config.ini
			always_populate_raw_post_data=-1
			EOF
		phpenv config-add mantis_config.ini
		;;
esac


# -----------------------------------------------------------------------------
step "Create database $MANTIS_DB_NAME"

case $DB in

	mysql)
		DB_TYPE='mysqli'
		DB_USER='root'
		DB_PASSWORD=''
		DB_CMD='mysql -e'
		DB_CMD_SCHEMA="$MANTIS_DB_NAME"

		$DB_CMD "$SQL_CREATE_DB"
		;;

	pgsql)
		DB_TYPE='pgsql'
		DB_USER='postgres'
		DB_PASSWORD=''
		DB_CMD="psql -U $DB_USER -c"
		DB_CMD_SCHEMA="-d $MANTIS_DB_NAME"

		# Wait a bit to make sure Postgres has started
		sleep 5
		$DB_CMD "$SQL_CREATE_DB"
		$DB_CMD "ALTER USER $DB_USER SET bytea_output = 'escape';"
		;;
esac


# -----------------------------------------------------------------------------
step "Web server setup"

# use PHP's embedded server
if [[ $PORT = 80 ]]
then
	# sudo required for port 80
	# get path of PHP as the path is not in $PATH for sudo
	myphp="sudo $(which php)"
else
	myphp=php
fi
$myphp -S $HOSTNAME:$PORT >& /dev/null &

# needed to allow web server to create config_inc.php
chmod 777 config

#  wait until server is up
sleep 10

# -----------------------------------------------------------------------------
step "MantisBT Installation"

# Define parameters for MantisBT installer
declare -A query=(
	[install]=2
	[db_type]=$DB_TYPE
	[hostname]=$HOSTNAME
	[database_name]=$MANTIS_DB_NAME
	[db_username]=$DB_USER
	[db_password]=$DB_PASSWORD
	[admin_username]=$DB_USER
	[admin_password]=$DB_PASSWORD
	[timezone]=UTC
)

# Build http query string
unset query_string
for param in "${!query[@]}"
do
	value=${query[$param]}
	query_string="${query_string}&${param}=${value}"
done

# trigger installation
curl --data "${query_string:1}" http://$HOSTNAME:$PORT/admin/install.php


# -----------------------------------------------------------------------------
step "Post-installation steps"

echo "Creating project, versions and tags"
$DB_CMD "$SQL_CREATE_PROJECT" $DB_CMD_SCHEMA
$DB_CMD "$SQL_CREATE_VERSIONS" $DB_CMD_SCHEMA
$DB_CMD "$SQL_CREATE_TAGS" $DB_CMD_SCHEMA

echo "Creating API Token"
TOKEN=$($myphp tests/travis_create_api_token.php)

# enable SOAP tests
echo "Creating PHPUnit Bootstrap file"
cat <<-EOF >> $MANTIS_BOOTSTRAP
	<?php
		\$GLOBALS['MANTIS_TESTSUITE_SOAP_ENABLED'] = true;
		\$GLOBALS['MANTIS_TESTSUITE_SOAP_HOST'] = 'http://$HOSTNAME:$PORT/api/soap/mantisconnect.php?wsdl';
		\$GLOBALS['MANTIS_TESTSUITE_REST_ENABLED'] = true;
		\$GLOBALS['MANTIS_TESTSUITE_REST_HOST'] = 'http://$HOSTNAME:$PORT/api/rest/';
		\$GLOBALS['MANTIS_TESTSUITE_API_TOKEN'] = '$TOKEN';
	EOF

echo "Adding custom configuration options"
sudo chmod 777 $MANTIS_CONFIG
cat <<-EOF >> $MANTIS_CONFIG

	# Configs required to ensure all PHPUnit tests are executed
	\$g_allow_no_category = ON;
	\$g_due_date_update_threshold = DEVELOPER;
	\$g_due_date_view_threshold = DEVELOPER;
	\$g_enable_product_build = ON;
	\$g_enable_project_documentation = ON;
	\$g_time_tracking_enabled = ON;
	EOF

step "Before-script execution completed successfully"
