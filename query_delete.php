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

	$f_query_id = gpc_get_int( 'source_query_id' );
	$t_redirect_url = 'query_view_page.php';

	if ( ! filter_db_can_delete_filter( $f_query_id ) ) {	
		print_header_redirect( $t_redirect_url );
	}
	else
	{	
		html_page_top1();
		html_page_top2();
		filter_db_delete_filter( $f_query_id );
		?>
		<br />
		<div align="center">
		<center><b><?php print filter_db_get_name( $f_query_id ) . ' ' . lang_get( 'query_deleted' ); ?></b></center>
		<form method="post" action="<?php print $t_redirect_url; ?>">
		<input type="submit" class="button" value="<?php print lang_get( 'go_back' ); ?>"/>
		</form>
	
		<?php
		echo '</div>';
		html_page_bottom1( __FILE__ );
	}
	?>
