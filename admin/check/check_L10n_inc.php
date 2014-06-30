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
 * This file contains configuration checks for localization issues
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses check_api.php
 * @uses config_api.php
 */

if( !defined( 'CHECK_L10N_INC_ALLOW' ) ) {
	return;
}

# MantisBT Check API
require_once( 'check_api.php' );
require_api( 'config_api.php' );

check_print_section_header_row( 'Localization' );

$t_possible_languages = config_get_global( 'language_choices_arr' );
$t_default_language = config_get_global( 'default_language' );
check_print_test_row(
	'default_language configuration option is set to a valid language',
	in_array( $t_default_language, $t_possible_languages ),
	array(
		true => 'The default language is currently specified as: ' . htmlentities( $t_default_language ),
		false => 'Invalid default language detected: ' . htmlentities( $t_default_language )
	)
);

$t_fallback_language = config_get_global( 'fallback_language' );
check_print_test_row(
	'fallback_language configuration option is set to a valid language',
	$t_fallback_language != 'auto' && in_array( $t_fallback_language, $t_possible_languages ),
	array(
		true => 'The fallback language is currently specified as: ' . htmlentities( $t_fallback_language ),
		false => 'Fallback language can not be set to auto or a non-implemented language. Invalid fallback language detected: ' . htmlentities( $t_fallback_language )
	)
);
