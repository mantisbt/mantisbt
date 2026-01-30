#!/bin/bash -e
# -----------------------------------------------------------------------------
# MantisBT CI script - Post-installation steps
# -----------------------------------------------------------------------------

MANTIS_BOOTSTRAP=tests/bootstrap.php
MANTIS_CONFIG=config/config_inc.php
MANTIS_ANONYMOUS=anonymous

# -----------------------------------------------------------------------------
echo "Creating project, versions and tags"
php tests/ci_create_test_project.php

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
	\$g_allow_anonymous_login = ON;
	\$g_anonymous_account = '$MANTIS_ANONYMOUS';
	EOF
