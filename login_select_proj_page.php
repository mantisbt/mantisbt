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
 * Allows the user to select a project that is visible to him
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses authentication_api.php
 * @uses constant_inc.php
 * @uses current_user_api.php
 * @uses gpc_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses string_api.php
 */

require_once( 'core.php' );
require_api( 'authentication_api.php' );
require_api( 'constant_inc.php' );
require_api( 'current_user_api.php' );
require_api( 'gpc_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );
require_api( 'print_api.php' );
require_api( 'string_api.php' );

auth_ensure_user_authenticated();

$f_ref = string_sanitize_url( gpc_get_string( 'ref', '' ) );

if( count( current_user_get_accessible_projects() ) == 1 ) {
	$t_project_ids = current_user_get_accessible_projects();
	$t_project_id = (int)$t_project_ids[0];
	if( count( current_user_get_accessible_subprojects( $t_project_id ) ) == 0 ) {
		$t_ref_urlencoded = string_url( $f_ref );
		print_header_redirect( 'set_project.php?project_id=' . $t_project_id . '&ref=' . $t_ref_urlencoded, true );
		# print_header_redirect terminates script execution
	}
}

layout_page_header( lang_get( 'select_project_button' ) );

layout_page_begin();
?>

<div class="col-md-12 col-xs-12">

<!-- Project Select Form BEGIN -->
<div id="select-project-div" class="form-container">
	<form id="select-project-form" method="post" action="set_project.php">
		<?php # CSRF protection not required here - form does not result in modifications ?>
	<div class="widget-box widget-color-blue2">
		<div class="widget-header widget-header-small">
			<h4 class="widget-title lighter">
				<?php print_icon( 'fa-puzzle-piece', 'ace-icon' ); ?>
				<?php echo lang_get( 'choose_project' ) ?>
			</h4>
		</div>
	<div class="widget-body">
	<div class="widget-main no-padding">
	<div class="table-responsive">
		<table class="table table-bordered table-condensed table-striped">
		<fieldset>
			<input type="hidden" name="ref" value="<?php echo string_html_specialchars( $f_ref ) ?>" />

			<tr>
				<td class="category">
					<?php echo lang_get( 'select_project_button' ) ?>
				</td>
				<td>
					<select id="select-project-id" name="project_id" class="input-sm">
						<?php print_project_option_list( ALL_PROJECTS, false, null, true, true ) ?>
					</select>
				</td>
			</tr>

			<tr>
				<td class="category">
					<?php echo lang_get( 'make_default' ) ?>
				</td>
				<td>
					<label>
						<input type="checkbox" class="ace" id="set-default" name="make_default" />
						<span class="lbl"></span>
					</label>
				</td>
			</tr>
		</fieldset>
		</table>
		</div>
		</div>
			<div class="widget-toolbox padding-8 clearfix">
				<input type="submit" class="btn btn-primary btn-white btn-round" value="<?php echo lang_get( 'select_project_button') ?>" />
			</div>
		</div>
	</div>
	</form>
</div>
</div>

<?php
layout_page_end();
