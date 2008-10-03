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
	# $Id: manage_user_reset.php,v 1.31.2.1 2007-10-13 22:33:57 giallu Exp $
	# --------------------------------------------------------

	require_once( 'core.php' );

	form_security_validate('manage_user_reset');

	auth_reauthenticate();
	access_ensure_global_level( config_get( 'manage_user_threshold' ) );

	$f_user_id = gpc_get_int( 'user_id' );
	$t_result = user_reset_password( $f_user_id );
	$t_redirect_url = 'manage_user_page.php';

	form_security_purge('manage_user_reset');

	html_page_top1();
	if ( $t_result ) {
		html_meta_redirect( $t_redirect_url );
	}
	html_page_top2();

	echo "<br />";
	echo "<div align=\"center\">";

	if ( false == $t_result ) {
		# PROTECTED
		echo lang_get( 'account_reset_protected_msg' ) . '<br />';
	} else {
		# SUCCESS
		if ( ( ON == config_get( 'send_reset_password' ) ) && ( ON == config_get( 'enable_email_notification' ) ) ) {
			# send the new random password via email
			echo lang_get( 'account_reset_msg' ) . '<br />';
		} else {
			# email notification disabled, then set the password to blank
			echo lang_get( 'account_reset_msg2' ) . '<br />';
		}
	}

	print_bracket_link( $t_redirect_url, lang_get( 'proceed' ) );
	echo "</div>";
	html_page_bottom1( __FILE__ );
?>
