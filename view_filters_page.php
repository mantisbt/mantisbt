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
 * View Filters Page
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses access_api.php
 * @uses authentication_api.php
 * @uses compress_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses current_user_api.php
 * @uses custom_field_api.php
 * @uses filter_api.php
 * @uses filter_constants_inc.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses string_api.php
 * @uses version_api.php
 */

require_once( 'core.php' );
require_api( 'access_api.php' );
require_api( 'authentication_api.php' );
require_api( 'compress_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'current_user_api.php' );
require_api( 'custom_field_api.php' );
require_api( 'filter_api.php' );
require_api( 'filter_constants_inc.php' );
require_api( 'gpc_api.php' );
require_api( 'helper_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );
require_api( 'print_api.php' );
require_api( 'string_api.php' );
require_api( 'version_api.php' );

auth_ensure_user_authenticated();

compress_enable();

html_page_top();

$t_filter = current_user_get_bug_filter();
filter_init( $t_filter );

$t_target_field = rtrim( gpc_get_string( 'target_field', '' ), '[]' );
if( !isset( $t_filter[$t_target_field] ) ) {
	$t_target_field = '';
}

/*
 * @TODO clean this code
 * $t_accessible_custom_fields_* are used as globals by filter api !!
 */
$t_project_id = helper_get_current_project();

$t_current_user_access_level = current_user_get_access_level();
$t_accessible_custom_fields_ids = array();
$t_accessible_custom_fields_names = array();
$t_accessible_custom_fields_type = array() ;
$t_accessible_custom_fields_values = array();
$t_filter_cols = config_get( 'filter_custom_fields_per_row' );
$t_custom_cols = 1;
$t_custom_rows = 0;

#get valid target fields
$t_fields = helper_get_columns_to_view();
$t_n_fields = count( $t_fields );
for( $i=0; $i < $t_n_fields; $i++ ) {
	if( in_array( $t_fields[$i], array( 'selection', 'edit', 'bugnotes_count', 'attachment_count' ) ) ) {
		unset( $t_fields[$i] );
	}
}

if( ON == config_get( 'filter_by_custom_fields' ) ) {
	$t_custom_cols = $t_filter_cols;
	$t_custom_fields = custom_field_get_linked_ids( $t_project_id );

	foreach ( $t_custom_fields as $t_cfid ) {
		$t_field_info = custom_field_cache_row( $t_cfid, true );
		if( $t_field_info['access_level_r'] <= $t_current_user_access_level && $t_field_info['filter_by'] ) {
			$t_accessible_custom_fields_ids[] = $t_cfid;
			$t_accessible_custom_fields_names[] = $t_field_info['name'];
			$t_accessible_custom_fields_types[] = $t_field_info['type'];
			$t_accessible_custom_fields_values[] = custom_field_distinct_values( $t_field_info, $t_project_id );
			$t_fields[] = 'custom_' . $t_field_info['name'];
		}
	}

	if( count( $t_accessible_custom_fields_ids ) > 0 ) {
		$t_per_row = config_get( 'filter_custom_fields_per_row' );
		$t_custom_rows = ceil( count( $t_accessible_custom_fields_ids ) / $t_per_row );
	}
}

if( !in_array( $t_target_field, $t_fields ) ) {
	$t_target_field = '';
}

$f_for_screen = gpc_get_bool( 'for_screen', true );

$t_action  = 'view_all_set.php?f=3';

if( $f_for_screen == false ) {
	$t_action  = 'view_all_set.php';
}

$f_default_view_type = 'simple';
if( ADVANCED_DEFAULT == config_get( 'view_filters' ) ) {
	$f_default_view_type = 'advanced';
}

$f_view_type = gpc_get_string( 'view_type', $f_default_view_type );
if( ADVANCED_ONLY == config_get( 'view_filters' ) ) {
	$f_view_type = 'advanced';
}
if( SIMPLE_ONLY == config_get( 'view_filters' ) ) {
	$f_view_type = 'simple';
}
if( !in_array( $f_view_type, array( 'simple', 'advanced' ) ) ) {
	$f_view_type = $f_default_view_type;
}

$g_select_modifier = '';
if( 'advanced' == $f_view_type ) {
	$g_select_modifier = ' multiple="multiple" size="10"';
}

$t_filter['_view_type'] = $f_view_type;

$f_static = gpc_get_bool( 'static', false );
?>

<div class="filter-box table-container">
	<form method="post" name="filters" id="filters_form_open" action="<?php echo $t_action; ?>">
		<?php # CSRF protection not required here - form does not result in modifications ?>
		<input type="hidden" name="type" value="1" />
		<input type="hidden" name="view_type" value="<?php echo $f_view_type; ?>" />
		<?php
			if( $f_for_screen == false ) {
				print '<input type="hidden" name="print" value="1" />';
				print '<input type="hidden" name="offset" value="0" />';
			}
		?>

		<h2><?php echo lang_get('edit_filter') ?></h2>

		<div class="section-link">
			<?php
			$f_switch_view_link = 'view_filters_page.php?target_field=' . $t_target_field . '&static=' . $f_static . '&view_type=';
				if( ( SIMPLE_ONLY != config_get( 'view_filters' ) ) && ( ADVANCED_ONLY != config_get( 'view_filters' ) ) ) {
					if( 'advanced' == $f_view_type ) {
						print_bracket_link( $f_switch_view_link . 'simple', lang_get( 'simple_filters' ) );
					} else {
						print_bracket_link( $f_switch_view_link . 'advanced', lang_get( 'advanced_filters' ) );
					}
				}
			?>
		</div>

		<?php
		filter_draw_selection_inputs( $t_filter, $f_for_screen, $f_static );
		?>

		<table>
			<tbody>
				<tr>
					<td>
						<label><?php echo lang_get( 'search' ) ?>&nbsp;
							<input type="text" size="16" name="search" value="<?php echo string_html_specialchars( $t_filter['search'] ); ?>">
						</label>
					</td>
					<td class="right">
						<input type="submit" name="filter" value="<?php echo lang_get( 'filter_button' ) ?>">
					</td>
				</tr>
			</tbody>
		</table>

	</form>
</div>
<?php
html_page_bottom();
