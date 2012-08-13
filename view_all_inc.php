<?php
# MantisBT - a php based bugtracking system

# MantisBT is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# MantisBT is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with MantisBT.  If not, see <http://www.gnu.org/licenses/>.

	/**
	 * @package MantisBT
	 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	 * @copyright Copyright (C) 2002 - 2012  MantisBT Team - mantisbt-dev@lists.sourceforge.net
	 * @link http://www.mantisbt.org
	 */

	/**
	 * requires current_user_api
	 */
	require_once( 'current_user_api.php' );
	/**
	 * requires bug_api
	 */
	require_once( 'bug_api.php' );
	/**
	 * requires string_api
	 */
	require_once( 'string_api.php' );
	/**
	 * requires date_api
	 */
	require_once( 'date_api.php' );
	/**
	 * requires icon_api
	 */
	require_once( 'icon_api.php' );
	/**
	 * requires columns_api
	 */
	require_once( 'columns_api.php' );

	$t_filter = current_user_get_bug_filter();
	# NOTE: this check might be better placed in current_user_get_bug_filter()
	if( $t_filter === false ) {
		$t_filter = filter_get_default();
	}

	list( $t_sort, ) = explode( ',', $t_filter['sort'] );
	list( $t_dir, ) = explode( ',', $t_filter['dir'] );

	$g_checkboxes_exist = false;

	$t_icon_path = config_get( 'icon_path' );

	# Improve performance by caching category data in one pass
	if( helper_get_current_project() > 0 ) {
		category_get_all_rows( helper_get_current_project() );
	} else {
		$t_categories = array();
		foreach( $rows as $t_row ) {
			$t_categories[] = $t_row->category_id;
		}
		category_cache_array_rows( array_unique( $t_categories ) );
	}
	$t_columns = helper_get_columns_to_view( COLUMNS_TARGET_VIEW_PAGE );

	$col_count = count( $t_columns );

	$t_filter_position = config_get( 'filter_position' );

	# -- ====================== FILTER FORM ========================= --
	if( ( $t_filter_position & FILTER_POSITION_TOP ) == FILTER_POSITION_TOP ) {
		filter_draw_selection_area( $f_page_number );
	}
	# -- ====================== end of FILTER FORM ================== --


	# -- ====================== BUG LIST ============================ --

	$t_status_legend_position = config_get( 'status_legend_position' );

	if( $t_status_legend_position == STATUS_LEGEND_POSITION_TOP || $t_status_legend_position == STATUS_LEGEND_POSITION_BOTH ) {
		html_status_legend();
	}

	/** @todo (thraxisp) this may want a browser check  ( MS IE >= 5.0, Mozilla >= 1.0, Safari >=1.2, ...) */
	if( ( ON == config_get( 'dhtml_filters' ) ) && ( ON == config_get( 'use_javascript' ) ) ){
		?>
		<script type="text/javascript" language="JavaScript">
		<!--
			var string_loading = '<?php echo lang_get( 'loading' );?>';
		// -->
		</script>
		<?php
			html_javascript_link( 'xmlhttprequest.js');
			html_javascript_link( 'addLoadEvent.js');
			html_javascript_link( 'dynamic_filters.js');
	}
?>
<br />
<form name="bug_action" method="get" action="bug_actiongroup_page.php">
<?php # CSRF protection not required here - form does not result in modifications ?>
<table id="buglist" class="width100" cellspacing="1">
<tr>
	<td class="form-title" colspan="<?php echo $col_count; ?>">
		<span class="floatleft">
		<?php
			# -- Viewing range info --

			$v_start = 0;
			$v_end   = 0;

			if( count( $rows ) > 0 ) {
				$v_start = $t_filter['per_page'] * ($f_page_number - 1) + 1;
				$v_end = $v_start + count( $rows ) - 1;
			}

			echo lang_get( 'viewing_bugs_title' );
			echo " ($v_start - $v_end / $t_bug_count)";
		?> </span>

		<span class="floatleft small"> <?php
				# -- Print and Export links --
				echo '&#160;';
				print_bracket_link( 'print_all_bug_page.php', lang_get( 'print_all_bug_page_link' ) );
				echo '&#160;';
				print_bracket_link( 'csv_export.php', lang_get( 'csv_export' ) );
				echo '&#160;';
				print_bracket_link( 'excel_xml_export.php', lang_get( 'excel_export' ) );

				$t_event_menu_options = $t_links = event_signal( 'EVENT_MENU_FILTER' );

				foreach( $t_event_menu_options as $t_plugin => $t_plugin_menu_options ) {
					foreach( $t_plugin_menu_options as $t_callback => $t_callback_menu_options ) {
						if( !is_array( $t_callback_menu_options ) ) {
							$t_callback_menu_options = array( $t_callback_menu_options );
						}

						foreach( $t_callback_menu_options as $t_menu_option ) {
							if( $t_menu_option ) {
								print_bracket_link_prepared( $t_menu_option );
							}
						}
					}
				}
		?> </span>

		<span class="floatright small"><?php
			# -- Page number links --
			$f_filter	= gpc_get_int( 'filter', 0);
			print_page_links( 'view_all_bug_page.php', 1, $t_page_count, (int)$f_page_number, $f_filter );
		?> </span>
	</td>
</tr>
<?php # -- Bug list column header row -- ?>
<tr class="row-category">
<?php
		$t_title_function = 'print_column_title';
		foreach( $t_columns as $t_column ) {
			helper_call_custom_function( $t_title_function, array( $t_column ) );
		}
?>
</tr>

<?php # -- Spacer row -- ?>
<tr class="spacer">
	<td colspan="<?php echo $col_count; ?>"></td>
</tr>
<?php
	function write_bug_rows( $p_rows )
	{
		global $t_columns, $t_filter;

		$t_in_stickies = ( $t_filter && ( 'on' == $t_filter['sticky_issues'] ) );

		# pre-cache custom column data
		columns_plugin_cache_issue_data( $p_rows );

		# -- Loop over bug rows --

		$t_rows = count( $p_rows );
		for( $i=0; $i < $t_rows; $i++ ) {
			$t_row = $p_rows[$i];

			if( ( 0 == $t_row->sticky ) && ( 0 == $i ) ) {
				$t_in_stickies = false;
			}
			if( ( 0 == $t_row->sticky ) && $t_in_stickies ) {	# demarcate stickies, if any have been shown
?>
				<tr>
					<td class="left" colspan="<?php echo count( $t_columns ); ?>" bgcolor="#999999">&#160;</td>
				</tr>
<?php
				$t_in_stickies = false;
			}

			# choose color based on status
			$status_color = get_status_color( $t_row->status, auth_get_current_user_id(), $t_row->project_id );

			echo '<tr bgcolor="', $status_color, '" border="1" valign="top">';

			$t_column_value_function = 'print_column_value';
			foreach( $t_columns as $t_column ) {
				helper_call_custom_function( $t_column_value_function, array( $t_column, $t_row ) );
			}

			echo '</tr>';
		}
	}


	write_bug_rows( $rows );
	# -- ====================== end of BUG LIST ========================= --

	# -- ====================== MASS BUG MANIPULATION =================== --
?>
	<tr>
		<td class="left" colspan="<?php echo $col_count; ?>">
			<span class="floatleft">
<?php
		if( $g_checkboxes_exist && ON == config_get( 'use_javascript' ) ) {
			echo "<input type=\"checkbox\" name=\"all_bugs\" value=\"all\" onclick=\"checkall('bug_action', this.form.all_bugs.checked)\" /><span class=\"small\">" . lang_get( 'select_all' ) . '</span>';
		}

		if( $g_checkboxes_exist ) {
?>
			<select name="action">
				<?php print_all_bug_action_option_list( $t_unique_project_ids ) ?>
			</select>
			<input type="submit" class="button" value="<?php echo lang_get( 'ok' ); ?>" />
<?php
		} else {
			echo '&#160;';
		}
?>			</span>
			<span class="floatright small">
				<?php
					$f_filter	= gpc_get_int( 'filter', 0);
					print_page_links( 'view_all_bug_page.php', 1, $t_page_count, (int)$f_page_number, $f_filter );
				?>
			</span>
		</td>
	</tr>
<?php # -- ====================== end of MASS BUG MANIPULATION ========================= -- ?>
</table>
</form>

<?php

	if( $t_status_legend_position == STATUS_LEGEND_POSITION_BOTTOM || $t_status_legend_position == STATUS_LEGEND_POSITION_BOTH ) {
		html_status_legend();
	}

	# -- ====================== FILTER FORM ========================= --
	if( ( $t_filter_position & FILTER_POSITION_BOTTOM ) == FILTER_POSITION_BOTTOM ) {
		filter_draw_selection_area( $f_page_number );
	}
	# -- ====================== end of FILTER FORM ================== --
