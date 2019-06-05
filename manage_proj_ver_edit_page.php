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
 * Edit Project Versions
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses access_api.php
 * @uses authentication_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses event_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses string_api.php
 * @uses version_api.php
 */

require_once( 'core.php' );
require_api( 'access_api.php' );
require_api( 'authentication_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'event_api.php' );
require_api( 'form_api.php' );
require_api( 'gpc_api.php' );
require_api( 'helper_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );
require_api( 'string_api.php' );
require_api( 'version_api.php' );

auth_reauthenticate();

$f_version_id = gpc_get_int( 'version_id' );

$t_version = version_get( $f_version_id );

access_ensure_project_level( config_get( 'manage_project_threshold' ), $t_version->project_id );

layout_page_header();

layout_page_begin( 'manage_overview_page.php' );

print_manage_menu( 'manage_proj_ver_edit_page.php' );
?>

<div class="col-md-12 col-xs-12">
	<div class="space-10"></div>
	<div id="manage-proj-version-update-div" class="form-container">
	<form id="manage-proj-version-update-form" method="post" action="manage_proj_ver_update.php">
		<div class="widget-box widget-color-blue2">
			<div class="widget-header widget-header-small">
				<h4 class="widget-title lighter">
					<i class="ace-icon fa fa-share-alt"></i>
					<?php echo lang_get( 'edit_project_version_title' ) ?>
				</h4>
			</div>
		<div class="widget-body">
		<div class="widget-main no-padding">
		<div class="table-responsive">
		<table class="table table-bordered table-condensed table-striped">
		<fieldset>
			<?php echo form_security_field( 'manage_proj_ver_update' ) ?>
			<input type="hidden" name="version_id" value="<?php echo string_attribute( $t_version->id ) ?>" />
			<tr>
				<td class="category">
					<?php echo lang_get( 'version' ) ?>
				</td>
				<td>
					<input type="text" id="proj-version-new-version" name="new_version" class="input-sm" size="32" maxlength="64" value="<?php echo string_attribute( $t_version->version ) ?>" />
				</td>
			</tr>
			<tr>
				<td class="category">
					<?php echo lang_get( 'date_order' ) ?>
				</td>
				<td>
					<input type="text" id="proj-version-date-order" name="date_order" class="datetimepicker input-sm"
						data-picker-locale="<?php echo lang_get_current_datetime_locale() ?>"
						data-picker-format="<?php echo config_get( 'datetime_picker_format' ) ?>"
						size="16" value="<?php echo (date_is_null( $t_version->date_order ) ? '' : string_attribute( date( config_get( 'normal_date_format' ), $t_version->date_order ) ) ) ?>" />
					<i class="fa fa-calendar fa-xlg datetimepicker"></i>
				</td>
			</tr>
			<tr>
				<td class="category">
					<?php echo lang_get( 'description' ) ?>
				</td>
				<td>
					<?php # Newline after opening textarea tag is intentional, see #25839 ?>
					<textarea class="form-control" id="proj-version-description" name="description" cols="60" rows="5">
<?php echo string_attribute( $t_version->description ) ?>
</textarea>
				</td>
			</tr>
			<tr>
				<td class="category">
					<?php echo lang_get( 'released' ) ?>
				</td>
				<td>
					<label>
						<input type="checkbox" class="ace" id="proj-version-released" name="released" <?php check_checked( (boolean)$t_version->released, VERSION_RELEASED ); ?> />
						<span class="lbl"></span>
					</label>
				</td>
			</tr>
			<tr>
				<td class="category">
					<?php echo lang_get( 'obsolete' ) ?>
				</td>
				<td>
					<label>
						<input type="checkbox" class="ace" id="proj-version-obsolete" name="obsolete" <?php check_checked( (boolean)$t_version->obsolete, true ); ?> />
						<span class="lbl"></span>
					</label>
				</td>
			</tr>
			<?php event_signal( 'EVENT_MANAGE_VERSION_UPDATE_FORM', array( $t_version->id ) ); ?>
		</fieldset>
		</table>
			</div>
			</div>
			<div class="widget-toolbox padding-8 clearfix">
				<span class="required pull-right"> * <?php echo lang_get( 'required' ) ?></span>
				<input type="submit" class="btn btn-primary btn-white btn-round" value="<?php echo lang_get( 'update_version_button' ) ?>" />
			</div>
			</div>
			</div>
	</form>
</div>
</div>

<div class="col-md-12 col-xs-12">
	<form method="post" action="manage_proj_ver_delete.php" class="pull-right">
		<fieldset>
			<?php echo form_security_field( 'manage_proj_ver_delete' ) ?>
			<input type="hidden" name="version_id" value="<?php echo string_attribute( $t_version->id ) ?>" />
			<input type="submit" class="btn btn-sm btn-primary btn-white btn-round" value="<?php echo lang_get( 'delete_version_button' ) ?>" />
		</fieldset>
	</form>
</div>

<?php
layout_page_end();
