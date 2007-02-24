<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2007  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: test_icons.php,v 1.1 2007-02-24 07:36:20 vboctor Exp $
	# --------------------------------------------------------
?>
<?php
	require_once( dirname( dirname( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'core.php' );

	$t_core_path = config_get( 'core_path' );

	foreach( $g_file_type_icons as $t_ext => $t_filename ) {
		$t_file_path = dirname( dirname( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'fileicons' . DIRECTORY_SEPARATOR . $t_filename;

		echo "Testing language '$t_ext'... $t_file_path ... ";
		flush();

		if ( file_exists( $t_file_path ) ) {
			echo 'OK';
		} else {
			echo '<font color="red">NOT FOUND</font>';
		}
		
		echo '<br />';
	}
?>