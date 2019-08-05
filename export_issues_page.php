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
 * @package MantisBT
 * @copyright Copyright 2019  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses export_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses layout_api.php
 */

use Mantis\Export\TableWriterFactory;

require_once( 'core.php' );
require_api( 'export_api.php' );
require_api( 'helper_api.php' );
require_api( 'html_api.php' );
require_api( 'layout_api.php' );

require_js( 'export_options.js' );
layout_page_header( 'EXPORT PAGE' );
layout_page_begin( 'export_issues_page.php.php' );

$t_default_provider = TableWriterFactory::getDefaultProvider();
if( $t_default_provider ) {
	$t_default_provider_id = $t_default_provider->unique_id;
} else {
	$t_default_provider_id = null;
}
?>
<div class="col-md-12 col-xs-12">
	<form id="export_issues_form" method="post"	action="export_issues.php">
		<?php echo form_security_field( 'export_issues' ) ?>
		<input type="hidden" id="input_project_id" name="project_id" value="<?php echo helper_get_current_project() ?>" />
		<div class="widget-box widget-color-blue2">
			<div class="widget-header widget-header-small">
				<h4 class="widget-title lighter">
					<i class="ace-icon fa fa-edit"></i>
					<?php echo 'EXPORT_OPTIONS' ?>
				</h4>
			</div>

			<div class="widget-body dz-clickable">
				<div class="widget-main no-padding">
					<div id="div_export_options" class="table-responsive">

<table class="table table-bordered table-condensed">
	<tr>
		<th class="category" width="30%">
			<label for="provider"><?php echo 'FORMAT_TYPE' ?></label>
		</th>
		<td>
			<select <?php echo helper_get_tab_index() ?> id="input_provider" name="provider" class="input-sm">
				<option selected disabled value=""><?php echo '[', 'SELECT', ']' ?></option>
				<?php export_print_format_option_list( $t_default_provider_id ) ?>
			</select>
		</td>
	</tr>
	<tr>
		<th class="category">
			<label for="filename"><?php echo 'FILENAME' ?></label>
		</th>
		<td>
			<input <?php echo helper_get_tab_index() ?> type="text" id="filename" name="filename" value="<?php echo export_get_default_filename() ?>" required />
		</td>
	</tr>
</table>

					</div>
					<div id="div_export_plugin_options" class="table-responsive">
					</div>
				</div>
				<div class="widget-toolbox padding-8 clearfix">
					<input <?php echo helper_get_tab_index() ?> type="submit" class="btn btn-primary btn-white btn-round" value="<?php echo 'EXPORT' ?>" />
				</div>
			</div>
		</div>
	</form>
</div>

<?php
layout_page_end();
