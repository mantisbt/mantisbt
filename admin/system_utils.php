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
<div class="table-container">
	<h2>System Utilities</h2>
	<table>
		<thead>
			<tr class="row-category">
				<th>Description</th>
				<th>Execute</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td>
					Move attachments stored between database schema and disk files.
				</td>
				<td class="center">
					<?php html_button( 'move_attachments_page.php', 'Move Attachments to Disk', array( 'type' => 'bug' ) );?>
				</td>
			</tr>
			<tr>
				<td>
					Move project files stored between database schema and disk.
				</td>
				<td class="center">
					<?php html_button( 'move_attachments_page.php', 'Move Project Files to Disk', array( 'type' => 'project' ) );?>
				</td>
			</tr>
			<tr>
				<td>
					Show database statistics.
				</td>
				<td class="center">
					<?php html_button( 'db_stats.php', 'Display', array() );?>
				</td>
			</tr>
		</tbody>
	</table>
</div>
<?php
	html_page_bottom();
