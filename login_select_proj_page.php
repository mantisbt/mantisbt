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

if ( count( current_user_get_accessible_projects() ) == 1) {
	$t_project_ids = current_user_get_accessible_projects();
	$t_project_id = (int) $t_project_ids[0];
	if ( count( current_user_get_accessible_subprojects( $t_project_id ) ) == 0 ) {
		$t_ref_urlencoded = string_url( $f_ref );
		print_header_redirect( "set_project.php?project_id=$t_project_id&ref=$t_ref_urlencoded", true);
		# print_header_redirect terminates script execution
	}
}

html_page_top( lang_get( 'select_project_button' ) );
?>

<!-- Project Select Form BEGIN -->
<div id="select-project-div" class="form-container">
	<form id="select-project-form" method="post" action="set_project.php">
		<?php # CSRF protection not required here - form does not result in modifications ?>
		<fieldset>
			<legend><span><?php echo lang_get( 'choose_project' ) ?></span></legend>
			<input type="hidden" name="ref" value="<?php echo string_html_specialchars( $f_ref ) ?>" />
			<div class="field-container">
				<label for="select-project-id"><span><?php echo lang_get( 'select_project_button' ) ?></span></label>
				<span class="select">
					<select id="select-project-id" name="project_id">
						<?php print_project_option_list( ALL_PROJECTS, false, null, true, true ) ?>
					</select>
				</span>
				<span class="label-style"></span>
			</div>
			<div class="field-container">
				<label for="set-default"><span><?php echo lang_get( 'make_default' ) ?></span></label>
				<span class="checkbox"><input id="set-default" type="checkbox" name="make_default" /></span>
				<span class="label-style"></span>
			</div>
			<span class="submit-button"><input type="submit" class="button" value="<?php echo lang_get( 'select_project_button') ?>" /></span>
		</fieldset>
	</form>
</div>

<?php
html_page_bottom();
