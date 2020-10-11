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
 * Tag Update Page
 *
 * @package MantisBT
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses access_api.php
 * @uses authentication_api.php
 * @uses compress_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses prepare_api.php
 * @uses print_api.php
 * @uses string_api.php
 * @uses tag_api.php
 * @uses user_api.php
 */

require_once( 'core.php' );
require_api( 'access_api.php' );
require_api( 'authentication_api.php' );
require_api( 'compress_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'form_api.php' );
require_api( 'gpc_api.php' );
require_api( 'helper_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );
require_api( 'prepare_api.php' );
require_api( 'print_api.php' );
require_api( 'string_api.php' );
require_api( 'tag_api.php' );
require_api( 'user_api.php' );

compress_enable();

$f_tag_id = gpc_get_int( 'tag_id' );
tag_ensure_exists( $f_tag_id );
$t_tag_row = tag_get( $f_tag_id );

$t_name = string_display_line( $t_tag_row['name'] );
$t_description = string_display( $t_tag_row['description'] );

if( !( access_has_global_level( config_get( 'tag_edit_threshold' ) )
	|| ( auth_get_current_user_id() == $t_tag_row['user_id'] )
		&& access_has_global_level( config_get( 'tag_edit_own_threshold' ) ) ) ) {
	access_denied();
}

layout_page_header( sprintf( lang_get( 'tag_update' ), $t_name ) );

layout_page_begin();
?>
<div class="col-md-12 col-xs-12">
	<div class="space-10"></div>
	<form method="post" action="tag_update.php">
	<div class="widget-box widget-color-blue2">
		<div class="widget-header widget-header-small">
			<h4 class="widget-title lighter">
				<?php print_icon( 'fa-tag', 'ace-icon' ); ?>
				<?php echo sprintf( lang_get( 'tag_update' ), $t_name ) ?>
			</h4>
		</div>
		<div class="widget-body">
		<div class="widget-main no-padding">
		<div class="widget-toolbox padding-8 clearfix">
			<?php print_link_button( 'tag_view_page.php?tag_id='.$f_tag_id, lang_get( 'tag_update_return' ),
				'btn-sm pull-right' ); ?>
		</div>
		<div class="form-container">
		<div class="table-responsive">
		<table class="table table-bordered table-condensed table-striped">
		<fieldset>
			<input type="hidden" name="tag_id" value="<?php echo $f_tag_id ?>"/>
			<?php echo form_security_field( 'tag_update' ) ?>
			<tr>
				<td class="category">
					<?php echo lang_get( 'tag_id' ) ?>
				</td>
				<td><?php echo $t_tag_row['id'] ?></td>
			</tr>
			<tr>
				<td class="category">
					<?php echo lang_get( 'tag_name' ) ?>
				</td>
				<td>
					<input type="text" <?php echo helper_get_tab_index() ?> id="tag-name" name="name" class="input-sm" value="<?php echo $t_name ?>"/>
				</td>
			</tr>
			<tr>
				<?php
				if( access_has_global_level( config_get( 'tag_edit_threshold' ) ) ) {
					echo '<td class="category">', lang_get( 'tag_creator' ), '</td>';
					echo '<td><select ', helper_get_tab_index(), ' id="tag-user-id" name="user_id" class="input-sm">';
					print_user_option_list( (int)$t_tag_row['user_id'], ALL_PROJECTS, (int)config_get( 'tag_create_threshold' ) );
					echo '</select></td>';
				} else { ?>
					<td class="category"><?php echo lang_get( 'tag_creator' ); ?></td>
					<td><?php echo string_display_line( user_get_name($t_tag_row['user_id']) ); ?></td><?php
				} ?>
			</tr>
			<tr>
				<td class="category">
					<?php echo lang_get( 'tag_created' ) ?>
				</td>
				<td><?php echo date( config_get( 'normal_date_format' ), $t_tag_row['date_created'] ) ?></td>
			</tr>
			<tr>
				<td class="category">
					<?php echo lang_get( 'tag_updated' ) ?>
				</td>
				<td><?php echo date( config_get( 'normal_date_format' ), $t_tag_row['date_updated'] ) ?></td>
			</tr>
			<tr>
				<td class="category">
					<?php echo lang_get( 'tag_description' ) ?>
				</td>
				<td>
					<?php # Newline after opening textarea tag is intentional, see #25839 ?>
					<textarea class="form-control" id="tag-description" name="description" <?php echo helper_get_tab_index() ?> cols="80" rows="6">
<?php echo string_textarea( $t_description ) ?>
</textarea>
				</td>
			</tr>
		</fieldset>
		</table>
		</div>
		</div>
		</div>
		</div>
		<div class="widget-toolbox padding-8 clearfix">
			<input <?php echo helper_get_tab_index() ?> type="submit" class="btn btn-primary btn-white btn-round" value="<?php echo lang_get( 'tag_update_button' ) ?>" />
		</div>
		</div>
	</form>
</div>

<?php
layout_page_end();
