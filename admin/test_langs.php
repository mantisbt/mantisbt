<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2005  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: test_langs.php,v 1.1 2005-06-12 05:24:32 vboctor Exp $
	# --------------------------------------------------------
?>
<?php
	require_once( dirname( dirname( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'core.php' );

	$t_core_path = config_get( 'core_path' );

	foreach( $g_language_choices_arr as $t_lang ) {
		if ( $t_lang == 'auto' ) {
			continue;
		}

		echo "Testing language '$t_lang'...<br />";
		flush();

		require_once( dirname( dirname( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR . 'strings_' . $t_lang . '.txt' );
	}
?>