<?php
# MantisBT - A PHP based bugtracking system

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
 * System Utilities
 * @todo FIXME: Looks like "From", "to", and "Copy" need i18n. Possibly more in this file.
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

require_once( dirname( dirname( __FILE__ ) ) . '/core.php' );

access_ensure_global_level( config_get_global( 'admin_site_threshold' ) );

html_page_top( 'MantisBT Administration - System Utilities' );

?>
<table width="100%" cellspacing="0" cellpadding="0">
	<tr class="top-bar">
		<td class="links">
			[ <a href="index.php">Back to MantisBT Administration</a> ]
		</td>
	</tr>
</table>
<br />
<h2>System Utilities</h2>
<table width="80%" cellpadding="10" cellspacing="1" border="1">
	<tr class="row-category">
		<th width="70%">Description</th><th width="30%">Execute</th>
	</tr>

	<tr class="row-1"><td>Move attachments stored in database schema to disk files.</td><td class="center">
	<?php html_button( 'move_attachments_page.php', 'Move Attachments to Disk', array( 'type' => 'bug' ) );?>
	</td></tr>

	<tr class="row-2"><td>Move project files stored in database schema to disk.</td><td class="center">
	<?php html_button( 'move_attachments_page.php', 'Move Project Files to Disk', array( 'type' => 'project' ) );?>
	</td></tr>

	<tr class="row-2"><td>Show database statistics.</td><td class="center">
	<?php html_button( 'db_stats.php', 'Display', array() );?>
	</td></tr>
</table>
<?php
	html_page_bottom();
