<?php
	# Unable to use DIRECTORY_SEPARATOR because it may not be defined at 
	# this stage.
	require_once( '../core/php_api.php' );

	$t_mantis_path = dirname( dirname( __FILE__ ) ) . DIRECTORY_SEPARATOR;

	require_once( $t_mantis_path . 'constant_inc.php' );

	$t_custom_constants = $t_mantis_path . 'custom_constant_inc.php';
	if ( file_exists( $t_custom_constants )) {
		require_once( $t_custom_constants );
	}

	require_once( $t_mantis_path . 'config_defaults_inc.php' );

	$t_custom_config = $t_mantis_path . 'custom_config_inc.php';
	if ( !file_exists( $t_custom_config )) {
		$t_custom_config = $t_mantis_path . 'config_inc.php';
	}
	
	if ( file_exists( $t_custom_config ) ) {
		require_once( $t_custom_config );
	}

	require_once( $g_core_path . 'database_api.php' );
	require_once( $g_core_path . 'config_api.php' );
	require_once( $g_core_path . 'obsolete.php' );

	# Checks whether the specified field in the specified database exists. 
	# If not, a message is displayed and the script exits.
	#
	# This function should be used to check whether an update was applied.
	function check_applied($version, $table, $field='') {
		global $PHP_SELF;
		
		$result = mysql_query("DESCRIBE $table $field");
		
		if ($result && db_num_rows($result)) { # Field exists -> update was applied ?>
<html>
<head><title>Error upgrading the database</title></head>
<body>
<h2>Error: update already applied</h2>
The upgrade script has determined that your database has already been converted to a format suitable for Mantis <?php echo $version; ?> or higher.<P>
<?php if (!ereg('admin_upgrade\.php$', $PHP_SELF)) { ?>
<a href="admin_upgrade.php">Select a different upgrade</a>
<?php } ?>
</body></html><?php

			exit;
		}
	}
?>