<?php
# MantisBT - a php based bugtracking system

# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
# Copyright (C) 2002 - 2012  MantisBT Team - mantisbt-dev@lists.sourceforge.net

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

$g_custom_field_type_definition[ CUSTOM_FIELD_TYPE_STRING ] = array (
	'#display_possible_values' => TRUE,
	'#display_valid_regexp' => TRUE,
	'#display_length_min' => TRUE,
	'#display_length_max' => TRUE,
	'#display_default_value' => TRUE,
	'#function_return_distinct_values' => null,
	'#function_value_to_database' => null,
	'#function_database_to_value' => null,
	'#function_print_input' => 'cfdef_input_textbox',
	'#function_string_value' => null,
	'#function_string_value_for_email' => null,
);

$g_custom_field_type_definition[ CUSTOM_FIELD_TYPE_NUMERIC ] = array (
	'#display_possible_values' => TRUE,
	'#display_valid_regexp' => TRUE,
	'#display_length_min' => TRUE,
	'#display_length_max' => TRUE,
	'#display_default_value' => TRUE,
	'#function_return_distinct_values' => null,
	'#function_value_to_database' => null,
	'#function_database_to_value' => null,
	'#function_print_input' => 'cfdef_input_textbox',
	'#function_string_value' => null,
	'#function_string_value_for_email' => null,
);

$g_custom_field_type_definition[ CUSTOM_FIELD_TYPE_FLOAT ] = array (
	'#display_possible_values' => TRUE,
	'#display_valid_regexp' => TRUE,
	'#display_length_min' => TRUE,
	'#display_length_max' => TRUE,
	'#display_default_value' => TRUE,
	'#function_return_distinct_values' => null,
	'#function_value_to_database' => null,
	'#function_database_to_value' => null,
	'#function_print_input' => 'cfdef_input_textbox',
	'#function_string_value' => null,
	'#function_string_value_for_email' => null,
);

$g_custom_field_type_definition[ CUSTOM_FIELD_TYPE_ENUM ] = array (
	'#display_possible_values' => TRUE,
	'#display_valid_regexp' => TRUE,
	'#display_length_min' => TRUE,
	'#display_length_max' => TRUE,
	'#display_default_value' => TRUE,
	'#function_return_distinct_values' => 'cfdef_prepare_list_distinct_values',
	'#function_value_to_database' => null,
	'#function_database_to_value' => null,
	'#function_print_input' => 'cfdef_input_list',
	'#function_string_value' => 'cfdef_prepare_list_value',
	'#function_string_value_for_email' => 'cfdef_prepare_list_value_for_email',
);

$g_custom_field_type_definition[ CUSTOM_FIELD_TYPE_EMAIL ] = array (
	'#display_possible_values' => TRUE,
	'#display_valid_regexp' => TRUE,
	'#display_length_min' => TRUE,
	'#display_length_max' => TRUE,
	'#display_default_value' => TRUE,
	'#function_return_distinct_values' => null,
	'#function_value_to_database' => null,
	'#function_database_to_value' => null,
	'#function_print_input' => 'cfdef_input_textbox',
	'#function_string_value' => 'cfdef_prepare_email_value',
	'#function_string_value_for_email' => 'cfdef_prepare_email_value_for_email',
);

$g_custom_field_type_definition[ CUSTOM_FIELD_TYPE_CHECKBOX ] = array (
	'#display_possible_values' => TRUE,
	'#display_valid_regexp' => TRUE,
	'#display_length_min' => TRUE,
	'#display_length_max' => TRUE,
	'#display_default_value' => TRUE,
	'#function_return_distinct_values' => 'cfdef_prepare_list_distinct_values',
	'#function_value_to_database' => 'cfdef_prepare_list_value_to_database',
	'#function_database_to_value' => 'cfdef_prepare_list_database_to_value',
	'#function_print_input' => 'cfdef_input_checkbox',
	'#function_string_value' => 'cfdef_prepare_list_value',
	'#function_string_value_for_email' => 'cfdef_prepare_list_value_for_email',
);

$g_custom_field_type_definition[ CUSTOM_FIELD_TYPE_RADIO ] = array (
	'#display_possible_values' => TRUE,
	'#display_valid_regexp' => FALSE,
	'#display_length_min' => FALSE,
	'#display_length_max' => FALSE,
	'#display_default_value' => TRUE,
	'#function_return_distinct_values' => 'cfdef_prepare_list_distinct_values',
	'#function_value_to_database' => null,
	'#function_database_to_value' => null,
	'#function_print_input' => 'cfdef_input_radio',
	'#function_string_value' => 'cfdef_prepare_list_value',
	'#function_string_value_for_email' => 'cfdef_prepare_list_value_for_email',
);

$g_custom_field_type_definition[ CUSTOM_FIELD_TYPE_LIST ] = array (
	'#display_possible_values' => TRUE,
	'#display_valid_regexp' => TRUE,
	'#display_length_min' => TRUE,
	'#display_length_max' => TRUE,
	'#display_default_value' => TRUE,
	'#function_return_distinct_values' => 'cfdef_prepare_list_distinct_values',
	'#function_value_to_database' => null,
	'#function_database_to_value' => null,
	'#function_print_input' => 'cfdef_input_list',
	'#function_string_value' => 'cfdef_prepare_list_value',
	'#function_string_value_for_email' => 'cfdef_prepare_list_value_for_email',
);

$g_custom_field_type_definition[ CUSTOM_FIELD_TYPE_MULTILIST ] = array (
	'#display_possible_values' => TRUE,
	'#display_valid_regexp' => TRUE,
	'#display_length_min' => TRUE,
	'#display_length_max' => TRUE,
	'#display_default_value' => TRUE,
	'#function_return_distinct_values' => 'cfdef_prepare_list_distinct_values',
	'#function_value_to_database' => 'cfdef_prepare_list_value_to_database',
	'#function_database_to_value' => 'cfdef_prepare_list_database_to_value',
	'#function_print_input' => 'cfdef_input_list',
	'#function_string_value' => 'cfdef_prepare_list_value',
	'#function_string_value_for_email' => 'cfdef_prepare_list_value_for_email',
);

$g_custom_field_type_definition[ CUSTOM_FIELD_TYPE_DATE ] = array (
	'#display_possible_values' => TRUE,
	'#display_valid_regexp' => TRUE,
	'#display_length_min' => TRUE,
	'#display_length_max' => TRUE,
	'#display_default_value' => TRUE,
	'#function_return_distinct_values' => null,
	'#function_value_to_database' => null,
	'#function_database_to_value' => null,
	'#function_default_to_value' => 'cfdef_prepare_date_default',
	'#function_print_input' => 'cfdef_input_date',
	'#function_string_value' => 'cfdef_prepare_date_value',
	'#function_string_value_for_email' => 'cfdef_prepare_date_value_for_email',
);

function cfdef_prepare_list_database_to_value($p_value) {
	return rtrim( ltrim( $p_value, '|' ), '|' );
}

function cfdef_prepare_list_value_for_email($p_value) {
	// strip start and end markers before converting markers to commas
	return str_replace( '|', ', ', utf8_substr( str_replace( '||', '|', '|' . $p_value . '|' ), 1, -1 ) );
}

function cfdef_prepare_email_value_for_email($p_value) {
	return 'mailto:'.$p_value;
}

function cfdef_prepare_date_value_for_email($p_value) {
	if ($p_value != null) {
		return date( config_get( 'short_date_format' ), $p_value) ;
	}
}

/**
 * Translates the default date value entered by the creator of the custom
 * field into a date value.  For example, translate '=tomorrow' to tomorrow's
 * date.
 * @param $p_value The default date string.
 * @returns The calculated default date value if $p_value starts with '=',
 *          otherwise, returns $p_value.
 */
function cfdef_prepare_date_default( $p_value ) {
	if ( is_blank( $p_value ) ) {
		return '';
	}

	$t_value = trim( $p_value );
	$t_value_length = utf8_strlen( $t_value );

	# We are expanding {tomorrow}, {yesterday}, {+3 days}, {-7 days}, {next week}
	# See strtotime() for more details about supported formats.
	if ( $t_value_length >= 3 && $t_value[0] == '{' && $t_value[$t_value_length - 1] == '}' ) {
		$t_value = utf8_substr( $t_value, 1, $t_value_length - 2 );
		$t_value = @strtotime( $t_value );

		# Different versions of PHP return different values in case of error.
		if ( $t_value == -1 || $t_value === false ) {
			return '';
		}
	}

	return $t_value;
}

#string_custom_field_value
function cfdef_prepare_list_value($p_value) {
	// strip start and end markers before converting markers to commas
	return string_display_line( str_replace( '|', ', ', utf8_substr( str_replace( '||', '|', '|' . $p_value . '|' ), 1, -1 ) ) );
}

function cfdef_prepare_email_value($p_value) {
	return "<a href=\"mailto:" . string_attribute( $p_value ) . "\">" . string_display_line( $p_value ) . "</a>";
}

function cfdef_prepare_date_value($p_value) {
	if ($p_value != null) {
		return date( config_get( 'short_date_format'), $p_value);
	}
}


#print_custom_field_input

function cfdef_input_list($p_field_def, $t_custom_field_value) {
	$t_values = explode( '|', custom_field_prepare_possible_values( $p_field_def['possible_values'] ) );
	$t_list_size = $t_possible_values_count = count( $t_values );

	if ( $t_possible_values_count > 5 ) {
		$t_list_size = 5;
	}

	if ( $p_field_def['type'] == CUSTOM_FIELD_TYPE_ENUM ) {
		$t_list_size = 0;	# for enums the size is 0
	}

	if ( $p_field_def['type'] == CUSTOM_FIELD_TYPE_MULTILIST ) {
		echo '<select ', helper_get_tab_index(), ' name="custom_field_' . $p_field_def['id'] . '[]" size="' . $t_list_size . '" multiple="multiple">';
	} else {
		echo '<select ', helper_get_tab_index(), ' name="custom_field_' . $p_field_def['id'] . '" size="' . $t_list_size . '">';
	}

	$t_selected_values = explode( '|', $t_custom_field_value );
	foreach( $t_values as $t_option ) {
		if( in_array( $t_option, $t_selected_values, true ) ) {
			echo '<option value="' . string_attribute( $t_option ) . '" selected="selected"> ' . string_display_line( $t_option ) . '</option>';
		} else {
			echo '<option value="' . string_attribute( $t_option ) . '">' . string_display_line( $t_option ) . '</option>';
		}
	}
	echo '</select>';
}

function cfdef_input_checkbox($p_field_def, $t_custom_field_value) {
	$t_values = explode( '|', custom_field_prepare_possible_values( $p_field_def['possible_values'] ) );
	$t_checked_values = explode( '|', $t_custom_field_value );
	foreach( $t_values as $t_option ) {
		echo '<input ', helper_get_tab_index(), ' type="checkbox" name="custom_field_' . $p_field_def['id'] . '[]"';
		if( in_array( $t_option, $t_checked_values, true ) ) {
			echo ' value="' . string_attribute( $t_option ) . '" checked="checked">&#160;' . string_display_line( $t_option ) . '&#160;&#160;';
		} else {
			echo ' value="' . string_attribute( $t_option ) . '">&#160;' . string_display_line( $t_option ) . '&#160;&#160;';
		}
	}
}

function cfdef_input_radio( $p_field_def, $p_custom_field_value ) {
	$t_values = explode( '|', custom_field_prepare_possible_values( $p_field_def['possible_values'] ) );

	$t_len = strlen( $p_custom_field_value );
	if ( $t_len >= 2 && ( $p_custom_field_value[0] == '|' ) && ( $p_custom_field_value[$t_len-1] == '|' ) ) {
		$t_checked_value = substr( $p_custom_field_value, 1, $t_len - 2 );
	} else {
		$t_checked_value = $p_custom_field_value;
	}

	foreach ( $t_values as $t_option ) {
		echo '<input ', helper_get_tab_index(), ' type="radio" name="custom_field_' . $p_field_def['id'] . '"';

		if ( $t_option == $t_checked_value ) {
			echo ' value="' . string_attribute( $t_option ) . '" checked="checked">&#160;' . string_display_line( $t_option ) . '&#160;&#160;';
		} else {
			echo ' value="' . string_attribute( $t_option ) . '">&#160;' . string_display_line( $t_option ) . '&#160;&#160;';
		}
	}
}

function cfdef_input_textbox($p_field_def, $t_custom_field_value) {
	echo '<input ', helper_get_tab_index(), ' type="text" name="custom_field_' . $p_field_def['id'] . '" size="80"';
	if( 0 < $p_field_def['length_max'] ) {
		echo ' maxlength="' . $p_field_def['length_max'] . '"';
	} else {
		echo ' maxlength="255"';
	}
	echo ' value="' . string_attribute( $t_custom_field_value ) .'"></input>';
}

/**
 * Prints the controls for the date selector.
 *
 * @param $p_field_def  The custom field definition.
 * @param $p_custom_field_value  The custom field value to print.
 */
function cfdef_input_date( $p_field_def, $p_custom_field_value ) {
	print_date_selection_set( 'custom_field_' . $p_field_def['id'], config_get( 'short_date_format' ), $p_custom_field_value, false, true );
}

#value to database
function cfdef_prepare_list_value_to_database($p_value) {
	if ( '' == $p_value ) {
		return '';
	} else {
		return '|' . $p_value . '|';
	}
}

function cfdef_prepare_list_distinct_values($p_field_def) {
	$t_custom_field_table = db_get_table( 'mantis_custom_field_table' );

	$query = "SELECT possible_values
			  FROM $t_custom_field_table
			  WHERE id=" . db_param();
	$result = db_query_bound( $query, Array( $p_field_def['id'] ) );

	$t_row_count = db_num_rows( $result );
	if ( 0 == $t_row_count ) {
		return false;
	}
	$row = db_fetch_array( $result );

	$t_possible_values = custom_field_prepare_possible_values( $row['possible_values'] );
	$t_values_arr = explode( '|', $t_possible_values );
	$t_return_arr = array();

	foreach( $t_values_arr as $t_option ) {
		array_push( $t_return_arr, $t_option );
	}
	return $t_return_arr;
}
