<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: my_view_inc.php,v 1.16 2005-08-07 13:42:04 thraxisp Exp $
	# --------------------------------------------------------
?>
<?php
	$t_core_path = config_get( 'core_path' );

	require_once( $t_core_path . 'current_user_api.php' );
	require_once( $t_core_path . 'bug_api.php' );
	require_once( $t_core_path . 'string_api.php' );
	require_once( $t_core_path . 'date_api.php' );
	require_once( $t_core_path . 'icon_api.php' );
?>
<?php
	$t_filter = current_user_get_bug_filter();

	$t_sort = $t_filter['sort'];
	$t_dir = $t_filter['dir'];

	$t_checkboxes_exist = false;

	$t_icon_path = config_get( 'icon_path' );
	$t_update_bug_threshold = config_get( 'update_bug_threshold' );
	$t_bug_resolved_status_threshold = config_get( 'bug_resolved_status_threshold' );
	$t_hide_status_default = config_get( 'hide_status_default' );
	$t_default_show_changed = config_get( 'default_show_changed' );
?>

<?php
	$c_filter['assigned'] = array(
		'show_category'		=> Array ( '0' => META_FILTER_ANY ),
		'show_severity'		=> Array ( '0' => META_FILTER_ANY ),
		'show_status'		=> Array ( '0' => META_FILTER_ANY ),
		'highlight_changed'	=> $t_default_show_changed,
		'reporter_id'		=> Array ( '0' => META_FILTER_ANY ),
		'handler_id'		=> Array ( '0' => $t_current_user_id ),
		'show_resolution'	=> Array ( '0' => META_FILTER_ANY ),
		'show_build'		=> Array ( '0' => META_FILTER_ANY ),
		'show_version'		=> Array ( '0' => META_FILTER_ANY ),
		'hide_status'		=> Array ( '0' => $t_bug_resolved_status_threshold ),
		'user_monitor'		=> Array ( '0' => META_FILTER_ANY )
	);
	$url_link_parameters['assigned'] = 'handler_id=' . $t_current_user_id . '&amp;hide_status=' . $t_bug_resolved_status_threshold;

	$c_filter['recent_mod'] = array(
		'show_category'		=> Array ( '0' => META_FILTER_ANY ),
		'show_severity'		=> Array ( '0' => META_FILTER_ANY ),
		'show_status'		=> Array ( '0' => META_FILTER_ANY ),
		'highlight_changed'	=> $t_default_show_changed,
		'reporter_id'		=> Array ( '0' => META_FILTER_ANY ),
		'handler_id'		=> Array ( '0' => META_FILTER_ANY ),
		'show_resolution'	=> Array ( '0' => META_FILTER_ANY ),
		'show_build'		=> Array ( '0' => META_FILTER_ANY ),
		'show_version'		=> Array ( '0' => META_FILTER_ANY ),
		'hide_status'		=> Array ( '0' => META_FILTER_NONE ),
		'user_monitor'		=> Array ( '0' => META_FILTER_ANY )
	);
	$url_link_parameters['recent_mod'] = 'hide_status=none';

	$c_filter['reported'] = array(
		'show_category'		=> Array ( '0' => META_FILTER_ANY ),
		'show_severity'		=> Array ( '0' => META_FILTER_ANY ),
		'show_status'		=> Array ( '0' => META_FILTER_ANY ),
		'highlight_changed'	=> $t_default_show_changed,
		'reporter_id'		=> Array ( '0' => $t_current_user_id ),
		'handler_id'		=> Array ( '0' => META_FILTER_ANY ),
		'sort'			=> 'last_updated',
		'show_resolution'	=> Array ( '0' => META_FILTER_ANY ),
		'show_build'		=> Array ( '0' => META_FILTER_ANY ),
		'show_version'		=> Array ( '0' => META_FILTER_ANY ),
		'hide_status'		=> Array ( '0' => $t_hide_status_default ),
		'user_monitor'		=> Array ( '0' => META_FILTER_ANY )
	);
	$url_link_parameters['reported'] = 'reporter_id=' . $t_current_user_id . '&amp;hide_status=' . $t_hide_status_default;

	$c_filter['resolved'] = array(
		'show_category'		=> Array ( '0' => META_FILTER_ANY ),
		'show_severity'		=> Array ( '0' => META_FILTER_ANY ),
		'show_status'		=> Array ( '0' => $t_bug_resolved_status_threshold ),
		'highlight_changed'	=> $t_default_show_changed,
		'reporter_id'		=> Array ( '0' => META_FILTER_ANY ),
		'handler_id'		=> Array ( '0' => META_FILTER_ANY ),
		'show_resolution'	=> Array ( '0' => META_FILTER_ANY ),
		'show_build'		=> Array ( '0' => META_FILTER_ANY ),
		'show_version'		=> Array ( '0' => META_FILTER_ANY ),
		'hide_status'		=> Array ( '0' => $t_hide_status_default ),
		'user_monitor'		=> Array ( '0' => META_FILTER_ANY )
	);
	$url_link_parameters['resolved'] = 'show_status=' . $t_bug_resolved_status_threshold . '&amp;hide_status=' . $t_bug_resolved_status_threshold;

	$c_filter['unassigned'] = array(
		'show_category'		=> Array ( '0' => META_FILTER_ANY ),
		'show_severity'		=> Array ( '0' => META_FILTER_ANY ),
		'show_status'		=> Array ( '0' => META_FILTER_ANY ),
		'highlight_changed'	=> $t_default_show_changed,
		'reporter_id'		=> Array ( '0' => META_FILTER_ANY ),
		'handler_id'		=> Array ( '0' => META_FILTER_NONE ),
		'show_resolution'	=> Array ( '0' => META_FILTER_ANY ),
		'show_build'		=> Array ( '0' => META_FILTER_ANY ),
		'show_version'		=> Array ( '0' => META_FILTER_ANY ),
		'hide_status'		=> Array ( '0' => $t_hide_status_default ),
		'user_monitor'		=> Array ( '0' => META_FILTER_ANY )
	);
	$url_link_parameters['unassigned'] = 'handler_id=[none]' . '&amp;hide_status=' . $t_hide_status_default;

	$c_filter['monitored'] = array(
		'show_category'		=> Array ( '0' => META_FILTER_ANY ),
		'show_severity'		=> Array ( '0' => META_FILTER_ANY ),
		'show_status'		=> Array ( '0' => META_FILTER_ANY ),
		'highlight_changed'	=> $t_default_show_changed,
		'reporter_id'		=> Array ( '0' => META_FILTER_ANY ),
		'handler_id'		=> Array ( '0' => META_FILTER_ANY ),
		'show_resolution'	=> Array ( '0' => META_FILTER_ANY ),
		'show_build'		=> Array ( '0' => META_FILTER_ANY ),
		'show_version'		=> Array ( '0' => META_FILTER_ANY ),
		'hide_status'		=> Array ( '0' => $t_hide_status_default ),
		'user_monitor'		=> Array ( '0' => $t_current_user_id )
	);
	$url_link_parameters['monitored'] = 'user_monitor=' . $t_current_user_id . '&amp;hide_status=' . $t_hide_status_default;


	$c_filter['feedback'] = array(
		'show_category'		=> Array ( '0' => META_FILTER_ANY ),
		'show_severity'		=> Array ( '0' => META_FILTER_ANY ),
		'show_status'		=> Array ( '0' => FEEDBACK ),
		'highlight_changed'	=> $t_default_show_changed,
		'reporter_id'		=> Array ( '0' => $t_current_user_id ),
		'handler_id'		=> Array ( '0' => META_FILTER_ANY ),
		'show_resolution'	=> Array ( '0' => META_FILTER_ANY ),
		'show_build'		=> Array ( '0' => META_FILTER_ANY ),
		'show_version'		=> Array ( '0' => META_FILTER_ANY ),
		'hide_status'		=> Array ( '0' => $t_hide_status_default ),
		'user_monitor'		=> Array ( '0' => META_FILTER_ANY )
	);
	$url_link_parameters['feedback'] = 'reporter_id=' . $t_current_user_id . '&amp;show_status=' . FEEDBACK . '&amp;hide_status=' . $t_hide_status_default;

	$c_filter['verify'] = array(
		'show_category'		=> Array ( '0' => META_FILTER_ANY ),
		'show_severity'		=> Array ( '0' => META_FILTER_ANY ),
		'show_status'		=> Array ( '0' => $t_bug_resolved_status_threshold ),
		'highlight_changed'	=> $t_default_show_changed,
		'reporter_id'		=> Array ( '0' => $t_current_user_id ),
		'handler_id'		=> Array ( '0' => META_FILTER_ANY ),
		'show_resolution'	=> Array ( '0' => META_FILTER_ANY ),
		'show_build'		=> Array ( '0' => META_FILTER_ANY ),
		'show_version'		=> Array ( '0' => META_FILTER_ANY ),
		'hide_status'		=> Array ( '0' => $t_hide_status_default ),
		'user_monitor'		=> Array ( '0' => META_FILTER_ANY )
	);
	$url_link_parameters['verify'] = 'reporter_id=' . $t_current_user_id . '&amp;show_status=' . $t_bug_resolved_status_threshold;

        $rows = filter_get_bug_rows ( $f_page_number, $t_per_page, $t_page_count, $t_bug_count, $c_filter[$t_box_title]  );

        $box_title = lang_get( 'my_view_title_' . $t_box_title );
?>


<?php # -- ====================== BUG LIST ========================= -- ?>

<table class="width100" cellspacing="1">
<?php # -- Navigation header row -- ?>
<tr>
	<?php # -- Viewing range info -- ?>
	<td class="form-title" colspan="2">
		<?php
			echo '<a class="subtle" href="view_all_set.php?type=1&amp;temporary=y&amp;' . $url_link_parameters[$t_box_title] . '">';
			echo $box_title;
			echo '</a>';
			echo ' [';
			echo '<a class="subtle" href="view_all_set.php?type=1&amp;temporary=y&amp;' . $url_link_parameters[$t_box_title] . '" target="_blank">';
			echo '^';
			echo '</a>]';
		?>
		<?php
			if ( sizeof( $rows ) > 0 ) {
				$v_start = $t_filter['per_page'] * ($f_page_number-1) +1;
				$v_end   = $v_start + sizeof( $rows ) -1;
			} else {
				$v_start = 0;
				$v_end   = 0;
			}
			echo "($v_start - $v_end / $t_bug_count)";
		?>
	</td>
</tr>

<?php mark_time( 'begin loop' ); ?>
<?php # -- Loop over bug rows and create $v_* variables -- ?>
<?php
	for($i=0; $i < sizeof( $rows ); $i++) {
		# prefix bug data with v_

		extract( $rows[$i], EXTR_PREFIX_ALL, 'v' );

		$t_summary = string_attribute( $v_summary );
		$t_last_updated = date( config_get( 'normal_date_format' ), $v_last_updated );

		# choose color based on status
		$status_color = get_status_color( $v_status );

		# grab the bugnote count
		# @@@ thraxisp - not used???
#		$bugnote_info = bug_get_bugnote_stats( $v_id );

		# Check for attachments
		$t_attachment_count = 0;
		if (  ( file_can_view_bug_attachments( $v_id ) ) ) {
		   $t_attachment_count = file_bug_attachment_count( $v_id );
		}

		# grab the project name
		$project_name = project_get_field( $v_project_id, 'name' );
?>

<tr bgcolor="<?php echo $status_color ?>">
	<?php # -- Bug ID and details link + Pencil shortcut -- ?>
	<td class="center" valign="top" width ="0" nowrap>
		<span class="small">
		<?php
			print_bug_link( $v_id );

			echo '<br />';

			if ( !bug_is_readonly( $v_id ) && access_has_bug_level( $t_update_bug_threshold, $v_id ) ) {
				echo '<a href="' . string_get_bug_update_url( $v_id ) . '"><img border="0" src="' . $t_icon_path . 'update.png' . '" alt="' . lang_get( 'update_bug_button' ) . '" /></a>';
			}
			print_status_icon( $v_priority );
			if ( 0 < $t_attachment_count ) {
				echo '<a href="' . string_get_bug_view_url( $v_id ) . '#attachments">';
				echo '<img border="0" src="' . $t_icon_path . 'attachment.png' . '"';
				echo ' alt="' . lang_get( 'attachment_alt' ) . '"';
				echo ' title="' . $t_attachment_count . ' ' . lang_get( 'attachments' ) . '"';
				echo ' />';
				echo '</a>';
			}
			if ( VS_PRIVATE == $v_view_state ) {
				echo '<img src="' . $t_icon_path . 'protected.gif" width="8" height="15" alt="' . lang_get( 'private' ) . '" />';
			}
		?>
		</span>
	</td>

	<?php # -- Summary -- ?>
	<td class="left" valign="top" width="100%">
		<span class="small">
		<?php
			echo $t_summary;
		?>
		<br />
		<?php
			# type project name if viewing 'all projects' or bug is in subproject
			if ( ON == config_get( 'show_bug_project_links' ) &&
				helper_get_current_project() != $v_project_id ) {
				echo '[';
				print( $project_name );
				echo '] ';
			}
			echo string_display( $v_category );

			if ( $v_last_updated > strtotime( '-'.$t_filter['highlight_changed'].' hours' ) ) {
				echo ' - <b>' . $t_last_updated . '</b>';
			} else {
				echo ' - ' . $t_last_updated;
			}
		?>
		</span>
	</td>
</tr>
<?php # -- end of Repeating bug row -- ?>
<?php
	}
?>
<?php # -- ====================== end of BUG LIST ========================= -- ?>

</table>

<?php mark_time( 'end loop' ); ?>
