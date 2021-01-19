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
 * Edit Project Categories
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses access_api.php
 * @uses authentication_api.php
 * @uses category_api.php
 * @uses config_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses string_api.php
 */

require_once( 'core.php' );
require_api( 'access_api.php' );
require_api( 'authentication_api.php' );
require_api( 'category_api.php' );
require_api( 'config_api.php' );
require_api( 'form_api.php' );
require_api( 'gpc_api.php' );
require_api( 'helper_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );
require_api( 'print_api.php' );
require_api( 'string_api.php' );

auth_reauthenticate();

$f_category_id		= gpc_get_int( 'id' );
$f_project_id		= gpc_get_int( 'project_id' );

$t_row = category_get_row( $f_category_id );
$t_assigned_to = (int)$t_row['user_id'];
$t_project_id = (int)$t_row['project_id'];
$t_name = $t_row['name'];

access_ensure_project_level( config_get( 'manage_project_threshold' ), $t_project_id );

layout_page_header();

layout_page_begin( 'manage_overview_page.php' );

print_manage_menu( 'manage_proj_cat_edit_page.php' );
?>

<div class="col-md-12 col-xs-12">
	<div class="space-10"></div>
	<div id="manage-proj-category-update-div" class="form-container">
	<form id="manage-proj-category-update-form" method="post" action="manage_proj_cat_update.php">
	<div class="widget-box widget-color-blue2">
		<div class="widget-header widget-header-small">
			<h4 class="widget-title lighter">
				<?php print_icon( 'fa-sitemap', 'ace-icon' ); ?>
				<?php echo lang_get('edit_project_category_title') ?>
			</h4>
		</div>
		<div class="widget-body">
		<div class="widget-main no-padding">
		<div class="table-responsive">
		<table class="table table-bordered table-condensed table-striped">
		<fieldset>
			<?php echo form_security_field( 'manage_proj_cat_update' ) ?>
			<input type="hidden" name="category_id" value="<?php echo string_attribute( $f_category_id ) ?>" />
			<tr>
				<td class="category">
					<?php echo lang_get( 'category' ) ?>
				</td>
				<td>
					<input type="text" id="proj-category-name" name="name" class="input-sm" size="32" maxlength="128" value="<?php echo string_attribute( $t_name ) ?>" />
				</td>
			</tr>
			<tr>
				<td class="category">
					<?php echo lang_get( 'assigned_to' ) ?>
				</td>
				<td>
					<select id="proj-category-assigned-to" name="assigned_to" class="input-sm">
						<option value="0"></option>
						<?php print_assign_to_option_list( $t_assigned_to, $t_project_id ) ?>
					</select>
				</td>
			</tr>
		</fieldset>
		</table>
		</div>
		</div>
		</div>
		<div class="widget-toolbox padding-8 clearfix">
			<input type="submit" class="btn btn-primary btn-white btn-round" value="<?php echo lang_get( 'update_category_button' ) ?>" />
		</div>
	</div>
	</form>
	</div>
</div>
<?php

layout_page_end();
