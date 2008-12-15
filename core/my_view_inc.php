<?php
# Mantis - a php based bugtracking system

# Mantis is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# Mantis is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with Mantis.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright (C) 2002 - 2009  Mantis Team   - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

$t_core_path = config_get( 'core_path' );

/**
 * requires current_user_api
 */
require_once( $t_core_path . 'current_user_api.php' );
/**
 * requires bug_api
 */
 require_once( $t_core_path . 'bug_api.php' );
/**
 * requires string_api
 */
require_once( $t_core_path . 'string_api.php' );
/**
 * requires date_api
 */
require_once( $t_core_path . 'date_api.php' );
/**
 * requires icon_api
 */
require_once( $t_core_path . 'icon_api.php' );

$t_filter = current_user_get_bug_filter();

if( $t_filter === false ) {
	$t_filter = filter_get_default();
}

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
	FILTER_PROPERTY_CATEGORY => Array(
		'0' => META_FILTER_ANY,
	),
	FILTER_PROPERTY_SEVERITY_ID => Array(
		'0' => META_FILTER_ANY,
	),
	FILTER_PROPERTY_STATUS_ID => Array(
		'0' => META_FILTER_ANY,
	),
	FILTER_PROPERTY_HIGHLIGHT_CHANGED => $t_default_show_changed,
	FILTER_PROPERTY_REPORTER_ID => Array(
		'0' => META_FILTER_ANY,
	),
	FILTER_PROPERTY_HANDLER_ID => Array(
		'0' => $t_current_user_id,
	),
	FILTER_PROPERTY_RESOLUTION_ID => Array(
		'0' => META_FILTER_ANY,
	),
	FILTER_PROPERTY_PRODUCT_BUILD => Array(
		'0' => META_FILTER_ANY,
	),
	FILTER_PROPERTY_PRODUCT_VERSION => Array(
		'0' => META_FILTER_ANY,
	),
	FILTER_PROPERTY_HIDE_STATUS_ID => Array(
		'0' => $t_bug_resolved_status_threshold,
	),
	FILTER_PROPERTY_MONITOR_USER_ID => Array(
		'0' => META_FILTER_ANY,
	),
);
$url_link_parameters['assigned'] = FILTER_PROPERTY_HANDLER_ID . '=' . $t_current_user_id . '&amp;' . FILTER_PROPERTY_HIDE_STATUS_ID . '=' . $t_bug_resolved_status_threshold;

$c_filter['recent_mod'] = array(
	FILTER_PROPERTY_CATEGORY => Array(
		'0' => META_FILTER_ANY,
	),
	FILTER_PROPERTY_SEVERITY_ID => Array(
		'0' => META_FILTER_ANY,
	),
	FILTER_PROPERTY_STATUS_ID => Array(
		'0' => META_FILTER_ANY,
	),
	FILTER_PROPERTY_HIGHLIGHT_CHANGED => $t_default_show_changed,
	FILTER_PROPERTY_REPORTER_ID => Array(
		'0' => META_FILTER_ANY,
	),
	FILTER_PROPERTY_HANDLER_ID => Array(
		'0' => META_FILTER_ANY,
	),
	FILTER_PROPERTY_RESOLUTION_ID => Array(
		'0' => META_FILTER_ANY,
	),
	FILTER_PROPERTY_PRODUCT_BUILD => Array(
		'0' => META_FILTER_ANY,
	),
	FILTER_PROPERTY_PRODUCT_VERSION => Array(
		'0' => META_FILTER_ANY,
	),
	FILTER_PROPERTY_HIDE_STATUS_ID => Array(
		'0' => META_FILTER_NONE,
	),
	FILTER_PROPERTY_MONITOR_USER_ID => Array(
		'0' => META_FILTER_ANY,
	),
);
$url_link_parameters['recent_mod'] = FILTER_PROPERTY_HIDE_STATUS_ID . '=none';

$c_filter['reported'] = array(
	FILTER_PROPERTY_CATEGORY => Array(
		'0' => META_FILTER_ANY,
	),
	FILTER_PROPERTY_SEVERITY_ID => Array(
		'0' => META_FILTER_ANY,
	),
	FILTER_PROPERTY_STATUS_ID => Array(
		'0' => META_FILTER_ANY,
	),
	FILTER_PROPERTY_HIGHLIGHT_CHANGED => $t_default_show_changed,
	FILTER_PROPERTY_REPORTER_ID => Array(
		'0' => $t_current_user_id,
	),
	FILTER_PROPERTY_HANDLER_ID => Array(
		'0' => META_FILTER_ANY,
	),
	FILTER_PROPERTY_SORT_FIELD_NAME => 'last_updated',
	FILTER_PROPERTY_RESOLUTION_ID => Array(
		'0' => META_FILTER_ANY,
	),
	FILTER_PROPERTY_PRODUCT_BUILD => Array(
		'0' => META_FILTER_ANY,
	),
	FILTER_PROPERTY_PRODUCT_VERSION => Array(
		'0' => META_FILTER_ANY,
	),
	FILTER_PROPERTY_HIDE_STATUS_ID => Array(
		'0' => $t_hide_status_default,
	),
	FILTER_PROPERTY_MONITOR_USER_ID => Array(
		'0' => META_FILTER_ANY,
	),
);
$url_link_parameters['reported'] = FILTER_PROPERTY_REPORTER_ID . '=' . $t_current_user_id . '&amp;' . FILTER_PROPERTY_HIDE_STATUS_ID . '=' . $t_hide_status_default;

$c_filter['resolved'] = array(
	FILTER_PROPERTY_CATEGORY => Array(
		'0' => META_FILTER_ANY,
	),
	FILTER_PROPERTY_SEVERITY_ID => Array(
		'0' => META_FILTER_ANY,
	),
	FILTER_PROPERTY_STATUS_ID => Array(
		'0' => $t_bug_resolved_status_threshold,
	),
	FILTER_PROPERTY_HIGHLIGHT_CHANGED => $t_default_show_changed,
	FILTER_PROPERTY_REPORTER_ID => Array(
		'0' => META_FILTER_ANY,
	),
	FILTER_PROPERTY_HANDLER_ID => Array(
		'0' => META_FILTER_ANY,
	),
	FILTER_PROPERTY_RESOLUTION_ID => Array(
		'0' => META_FILTER_ANY,
	),
	FILTER_PROPERTY_PRODUCT_BUILD => Array(
		'0' => META_FILTER_ANY,
	),
	FILTER_PROPERTY_PRODUCT_VERSION => Array(
		'0' => META_FILTER_ANY,
	),
	FILTER_PROPERTY_HIDE_STATUS_ID => Array(
		'0' => $t_hide_status_default,
	),
	FILTER_PROPERTY_MONITOR_USER_ID => Array(
		'0' => META_FILTER_ANY,
	),
);
$url_link_parameters['resolved'] = FILTER_PROPERTY_STATUS_ID . '=' . $t_bug_resolved_status_threshold . '&amp;' . FILTER_PROPERTY_HIDE_STATUS_ID . '=' . $t_bug_resolved_status_threshold;

$c_filter['unassigned'] = array(
	FILTER_PROPERTY_CATEGORY => Array(
		'0' => META_FILTER_ANY,
	),
	FILTER_PROPERTY_SEVERITY_ID => Array(
		'0' => META_FILTER_ANY,
	),
	FILTER_PROPERTY_STATUS_ID => Array(
		'0' => META_FILTER_ANY,
	),
	FILTER_PROPERTY_HIGHLIGHT_CHANGED => $t_default_show_changed,
	FILTER_PROPERTY_REPORTER_ID => Array(
		'0' => META_FILTER_ANY,
	),
	FILTER_PROPERTY_HANDLER_ID => Array(
		'0' => META_FILTER_NONE,
	),
	FILTER_PROPERTY_RESOLUTION_ID => Array(
		'0' => META_FILTER_ANY,
	),
	FILTER_PROPERTY_PRODUCT_BUILD => Array(
		'0' => META_FILTER_ANY,
	),
	FILTER_PROPERTY_PRODUCT_VERSION => Array(
		'0' => META_FILTER_ANY,
	),
	FILTER_PROPERTY_HIDE_STATUS_ID => Array(
		'0' => $t_hide_status_default,
	),
	FILTER_PROPERTY_MONITOR_USER_ID => Array(
		'0' => META_FILTER_ANY,
	),
);
$url_link_parameters['unassigned'] = FILTER_PROPERTY_HANDLER_ID . '=[none]' . '&amp;' . FILTER_PROPERTY_HIDE_STATUS_ID . '=' . $t_hide_status_default;

# TODO: check. handler value looks wrong

$c_filter['monitored'] = array(
	FILTER_PROPERTY_CATEGORY => Array(
		'0' => META_FILTER_ANY,
	),
	FILTER_PROPERTY_SEVERITY_ID => Array(
		'0' => META_FILTER_ANY,
	),
	FILTER_PROPERTY_STATUS_ID => Array(
		'0' => META_FILTER_ANY,
	),
	FILTER_PROPERTY_HIGHLIGHT_CHANGED => $t_default_show_changed,
	FILTER_PROPERTY_REPORTER_ID => Array(
		'0' => META_FILTER_ANY,
	),
	FILTER_PROPERTY_HANDLER_ID => Array(
		'0' => META_FILTER_ANY,
	),
	FILTER_PROPERTY_RESOLUTION_ID => Array(
		'0' => META_FILTER_ANY,
	),
	FILTER_PROPERTY_PRODUCT_BUILD => Array(
		'0' => META_FILTER_ANY,
	),
	FILTER_PROPERTY_PRODUCT_VERSION => Array(
		'0' => META_FILTER_ANY,
	),
	FILTER_PROPERTY_HIDE_STATUS_ID => Array(
		'0' => $t_hide_status_default,
	),
	FILTER_PROPERTY_MONITOR_USER_ID => Array(
		'0' => $t_current_user_id,
	),
);
$url_link_parameters['monitored'] = FILTER_PROPERTY_MONITOR_USER_ID . '=' . $t_current_user_id . '&amp;' . FILTER_PROPERTY_HIDE_STATUS_ID . '=' . $t_hide_status_default;


$c_filter['feedback'] = array(
	FILTER_PROPERTY_CATEGORY => Array(
		'0' => META_FILTER_ANY,
	),
	FILTER_PROPERTY_SEVERITY_ID => Array(
		'0' => META_FILTER_ANY,
	),
	FILTER_PROPERTY_STATUS_ID => Array(
		'0' => FEEDBACK,
	),
	FILTER_PROPERTY_HIGHLIGHT_CHANGED => $t_default_show_changed,
	FILTER_PROPERTY_REPORTER_ID => Array(
		'0' => $t_current_user_id,
	),
	FILTER_PROPERTY_HANDLER_ID => Array(
		'0' => META_FILTER_ANY,
	),
	FILTER_PROPERTY_RESOLUTION_ID => Array(
		'0' => META_FILTER_ANY,
	),
	FILTER_PROPERTY_PRODUCT_BUILD => Array(
		'0' => META_FILTER_ANY,
	),
	FILTER_PROPERTY_PRODUCT_VERSION => Array(
		'0' => META_FILTER_ANY,
	),
	FILTER_PROPERTY_HIDE_STATUS_ID => Array(
		'0' => $t_hide_status_default,
	),
	FILTER_PROPERTY_MONITOR_USER_ID => Array(
		'0' => META_FILTER_ANY,
	),
);
$url_link_parameters['feedback'] = FILTER_PROPERTY_REPORTER_ID . '=' . $t_current_user_id . '&amp;' . FILTER_PROPERTY_STATUS_ID . '=' . FEEDBACK . '&amp;' . FILTER_PROPERTY_HIDE_STATUS_ID . '=' . $t_hide_status_default;

$c_filter['verify'] = array(
	FILTER_PROPERTY_CATEGORY => Array(
		'0' => META_FILTER_ANY,
	),
	FILTER_PROPERTY_SEVERITY_ID => Array(
		'0' => META_FILTER_ANY,
	),
	FILTER_PROPERTY_STATUS_ID => Array(
		'0' => $t_bug_resolved_status_threshold,
	),
	FILTER_PROPERTY_HIGHLIGHT_CHANGED => $t_default_show_changed,
	FILTER_PROPERTY_REPORTER_ID => Array(
		'0' => $t_current_user_id,
	),
	FILTER_PROPERTY_HANDLER_ID => Array(
		'0' => META_FILTER_ANY,
	),
	FILTER_PROPERTY_RESOLUTION_ID => Array(
		'0' => META_FILTER_ANY,
	),
	FILTER_PROPERTY_PRODUCT_BUILD => Array(
		'0' => META_FILTER_ANY,
	),
	FILTER_PROPERTY_PRODUCT_VERSION => Array(
		'0' => META_FILTER_ANY,
	),
	FILTER_PROPERTY_HIDE_STATUS_ID => Array(
		'0' => $t_hide_status_default,
	),
	FILTER_PROPERTY_MONITOR_USER_ID => Array(
		'0' => META_FILTER_ANY,
	),
);
$url_link_parameters['verify'] = FILTER_PROPERTY_REPORTER_ID . '=' . $t_current_user_id . '&amp;' . FILTER_PROPERTY_STATUS_ID . '=' . $t_bug_resolved_status_threshold;

$rows = filter_get_bug_rows( $f_page_number, $t_per_page, $t_page_count, $t_bug_count, $c_filter[$t_box_title] );

# Improve performance by caching category data in one pass
if( helper_get_current_project() == 0 ) {
	$t_categories = array();
	foreach( $rows as $t_row ) {
		$t_categories[] = $t_row['category_id'];
	}

	category_cache_array_rows( array_unique( $t_categories ) );
}

$t_filter = array_merge( $c_filter[$t_box_title], $t_filter );

$box_title = lang_get( 'my_view_title_' . $t_box_title );
?>


<?php
# -- ====================== BUG LIST ========================= --?>

<table class="width100" cellspacing="1">
<?php
# -- Navigation header row --?>
<tr>
	<?php
# -- Viewing range info --?>
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
			if( sizeof( $rows ) > 0 ) {
	$v_start = $t_filter[FILTER_PROPERTY_ISSUES_PER_PAGE] * ( $f_page_number - 1 ) + 1;
	$v_end = $v_start + sizeof( $rows ) - 1;
}
else {
	$v_start = 0;
	$v_end = 0;
}
echo "($v_start - $v_end / $t_bug_count)";
?>
	</td>
</tr>

<?php mark_time( 'begin loop' );?>
<?php
# -- Loop over bug rows and create $v_* variables --?>
<?php
	for( $i = 0;$i < sizeof( $rows );$i++ ) {

	# prefix bug data with v_

	extract( $rows[$i], EXTR_PREFIX_ALL, 'v' );

	$t_summary = string_display_line_links( $v_summary );
	$t_last_updated = date( config_get( 'normal_date_format' ), $v_last_updated );

	# choose color based on status
	$status_color = get_status_color( $v_status );

	# Check for attachments
	$t_attachment_count = 0;
	if(( file_can_view_bug_attachments( $v_id ) ) ) {
		$t_attachment_count = file_bug_attachment_count( $v_id );
	}

	# grab the project name
	$project_name = project_get_field( $v_project_id, 'name' );
	?>

<tr bgcolor="<?php echo $status_color?>">
	<?php
	# -- Bug ID and details link + Pencil shortcut --?>
	<td class="center" valign="top" width ="0" nowrap="nowrap">
		<span class="small">
		<?php
			print_bug_link( $v_id );

	echo '<br />';

	if( !bug_is_readonly( $v_id ) && access_has_bug_level( $t_update_bug_threshold, $v_id ) ) {
		echo '<a href="' . string_get_bug_update_url( $v_id ) . '"><img border="0" src="' . $t_icon_path . 'update.png' . '" alt="' . lang_get( 'update_bug_button' ) . '" /></a>';
	}

	if( ON == config_get( 'show_priority_text' ) ) {
		print_formatted_priority_string( $v_status, $v_priority );
	} else {
		print_status_icon( $v_priority );
	}

	if( 0 < $t_attachment_count ) {
		echo '<a href="' . string_get_bug_view_url( $v_id ) . '#attachments">';
		echo '<img border="0" src="' . $t_icon_path . 'attachment.png' . '"';
		echo ' alt="' . lang_get( 'attachment_alt' ) . '"';
		echo ' title="' . $t_attachment_count . ' ' . lang_get( 'attachments' ) . '"';
		echo ' />';
		echo '</a>';
	}
	if( VS_PRIVATE == $v_view_state ) {
		echo '<img src="' . $t_icon_path . 'protected.gif" width="8" height="15" alt="' . lang_get( 'private' ) . '" />';
	}
	?>
		</span>
	</td>

	<?php
	# -- Summary --?>
	<td class="left" valign="top" width="100%">
		<span class="small">
		<?php
			echo $t_summary;
	?>
		<br />
		<?php
	# type project name if viewing 'all projects' or bug is in subproject
	if( ON == config_get( 'show_bug_project_links' ) && helper_get_current_project() != $v_project_id ) {
		echo string_display( category_full_name( $v_category_id ) );
	} else {
		echo string_display( category_full_name( $v_category_id, false ) );
	}

	if( $v_last_updated > strtotime( '-' . $t_filter[FILTER_PROPERTY_HIGHLIGHT_CHANGED] . ' hours' ) ) {
		echo ' - <b>' . $t_last_updated . '</b>';
	} else {
		echo ' - ' . $t_last_updated;
	}
	?>
		</span>
	</td>
</tr>
<?php
	# -- end of Repeating bug row --?>
<?php
}
?>
<?php
# -- ====================== end of BUG LIST ========================= --?>

</table>

<?php
// Free the memory allocated for the rows in this box since it is not longer needed.
unset( $rows );

mark_time( 'end loop' );
