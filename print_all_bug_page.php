<?php
# MantisBT - A PHP based bugtracking system

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
 * Bugs to display / print / export can be selected with the checkboxes
 * A printing Options link allows to choose the fields to export
 * Export :
 *  - the bugs displayed in print_all_bug_page.php are saved in a .doc or .xls file
 *  - the IE icons allows to see or directly print the same result
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses authentication_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses filter_api.php
 * @uses filter_constants_inc.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses project_api.php
 * @uses string_api.php
 * @uses utility_api.php
 */

require_once( 'core.php' );
require_api( 'authentication_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'filter_api.php' );
require_api( 'filter_constants_inc.php' );
require_api( 'gpc_api.php' );
require_api( 'helper_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );
require_api( 'print_api.php' );
require_api( 'project_api.php' );
require_api( 'string_api.php' );
require_api( 'utility_api.php' );

auth_ensure_user_authenticated();

$f_search		= gpc_get_string( FILTER_PROPERTY_SEARCH, false ); /** @todo need a better default */
$f_offset		= gpc_get_int( 'offset', 0 );

$t_cookie_value_id = gpc_get_cookie( config_get( 'view_all_cookie' ), '' );
$t_cookie_value = filter_db_get_filter( $t_cookie_value_id );

$f_highlight_changed 	= 0;
$f_sort 				= null;
$f_dir		 			= null;
$t_project_id 			= 0;

$t_columns = helper_get_columns_to_view( COLUMNS_TARGET_PRINT_PAGE );
$t_num_of_columns = count( $t_columns );

# check to see if the cookie exists
if ( !is_blank( $t_cookie_value ) ) {

	# check to see if new cookie is needed
	if ( !filter_is_cookie_valid() ) {
		print_header_redirect( 'view_all_set.php?type=0&print=1' );
	}

	$t_setting_arr = explode( '#', $t_cookie_value, 2 );
	$t_filter_cookie_arr = unserialize( $t_setting_arr[1] );

	$f_highlight_changed 	= $t_filter_cookie_arr[ FILTER_PROPERTY_HIGHLIGHT_CHANGED ];
	$f_sort 				= $t_filter_cookie_arr[ FILTER_PROPERTY_SORT_FIELD_NAME ];
	$f_dir		 			= $t_filter_cookie_arr[ FILTER_PROPERTY_SORT_DIRECTION ];
	$t_project_id 			= helper_get_current_project();
}

# This replaces the actual search that used to be here
$f_page_number = gpc_get_int( 'page_number', 1 );
$t_per_page = -1;
$t_bug_count = null;
$t_page_count = null;

$t_result = filter_get_bug_rows( $f_page_number, $t_per_page, $t_page_count, $t_bug_count );
$row_count = count( $t_result );

# pre-cache custom column data
columns_plugin_cache_issue_data( $t_result );

# for export
$t_show_flag = gpc_get_int( 'show_flag', 0 );

html_page_top1();
html_head_end();
html_body_begin();
?>

<table class="width100"><tr><td class="form-title">
	<div class="center">
		<?php echo string_display( config_get( 'window_title' ) ) . ' - ' . string_display( project_get_name( $t_project_id ) ); ?>
	</div>
</td></tr></table>

<br />

<form method="post" action="view_all_set.php">
<?php # CSRF protection not required here - form does not result in modifications ?>
<input type="hidden" name="type" value="1" />
<input type="hidden" name="print" value="1" />
<input type="hidden" name="offset" value="0" />
<input type="hidden" name="<?php echo FILTER_PROPERTY_SORT_FIELD_NAME; ?>" value="<?php echo $f_sort ?>" />
<input type="hidden" name="<?php echo FILTER_PROPERTY_SORT_DIRECTION; ?>" value="<?php echo $f_dir ?>" />

<table class="width100" cellpadding="2px">
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
	<td colspan="<?php echo $t_num_of_columns ?>">
<?php
	if ( 'DESC' == $f_dir ) {
		$t_new_dir = 'ASC';
	} else {
		$t_new_dir = 'DESC';
	}

	$t_search = urlencode( $f_search );

	$t_icons = array(
		array( 'print_all_bug_page_word', 'word', '', 'fileicons/doc.gif', 'Word 2000' ),
		array( 'print_all_bug_page_word', 'html', 'target="_blank"', 'ie.gif', 'Word View' ) );

	foreach ( $t_icons as $t_icon ) {
		echo '<a href="' . $t_icon[0] . '.php' .
			'?' . FILTER_PROPERTY_SEARCH. "=$t_search" .
			'&amp;' . FILTER_PROPERTY_SORT_FIELD_NAME . "=$f_sort" .
			'&amp;' . FILTER_PROPERTY_SORT_DIRECTION . "=$t_new_dir" .
			'&amp;type_page=' . $t_icon[1] .
			"&amp;export=$f_export" .
			"&amp;show_flag=$t_show_flag" .
			'" ' . $t_icon[2] . '>' .
			'<img src="' . $t_icon_path . $t_icon[3] . '" alt="' . $t_icon[4] . '" /></a> ';
	}
?>
	</td>
</tr>
<?php #<SQLI> ?>
</table>

</form>

<br />

<form method="post" action="print_all_bug_page.php">
<?php # CSRF protection not required here - form does not result in modifications ?>
<table class="width100" cellspacing="1" cellpadding="2px">
<tr>
	<td class="form-title" colspan="<?php echo $t_num_of_columns / 2 + $t_num_of_columns % 2; ?>">
		<?php
			echo lang_get( 'viewing_bugs_title' );

			if ( $row_count > 0 ) {
				$v_start = $f_offset+1;
				$v_end   = $f_offset+$row_count;
			} else {
				$v_start = 0;
				$v_end   = 0;
			}
			echo "( $v_start - $v_end )";
		?>
	</td>
	<td class="right" colspan="<?php echo $t_num_of_columns / 2 ?>">
		<?php
			# print_bracket_link( 'print_all_bug_options_page.php', lang_get( 'printing_options_link' ) );
			# print_bracket_link( 'view_all_bug_page.php', lang_get( 'view_bugs_link' ) );
			# print_bracket_link( 'summary_page.php', lang_get( 'summary' ) );
		?>
	</td>
</tr>
<tr class="row-category">
	<?php
		$t_sort = $f_sort;	# used within the custom function called in the loop (@todo cleanup)
		$t_dir = $f_dir;    # used within the custom function called in the loop (@todo cleanup)

		foreach( $t_columns as $t_column ) {
			$t_title_function = 'print_column_title';
			helper_call_custom_function( $t_title_function, array( $t_column, COLUMNS_TARGET_PRINT_PAGE ) );
		}
	?>
</tr>
<tr class="spacer">
	<td colspan="9"></td>
</tr>
<?php
	for( $i=0; $i < $row_count; $i++ ) {
		$t_row = $t_result[$i];

		# alternate row colors
		$status_color = helper_alternate_colors( $i, '#ffffff', '#dddddd' );
		if ( isset( $t_bug_arr_sort[ $t_row->id ] ) || ( $t_show_flag==0 ) ) {
?>
<tr bgcolor="<?php echo $status_color ?>">
<?php
		foreach( $t_columns as $t_column ) {
			$t_column_value_function = 'print_column_value';
			helper_call_custom_function( $t_column_value_function, array( $t_column, $t_row, COLUMNS_TARGET_PRINT_PAGE ) );
		}
?>
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

<?php
html_body_end();
html_end();
