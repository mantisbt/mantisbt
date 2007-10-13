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
	# $Id: login_cookie_test.php,v 1.10.2.1 2007-10-13 22:33:19 giallu Exp $
	# --------------------------------------------------------
?>
<?php
	# Check to see if cookies are working
?>
<?php require_once( 'core.php' ) ?>
<?php
	$f_return = gpc_get_string( 'return', config_get( 'default_home_page' ) );

	$c_return = string_prepare_header( $f_return );

	if ( auth_is_user_authenticated() ) {
		$t_redirect_url = $c_return;
	} else {
		$t_redirect_url = 'login_page.php?cookie_error=1';
	}

	print_header_redirect( $t_redirect_url, true, true );
?>
