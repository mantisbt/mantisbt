<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: view_all_inc.php,v 1.151 2005-01-25 12:44:22 vboctor Exp $
	# --------------------------------------------------------
?>
<?php
	$t_core_path = config_get( 'core_path' );
	
	require_once( $t_core_path.'current_user_api.php' );
	require_once( $t_core_path.'bug_api.php' );
	require_once( $t_core_path.'string_api.php' );
	require_once( $t_core_path.'date_api.php' );
	require_once( $t_core_path.'icon_api.php' );

	$t_filter = current_user_get_bug_filter();

	$t_sort = $t_filter['sort'];
	$t_dir = $t_filter['dir'];

	$t_checkboxes_exist = false;

	$t_icon_path = config_get( 'icon_path' );
	$t_update_bug_threshold = config_get( 'update_bug_threshold' );

	$t_columns = helper_call_custom_function( 'get_columns_to_view', array() );

	# -- ====================== FILTER FORM ========================= --
	filter_draw_selection_area( $f_page_number );
	# -- ====================== end of FILTER FORM ================== --

	function print_column_title_selection() {
		echo '<td> &nbsp; </td>';
	}

	function print_column_title_edit() {
		echo '<td> &nbsp; </td>';
	}

	function print_column_title_bug_id() {
		global $t_sort, $t_dir;

		echo '<td>';
		print_view_bug_sort_link( lang_get( 'id' ), 'id', $t_sort, $t_dir );
		print_sort_icon( $t_dir, $t_sort, 'id' );
		echo '</td>';
	}

	function print_column_title_priority() {
		global $t_sort, $t_dir;

		echo '<td>';
		print_view_bug_sort_link( 'P', 'priority', $t_sort, $t_dir ); 
		print_sort_icon( $t_dir, $t_sort, 'priority' );
		echo '</td>';
	}

	function print_column_title_attachment() {
		$t_show_attachments = config_get( 'show_attachment_indicator' );

		if ( ON == $t_show_attachments ) {
			echo "\t<td>";
			echo '<img src="' . $t_icon_path . 'attachment.png' . '" alt="" />';
			echo "</td>\n";
		}
	}

	function print_column_title_category() {
		global $t_sort, $t_dir;

		echo '<td>';
		print_view_bug_sort_link( lang_get( 'category' ), 'category', $t_sort, $t_dir );
		print_sort_icon( $t_dir, $t_sort, 'category' );
		echo '</td>';
	}

	function print_column_title_sponsorship() {
		global $t_sort, $t_dir;

		$t_enable_sponsorship = config_get( 'enable_sponsorship' );

		if ( ON == $t_enable_sponsorship ) {
			echo "\t<td>";
			print_view_bug_sort_link( sponsorship_get_currency(), 'sponsorship_total', $t_sort, $t_dir );
			print_sort_icon( $t_dir, $t_sort, 'sponsorship_total' );
			echo "</td>\n";
		}
	}

	function print_column_title_severity() {
		global $t_sort, $t_dir;

		echo '<td>';
		print_view_bug_sort_link( lang_get( 'severity' ), 'severity', $t_sort, $t_dir );
		print_sort_icon( $t_dir, $t_sort, 'severity' );
		echo '</td>';
	}

	function print_column_title_status() {
		global $t_sort, $t_dir;

		echo '<td>';
		print_view_bug_sort_link( lang_get( 'status' ), 'status', $t_sort, $t_dir );
		print_sort_icon( $t_dir, $t_sort, 'status' );
		echo '</td>';
	}

	function print_column_title_last_updated() {
		global $t_sort, $t_dir;

		echo '<td>';
		print_view_bug_sort_link( lang_get( 'updated' ), 'last_updated', $t_sort, $t_dir );
		print_sort_icon( $t_dir, $t_sort, 'last_updated' );
		echo '</td>';
	}

	function print_column_title_summary() {
		global $t_sort, $t_dir;

		echo '<td>';
		print_view_bug_sort_link( lang_get( 'summary' ), 'summary', $t_sort, $t_dir );
		print_sort_icon( $t_dir, $t_sort, 'summary' );
		echo '</td>';
	}

	function print_column_title_bugnotes_count() {
		echo '<td> # </td>';
	}

	function print_column_selection( $p_row ) {
		global $t_checkboxes_exist, $t_update_bug_threshold;

		echo '<td>';
		if ( access_has_bug_level( $t_update_bug_threshold, $p_row['id'] ) ) {
			$t_checkboxes_exist = true;
			printf( "<input type=\"checkbox\" name=\"bug_arr[]\" value=\"%d\" />" , $p_row['id'] );
		} else {
			echo "&nbsp;";
		}
		echo '</td>';
	}

	function print_column_edit( $p_row ) {
		global $t_icon_path, $t_update_bug_threshold;

		echo '<td>';
		if ( !bug_is_readonly( $p_row['id'] )
		  && access_has_bug_level( $t_update_bug_threshold, $p_row['id'] ) ) {
			echo '<a href="' . string_get_bug_update_url( $p_row['id'] ) . '">';
			echo '<img border="0" width="16" height="16" src="' . $t_icon_path . 'update.png';
			echo '" alt="' . lang_get( 'update_bug_button' ) . '"';
			echo ' title="' . lang_get( 'update_bug_button' ) . '" /></a>';
		} else {
			echo '&nbsp;';
		}
		echo '</td>';
	}

	function print_column_priority( $p_row ) {
		echo '<td>';
		if ( ON == config_get( 'show_priority_text' ) ) {
			print_formatted_priority_string( $p_row['status'], $p_row['priority'] );
		} else {
			print_status_icon( $p_row['priority'] );
		}
		echo '</td>';
	}

	function print_column_bug_id( $p_row ) {
		echo '<td>';
		print_bug_link( $p_row['id'], false );
		echo '</td>';
	}

	function print_column_sponsorship( $p_row ) {
		$t_enable_sponsorship = config_get( 'enable_sponsorship' );

		if ( $t_enable_sponsorship == ON ) {
			echo "\t<td class=\"right\">";
			if ( $v_sponsorship_total > 0 ) {
				$t_sponsorship_amount = sponsorship_format_amount( $v_sponsorship_total );
				echo string_no_break( $t_sponsorship_amount );
			}
			echo "</td>\n";
		}
	}

	function print_column_bugnotes_count( $p_row ) {
		global $t_filter;

		# grab the bugnote count
		$t_bugnote_stats = bug_get_bugnote_stats( $p_row['id'] );
		if ( NULL != $t_bugnote_stats ) {
			$bugnote_count = $t_bugnote_stats['count'];
			$v_bugnote_updated = $t_bugnote_stats['last_modified'];
		} else {
			$bugnote_count = 0;
		}

		echo '<td class="center">';
		if ( $bugnote_count > 0 ) {
			$t_bugnote_link = '<a href="' . string_get_bug_view_url( $p_row['id'] ) 
				. '&amp;nbn=' . $bugnote_count . '#bugnotes">' 
				. $bugnote_count . '</a>'; 
	
			if ( $v_bugnote_updated > strtotime( '-'.$t_filter['highlight_changed'].' hours' ) ) {
				printf( '<span class="bold">%s</span>', $t_bugnote_link );
			} else {
				echo $t_bugnote_link;
			}
		} else {
			echo '&nbsp;';
		}
		echo '</td>';
	}

	function print_column_attachment( $p_row ) {
		$t_show_attachments = config_get( 'show_attachment_indicator' );

		# Check for attachments
		$t_attachment_count = 0;
		if ( ( ON == $t_show_attachments ) 
		  && ( file_can_view_bug_attachments( $p_row['id'] ) ) ) {
			$t_attachment_count = file_bug_attachment_count( $p_row['id'] );
		}
	
		if ( ON == $t_show_attachments ) {
			echo "\t<td>";
			if ( 0 < $t_attachment_count ) {
				echo '<a href="' . string_get_bug_view_url( $p_row['id'] ) . '#attachments">';
				echo '<img border="0" src="' . $t_icon_path . 'attachment.png' . '"';
				echo ' alt="' . lang_get( 'attachment_alt' ) . '"';
				echo ' title="' . $t_attachment_count . ' ' . lang_get( 'attachments' ) . '"';
				echo ' />';
				echo '</a>';
			} else {
				echo ' &nbsp; ';
			}
			echo "</td>\n";
		}
	}

	function print_column_category( $p_row ) {
		global $t_sort, $t_dir;

		# grab the project name
		$t_project_name = project_get_field( $p_row['project_id'], 'name' );

		echo '<td class="center">';

		# type project name if viewing 'all projects'
		if ( ON == config_get( 'show_bug_project_links' ) 
		  && helper_get_current_project() == ALL_PROJECTS ) {
			echo '<small>[';
			print_view_bug_sort_link( $t_project_name, 'project_id', $t_sort, $t_dir );
			echo ']</small><br />';
		}

		echo string_display( $p_row['category'] );
		echo '</td>';
	}

	function print_column_severity( $p_row ) {
		echo '<td class="center">';
		print_formatted_severity_string( $p_row['status'], $p_row['severity'] );
		echo '</td>';
	}

	function print_column_status( $p_row ) {
		echo '<td class="center">';
		printf( '<u><a title="%s">%s</a></u>'
			, get_enum_element( 'resolution', $p_row['resolution'] ) 
			, get_enum_element( 'status', $p_row['status'] )
		);
	
		# print username instead of status
		if ( ON == config_get( 'show_assigned_names' ) 
		  && $p_row['handler_id'] > 0 ) {
			printf( ' (%s)', $p_row['handler_id'] );
		}
		echo '</td>';
	}

	function print_column_last_updated( $p_row ) {
		global $t_filter;

		$t_last_updated = date( config_get( 'short_date_format' ), $p_row['last_updated'] );

		echo '<td class="center">';
		if ( $p_row['last_updated'] > strtotime( '-'.$t_filter['highlight_changed'].' hours' ) ) {
			printf( '<span class="bold">%s</span>', $t_last_updated );
		} else {
			echo $t_last_updated;
		}
		echo '</td>';
	}

	function print_column_summary( $p_row ) {
		global $t_icon_path;

		$t_summary = string_attribute( $p_row['summary'] );

		echo '<td class="left">', $t_summary;
		if ( VS_PRIVATE == $p_row['view_state'] ) {
			printf( ' <img src="%s" alt="(%s)" title="%s" />'
				, $t_icon_path . 'protected.gif'
				, lang_get( 'private' )
				, lang_get( 'private' )
			);
		}
		echo '</td>';
	}

	# -- ====================== BUG LIST ============================ -- 

	$col_count = 10;

	if ( STATUS_LEGEND_POSITION_TOP == config_get( 'status_legend_position' ) ) {
		html_status_legend();
	}

	$t_show_attachments = config_get( 'show_attachment_indicator' );
	if ( ON == $t_show_attachments ) {
		$col_count++;
	}

	$t_enable_sponsorship = config_get( 'enable_sponsorship' );
	if ( ON == $t_enable_sponsorship ) {
		$col_count++;
	}

# @@@ (thraxisp) this may want a browser check  ( MS IE >= 5.0, Mozilla >= 1.0, Safari >=1.2, ...)
	if ( ( ON == config_get( 'dhtml_filters' ) ) && ( ON == config_get( 'use_javascript' ) ) ){
		?>
		<script type="text/javascript">
			var string_loading = '<?php echo lang_get( 'loading' );?>';
		</script>
		<script type="text/javascript" src="javascript/xmlhttprequest.js"></script>
		<script type="text/javascript" src="javascript/addLoadEvent.js"></script>
		<script type="text/javascript" src="javascript/dynamic_filters.js"></script>
		<?php
	}
?>
<br />
<form name="bug_action" method="get" action="bug_actiongroup_page.php">
<table id="buglist" class="width100" cellspacing="1">
<tr>
	<td class="form-title" colspan="<?php echo $col_count - 2; ?>">
		<?php 
			# -- Viewing range info --

			$v_start = 0;
			$v_end   = 0;

			if ( sizeof( $rows ) > 0 ) {
				$v_start = $t_filter['per_page'] * ($f_page_number-1) +1;
				$v_end   = $v_start + sizeof( $rows ) -1;
			}

			echo lang_get( 'viewing_bugs_title' );
			echo " ($v_start - $v_end / $t_bug_count)";
		?>

		<span class="small"> <?php
				# -- Print and Export links --

				print_bracket_link( 'print_all_bug_page.php', lang_get( 'print_all_bug_page_link' ) );
				echo '&nbsp;';
				print_bracket_link( 'csv_export.php', lang_get( 'csv_export' ) );
		?> </span>
	</td>

	<td class="right" colspan="2">
		<span class="small"> <?php 
			# -- Page number links -- 

			print_page_links( 'view_all_bug_page.php', 1, $t_page_count, $f_page_number ); 
		?> </span>
	</td>
</tr>
<?php # -- Bug list column header row -- ?>
<tr class="row-category">
<?php
		foreach( $t_columns as $t_column ) {
			$t_title_function = 'print_column_title';
			helper_call_custom_function( $t_title_function, array( $t_column ) );
		}
?>
</tr>

<?php # -- Spacer row -- ?>
<tr>
	<td class="spacer" colspan="<?php echo $col_count; ?>"> &nbsp; </td>
</tr>
<?php 
	function write_bug_rows ( $p_rows )
	{
		global $t_columns;
		
		mark_time( 'begin loop' );
	
		# -- Loop over bug rows and create $v_* variables -- 
	
		for ( $i = 0; $i < sizeof( $p_rows ); $i++ ) {
			$t_row = $p_rows[$i];
	
			# choose color based on status
			$status_color = get_status_color( $t_row['status'] );
	
			echo '<tr bgcolor="', $status_color, '" border="1">';

			foreach( $t_columns as $t_column ) {
				$t_column_value_function = 'print_column_value';
				helper_call_custom_function( $t_column_value_function, array( $t_column, $t_row ) );
			}

			echo '</tr>';
		}
	}

	write_bug_rows( $sticky_rows );

	# -- ====================== end of STICKY BUG LIST ========================= --
	if ( 0 < sizeof($sticky_rows) ) {
?>
		<tr>
			<td class="left" colspan="<?php echo $col_count; ?>" bgcolor="#999999">&nbsp;</td>
		</tr>
<?php
	}
	write_bug_rows($rows);
	# -- ====================== end of BUG LIST ========================= --

	# -- ====================== MASS BUG MANIPULATION =================== -- 
?>
	<tr>
		<td class="left" colspan="<?php echo $col_count-2; ?>">
<?php
		if ( $t_checkboxes_exist && ON == config_get( 'use_javascript' ) ) {
			echo "<input type=\"checkbox\" name=\"all_bugs\" value=\"all\" onClick=\"checkall('bug_action', this.form.all_bugs.checked)\"><span class=\"small\">" . lang_get( 'select_all' ) . '</span>';
		}

		if ( $t_checkboxes_exist ) {
?>
			<select name="action">
				<?php print_all_bug_action_option_list() ?>
			</select>
			<input type="submit" class="button" value="<?php echo lang_get( 'ok' ); ?>" />
<?php
		} else {
			echo '&nbsp;';
		}
?>
		</td>
		<?php # -- Page number links -- ?>
		<td class="right" colspan="2">
			<span class="small">
				<?php print_page_links( 'view_all_bug_page.php', 1, $t_page_count, $f_page_number ) ?>
			</span>
		</td>
	</tr>
<?php # -- ====================== end of MASS BUG MANIPULATION ========================= -- ?>
</table>
</form>

<?php 

	mark_time( 'end loop' ); 

	if ( STATUS_LEGEND_POSITION_BOTTOM == config_get( 'status_legend_position' ) ) {
		html_status_legend();
	}
?>