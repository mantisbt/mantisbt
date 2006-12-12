<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: billing_page.php,v 1.1 2006-12-12 18:26:28 davidnewcomb Exp $
	# --------------------------------------------------------
?>
<?php
	require_once( 'core.php' );
	
	$t_core_path = config_get( 'core_path' );
?>
<?php
/*
	compress_enable();
*/
?>
<?php html_page_top1( lang_get( 'time_tracking_billing_link' )  ) ?>
<?php html_page_top2() ?>

<br />

<?php
	$t_mantis_dir = dirname( __FILE__ ) . DIRECTORY_SEPARATOR;
?>
	<!-- Jump to Bugnote add form -->
<?php
	# Work break-down
	include( $t_mantis_dir . 'billing_inc.php' );
	
	html_page_bottom1( __FILE__ );
?>
