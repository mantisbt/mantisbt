<?php
# Mantis - a php based bugtracking system

# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
# Copyright (C) 2002 - 2007  Mantis Team   - mantisbt-dev@lists.sourceforge.net

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
?>
<?php
	require_once( 'core.php' );
	$t_core_path = config_get( 'core_path' );

	require_once( $t_core_path.'compress_api.php' );
	require_once( $t_core_path.'filter_api.php' );
	require_once( $t_core_path.'current_user_api.php' );
	require_once( $t_core_path.'bug_api.php' );
	require_once( $t_core_path.'string_api.php' );
	require_once( $t_core_path.'date_api.php' );

	auth_ensure_user_authenticated();

	compress_enable();

	global $t_filter;
	global $t_select_modifier;
	$t_filter = current_user_get_bug_filter();
	$t_filter = filter_ensure_valid_filter( $t_filter );
	$t_project_id = helper_get_current_project();

	$t_sort = $t_filter['sort'];
	$t_dir = $t_filter['dir'];

	$t_current_user_access_level = current_user_get_access_level();
	$t_accessible_custom_fields_ids = array();
	$t_accessible_custom_fields_names = array();
	$t_accessible_custom_fields_types = array();
	$t_accessible_custom_fields_values = array();
	$t_filter_cols = 7;
	$t_custom_cols = 1;
	$t_custom_rows = 0;

	if ( ON == config_get( 'filter_by_custom_fields' ) ) {
		$t_custom_cols = config_get( 'filter_custom_fields_per_row' );
		$t_custom_fields = custom_field_get_linked_ids( $t_project_id );

		foreach ( $t_custom_fields as $t_cfid ) {
			$t_field_info = custom_field_cache_row( $t_cfid, true );
			if ( $t_field_info['access_level_r'] <= $t_current_user_access_level ) {
				$t_accessible_custom_fields_ids[] = $t_cfid;
				$t_accessible_custom_fields_names[] = $t_field_info['name'];
				$t_accessible_custom_fields_types[] = $t_field_info['type'];
				$t_accessible_custom_fields_values[] = custom_field_distinct_values( $t_cfid, $t_project_id );
			}
		}

		if ( sizeof( $t_accessible_custom_fields_ids ) > 0 ) {
			$t_per_row = config_get( 'filter_custom_fields_per_row' );
			$t_custom_rows = ceil( sizeof( $t_accessible_custom_fields_ids ) / $t_per_row );
		}
	}

	$f_for_screen = gpc_get_bool( 'for_screen', true );

	$t_sort = $t_filter['sort'];
	$t_dir = $t_filter['dir'];
	$t_action  = "view_all_set.php?f=3";

	if ( $f_for_screen == false )
	{
		$t_action  = "view_all_set.php";
	}

	$f_default_view_type = 'simple';
	if ( ADVANCED_DEFAULT == config_get( 'view_filters' ) ) {
		$f_default_view_type = 'advanced';
	}

	$f_view_type = gpc_get_string( 'view_type', $f_default_view_type );
	if ( ADVANCED_ONLY == config_get( 'view_filters' ) ) {
		$f_view_type = 'advanced';
	}
	if ( SIMPLE_ONLY == config_get( 'view_filters' ) ) {
		$f_view_type = 'simple';
	}

	$t_select_modifier = '';
	if ( 'advanced' == $f_view_type ) {
		$t_select_modifier = 'multiple="multiple" size="10" ';
	}

	#
	# Controller
	#
	function act(){
		if(isset($_GET['filter_target'])){
		    if ( ! headers_sent() ) {
			    header( 'Content-Type: text/html; charset=' . lang_get( 'charset' ) );
			}
			$filter = $_GET['filter_target'];
			$t_functionName = 'print_filter_'. substr($filter,0,-7);
			echo "<!-- " . string_display_line( $filter ) . " -->";
			if(function_exists($t_functionName)){
				call_user_func($t_functionName);
			}elseif('custom_field' == substr($filter, 0, 12)){
				# custom function
				$t_custom_id = substr($filter, 13,-7);
				print_filter_custom_field($t_custom_id);
			}else {
				# error - no function to populate the target (e.g., print_filter_foo)
				?>
				<span style="color:red;weight:bold;">
					unknown filter (<?php echo string_display_line( $filter ); ?>)
				</span>
				<?php
			}
		} else {
			# error - no filter given
			?>
			<span style="color:red;weight:bold;">
				no filter selected
			</span>
			<?php
		}
	}

act();

?>
