<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php
	# Bugs to display / print / export can be selected with the checkboxes
	# A printing Options link allows to choose the fields to export
	# Export :
	#	- the bugs displayed in print_all_bug_page.php are saved in a .doc or .xls file
	#   - the IE icons allows to see or directly print the same result
?>
<?php require_once( 'core.php' ) ?>
<?php login_cookie_check() ?>
<?php
	# check to see if the cookie does not exist
	if ( empty( $g_view_all_cookie_val ) ) {
		print_header_redirect( 'view_all_set.php?f_type=0&amp;f_print=1' );
	}

	# check to see if new cookie is needed
	$t_setting_arr 			= explode( '#', $g_view_all_cookie_val );
	if ( $t_setting_arr[0] != $g_cookie_version ) {
		print_header_redirect( 'view_all_set.php?f_type=0&amp;f_print=1' );
	}

	check_varset( $f_search, false );
	check_varset( $f_offset, 0 );

	# Load preferences
	$f_show_category 		= $t_setting_arr[1];
	$f_show_severity	 	= $t_setting_arr[2];
	$f_show_status 			= $t_setting_arr[3];
	$f_per_page 			= $t_setting_arr[4];
	$f_highlight_changed 	= $t_setting_arr[5];
	$f_hide_closed 			= $t_setting_arr[6];
	$f_user_id 				= $t_setting_arr[7];
	$f_assign_id 			= $t_setting_arr[8];
	$f_sort 				= $t_setting_arr[9];
	$f_dir		 			= $t_setting_arr[10];
	$f_start_month			= $t_setting_arr[11];
	$f_start_day 			= $t_setting_arr[12];
	$f_start_year 			= $t_setting_arr[13];
	$f_end_month 			= $t_setting_arr[14];
	$f_end_day				= $t_setting_arr[15];
	$f_end_year				= $t_setting_arr[16];

	# Clean input
	$c_offset 				= (integer)$f_offset;
	$c_user_id				= (integer)$f_user_id;
	$c_assign_id			= (integer)$f_assign_id;
	$c_per_page				= (integer)$f_per_page;
	$c_show_category		= addslashes($f_show_category);
	$c_show_severity		= addslashes($f_show_severity);
	$c_show_status			= addslashes($f_show_status);
	$c_search				= addslashes($f_search);
	$c_sort					= addslashes($f_sort);

	if ('DESC' == $f_dir) {
		$c_dir = 'DESC';
	} else {
		$c_dir = 'ASC';
	}

	# Limit reporters to only see their reported bugs
	if (( ON == $g_limit_reporters ) &&
		( !access_level_check_greater_or_equal( UPDATER  ) )) {
		$c_user_id = current_user_get_field( 'id' );
	}

	# Build our query string based on our viewing criteria

	$query = 'SELECT DISTINCT *, UNIX_TIMESTAMP(last_updated) as last_updated
			 FROM $g_mantis_bug_table';

	# project selection
	if ( '0000000' == $g_project_cookie_val ) { # ALL projects
		$t_access_level = current_user_get_field( 'access_level' );
		$t_user_id = current_user_get_field( 'id' );

		$t_pub = PUBLIC;
		$t_prv = PRIVATE;
		$query2 = "SELECT DISTINCT( p.id )
			FROM $g_mantis_project_table p, $g_mantis_project_user_list_table u
			WHERE (p.enabled=1 AND
				p.view_state='$t_pub') OR
				(p.enabled=1 AND
				p.view_state='$t_prv' AND
				u.user_id='$t_user_id'  AND
				u.project_id=p.id)
			ORDER BY p.name";
		$result2 = db_query( $query2 );
		$project_count = db_num_rows( $result2 );

		if ( 0 == $project_count ) {
			$t_where_clause = ' WHERE 1=1';
		} else {
			$t_where_clause = ' WHERE (';
			for ($i=0;$i<$project_count;$i++) {
				$row = db_fetch_array( $result2 );
				extract( $row, EXTR_PREFIX_ALL, 'v' );

				$t_where_clause .= "(project_id='$v_id')";
				if ( $i < $project_count - 1 ) {
					$t_where_clause .= ' OR ';
				}
			} # end for
			$t_where_clause .= ')';
		}
	} else {
		$t_where_clause = " WHERE project_id='$g_project_cookie_val'";
	}
	# end project selection

	if ( $c_user_id != 'any' ) {
		$t_where_clause .= " AND reporter_id='$c_user_id'";
	}

	if ( 'none' == $f_assign_id ) {
		$t_where_clause .= ' AND handler_id=0';
	} else if ( $f_assign_id != 'any' ) {
		$t_where_clause .= " AND handler_id='$c_assign_id'";
	}

	$t_clo_val = CLOSED;
	if ( ( 'on' == $f_hide_closed  )&&( 'closed' != $f_show_status )) {
		$t_where_clause = $t_where_clause." AND status<>'$t_clo_val'";
	}

	if ( $f_show_category != 'any' ) {
		$t_where_clause = $t_where_clause." AND category='$c_show_category'";
	}
	if ( $f_show_severity != 'any' ) {
		$t_where_clause = $t_where_clause." AND severity='$c_show_severity'";
	}
	if ( $f_show_status != 'any' ) {
		$t_where_clause = $t_where_clause." AND status='$c_show_status'";
	}

	# Simple Text Search - Thnaks to Alan Knowles
	if ($f_search) {
		$t_columns_clause = " $g_mantis_bug_table.*";

		$t_where_clause .= " AND ((summary LIKE '%$c_search%')
							OR (description LIKE '%$c_search%')
							OR (steps_to_reproduce LIKE '%$c_search%')
							OR (additional_information LIKE '%$c_search%')
							OR ($g_mantis_bug_table.id LIKE '%$c_search%')
							OR ($g_mantis_bugnote_text_table.note LIKE '%$c_search%'))
							AND $g_mantis_bug_text_table.id = $g_mantis_bug_table.bug_text_id";

		$t_from_clause = " FROM $g_mantis_bug_table, $g_mantis_bug_text_table
							LEFT JOIN $g_mantis_bugnote_table      ON $g_mantis_bugnote_table.bug_id  = $g_mantis_bug_table.id
							LEFT JOIN $g_mantis_bugnote_text_table ON $g_mantis_bugnote_text_table.id = $g_mantis_bugnote_table.bugnote_text_id ";
	} else {
		$t_columns_clause = ' *';
		$t_from_clause = " FROM $g_mantis_bug_table";
	}

	if ( empty($c_sort) ) {
		$c_sort='last_updated';
	}
	$query  = 'SELECT DISTINCT '.$t_columns_clause.', UNIX_TIMESTAMP(last_updated) as last_updated';
	$query .= $t_from_clause;
	$query .= $t_where_clause;

	$query = $query." ORDER BY '$c_sort' $c_dir";
	if ( $f_sort != 'priority' ) {
		$query = $query.', priority DESC';
	}

	$query = $query." LIMIT $c_offset, $c_per_page";

	# perform query
    $result = db_query( $query );
	$row_count = db_num_rows( $result );

	# for export
	check_varset( $t_show_flag, 0 );
?>
<?php print_page_top1() ?>
<?php print_head_bottom() ?>
<?php print_body_top() ?>

<table class="width100">
<tr>
    <td class="print">
		<form method="post" action="view_all_set.php">
		<input type="hidden" name="f_type" value="1">
		<input type="hidden" name="f_print" value="1">
		<input type="hidden" name="f_offset" value="0">
		<input type="hidden" name="f_sort" value="<?php echo $f_sort ?>">
		<input type="hidden" name="f_dir" value="<?php echo $f_dir ?>">
        <?php echo $s_search ?>
    </td>
    <td class="print">
		<?php echo $s_reporter ?>
	</td>
    <td class="print">
		<?php echo $s_assigned_to ?>
	</td>
    <td class="print">
		<?php echo $s_category ?>
	</td>
    <td class="print">
		<?php echo $s_severity ?>
	</td>
    <td class="print">
		<?php echo $s_status ?>
	</td>
    <td class="print">
		<?php echo $s_show ?>
	</td>
    <td class="print">
		<?php echo $s_changed ?>
	</td>
    <td class="print">
		<?php echo $s_hide_closed ?>
	</td>
    <td class="print">
		&nbsp;
	</td>
</tr>
<tr>
	<td>
	    <input type="text" name="f_search" value="<?php echo $f_search; ?>">
	</td>
	<td>
		<select name="f_user_id">
			<option value="any"><?php echo $s_any ?></option>
			<option value="any"></option>
			<?php print_reporter_option_list( $f_user_id ) ?>
		</select>
	</td>
	<td>
		<select name="f_assign_id">
			<option value="any"><?php echo $s_any ?></option>
			<option value="none" <?php check_selected( $f_assign_id, 'none' ); ?>><?php echo $s_none ?></option>
			<option value="any"></option>
			<?php print_assign_to_option_list( $f_assign_id ) ?>
		</select>
	</td>
	<td>
		<select name="f_show_category">
			<option value="any"><?php echo $s_any ?></option>
			<option value="any"></option>
			<?php print_category_option_list( $f_show_category ) ?>
		</select>
	</td>
	<td>
		<select name="f_show_severity">
			<option value="any"><?php echo $s_any ?></option>
			<option value="any"></option>
			<?php print_enum_string_option_list( 'severity', $f_show_severity ) ?>
		</select>
	</td>
	<td>
		<select name="f_show_status">
			<option value="any"><?php echo $s_any ?></option>
			<option value="any"></option>
			<?php print_enum_string_option_list( 'status', $f_show_status ) ?>
		</select>
	</td>
	<td>
		<input type="text" name="f_per_page" size="3" maxlength="7" value="<?php echo $f_per_page ?>">
	</td>
	<td>
		<input type="text" name="f_highlight_changed" size="3" maxlength="7" value="<?php echo $f_highlight_changed ?>">
	</td>
	<td>
		<input type="checkbox" name="f_hide_closed" <?php check_checked( $f_hide_closed, 'on' ); ?>>
	</td>
	<td>
		<input type="submit" value="<?php echo $s_filter_button ?>">
		</form>
	</td>
</tr>

<?php
	#<SQLI> Excel & Print export
	#$f_bug_array stores the number of the selected rows
	#$t_bug_arr_sort is used for displaying
	#$f_export is a string for the word and excel pages

	$f_bug_arr[$row_count]=-1;

	for($i=0; $i < $row_count; $i++) {
		if ( isset($f_bug_arr[$i]) ) {
			$index = $f_bug_arr[$i];
			$t_bug_arr_sort[$index]=1;
		}
	}
	$f_export = implode(',',$f_bug_arr);
?>

<tr>
	<td>
	<a href="<? echo "print_all_bug_page_excel.php"; ?>?f_search=<? echo urlencode($f_search) ?>&amp;f_sort=<? echo $f_sort ?>&amp;f_dir=<? if ( $f_dir == "DESC" ) { echo "ASC"; } else { echo "DESC"; } ?>&amp;f_type_page=excel&amp;f_export=<? echo $f_export ?>&amp;f_show_flag=<? echo $t_show_flag ?>"><img src="images/excelicon.gif" border="0" align="absmiddle" alt="Excel 2000"></a> <a href="<? echo "print_all_bug_page_excel.php" ?>?f_search=<? echo urlencode($f_search) ?>&amp;f_sort=<? echo $f_sort ?>&amp;f_dir=<? if ( $f_dir == "DESC" ) { echo "ASC"; } else { echo "DESC"; } ?>&amp;f_type_page=html&amp;f_export=<? echo $f_export ?>&amp;f_show_flag=<? echo $t_show_flag ?>" target="_blank"><img src="images/ieicon.gif" border="0" align="absmiddle" alt="Excel View" /></a>
	- <a href="<? echo "print_all_bug_page_word.php" ?>?f_search=<? echo urlencode($f_search) ?>&amp;f_sort=<? echo $f_sort ?>&amp;f_dir=<? if ( $f_dir == "DESC" ) { echo "ASC"; } else { echo "DESC"; } ?>&amp;f_type_page=word&amp;f_export=<? echo $f_export ?>&amp;f_show_flag=<? echo $t_show_flag ?>"><img src="images/wordicon.gif" border="0" align="absmiddle" alt="Word 2000" /></a>
	<a href="<? echo "print_all_bug_page_word.php" ?>?f_search=<? echo urlencode($f_search) ?>&amp;f_sort=<? echo $f_sort ?>&amp;f_dir=<? if ( $f_dir == "DESC" ) { echo "ASC"; } else { echo "DESC"; } ?>&amp;f_type_page=html&amp;f_export=<? echo $f_export ?>&amp;f_show_flag=<? echo $t_show_flag ?>" target="_blank"><img src="images/ieicon.gif" border="0" align="absmiddle" alt="Word View" />
	</td>
</tr>
<?php #<SQLI> ?>
</table>

<form method="post" action="print_all_bug_page.php">

<table class="width100" cellspacing="1">
<tr>
	<td class="form-title" colspan="7">
		<?php echo $s_viewing_bugs_title ?>
		<?php
			if ( $row_count > 0 ) {
				$v_start = $f_offset+1;
				$v_end   = $f_offset+$row_count;
			} else {
				$v_start = 0;
				$v_end   = 0;
			}
			PRINT "($v_start - $v_end)";
		?>
	</td>
	<td>
	</td>
	<td class="right">
		<?php print_bracket_link( 'print_all_bug_options_page.php', $s_printing_options_link ) ?>
		<?php print_bracket_link( 'view_all_bug_page.php', $s_view_bugs_link ) ?>
		<?php print_bracket_link( 'summary_page.php', $s_summary ) ?>
	</td>
<p>
</tr>
<tr class="row-category">
	<td class="center" width="2%">
		&nbsp;
	</td>
	<td class="center" width="8%">
		<?php print_view_bug_sort_link2( 'P', 'priority', $f_sort, $f_dir ) ?>
		<?php print_sort_icon( $f_dir, $f_sort, 'priority' ) ?>
	</td>
	<td class="center" width="8%">
		<?php print_view_bug_sort_link2( $s_id, 'id', $f_sort, $f_dir ) ?>
		<?php print_sort_icon( $f_dir, $f_sort, 'id' ) ?>
	</td>
	<td class="center" width="3%">
		#
	</td>
	<td class="center" width="12%">
		<?php print_view_bug_sort_link2( $s_category, 'category', $f_sort, $f_dir ) ?>
		<?php print_sort_icon( $f_dir, $f_sort, 'category' ) ?>
	</td>
	<td class="center" width="10%">
		<?php print_view_bug_sort_link2( $s_severity, 'severity', $f_sort, $f_dir ) ?>
		<?php print_sort_icon( $f_dir, $f_sort, 'severity' ) ?>
	</td>
	<td class="center" width="10%">
		<?php print_view_bug_sort_link2( $s_status, 'status', $f_sort, $f_dir ) ?>
		<?php print_sort_icon( $f_dir, $f_sort, 'status' ) ?>
	</td>
	<td class="center" width="12%">
		<?php print_view_bug_sort_link2( $s_updated, 'last_updated', $f_sort, $f_dir ) ?>
		<?php print_sort_icon( $f_dir, $f_sort, 'last_updated' ) ?>
	</td>
	<td class="center" width="37%">
		<?php print_view_bug_sort_link2( $s_summary, 'summary', $f_sort, $f_dir ) ?>
		<?php print_sort_icon( $f_dir, $f_sort, 'summary' ) ?>
	</td>
</tr>
<tr>
	<td class="spacer" colspan="8">
		&nbsp;
	</td>
</tr>
<?php
	for($i=0; $i < $row_count; $i++) {
		# prefix bug data with v_
		$row = db_fetch_array($result);
		extract( $row, EXTR_PREFIX_ALL, 'v' );

		$v_summary = string_display( $v_summary );
		$t_last_updated = date( $g_short_date_format, $v_last_updated );

		# alternate row colors
		$status_color = alternate_colors( $i, '#ffffff', $g_primary_color2 );

		# grab the bugnote count
		$bugnote_count = bug_bugnote_count( $v_id );

		# grab the project name
		$project_name = project_get_field( $v_project_id, 'name' );

		$query = "SELECT MAX(last_modified)
				FROM $g_mantis_bugnote_table
				WHERE bug_id='$v_id'";
		$res2 = db_query( $query );
		$v_bugnote_updated = db_result( $res2, 0, 0 );

		if (isset($t_bug_arr_sort[$i])||($t_show_flag==0)) {
?>

<tr>
	<td class="print" bgcolor="<?php echo $status_color ?>">
		<input type="checkbox" name="f_bug_arr[]" value="<?php echo $i ?>">
	</td>
	<td class="print" bgcolor="<?php echo $status_color ?>">
		<?php echo get_enum_element( 'priority', $v_priority ) ?>
	</td>
	<td class="print" bgcolor="<?php echo $status_color ?>">
		<?php echo $v_id ?>
		<?php # type project name if viewing 'all projects'?>
		<?php if ( '0000000' == $g_project_cookie_val ) {?>
		<br /><?php print "[$project_name]"; }?>
	</td>
	<td class="print" bgcolor="<?php echo $status_color ?>">
		<?php
			if ($bugnote_count > 0){
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
		<?php echo $v_category ?>
	</td>
	<td class="print" bgcolor="<?php echo $status_color ?>">
		<?php print_formatted_severity_string( $v_status, $v_severity ) ?>
	</td>
	<td class="print" bgcolor="<?php echo $status_color ?>">
		<?php
			# print username instead of status
			if (( ON == $g_show_assigned_names )&&( $v_handler_id > 0 )&&
				( $v_status!=CLOSED )&&( $v_status!=RESOLVED )) {
				echo '('.user_get_field( $v_handler_id, 'username' ).')';
			} else {
				echo get_enum_element( 'status', $v_status );
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
<input type="hidden" name="t_show_flag" value="1">
</table>
<input type="submit" value="<?php echo $s_hide_button ?>">
