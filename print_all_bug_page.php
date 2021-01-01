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

$f_search		= gpc_get_string( FILTER_PROPERTY_SEARCH, false ); # @todo need a better default
$f_offset		= gpc_get_int( 'offset', 0 );

$f_highlight_changed 	= 0;
$f_sort 				= null;
$f_dir		 			= null;
$t_project_id 			= 0;

$t_columns = helper_get_columns_to_view( COLUMNS_TARGET_PRINT_PAGE );
$t_num_of_columns = count( $t_columns );

# Get the filter in use
$t_filter = current_user_get_bug_filter();
filter_init( $t_filter );

$f_highlight_changed = $t_filter[FILTER_PROPERTY_HIGHLIGHT_CHANGED];
$f_sort              = $t_filter[FILTER_PROPERTY_SORT_FIELD_NAME];
$f_dir               = $t_filter[FILTER_PROPERTY_SORT_DIRECTION];
$t_project_id        = helper_get_current_project();

# This replaces the actual search that used to be here
$f_page_number = gpc_get_int( 'page_number', 1 );
$t_per_page = -1;
$t_bug_count = null;
$t_page_count = null;

$t_result = filter_get_bug_rows( $f_page_number, $t_per_page, $t_page_count, $t_bug_count );
$t_row_count = count( $t_result );

# pre-cache column data
bug_cache_columns_data( $t_result, $t_columns );

# for export
$t_show_flag = gpc_get_int( 'show_flag', 0 );

layout_page_header();
?>

<table class="table table-condensed no-margin"><tr><td class="bold bigger-120">
	<div class="center">
		<?php echo string_display_line( config_get( 'window_title' ) ) . ' - ' . string_display_line( project_get_name( $t_project_id ) ); ?>
	</div>
</td></tr></table>

<form method="post" action="view_all_set.php">
<?php # CSRF protection not required here - form does not result in modifications ?>
	<input type="hidden" name="type" value="1" />
	<input type="hidden" name="print" value="1" />
	<input type="hidden" name="offset" value="0" />
	<input type="hidden" name="<?php echo FILTER_PROPERTY_SORT_FIELD_NAME; ?>" value="<?php echo $f_sort ?>" />
	<input type="hidden" name="<?php echo FILTER_PROPERTY_SORT_DIRECTION; ?>" value="<?php echo $f_dir ?>" />

<table class="table table-striped table-bordered table-condensed no-margin">
<?php
#<SQLI> Excel & Print export
#$f_bug_array stores the number of the selected rows
#$t_bug_arr_sort is used for displaying
#$f_export is a string for the word and excel pages

$f_bug_arr = gpc_get_int_array( 'bug_arr', array() );
$f_bug_arr[$t_row_count]=-1;

for( $i=0; $i < $t_row_count; $i++ ) {
	if( isset( $f_bug_arr[$i] ) ) {
		$t_index = $f_bug_arr[$i];
		$t_bug_arr_sort[$t_index]=1;
	}
}
$f_export = implode( ',', $f_bug_arr );

?>

<tr>
	<td colspan="<?php echo $t_num_of_columns ?>">
<?php
	if( 'DESC' == $f_dir ) {
		$t_new_dir = 'ASC';
	} else {
		$t_new_dir = 'DESC';
	}

	$t_search = urlencode( $f_search );

	$t_icons = array(
		array( 'print_all_bug_page_word', 'word', '', 'fa-file-word-o', 'Word 2000' ),
		array( 'print_all_bug_page_word', 'html', 'target="_blank"', 'fa-internet-explorer', 'Word View' ) );

	foreach ( $t_icons as $t_icon ) {
		$t_params = array(
			FILTER_PROPERTY_SEARCH => $t_search,
			FILTER_PROPERTY_SORT_FIELD_NAME => $f_sort,
			FILTER_PROPERTY_SORT_DIRECTION => $t_new_dir,
			'type_page' => $t_icon[1],
			'export' => $f_export,
			'show_flag' => $t_show_flag,
		);
		if( filter_is_temporary( $t_filter ) ) {
			$t_params['filter'] = filter_get_temporary_key( $t_filter );
		}

		echo '<a href="' . $t_icon[0] . '.php?' . http_build_query( $t_params ) . '" ' . $t_icon[2] . '>';
		print_icon( $t_icon[3], '', $t_icon[4] );
		echo '</a> ';
	}
?>

	</td>
</tr>
</table>
</form>

<?php
$t_form_url = 'print_all_bug_page.php';
if( filter_is_temporary( $t_filter ) ) {
	$t_form_url .='?' . filter_get_temporary_key_param( $t_filter );
}
?>
<form method="post" action="<?php echo $t_form_url ?>">
<?php # CSRF protection not required here - form does not result in modifications ?>

<table id="buglist" class="table table-striped table-bordered table-condensed no-margin">
<tr>
    <td class="bold bigger-110" colspan="<?php echo $t_num_of_columns / 2 + $t_num_of_columns % 2; ?>">
		<?php
			echo lang_get( 'viewing_bugs_title' );

			if( $t_row_count > 0 ) {
				$v_start = $f_offset+1;
				$v_end   = $f_offset+$t_row_count;
			} else {
				$v_start = 0;
				$v_end   = 0;
			}
			echo '( ' . $v_start . ' - ' . $v_end . ' )';
		?>
	</td>
<tr>
	</td>
</tr>
<tr class="row-category">
	<?php
		$t_sort_properties = filter_get_visible_sort_properties_array( $t_filter, COLUMNS_TARGET_PRINT_PAGE );
		foreach( $t_columns as $t_column ) {
			helper_call_custom_function( 'print_column_title', array( $t_column, COLUMNS_TARGET_PRINT_PAGE, $t_sort_properties ) );
		}
	?>
</tr>
<tr class="spacer">
	<td colspan="<?php echo $t_num_of_columns ?>"></td>
</tr>
</thead>

<tbody>
<?php
	for( $i=0; $i < $t_row_count; $i++ ) {
		$t_row = $t_result[$i];

		if( isset( $t_bug_arr_sort[ $t_row->id ] ) || ( $t_show_flag==0 ) ) {
?>
<tr>
<?php
			foreach( $t_columns as $t_column ) {
				helper_call_custom_function( 'print_column_value', array( $t_column, $t_row, COLUMNS_TARGET_PRINT_PAGE ) );
			}
?>
</tr>
<?php
		} # isset_loop
	} # for_loop
?>
<tr class="spacer">
    <td colspan="<?php echo $t_num_of_columns ?>"></td>
</tr>
<tr>
    <td colspan="<?php echo $t_num_of_columns ?>">
        <input type="hidden" name="show_flag" value="1" />
        <input type="submit" class="btn btn-sm btn-primary btn-white btn-round" value="<?php echo lang_get( 'hide_button' ) ?>" />
    </td>
</tr>
</table>
</form>
<?php
layout_body_javascript();
html_body_end();
html_end();
