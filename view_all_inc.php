<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: view_all_inc.php,v 1.145 2004-10-17 15:38:19 thraxisp Exp $
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

	# -- ====================== FILTER FORM ========================= --
	filter_draw_selection_area( $f_page_number );
	# -- ====================== end of FILTER FORM ================== --

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
	<td> &nbsp; </td>
	<td> &nbsp; </td>
	<td> <?php 
		# -- Priority column -- 

		print_view_bug_sort_link( 'P', 'priority', $t_sort, $t_dir ); 
		print_sort_icon( $t_dir, $t_sort, 'priority' );
	?> </td>
	<td> <?php 
		# -- Bug ID column --

		print_view_bug_sort_link( lang_get( 'id' ), 'id', $t_sort, $t_dir );
		print_sort_icon( $t_dir, $t_sort, 'id' );
	?> </td>
<?php
		# -- Sponsorship Amount -- 

		if ( ON == $t_enable_sponsorship ) {
			echo "\t<td>";
			print_view_bug_sort_link( sponsorship_get_currency(), 'sponsorship_total', $t_sort, $t_dir );
			print_sort_icon( $t_dir, $t_sort, 'sponsorship_total' );
			echo "</td>\n";
		}
	
		# -- Bugnote count column -- 
?>
	<td> # </td>
<?php 
		# -- Attachment indicator --

		if ( ON == $t_show_attachments ) {
			echo "\t<td>";
			echo '<img src="' . $t_icon_path . 'attachment.png' . '" alt="" />';
			echo "</td>\n";
		}
?>
	<td> <?php 
		# -- Category column -- 

		print_view_bug_sort_link( lang_get( 'category' ), 'category', $t_sort, $t_dir );
		print_sort_icon( $t_dir, $t_sort, 'category' );
	?> </td>
	<td> <?php 
		# -- Severity column -- 

		print_view_bug_sort_link( lang_get( 'severity' ), 'severity', $t_sort, $t_dir );
		print_sort_icon( $t_dir, $t_sort, 'severity' );
	?> </td>
	<td> <?php
		# -- Status column --

		print_view_bug_sort_link( lang_get( 'status' ), 'status', $t_sort, $t_dir );
		print_sort_icon( $t_dir, $t_sort, 'status' );
	?> </td>
	<td> <?php 
		# -- Last Updated column -- 

		print_view_bug_sort_link( lang_get( 'updated' ), 'last_updated', $t_sort, $t_dir );
		print_sort_icon( $t_dir, $t_sort, 'last_updated' );
	?> </td>
	<td> <?php 
		# -- Summary column -- 

		print_view_bug_sort_link( lang_get( 'summary' ), 'summary', $t_sort, $t_dir );
		print_sort_icon( $t_dir, $t_sort, 'summary' );
	?> </td>
</tr>

<?php # -- Spacer row -- ?>
<tr>
	<td class="spacer" colspan="<?php echo $col_count; ?>"> &nbsp; </td>
</tr>

<?php 
	mark_time( 'begin loop' );

	# -- Loop over bug rows and create $v_* variables -- 

	for($i=0; $i < sizeof( $rows ); $i++) {
		# prefix bug data with v_

		extract( $rows[$i], EXTR_PREFIX_ALL, 'v' );

		$v_summary = string_display_links( $v_summary );
		$t_last_updated = date( config_get( 'short_date_format' ), $v_last_updated );

		# choose color based on status
		$status_color = get_status_color( $v_status );

		# grab the bugnote count
		$t_bugnote_stats = bug_get_bugnote_stats( $v_id );
		if ( NULL != $t_bugnote_stats ) {
			$bugnote_count = $t_bugnote_stats['count'];
			$v_bugnote_updated = $t_bugnote_stats['last_modified'];
		} else {
			$bugnote_count = 0;
		}

		# Check for attachments
		$t_attachment_count = 0;
		if ( ( ON == $t_show_attachments ) 
		  && ( file_can_view_bug_attachments( $v_id ) ) ) {
			$t_attachment_count = file_bug_attachment_count( $v_id );
		}

		# grab the project name
		$project_name = project_get_field( $v_project_id, 'name' );
?>
<tr bgcolor="<?php echo $status_color; ?>">
	<td> <?php
		# -- Checkbox -- 

		if ( access_has_bug_level( $t_update_bug_threshold, $v_id ) ) {
			$t_checkboxes_exist = true;
			printf( "<input type=\"checkbox\" name=\"bug_arr[]\" value=\"%d\" />" , $v_id );
		} else {
			echo "&nbsp;";
		}
	?> </td>
	<td> <?php 
		# -- Pencil shortcut --

		if ( !bug_is_readonly( $v_id ) 
		  && access_has_bug_level( $t_update_bug_threshold, $v_id ) ) {
			echo '<a href="' . string_get_bug_update_url( $v_id ) . '">';
			echo '<img border="0" src="' . $t_icon_path . 'update.png';
			echo '" alt="' . lang_get( 'update_bug_button' ) . '" /></a>';
		} else {
			echo '&nbsp;';
		}
	?> </td>
	<td> <?php
		# -- Priority --

		if ( ON == config_get( 'show_priority_text' ) ) {
			print_formatted_priority_string( $v_status, $v_priority );
		} else {
			print_status_icon( $v_priority );
		}
	?> </td>
	<td class="center"> <?php 
		# -- Bug ID and details link -- 

		print_bug_link( $v_id, false );
	?> </td>
<?php
		# -- Sponsorship Amount -- 

		if ( $t_enable_sponsorship == ON ) {
			echo "\t<td class=\"right\">";
			if ( $v_sponsorship_total > 0 ) {
				$t_sponsorship_amount = sponsorship_format_amount( $v_sponsorship_total );
				echo string_no_break( $t_sponsorship_amount );
			}
			echo "</td>\n";
		}
?>
	<td class="center"> <?php
		# -- Bugnote count --

		if ( $bugnote_count > 0 ) {
			$t_bugnote_link = '<a href="' . string_get_bug_view_url( $v_id ) 
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
	?> </td>
<?php 
		# -- Attachment indicator --
	  
		if ( ON == $t_show_attachments ) {
			echo "\t<td>";
			if ( 0 < $t_attachment_count ) {
				echo '<a href="' . string_get_bug_view_url( $v_id ) . '#attachments">';
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
?>
	<td class="center"> <?php 
		# -- Category -- 

		# type project name if viewing 'all projects'
		if ( ON == config_get( 'show_bug_project_links' ) 
		  && helper_get_current_project() == ALL_PROJECTS ) {
			echo '<small>[';
			print_view_bug_sort_link( $project_name, 'project_id', $t_sort, $t_dir );
			echo ']</small><br />';
		}

		echo string_display( $v_category );
	?> </td>
	<td class="center"> <?php 
		# -- Severity --

		print_formatted_severity_string( $v_status, $v_severity );
	?> </td>
	<td class="center"> <?php 
		# -- Status / Handler --

		printf( '<u><a title="%s">%s</a></u>'
			, get_enum_element( 'resolution', $v_resolution ) 
			, get_enum_element( 'status', $v_status )
		);

		# print username instead of status
		if ( ON == config_get( 'show_assigned_names' ) 
		  && $v_handler_id > 0 ) {
			echo ' (';
			print_user( $v_handler_id );
			echo ')';
		}
	?> </td>
	<td class="center"> <?php 
		# -- Last Updated -- 
	
		if ( $v_last_updated > strtotime( '-'.$t_filter['highlight_changed'].' hours' ) ) {
			printf( '<span class="bold">%s</span>', $t_last_updated );
		} else {
			echo $t_last_updated;
		}
	?> </td>
	<td class="left"> <?php 
		# -- Summary --

		echo $v_summary;
		if ( VS_PRIVATE == $v_view_state ) {
			printf( ' <img src="%s" alt="(%s)" title="%s" />'
				, $t_icon_path . 'protected.gif'
				, lang_get( 'private' )
				, lang_get( 'private' )
			);
		}
	 ?> </td>
</tr>
<?php 
	# -- end of Repeating bug row --
	}

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
