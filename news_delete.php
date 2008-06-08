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
	# $Id: news_delete.php,v 1.23.18.1 2007-10-13 22:34:02 giallu Exp $
	# --------------------------------------------------------

	require_once( 'core.php' );

	$t_core_path = config_get( 'core_path' );

	require_once( $t_core_path.'news_api.php' );

	$f_news_id = gpc_get_int( 'news_id' );

	$row = news_get_row( $f_news_id );

	# This check is to allow deleting of news items that were left orphan due to bug #3723
	if ( project_exists( $row['project_id'] ) ) {
		access_ensure_project_level( config_get( 'manage_news_threshold' ), $row['project_id'] );
	}

	helper_ensure_confirmed( lang_get( 'delete_news_sure_msg' ),
							 lang_get( 'delete_news_item_button' ) );

	form_security_validate( 'news_delete' );

	news_delete( $f_news_id );

	$t_redirect_url = 'news_menu_page.php';
	html_page_top1();
	html_meta_redirect( $t_redirect_url );
	html_page_top2();
?>

<br />
<div align="center">
<?php
	echo lang_get( 'operation_successful' ) . '<br />';
	print_bracket_link( $t_redirect_url, lang_get( 'proceed' ) );
?>
</div>

<?php html_page_bottom1( __FILE__ ) ?>
