<?php
# MantisBT - a php based bugtracking system

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
	 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	 * @copyright Copyright (C) 2002 - 2014  MantisBT Team - mantisbt-dev@lists.sourceforge.net
	 * @link http://www.mantisbt.org
	 */
	 /**
	  * MantisBT Core API's
	  */
	require_once( 'core.php' );

	require_once( 'news_api.php' );
	require_once( 'print_api.php' );

	news_ensure_enabled();

	$f_news_id = gpc_get_int( 'news_id', null );

	html_page_top();
?>
<br />

<?php
	if ( $f_news_id !== null ) {
		$t_project_id = news_get_field( $f_news_id, 'project_id' );
		if ( news_is_private( $f_news_id ) ) {
			access_ensure_project_level(	config_get( 'private_news_threshold' ),
							$t_project_id );
		} else {
			access_ensure_project_level( VIEWER, $t_project_id );
		}

		print_news_string_by_news_id( $f_news_id );
	}
?>

<br />
<div align="center">
	<?php print_bracket_link( 'news_list_page.php', lang_get( 'archives' ) ); ?>
</div>

<?php
	html_page_bottom();
