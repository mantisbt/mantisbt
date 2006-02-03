<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2005  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: view_all_inc.php,v 1.161.10.1 2006-01-24 04:10:44 thraxisp Exp $
	# --------------------------------------------------------
?>
<?php
	$t_core_path = config_get( 'core_path' );

	require_once( $t_core_path.'current_user_api.php' );
	require_once( $t_core_path.'bug_api.php' );
	require_once( $t_core_path.'string_api.php' );
	require_once( $t_core_path.'date_api.php' );
	require_once( $t_core_path.'icon_api.php' );
	require_once( $t_core_path.'columns_api.php' );

	$t_filter = current_user_get_bug_filter();

	list( $t_sort, ) = split( ',', $t_filter['sort'] );
	list( $t_dir, ) = split( ',', $t_filter['dir'] );

	$t_checkboxes_exist = false;

	$t_icon_path = config_get( 'icon_path' );
	$t_update_bug_threshold = config_get( 'update_bug_threshold' );

	$t_columns = helper_call_custom_function( 'get_columns_to_view', array( COLUMNS_TARGET_VIEW_PAGE ) );
	$col_count = sizeof( $t_columns );

	$t_filter_position = config_get( 'filter_position' );

	# -- ====================== FILTER FORM ========================= --
	if ( ( $t_filter_position & FILTER_POSITION_TOP ) == FILTER_POSITION_TOP ) {
		filter_draw_selection_area( $f_page_number );
	}
	# -- ====================== end of FILTER FORM ================== --


	# -- ====================== BUG LIST ============================ --

	if ( STATUS_LEGEND_POSITION_TOP == config_get( 'status_legend_position' ) ) {
		html_status_legend();
	}

# @@@ (thraxisp) this may want a browser check  ( MS IE >= 5.0, Mozilla >= 1.0, Safari >=1.2, ...)
	if ( ( ON == config_get( 'dhtml_filters' ) ) && ( ON == config_get( 'use_javascript' ) ) ){
		?>
		<script type="text/javascript">
		<!--
			var string_loading = '<?php echo lang_get( 'loading' );?>';
		// -->
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
				$v_start = $t_filter['per_page'] * (int)($f_page_number-1) +1;
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

			print_page_links( 'view_all_bug_page.php', 1, $t_page_count, (int)$f_page_number );
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
		global $t_columns, $t_filter;

		$t_in_stickies = ( 'on' == $t_filter['sticky_issues'] );

		mark_time( 'begin loop' );

		# -- Loop over bug rows --

		for( $i=0; $i < sizeof( $p_rows ); $i++ ) {
			$t_row = $p_rows[$i];

			if ( ( 0 == $t_row['sticky'] ) && ( 0 == $i ) ) {
				$t_in_stickies = false;
			}
			if ( ( 0 == $t_row['sticky'] ) && $t_in_stickies ) {	# demarcate stickies, if any have been shown
?>
               <tr>
                       <td class="left" colspan="<?php echo sizeof( $t_columns ); ?>" bgcolor="#999999">&nbsp;</td>
               </tr>
<?php
				$t_in_stickies = false;
			}

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


	write_bug_rows($rows);
	# -- ====================== end of BUG LIST ========================= --

	# -- ====================== MASS BUG MANIPULATION =================== --
?>
	<tr>
		<td class="left" colspan="<?php echo $col_count-2; ?>">
<?php
		if ( $t_checkboxes_exist && ON == config_get( 'use_javascript' ) ) {
			echo "<input type=\"checkbox\" name=\"all_bugs\" value=\"all\" onclick=\"checkall('bug_action', this.form.all_bugs.checked)\"><span class=\"small\">" . lang_get( 'select_all' ) . '</span>';
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
				<?php print_page_links( 'view_all_bug_page.php', 1, $t_page_count, (int)$f_page_number ) ?>
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

	# -- ====================== FILTER FORM ========================= --
	if ( ( $t_filter_position & FILTER_POSITION_BOTTOM ) == FILTER_POSITION_BOTTOM ) {
		filter_draw_selection_area( $f_page_number );
	}
	# -- ====================== end of FILTER FORM ================== --
?>
