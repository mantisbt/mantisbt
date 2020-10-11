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
 * Manage Filter Page
 *
 * @package MantisBT
 * @copyright Copyright 2016  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses authentication_api.php
 * @uses compress_api.php
 * @uses config_api.php
 * @uses database_api.php
 * @uses filter_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses rss_api.php
 */

require_once( 'core.php' );
require_api( 'authentication_api.php' );
require_api( 'compress_api.php' );
require_api( 'config_api.php' );
require_api( 'database_api.php' );
require_api( 'filter_api.php' );
require_api( 'helper_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );
require_api( 'print_api.php' );
require_api( 'rss_api.php' );

auth_ensure_user_authenticated();

layout_page_header( lang_get('manage_filter_page_title' ) );

layout_page_begin( 'manage_filter_page.php' );

$t_project_id = helper_get_current_project();
$t_user_id = auth_get_current_user_id();

if( !access_has_project_level( config_get( 'stored_query_use_threshold' ) ) ) {
	access_denied();
}

$t_filter_ids_available =
		filter_db_get_named_filters( ALL_PROJECTS, $t_user_id, false ) +
		filter_db_get_named_filters( ALL_PROJECTS, null, true ) +
		filter_db_get_named_filters( $t_project_id, $t_user_id, false ) +
		filter_db_get_named_filters( $t_project_id, null, true )
		;
filter_cache_rows( $t_filter_ids_available );

$t_rss_enabled = config_get( 'rss_enabled' );

function table_print_filter_headers() {
	global $t_rss_enabled;
?>
	<thead>
		<tr>
			<th><?php echo lang_get( 'query_name' ) ?></td>
			<?php if( $t_rss_enabled ) { ?>
				<th><?php echo lang_get( 'rss' ) ?></td>
			<?php } ?>
			<th><?php echo lang_get( 'email_project' ) ?></td>
			<th><?php echo lang_get( 'public' ) ?></td>
			<th><?php echo lang_get( 'owner' ) ?></td>
			<th><?php echo lang_get( 'actions' ) ?></td>
		</tr>
	</thead>
<?php
}

function table_print_filter_row( $p_filter_id ) {
	global $t_rss_enabled;
	$t_editable = filter_db_can_delete_filter( $p_filter_id );
	echo '<tr>';
	# Filter name
	echo '<td>';
	$t_name = string_display_line( filter_get_field( $p_filter_id, 'name' ) );
	print_link( 'view_filters_page.php?filter_id=' . $p_filter_id, $t_name );
	echo '</td>';
	# RSS
	if( $t_rss_enabled ) {
		echo '<td class="center">';
		print_rss( rss_get_issues_feed_url( null, null, $p_filter_id ), lang_get( 'rss' ) );
		echo '</td>';
	}
	# Project
	echo '<td>' . string_display_line( project_get_name( filter_get_field( $p_filter_id, 'project_id' ) ) ) . '</td>';
	# Public
	echo '<td class="center">' . trans_bool( filter_get_field( $p_filter_id, 'is_public' ) ) . '</td>';
	# Owner
	echo '<td>' . user_get_name( filter_get_field( $p_filter_id, 'user_id' ) ) . '</td>';
	# Actions
	echo '<td>';
	echo '<div class="pull-left">';
	print_form_button( 'view_all_set.php', lang_get( 'apply_filter_button' ), array( 'type' => 3, 'source_query_id' =>  $p_filter_id ), /* security token */ OFF );
	echo '</div>';
	if( $t_editable ) {
		echo '<div class="pull-left">';
		print_form_button( 'manage_filter_edit_page.php', lang_get( 'edit' ), array( 'filter_id' =>  $p_filter_id ) );
		echo '</div>';
		echo '<div class="pull-left">';
		print_form_button( 'manage_filter_delete.php', lang_get( 'delete' ), array( 'filter_id' =>  $p_filter_id ) );
		echo '</div>';
	}
	echo '</div>';
	echo '</td>';
	echo '</tr>';
}

function table_print_filters( array $p_filter_array ) {
	?>
	<table class="table table-striped table-bordered table-condensed table-hover">
		<?php table_print_filter_headers() ?>
		<tbody>
		<?php
			foreach( $p_filter_array as $t_id => $t_name ) {
				if( !is_blank( $t_name ) ) {
					table_print_filter_row( $t_id );
				}
			}
		?>
		</tbody>
	</table>
	<?php
}

?>

<div class="col-md-12 col-xs-12">
	<div class="space-10"></div>

	<div class="widget-box widget-color-blue2">
		<div class="widget-header widget-header-small">
			<h4 class="widget-title lighter">
				<?php print_icon( 'fa-filter', 'ace-icon' ); ?>
				<?php echo lang_get('available_filter_for_project') . ': ' . string_display_line( project_get_name( $t_project_id ) ) ?>
			</h4>
		</div>

		<div class="widget-main no-padding">
			<div class="table-responsive">
			<?php
			if( count( $t_filter_ids_available ) > 0 ) {
				table_print_filters( $t_filter_ids_available );
			}
			?>
			</div>
		</div>
	</div>
</div>
<?php

layout_page_end();