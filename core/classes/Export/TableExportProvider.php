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
 * @package MantisBT
 * @copyright Copyright 2019  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

namespace Mantis\Export;

/**
 * This is a struct for a set of properties related to a TableWriter object
 * Encapsulates information used by the plugin system to be aware of each export plugin
 * properties and capabilities.
 */
class TableExportProvider {
	# This is the identifier of the tablewriter method. Must be unique across all
	# external plugins, and within each plugin, unique btween different provided methods
	# For example: "my_plugin__csv"
	# This identifier will usually be used internally
	public $unique_id;

	# The file extension for this format
	public $file_extension;

	# A short description for this format. This will be shown to the user.
	# For example: "CSV Text"
	# Note that this can be localized at run time by the plugin
	public $short_name;

	# A reference to the plugin responsible for generating this format.
	# This will be used to differentiate when a same format is provided by
	# different plugin/libraries
	public $provider_name;

	# A path to a configuration page to be used to configure preferences by each user
	public $config_page_for_user = null;

	# A path to a configuration page to be used to configure global preferences
	public $config_page_for_admin = null;

	# Whether this format should be enabled by default right after plugin installation
	public $enabled_by_default = true;
}
