<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: manage_proj_cat_add.php,v 1.31 2005-02-12 20:01:05 jlatour Exp $
	# --------------------------------------------------------
?>
<?php
	require_once( 'core.php' );

	$t_core_path = config_get( 'core_path' );

	require_once( $t_core_path.'category_api.php' );
?>
<?php
	$f_project_id	= gpc_get_int( 'project_id' );
	$f_category		= gpc_get_string( 'category' );

	access_ensure_project_level( config_get( 'manage_project_threshold' ), $f_project_id );

	if ( is_blank( $f_category ) ) {
		trigger_error( ERROR_EMPTY_FIELD, ERROR );
	}

	$t_categories = explode( '|', $f_category );
	$t_category_count = count( $t_categories );

	foreach ( $t_categories as $t_category ) {
		if ( is_blank( $t_category ) ) {
			continue;
		}

		$t_category = trim( $t_category );
		if ( category_is_unique( $f_project_id, $t_category ) ) {
			category_add( $f_project_id, $t_category );
		} else if ( 1 == $t_category_count ) {
			# We only error out on duplicates when a single value was
			#  given.  If multiple values were given, we just add the
			#  ones we can.  The others already exist so it isn't really
			#  an error.

			trigger_error( ERROR_CATEGORY_DUPLICATE, ERROR );
		}
	}

	$t_redirect_url = 'manage_proj_edit_page.php?project_id=' . $f_project_id;
?>
<?php
	html_page_top1();

	html_meta_redirect( $t_redirect_url );

	html_page_top2();
?>
<br />
<div align="center">
<?php
	echo lang_get( 'operation_successful' ) . '<br />';

	print_bracket_link( $t_redirect_url, lang_get( 'proceed' ) );
?>
</div>

<?php html_page_bottom1( __FILE__ ) ?>
