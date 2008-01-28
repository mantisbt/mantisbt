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

/**
 * Plugin information.
 */
function plugin_callback_format_info() {
	return array(
		'name'			=> lang_get( 'plugin_format_title' ),
		'description'	=> lang_get( 'plugin_format_description' ),
		'version'		=> '1.0',
		'author'		=> 'Mantis Team',
		'contact'		=> 'mantisbt-dev@lists.sourceforge.net',
		'url'			=> 'http://www.mantisbt.org',
		'page'			=> 'config',
		'requires'		=> array(
			'mantis'		=> '1.2.0',
		),
	);
}

/**
 * Default plugin configuration.
 */
function plugin_callback_format_config() {
	return array(
		'process_text'		=> ON,
		'process_urls'		=> ON,
		'process_buglinks'	=> ON,
		'process_vcslinks'	=> ON,
	);
}

/**
 * Event hook declaration.
 */
function plugin_callback_format_hook() {
	return array(

		# Text String Display
		'EVENT_DISPLAY_TEXT'		=> 'text',

		# Formatted String Display
		'EVENT_DISPLAY_FORMATTED'	=> 'formatted',

		# RSS String Display
		'EVENT_DISPLAY_RSS'			=> 'rss',

		# Email String Display
		'EVENT_DISPLAY_EMAIL'		=> 'email',

	);
}
