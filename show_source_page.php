<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2003  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php require_once( 'core.php' ) ?>
<?php
	access_ensure_global_level( ADMINISTRATOR );

	$f_url = gpc_get_string( 'url' );

	# Check to make sure that the access is legal
	# NOTE: enabling this could be a bad idea
	if ( ON != config_get( 'show_source' ) ) {
		access_denied();
	}
?>
<?php print_page_top1() ?>
<?php print_page_top2() ?>

<br />
<div align="left">
<?php
	echo lang_get( 'show_source_for_msg' ) . ": $f_url<br />";

	# Print source
	show_source( $f_url );
?>
</div>

<?php print_page_bot1( __FILE__ ) ?>
