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
 * Custom Fields Checks
 * @package MantisBT
 * @copyright Copyright (C) 2021  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link https://mantisbt.org
 *
 * @uses check_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 */

if( !defined( 'CHECK_CUSTOMFIELDS_INC_ALLOW' ) ) {
	return;
}

# MantisBT Check API
require_once( 'check_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );

check_print_section_header_row( 'Custom Fields' );

# Check for deprecated usage of {} in Date CF default values
$t_date_cf_with_bracket = array();
foreach( custom_field_get_ids() as $t_id ) {
	/**
	 * @var int        $v_id
	 * @var string     $v_name
	 * @var int        $v_type
	 * @var string|int $v_default_value
	 */
	extract( custom_field_get_definition( $t_id ), EXTR_PREFIX_ALL, 'v');

	if( $v_type != CUSTOM_FIELD_TYPE_DATE ) {
		continue;
	}
	if( preg_match( '/^{(.*)}$/', $v_default_value, $t_matches ) ) {
		$t_date_cf_with_bracket[$v_name] = array( $v_id, $t_matches[1] );
	}
}
if( $t_date_cf_with_bracket ) {
	$t_manage_cf_link = '<a href="'
		. helper_mantis_url( 'manage_custom_field_edit_page.php' )
		. '?field_id=%d">Edit the Custom Field</a>';
	ksort( $t_date_cf_with_bracket );
	foreach( $t_date_cf_with_bracket as $t_name => list( $t_id, $t_new_value ) ) {
		check_print_test_warn_row(
			"Date Custom Field '$t_name' specifies its Default Value with deprecated curly brackets format.",
			false,
			array( false => "Use the same format, but without the '{}', i.e. '$t_new_value'. "
				. sprintf( $t_manage_cf_link, $t_id )
			)
		);
	}
} else {
	check_print_test_warn_row(
		'Deprecated usage of curly brackets in Date Custom Fields default value',
		true,
		''
	);
}
