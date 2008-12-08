<?php
# Mantis - a php based bugtracking system

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

	/**
	 * @package MantisBT
	 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	 * @copyright Copyright (C) 2002 - 2009  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	 * @link http://www.mantisbt.org
	 */
	 /**
	  * Mantis Core API's
	  */
	require_once( 'core.php' );

	$t_core_path = config_get( 'core_path' );

	require_once( $t_core_path.'news_api.php' );
	require_once( $t_core_path.'string_api.php' );
	require_once( $t_core_path.'print_api.php' );

	form_security_validate( 'news_update' );

	$f_news_id		= gpc_get_int( 'news_id' );
	$f_project_id	= gpc_get_int( 'project_id' );
	$f_view_state	= gpc_get_int( 'view_state' );
	$f_headline		= gpc_get_string( 'headline' );
	$f_announcement	= gpc_get_string( 'announcement', '' );
	$f_body			= gpc_get_string( 'body', '' );

	$row = news_get_row( $f_news_id );

	# Check both the old project and the new project
	access_ensure_project_level( config_get( 'manage_news_threshold' ), $row['project_id'] );
	access_ensure_project_level( config_get( 'manage_news_threshold' ), $f_project_id );

	news_update( $f_news_id, $f_project_id, $f_view_state, $f_announcement, $f_headline, $f_body );

	form_security_purge( 'news_update' );
?>
<?php html_page_top1() ?>
<?php html_page_top2() ?>

<br />
<div align="center">
	<?php echo lang_get( 'operation_successful' ) ?><br />
<?php
	print_bracket_link( 'news_edit_page.php?news_id='.$f_news_id.'&amp;action=edit', lang_get( 'edit_link' ) );
	print_bracket_link( 'news_menu_page.php', lang_get( 'proceed' ) );

	echo '<br /><br />';
	print_news_entry( $f_headline, $f_body, $row['poster_id'], $f_view_state, $f_announcement, $row['date_posted'] );
?>
</div>

<?php html_page_bottom1( __FILE__ ) ?>
