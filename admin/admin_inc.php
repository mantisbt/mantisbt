<?php
	$t_mantis_path = '../';

	require ( $t_mantis_path . 'constant_inc.php' );

	$t_custom_constants = $t_mantis_path . 'custom_constant_inc.php';
	if ( file_exists( $t_custom_constants )) {
		include ( $t_custom_constants );
	}

	require $t_mantis_path . 'config_defaults_inc.php';

	$t_custom_config = $t_mantis_path . 'custom_config_inc.php';
	if ( !file_exists( $t_custom_config )) {
		$t_custom_config = $t_mantis_path . 'config_inc.php';
	}
	
	if ( file_exists( $t_custom_config ) ) {
		include ( $t_custom_config );
	}

	require $t_mantis_path . 'core_database_API.php';
?>