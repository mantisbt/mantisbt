<?php
# Mantis - a php based bugtracking system

# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
# Copyright (C) 2002 - 2007  Mantis Team   - mantisbt-dev@lists.sourceforge.net

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

	# --------------------------------------------------------
	# $Id: news_view_page.php,v 1.34.22.1 2007-10-13 22:34:10 giallu Exp $
	# --------------------------------------------------------
?>
<?php
	require_once( 'core.php' );

	$t_core_path = config_get( 'core_path' );

	require_once( $t_core_path . 'news_api.php' );
	require_once( $t_core_path . 'print_api.php' );
?>
<?php
	$f_news_id = gpc_get_int( 'news_id', null );
?>
<?php html_page_top1() ?>
<?php html_page_top2() ?>

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

<?php html_page_bottom1( __FILE__ ) ?>
