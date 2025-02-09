#!/bin/bash -e
# -----------------------------------------------------------------------------
# MantisBT CI script - Post-installation steps
# -----------------------------------------------------------------------------

MANTIS_BOOTSTRAP=tests/bootstrap.php
MANTIS_CONFIG=config/config_inc.php
MANTIS_ANONYMOUS=anonymous

# Reference date/time for seed data (unix timestamp)
TIMESTAMP=$(date "+%s")

# To ensure we are not hit by unique key constraint when creating anonymous account,
# we generate a random cookie_string like auth_generate_unique_cookie_string() does.
COOKIE_STRING=$(head -c48 /dev/urandom |base64 |tr '+' '-' |tr '/' '_')


# -----------------------------------------------------------------------------
echo "Creating project, versions and tags"

SQL_CREATE_PROJECT="INSERT INTO mantis_project_table
	(name, inherit_global, description)
	VALUES
	('Test Project',true,'Travis-CI Test Project');"
SQL_CREATE_VERSIONS="INSERT INTO mantis_project_version_table
	(project_id, version, description, released, obsolete, date_order)
	VALUES
	(1, '1.0.0', 'Obsolete version', true, true, $((TIMESTAMP - 120))),
	(1, '1.1.0', 'Released version', true, false, $((TIMESTAMP - 60))),
	(1, '2.0.0', 'Future version', false, false, $TIMESTAMP);"
SQL_CREATE_TAGS="INSERT INTO mantis_tag_table
	(user_id, name, description, date_created, date_updated)
	VALUES
	(0, 'modern-ui', '', $TIMESTAMP, $TIMESTAMP),
	(0, 'patch', '', $TIMESTAMP, $TIMESTAMP);"
SQL_CREATE_ANONYMOUS_USER="INSERT INTO mantis_user_table
	(username, realname, email, password, cookie_string,
	 enabled, protected, access_level, last_visit, date_created)
	VALUES
	('$MANTIS_ANONYMOUS', 'Anonymous User', '$MANTIS_ANONYMOUS@localhost',
	 MD5('123456'), '$COOKIE_STRING', '1', '1', 10, $TIMESTAMP, $TIMESTAMP);"

$DB_CMD "$SQL_CREATE_PROJECT"
$DB_CMD "$SQL_CREATE_VERSIONS"
$DB_CMD "$SQL_CREATE_TAGS"
$DB_CMD "$SQL_CREATE_ANONYMOUS_USER"

# -----------------------------------------------------------------------------
echo "Creating API Token"
TOKEN=$(php tests/ci_create_api_token.php)

# enable SOAP tests
# -----------------------------------------------------------------------------
echo "Creating PHPUnit Bootstrap file"
cat <<-EOF >> $MANTIS_BOOTSTRAP
	<?php
		\$GLOBALS['MANTIS_TESTSUITE_SOAP_ENABLED'] = true;
		\$GLOBALS['MANTIS_TESTSUITE_SOAP_HOST'] = 'http://$HOSTNAME:$PORT/api/soap/mantisconnect.php?wsdl';
		\$GLOBALS['MANTIS_TESTSUITE_REST_ENABLED'] = true;
		\$GLOBALS['MANTIS_TESTSUITE_REST_HOST'] = 'http://$HOSTNAME:$PORT/api/rest/';
		\$GLOBALS['MANTIS_TESTSUITE_API_TOKEN'] = '$TOKEN';
	EOF

# -----------------------------------------------------------------------------
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
	\$g_time_tracking_enabled = ON;
	\$g_allow_anonymous_login = ON;
  \$g_anonymous_account = '$MANTIS_ANONYMOUS';
	EOF
