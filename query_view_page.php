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

	$t_query_arr = filter_db_get_available_queries();

	# Special case: if we've deleted our last query, we have nothing to show here.
	if ( sizeof( $t_query_arr ) < 1 ) {
		print_header_redirect( 'view_all_bug_page.php' );
	}

	compress_enable();

	html_page_top1();
	html_page_top2();
	
	$t_use_query_url = 'view_all_set.php?type=3&amp;source_query_id=';
	$t_delete_query_url = 'query_delete_page.php?source_query_id=';
?>
<br />
<div align="center">
<table class="width75" cellspacing="0">
<?php
	$t_column_count = 0;
	$t_max_column_count = 2;
	
	foreach( $t_query_arr as $t_id => $t_name ) {
		if ( $t_column_count == 0 ) {
			print '<tr ' . helper_alternate_class() . '>';
		}

		print '<td>';
		
		print '<a href=' . $t_use_query_url . db_prepare_int( $t_id ) . '>' . string_display( $t_name ) . '</a>';

		if ( filter_db_can_delete_filter( $t_id ) ) {
			print " [<a href=" . $t_delete_query_url . db_prepare_int( $t_id ) . ">" . lang_get( 'delete_query' ) . "</a>]";
		}		
				
		print '</td>';

		$t_column_count++;
		if ( $t_column_count == $t_max_column_count ) {
			print '</tr>';
			$t_column_count = 0;
		}
	} 
	
	# Tidy up this row
	if ( ( $t_column_count > 0 ) && ( $t_column_count < $t_max_column_count ) ) {
		for ( $i = $t_column_count; $i < $t_max_column_count; $i++ ) {
			print '<td>&nbsp;</td>';
		}
		print '</tr>';
	}
?>
</table>
</div>
<?php html_page_bottom1( __FILE__ ) ?>