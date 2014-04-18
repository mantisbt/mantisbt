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
 * A webservice interface to Mantis Bug Tracker
 *
 * @package MantisBT
 * @copyright Copyright 2004  Victor Boctor - vboctor@users.sourceforge.net
 * @copyright Copyright 2005  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

# Minimum global access level required to access webservice for readonly operations.
$g_mc_readonly_access_level_threshold = VIEWER;

# Minimum global access level required to access webservice for read/write operations.
$g_mc_readwrite_access_level_threshold = REPORTER;

# Minimum global access level required to access the administrator webservices
$g_mc_admin_access_level_threshold = MANAGER;

# Minimum project access level required to be able to specify a reporter name when
# adding an issue.  Otherwise, the current user is used as the reporter.  Users
# who don't have this access level can always do another step to modify the issue
# and specify a different name, but in this case it will be logged in the history
# who original reported the issue.
$g_mc_specify_reporter_on_add_access_level_threshold = DEVELOPER;

# The following enum ids are used when the webservices get enum labels that are not
# defined in the associated MantisBT installation.  In this case, the enum id is set
# to the value specified by the corresponding configuration option.
$g_mc_priority_enum_default_when_not_found = 0;
$g_mc_severity_enum_default_when_not_found = 0;
$g_mc_status_enum_default_when_not_found = 0;
$g_mc_resolution_enum_default_when_not_found = 0;
$g_mc_projection_enum_default_when_not_found = 0;
$g_mc_eta_enum_default_when_not_found = 0;

# If ON and the supplied category is not found, then a SoapException will be raised.
# (at the moment this value does not depend on the project).
$g_mc_error_when_category_not_found = ON;

# Default category to be used if the specified category is not found and $g_mc_error_when_category_not_found == OFF.
$g_mc_category_when_not_found = '';

# If ON and the supplied version is not found, then a SoapException will be raised.
$g_mc_error_when_version_not_found = ON;

# Default version to be used if the specified version is not found and $g_mc_error_when_version_not_found == OFF.
# (at the moment this value does not depend on the project).
$g_mc_version_when_not_found = '';
