<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2003  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php
	require_once( 'core.php' );
	$t_core_path = config_get( 'core_path' );
	
	require_once( $t_core_path.'compress_api.php' );
	require_once( $t_core_path.'filter_api.php' );
	require_once( $t_core_path.'current_user_api.php' );
	require_once( $t_core_path.'bug_api.php' );
	require_once( $t_core_path.'string_api.php' );
	require_once( $t_core_path.'date_api.php' );

	auth_ensure_user_authenticated();

	compress_enable();

	html_page_top1();
	html_page_top2();
?>
	<br />
	<div align="center">
<?php
	$t_query_to_store = filter_db_get_filter( gpc_get_cookie( config_get( 'view_all_cookie' ), '' ) );
	$t_query_arr = filter_db_get_available_queries();
	
	# Let's just see if any of the current filters are the
	# same as the one we're about the try and save
	foreach( $t_query_arr as $t_id => $t_name ) {
		if ( filter_db_get_filter( $t_id ) == $t_query_to_store ) {
			print lang_get( 'query_exists' ) . ' (' . $t_name . ')<br />';
		}
	} 
	
	# Check for an error
	$t_error_msg = gpc_get_string( 'error_msg', null );
	if ( $t_error_msg != null ) {
		print "<br />$t_error_msg<br /><br />";
	}
	
	print lang_get( 'query_name' ) . ': ';
?>
	<form method="POST" action="query_store.php">
	<input type="text" name="query_name"><br />
	<?php
	if ( access_has_project_level( config_get( 'stored_query_create_shared_threshold' ) ) ) {
		print '<input type="checkbox" name="is_public" value="on"> ';
		print lang_get( 'make_public' );
		print '<br />';
	}
	?>
	<input type="checkbox" name="all_projects" value="on" <?php check_checked( ALL_PROJECTS == helper_get_current_project() ) ?> >
	<?php print lang_get( 'all_projects' ); ?><br /><br />
	<input type="submit" class="button" value="<?php print lang_get( 'save_query' ); ?>">
	</form>
	<form action="view_all_bug_page.php">
	<input type="submit" class="button" value="<?php print lang_get( 'go_back' ); ?>">
	</form>
<?php
	echo '</div>';
	html_page_bottom1( __FILE__ );
?>
