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
	# $Id: login_anon.php,v 1.15.22.1 2007-10-13 22:33:18 giallu Exp $
	# --------------------------------------------------------
?>
<?php
 /* login_anon.php logs a user in anonymously without having to enter a username
 * or password.
 *
 * Depends on two global configuration variables:
 * allow_anonymous_login - bool which must be true to allow anonymous login.
 * anonymous_account - name of account to login with.
 *
 * TODO:
 * Check how manage account is impacted.
 * Might be extended to allow redirects for bug links etc.
 */
	require_once( 'core.php' );

	print_header_redirect( 'login.php?username=' . config_get( 'anonymous_account' ) . '&amp;perm_login=false' );
?>
