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

# @todo thraxisp - could this be replaced by a call to filter_draw_selection_area2

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

$t_show_product_version = version_should_show_product_version( $t_project_id );
$t_show_build = $t_show_product_version && ( config_get( 'enable_product_build' ) == ON );

$t_show_tags = access_has_global_level( config_get( 'tag_view_threshold' ) );
?>
<div class="filter-box">

<form method="post" name="filters" action="<?php echo $t_action; ?>">

<?php # CSRF protection not required here - form does not result in modifications ?>
<input type="hidden" name="type" value="1" />
<input type="hidden" name="view_type" value="<?php echo $f_view_type; ?>" />
<?php
	if( $f_for_screen == false ) {
		print '<input type="hidden" name="print" value="1" />';
		print '<input type="hidden" name="offset" value="0" />';
	}
?>

<table class="width100" cellspacing="1">

<tr>
	<td class="right" colspan="<?php echo ( 8 * $t_custom_cols ); ?>">
	<?php
		$f_switch_view_link = 'view_filters_page.php?target_field=' . $t_target_field . '&view_type=';

		if( ( SIMPLE_ONLY != config_get( 'view_filters' ) ) && ( ADVANCED_ONLY != config_get( 'view_filters' ) ) ) {
			if( 'advanced' == $f_view_type ) {
				print_bracket_link( $f_switch_view_link . 'simple', lang_get( 'simple_filters' ) );
			} else {
				print_bracket_link( $f_switch_view_link . 'advanced', lang_get( 'advanced_filters' ) );
			}
		}
	?>
	</td>
</tr>

<!-- Filter row 1 -->

<tr class="row-category2">
	<th class="small-caption" colspan="<?php echo ( 1 * $t_custom_cols ); ?>"><?php echo lang_get( 'reporter' ) ?></th>
	<th class="small-caption" colspan="<?php echo ( 1 * $t_custom_cols ); ?>"><?php echo lang_get( 'monitored_by' ) ?></th>
	<th class="small-caption" colspan="<?php echo ( 1 * $t_custom_cols ); ?>"><?php echo lang_get( 'assigned_to' ) ?></th>
	<th class="small-caption" colspan="<?php echo ( 2 * $t_custom_cols ); ?>"><?php echo lang_get( 'category' ) ?></th>
	<th class="small-caption" colspan="<?php echo ( 1 * $t_custom_cols ); ?>"><?php echo lang_get( 'severity' ) ?></th>
	<th class="small-caption" colspan="<?php echo ( 1 * $t_custom_cols ); ?>"><?php echo lang_get( 'resolution' ) ?></th>
	<th class="small-caption" colspan="<?php echo ( ( $t_filter_cols - 7 ) * $t_custom_cols ); ?>"><?php echo lang_get( 'profile' ) ?></th>
</tr>
<tr class="row-1">
	<!-- Reporter -->
	<td colspan="<?php echo ( 1 * $t_custom_cols ); ?>">
		<?php print_filter_reporter_id(); ?>
	</td>
	<!-- Monitored by -->
	<td colspan="<?php echo ( 1 * $t_custom_cols ); ?>">
		<?php print_filter_user_monitor(); ?>
	</td>
	<!-- Handler -->
	<td colspan="<?php echo ( 1 * $t_custom_cols ); ?>">
		<?php print_filter_handler_id(); ?>
	</td>
	<!-- Category -->
	<td colspan="<?php echo ( 2 * $t_custom_cols ); ?>">
		<?php print_filter_show_category(); ?>
	</td>
	<!-- Severity -->
	<td colspan="<?php echo ( 1 * $t_custom_cols ); ?>">
		<?php print_filter_show_severity(); ?>
	</td>
	<!-- Resolution -->
	<td colspan="<?php echo ( 1 * $t_custom_cols ); ?>">
		<?php print_filter_show_resolution(); ?>
	</td>
	<!-- Profile -->
	<td colspan="<?php echo ( ( $t_filter_cols - 7 ) * $t_custom_cols ); ?>">
		<?php print_filter_show_profile(); ?>
	</td>
</tr>

<!-- Filter row 2 -->

<tr class="row-category2">
	<th class="small-caption" colspan="<?php echo ( 1 * $t_custom_cols ); ?>"><?php echo lang_get( 'status' ) ?></td>
	<th class="small-caption" colspan="<?php echo ( 1 * $t_custom_cols ); ?>">
		<?php echo ( 'simple' == $f_view_type ) ? lang_get( 'hide_status' ) : '&#160;'; ?>
	</th>
<?php if( $t_show_build ) { ?>
	<th class="small-caption" colspan="<?php echo ( 1 * $t_custom_cols ); ?>"><?php echo lang_get( 'product_build' ) ?></th>
<?php } else { ?>
	<th class="small-caption" colspan="<?php echo ( 1 * $t_custom_cols ); ?>">&#160;</th>
<?php } ?>

<?php if( $t_show_product_version ) { ?>
	<th class="small-caption" colspan="<?php echo ( 1 * $t_custom_cols ); ?>"><?php echo lang_get( 'product_version' ) ?></th>
	<th class="small-caption" colspan="<?php echo ( 1 * $t_custom_cols ); ?>"><?php echo lang_get( 'fixed_in_version' ) ?></th>
<?php } else { ?>
	<th class="small-caption" colspan="<?php echo ( 1 * $t_custom_cols ); ?>">&#160;</th>
	<th class="small-caption" colspan="<?php echo ( 1 * $t_custom_cols ); ?>">&#160;</th>
<?php } ?>

	<th class="small-caption" colspan="<?php echo ( 1 * $t_custom_cols ); ?>"><?php echo lang_get( 'priority' ) ?></th>

<?php if( $t_show_product_version ) { ?>
	<th class="small-caption" colspan="<?php echo ( 1 * $t_custom_cols ); ?>"><?php echo lang_get( 'target_version' ) ?></th>
<?php } else { ?>
	<th class="small-caption" colspan="<?php echo ( ( $t_filter_cols - 8 ) * $t_custom_cols ); ?>">&#160;</th>
<?php } ?>
</tr>
<tr class="row-2">
	<!-- Status -->
	<td colspan="<?php echo ( 1 * $t_custom_cols ); ?>">
		<?php print_filter_show_status(); ?>
	</td>
	<!-- Hide Status -->
	<td colspan="<?php echo ( 1 * $t_custom_cols ); ?>">
	<?php
	if( 'simple' == $f_view_type ) {
		print_filter_hide_status();
	} else {
		echo '&#160;';
	}
	?>
	</td>
	<!-- Build -->
	<td colspan="<?php echo ( 1 * $t_custom_cols ); ?>">
		<?php if( $t_show_build ) {
			print_filter_show_build();
		} ?>
	</td>
	<!-- Version -->
	<td colspan="<?php echo ( 1 * $t_custom_cols ); ?>">
		<?php if( $t_show_product_version ) {
			print_filter_show_version();
		} else {
			echo '&#160;';
		} ?>
	</td>
	<!-- Fixed in Version -->
	<td colspan="<?php echo ( 1 * $t_custom_cols ); ?>">
		<?php if( $t_show_product_version ) {
			print_filter_show_fixed_in_version();
		} else {
			echo '&#160;';
		} ?>
	</td>
	<!-- Priority -->
	<td colspan="<?php echo ( 1 * $t_custom_cols ); ?>">
		<?php print_filter_show_priority(); ?>
	</td>
	<!-- Target Version -->
	<td colspan="<?php echo ( ( $t_filter_cols - 8 ) * $t_custom_cols ); ?>">
		<?php if( $t_show_product_version ) {
			print_filter_show_target_version();
		} else {
			echo '&#160;';
		} ?>
	</td>
</tr>

<!-- Filter row 3 -->

<tr class="row-category2">
	<th class="small-caption" colspan="<?php echo ( 1 * $t_custom_cols ); ?>"><?php echo lang_get( 'show' ) ?></th>
	<th class="small-caption" colspan="<?php echo ( 1 * $t_custom_cols ); ?>"><?php echo lang_get( 'view_status' ) ?></th>
	<th class="small-caption" colspan="<?php echo ( 1 * $t_custom_cols ); ?>"><?php echo lang_get( 'sticky' ) ?></th>
	<th class="small-caption" colspan="<?php echo ( 3 * $t_custom_cols ); ?>">
		<label>
			<input type="checkbox" id="use_date_filters"
				name="<?php echo FILTER_PROPERTY_FILTER_BY_DATE ?>"
				<?php check_checked( $t_filter['filter_by_date'], true ) ?>
			/>
			<?php echo lang_get( 'use_date_filters' )?>
		</label>
	</th>
	<th class="small-caption" colspan="<?php echo ( ( $t_filter_cols -8 ) * $t_custom_cols ); ?>">
		<?php echo lang_get( 'bug_relationships' ) ?>
	</th>
</tr>
<tr class="row-1">
	<!-- Number of bugs per page -->
	<td colspan="<?php echo ( 1 * $t_custom_cols ); ?>">
		<?php print_filter_per_page(); ?>
	</td>
	<!-- View Status -->
	<td colspan="<?php echo ( 1 * $t_custom_cols ); ?>">
		<?php print_filter_view_state(); ?>
	</td>
	<!-- Show Sticky bugs -->
	<td colspan="<?php echo ( 1 * $t_custom_cols ); ?>">
		<?php print_filter_sticky_issues(); ?>
	</td>
	<!-- Date filters -->
	<td class="left" colspan="<?php echo ( 3 * $t_custom_cols ); ?>">
		<?php print_filter_do_filter_by_date( true ); # hide checkbox as it's already been shown ?>
	</td>
	<!-- Relationships -->
	<td colspan="<?php echo ( ( $t_filter_cols - 8 ) * $t_custom_cols ); ?>">
		<?php print_filter_relationship_type(); ?>
	</td>
</tr>

<!-- Filter row 4 (custom fields) -->

<?php
if( ON == config_get( 'filter_by_custom_fields' ) ) {

	# -- Custom Field Searching --
	if( count( $t_accessible_custom_fields_ids ) > 0 ) {
		$t_per_row = config_get( 'filter_custom_fields_per_row' );
		$t_num_rows = ceil( count( $t_accessible_custom_fields_ids ) / $t_per_row );
		$t_base = 0;

		for( $i = 0; $i < $t_num_rows; $i++ ) {
			?>
			<tr class="row-category2">
			<?php
			for( $j = 0; $j < $t_per_row; $j++ ) {
				echo '<th class="small-caption" colspan="' . ( 1 * $t_filter_cols ) . '">';
				if( isset( $t_accessible_custom_fields_names[$t_base + $j] ) ) {
					echo string_display( lang_get_defaulted( $t_accessible_custom_fields_names[$t_base + $j] ) );
				} else {
					echo '&#160;';
				}
				echo '</th>';
			}
			?>
			</tr>
			<tr class="row-2">
			<?php
			for( $j = 0; $j < $t_per_row; $j++ ) {
				echo '<td colspan="' . ( 1 * $t_filter_cols ) . '">';
				if( isset( $t_accessible_custom_fields_ids[$t_base + $j] ) ) {
					print_filter_custom_field( $t_accessible_custom_fields_ids[$t_base + $j] );
				} else {
					echo '&#160;';
				}
				echo '</td>';
			}

			?>
			</tr>
			<?php
			$t_base += $t_per_row;
		}
	}
}

if( 'simple' == $f_view_type ) {
	$t_project_cols = 0;
} else {
	$t_project_cols = 3;
}
?>

<tr class="row-1">
	<!-- Sort by -->
	<th class="small-caption category2" colspan="<?php echo ( 1 * $t_custom_cols ); ?>">
		<?php echo lang_get( 'sort_label' ) ?>
	</th>
	<td colspan="<?php echo ( 2 * $t_custom_cols ); ?>">
		<?php
			print_filter_show_sort();
		?>
	</td>

	<!-- Highlight changed bugs -->
	<th class="small-caption category2" colspan="<?php echo ( 1 * $t_custom_cols ); ?>"><?php echo lang_get( 'changed' ) ?></th>
	<td colspan="<?php echo ( $t_filter_cols - 4 - $t_project_cols ) * $t_custom_cols; ?>">
		<?php print_filter_highlight_changed(); ?>
	</td>

	<?php
		if( 'advanced' == $f_view_type ) {
	?>
	<!-- Projects -->
			<th class="small-caption category2" colspan="<?php echo ( 1 * $t_custom_cols ); ?>">
				<?php echo lang_get( 'email_project_label' ) ?>
			</th>
			<td colspan="<?php echo( 2 * $t_custom_cols ); ?>">
				<?php
					print_filter_project_id();
				?>
			</td>
	<?php
		}
	?>
</tr>

<?php

# get plugin filters
$t_plugin_filters = filter_get_plugin_filters();
$t_column = 0;
$t_fields = '';
$t_row_filters = array();

# output a filter form element for each plugin filter
foreach( $t_plugin_filters as $t_field_name => $t_filter_object ) {
	$t_fields .= '<td class="small-caption" colspan="' . $t_custom_cols . '"> ' . string_display_line( $t_filter_object->title ) . ' </td>';
	$t_row_filters[] = $t_field_name;

	$t_column++;

	# wrap at the appropriate column
	if( $t_column >= $t_filter_cols ) {
		echo '<tr class="row-category2">', $t_fields, '</tr>';
		echo '<tr class="row-1">';
		foreach( $t_row_filters as $t_row_field_name ) {
			echo '<td class="small-caption" colspan="' . $t_custom_cols . '"> ';
			print_filter_plugin_field( $t_row_field_name, $t_plugin_filters[$t_row_field_name] );
			echo '</td>';
		}
		echo '</tr>';

		$t_fields = '';
		$t_row_filters = array();
	}
}

# output any remaining plugin filters
if( $t_column > 0 ) {
	if( $t_column < $t_filter_cols ) {
		$t_fields .= '<td class="small-caption" colspan="' . ( $t_filter_cols - $t_column ) * $t_custom_cols . '">&#160;</td>';
	}

	echo '<tr class="row-category2">', $t_fields, '</tr>';
	echo '<tr class="row-1">';
	foreach( $t_row_filters as $t_row_field_name ) {
		echo '<td class="small-caption" colspan="' . $t_custom_cols . '"> ';
		print_filter_plugin_field( $t_row_field_name, $t_plugin_filters[$t_row_field_name] );
		echo '</td>';
	}

	if( $t_column < $t_filter_cols ) {
		echo '<td class="small-caption" colspan="' . ( $t_filter_cols - $t_column ) * $t_custom_cols . '">&#160;</td>';
	}

	echo '</tr>';
}

?>

<!-- Last Filter row (Search/tags) -->
<tr class="row-category2">
	<th class="small-caption" colspan="<?php echo ( 1 * $t_custom_cols ); ?>">
		<?php echo lang_get( 'search' ) ?>
	</th>
	<th class="small-caption" colspan="<?php echo ( ( $t_filter_cols - 2 ) * $t_custom_cols ); ?>">
		<?php
			if( $t_show_tags ) {
				echo lang_get( 'tags' );
			}
		?>
	</th>
	<th class="small-caption" colspan="<?php echo ( 1 * $t_custom_cols ); ?>">
	</th>
</tr>
<tr class="row-1">
	<!-- Search field -->
	<td colspan="<?php echo ( 1 * $t_custom_cols ); ?>">
		<input type="text" size="16" name="search" value="<?php echo string_html_specialchars( $t_filter['search'] ); ?>" />
	</td>

	<td class="small-caption" colspan="<?php echo ( ( $t_filter_cols - 2 ) * $t_custom_cols ); ?>">
		<?php
			if( $t_show_tags ) {
				print_filter_tag_string();
			}
		?>
	</td>

	<!-- Submit button -->
	<td class="center" colspan="<?php echo ( 1 * $t_custom_cols ); ?>">
		<input type="submit" name="filter" class="button" value="<?php echo lang_get( 'filter_button' ) ?>" />
	</td>
</tr>

</table>
</form>
</div>
<?php
html_page_bottom();
