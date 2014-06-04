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
 * Page to allow user to store a stored filter
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
 * @uses filter_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 */

require_once( 'core.php' );
require_api( 'access_api.php' );
require_api( 'authentication_api.php' );
require_api( 'compress_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'filter_api.php' );
require_api( 'form_api.php' );
require_api( 'gpc_api.php' );
require_api( 'helper_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );

auth_ensure_user_authenticated();

compress_enable();

html_page_top();
?>
<br />
<div id="save-filter">
<?php
$t_query_to_store = filter_db_get_filter( gpc_get_cookie( config_get( 'view_all_cookie' ), '' ) );
$t_query_arr = filter_db_get_available_queries();

# Let's just see if any of the current filters are the
# same as the one we're about the try and save
foreach( $t_query_arr as $t_id => $t_name ) {
	if( filter_db_get_filter( $t_id ) == $t_query_to_store ) {
		print lang_get( 'query_exists' ) . ' (' . $t_name . ')<br />';
	}
}

# Check for an error
$t_error_msg = strip_tags( gpc_get_string( 'error_msg', null ) );
if( $t_error_msg != null ) {
	print "<br />$t_error_msg<br /><br />";
}

print lang_get( 'query_name_label' ) . lang_get( 'word_separator' );
?>
<form method="post" action="query_store.php">
<?php echo form_security_field( 'query_store' ) ?>
<input type="text" name="query_name" /><br />
<?php
if( access_has_project_level( config_get( 'stored_query_create_shared_threshold' ) ) ) {
	print '<input type="checkbox" name="is_public" value="on" /> ';
	print lang_get( 'make_public' );
	print '<br />';
}
?>
<input type="checkbox" name="all_projects" value="on" <?php check_checked( ALL_PROJECTS == helper_get_current_project() ) ?> />
<?php print lang_get( 'all_projects' ); ?><br /><br />
<input type="submit" class="button" value="<?php print lang_get( 'save_query' ); ?>" />
</form>
<form action="view_all_bug_page.php">
<?php # CSRF protection not required here - form does not result in modifications ?>
<input type="submit" class="button" value="<?php print lang_get( 'go_back' ); ?>" />
</form>
<?php
echo '</div>';
html_page_bottom();
