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
	# $Id: manage_user_delete.php,v 1.31.2.1 2007-10-13 22:33:52 giallu Exp $
	# --------------------------------------------------------

	require_once( 'core.php' );

	form_security_validate('manage_user_delete');

	auth_reauthenticate();
	access_ensure_global_level( config_get( 'manage_user_threshold' ) );

	$f_user_id	= gpc_get_int( 'user_id' );

	$t_user = user_get_row( $f_user_id );

	helper_ensure_confirmed( lang_get( 'delete_account_sure_msg' ) .
		'<br/>' . lang_get( 'username' ) . ': ' . $t_user['username'],
		lang_get( 'delete_account_button' ) );

	user_delete( $f_user_id );

	form_security_purge('manage_user_delete');

	$t_redirect_url = 'manage_user_page.php';

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
