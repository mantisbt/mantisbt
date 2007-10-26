<?php
#!/usr/local/bin/php -q
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
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: send_emails.php,v 1.1.2.2 2007-10-26 13:36:15 giallu Exp $
	# --------------------------------------------------------

	global $g_bypass_headers;
	$g_bypass_headers = 1;

	require_once( dirname( dirname( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'core.php' );

	$t_core_path = config_get( 'core_path' );

	require_once( $t_core_path . 'email_api.php' );

	# Make sure this script doesn't run via the webserver
	# @@@ This is a hack to detect php-cgi, there must be a better way.
	if ( isset( $_SERVER['SERVER_PORT'] ) ) {
		echo "send_emails.php is not allowed to run through the webserver.\n";
		exit( 1 );
	}

	echo "Sending emails...\n";
	email_send_all();
	echo "Done.\n";

	exit( 0 );
?>
