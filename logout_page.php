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
	# $Id: logout_page.php,v 1.18.4.1 2007-10-13 22:33:20 giallu Exp $
	# --------------------------------------------------------
?>
<?php
	require_once( 'core.php' );

	auth_logout();

	if ( HTTP_AUTH == config_get( 'login_method' ) ) {
		auth_http_set_logout_pending( true );
	}

	print_header_redirect( config_get( 'logout_redirect_page' ), /* die */ true, /* sanitize */ false );
?>
