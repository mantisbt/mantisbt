<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: print_all_bug_page.php,v 1.80 2004-07-24 14:31:43 vboctor Exp $
	# --------------------------------------------------------
?>
<?php
	# Bugs to display / print / export can be selected with the checkboxes
	# A printing Options link allows to choose the fields to export
	# Export :
	#	- the bugs displayed in print_all_bug_page.php are saved in a .doc or .xls file
	#   - the IE icons allows to see or directly print the same result
?>
<?php
	require_once( 'core.php' );

	$t_core_path = config_get( 'core_path' );

	require_once( $t_core_path.'current_user_api.php' );
	require_once( $t_core_path.'bug_api.php' );
	require_once( $t_core_path.'date_api.php' );
	require_once( $t_core_path.'icon_api.php' );
	require_once( $t_core_path.'string_api.php' );
?>
<?php auth_ensure_user_authenticated( ) ?>
<?php
	$f_search		= gpc_get_string( 'search', false ); # @@@ need a better default
	$f_offset		= gpc_get_int( 'offset', 0 );

	$t_cookie_value_id = gpc_get_cookie( config_get( 'view_all_cookie' ), '' );
	$t_cookie_value = filter_db_get_filter( $t_cookie_value_id );

	$f_highlight_changed 	= 0;
	$f_sort 				= null;
	$f_dir		 			= null;
	$t_project_id 			= 0;

	# check to see if the cookie exists
	if ( ! is_blank( $t_cookie_value ) ) {

		# check to see if new cookie is needed
		if ( ! filter_is_cookie_valid() ) {
			print_header_redirect( 'view_all_set.php?type=0&amp;print=1' );
		}

		$t_setting_arr = explode( '#', $t_cookie_value, 2 );
		$t_filter_cookie_arr = unserialize( $t_setting_arr[1] );
	
		$f_highlight_changed 	= $t_filter_cookie_arr['highlight_changed'];
		$f_sort 				= $t_filter_cookie_arr['sort'];
		$f_dir		 			= $t_filter_cookie_arr['dir'];
		$t_project_id 			= helper_get_current_project( );
	}

	# This replaces the actual search that used to be here
	$f_page_number = gpc_get_int( 'page_number', 1 );
	$t_per_page = -1;
	$t_bug_count = null;
	$t_page_count = null;
	
	$result = filter_get_bug_rows( $t_page_number, $t_per_page, $t_page_count, $t_bug_count );
	$row_count = sizeof( $result );

	# for export
	$t_show_flag = gpc_get_int( 'show_flag', 0 );
?>
<?php html_page_top1( ) ?>
<?php html_head_end( ) ?>
<?php html_body_begin( ) ?>

<table class="width100"><tr><td class="form-title">
	<div class="center">
		<?php echo config_get( 'window_title' ) . ' - ' . project_get_name( $t_project_id ); ?>
	</div>
</td></tr></table>

<br />

<form method="post" action="view_all_set.php">
<input type="hidden" name="type" value="1" />
<input type="hidden" name="print" value="1" />
<input type="hidden" name="offset" value="0" />
<input type="hidden" name="sort" value="<?php echo $f_sort ?>" />
<input type="hidden" name="dir" value="<?php echo $f_dir ?>" />

<table class="width100">
<?php
	#<SQLI> Excel & Print export
	#$f_bug_array stores the number of the selected rows
	#$t_bug_arr_sort is used for displaying
	#$f_export is a string for the word and excel pages

	$f_bug_arr = gpc_get_int_array( 'bug_arr', array() );
	$f_bug_arr[$row_count]=-1;

	for( $i=0; $i < $row_count; $i++ ) {
		if ( isset( $f_bug_arr[$i] ) ) {
			$index = $f_bug_arr[$i];
			$t_bug_arr_sort[$index]=1;
		}
	}
	$f_export = implode( ',', $f_bug_arr );

	$t_icon_path = config_get( 'icon_path' );
?>

<tr>
	<td colspan="8">
<?php
		if ( 'DESC' == $f_dir ) {
			$t_new_dir = 'ASC';
		} else {
			$t_new_dir = 'DESC';
		}

		$t_search = urlencode( $f_search );

		$t_icons = array(
			array( 'print_all_bug_page_excel', 'excel', '', 'excelicon.gif', 'Excel 2000' ),
			array( 'print_all_bug_page_excel', 'html', 'target="_blank"', 'ieicon.gif', 'Excel View' ),
			array( 'print_all_bug_page_word', 'word', '', 'wordicon.gif', 'Word 2000' ),
			array( 'print_all_bug_page_word', 'html', 'target="_blank"', 'ieicon.gif', 'Word View' ) );

		foreach ( $t_icons as $t_icon ) {
			echo '<a href="' . $t_icon[0] . '.php' .
				"?search=$t_search" .
				"&amp;sort=$f_sort" .
				"&amp;dir=$t_new_dir" .
				'&amp;type_page=' . $t_icon[1] .
				"&amp;export=$f_export" .
				"&amp;show_flag=$t_show_flag" .
				'" ' . $t_icon[2] . '>' .
				'<img src="' . $t_icon_path . $t_icon[3] . '" border="0" align="absmiddle" alt="' . $t_icon[4] . '"></a> ';
		}
?>
	</td>
</tr>
<?php #<SQLI> ?>
</table>

</form>

<br />

<form method="post" action="print_all_bug_page.php">
<table class="width100" cellspacing="1">
<tr>
	<td class="form-title" colspan="6">
		<?php echo lang_get( 'viewing_bugs_title' ) ?>
		<?php
			if ( $row_count > 0 ) {
				$v_start = $f_offset+1;
				$v_end   = $f_offset+$row_count;
			} else {
				$v_start = 0;
				$v_end   = 0;
			}
			PRINT "( $v_start - $v_end )";
		?>
	</td>
	<td class="right" colspan="3">
		<?php print_bracket_link( 'print_all_bug_options_page.php', lang_get( 'printing_options_link' ) ) ?>
		<?php print_bracket_link( 'view_all_bug_page.php', lang_get( 'view_bugs_link' ) ) ?>
		<?php print_bracket_link( 'summary_page.php', lang_get( 'summary' ) ) ?>
	</td>
</tr>
<tr class="row-category">
	<td class="center" width="2%">&nbsp;</td>
	<td class="center" width="8%">
		<?php print_view_bug_sort_link2( 'P', 'priority', $f_sort, $f_dir ) ?>
		<?php print_sort_icon( $f_dir, $f_sort, 'priority' ) ?>
	</td>
	<td class="center" width="8%">
		<?php print_view_bug_sort_link2( lang_get( 'id' ), 'id', $f_sort, $f_dir ) ?>
		<?php print_sort_icon( $f_dir, $f_sort, 'id' ) ?>
	</td>
	<td class="center" width="3%">
		#
	</td>
	<td class="center" width="12%">
		<?php print_view_bug_sort_link2( lang_get( 'category' ), 'category', $f_sort, $f_dir ) ?>
		<?php print_sort_icon( $f_dir, $f_sort, 'category' ) ?>
	</td>
	<td class="center" width="10%">
		<?php print_view_bug_sort_link2( lang_get( 'severity' ), 'severity', $f_sort, $f_dir ) ?>
		<?php print_sort_icon( $f_dir, $f_sort, 'severity' ) ?>
	</td>
	<td class="center" width="10%">
		<?php print_view_bug_sort_link2( lang_get( 'status' ), 'status', $f_sort, $f_dir ) ?>
		<?php print_sort_icon( $f_dir, $f_sort, 'status' ) ?>
	</td>
	<td class="center" width="12%">
		<?php print_view_bug_sort_link2( lang_get( 'updated' ), 'last_updated', $f_sort, $f_dir ) ?>
		<?php print_sort_icon( $f_dir, $f_sort, 'last_updated' ) ?>
	</td>
	<td class="center" width="37%">
		<?php print_view_bug_sort_link2( lang_get( 'summary' ), 'summary', $f_sort, $f_dir ) ?>
		<?php print_sort_icon( $f_dir, $f_sort, 'summary' ) ?>
	</td>
</tr>
<tr>
	<td class="spacer" colspan="9">&nbsp;</td>
</tr>
<?php
	for( $i=0; $i < $row_count; $i++ ) {
		# prefix bug data with v_
		extract( $result[$i], EXTR_PREFIX_ALL, 'v' );

		$v_summary = string_display_links( $v_summary );
		$t_last_updated = date( $g_short_date_format, $v_last_updated );

		# alternate row colors
		$status_color = helper_alternate_colors( $i, '#ffffff', '#dddddd' );

		# grab the bugnote count
		$bugnote_count = bug_get_bugnote_count( $v_id );

		# grab the project name
		$project_name = project_get_field( $v_project_id, 'name' );

		$query = "SELECT MAX( last_modified )
				FROM $g_mantis_bugnote_table
				WHERE bug_id='$v_id'";
		$res2 = db_query( $query );
		$v_bugnote_updated = db_result( $res2, 0, 0 );

		if ( isset( $t_bug_arr_sort[$i] ) || ( $t_show_flag==0 ) ) {
?>

<tr>
	<td class="print" bgcolor="<?php echo $status_color ?>">
		<input type="checkbox" name="bug_arr[]" value="<?php echo $i ?>" />
	</td>
	<td class="print" bgcolor="<?php echo $status_color ?>">
		<?php print_formatted_priority_string( $v_status, $v_priority ) ?>
	</td>
	<td class="print" bgcolor="<?php echo $status_color ?>">
		<?php echo bug_format_id( $v_id ) ?>
	</td>
	<td class="print" bgcolor="<?php echo $status_color ?>">
		<?php
			if ( $bugnote_count > 0 ){
				if ( $v_bugnote_updated >
					strtotime( "-$f_highlight_changed hours" ) ) {
					PRINT "<span class=\"bold\">$bugnote_count</span>";
				} else {
					echo $bugnote_count;
				}
			} else {
				PRINT '&nbsp;';
			}
		?>
	</td>
	<td class="print" bgcolor="<?php echo $status_color ?>">
		<?php
			# Print project name if viewing 'all projects'
			if ( ALL_PROJECTS == $t_project_id ) {
				print "[$project_name] <br />";
			}
		?>
		<?php echo $v_category ?>
	</td>
	<td class="print" bgcolor="<?php echo $status_color ?>">
		<?php print_formatted_severity_string( $v_status, $v_severity ) ?>
	</td>
	<td class="print" bgcolor="<?php echo $status_color ?>">
		<?php
			echo get_enum_element( 'status', $v_status );
			# print username instead of status
			if ( $v_handler_id > 0 && ON == config_get( 'show_assigned_names' ) ) {
				echo '(' . user_get_name( $v_handler_id ) . ')';
			}
		?>
	</td>
	<td class="print" bgcolor="<?php echo $status_color ?>">
		<?php
			if ( $v_last_updated >
				strtotime( "-$f_highlight_changed hours" ) ) {

				PRINT "<span class=\"bold\">$t_last_updated</span>";
			} else {
				echo $t_last_updated;
			}
		?>
	</td>
	<td class="left" bgcolor="<?php echo $status_color ?>">
		<span class="print"><?php echo $v_summary ?></a>
	</td>
</tr>
<?php
	} # isset_loop
} # for_loop
?>
<input type="hidden" name="show_flag" value="1" />
</table>

<br />

<input type="submit" class="button" value="<?php echo lang_get( 'hide_button' ) ?>" />
</form>

<?php # @@@ BUG ?  Where is the closing FORM tag??? ?>
