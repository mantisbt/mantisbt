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
require_once( dirname( dirname( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'core.php' );

access_ensure_global_level( ADMINISTRATOR );
?>
<html>
<head>
<title> Mantis Administration </title>
<link rel="stylesheet" type="text/css" href="admin.css" />
</head>
<body>
<table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#ffffff">
	<tr class="top-bar">
		<td class="links">
			&nbsp;
		</td>
		<td class="title">
			&nbsp;
		</td>
	</tr>
</table>
<br /><br />
<div align="center">
	<table width="75%"><tr><td align="center">
	<h1>Mantis Administration</h1>
	<p>Note: be sure to secure this area or remove it from your Mantis installation when you are done.  Leaving the administration area unprotected after installation leaves system information and database update capabilities open to any unauthorized person.</p>
	<p>[ <a href="check.php">Check your installation</a> ]</p>
	<p>[ <a href="upgrade_warning.php">Upgrade your installation</a> ]</p>
	<p>[ <a href="system_utils.php">System Utilities</a> ]</p>
	</td></tr></table>
	<h2>advanced tools</h2>
	<table width="75%"><tr><td align="center">
		<p>[ <a href="test_icons.php">Test Icons</a> ]</p>
		<p>[ <a href="test_langs.php">Test Langs</a> ]</p>

	</td></tr></table>
</div>
</body>
</html>
