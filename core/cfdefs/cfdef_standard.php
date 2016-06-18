<?php
/**
 * MantisBT - A PHP based bugtracking system
 *
 * MantisBT is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * MantisBT is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with MantisBT.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 * @package MantisBT
 */

$g_custom_field_type_definition[CUSTOM_FIELD_TYPE_STRING] = array (
	'#display_possible_values' => true,
	'#display_valid_regexp' => true,
	'#display_length_min' => true,
	'#display_length_max' => true,
	'#display_default_value' => true,
	'#function_return_distinct_values' => null,
	# MySQL 4-bytes UTF-8 chars workaround #21101
	'#function_value_to_database' => 'db_mysql_fix_utf8',
	'#function_database_to_value' => null,
	'#function_print_input' => 'cfdef_input_textbox',
	'#function_string_value' => null,
	'#function_string_value_for_email' => null,
);

$g_custom_field_type_definition[CUSTOM_FIELD_TYPE_TEXTAREA] = array (
	'#display_possible_values' => true,
	'#display_valid_regexp' => true,
	'#display_length_min' => true,
	'#display_length_max' => true,
	'#display_default_value' => true,
	'#function_return_distinct_values' => null,
	# MySQL 4-bytes UTF-8 chars workaround #21101
	'#function_value_to_database' => 'db_mysql_fix_utf8',
	'#function_database_to_value' => null,
	'#function_print_input' => 'cfdef_input_textarea',
	'#function_string_value' => null,
	'#function_string_value_for_email' => null,
);

$g_custom_field_type_definition[CUSTOM_FIELD_TYPE_NUMERIC] = array (
	'#display_possible_values' => true,
	'#display_valid_regexp' => true,
	'#display_length_min' => true,
	'#display_length_max' => true,
	'#display_default_value' => true,
	'#function_return_distinct_values' => null,
	'#function_value_to_database' => null,
	'#function_database_to_value' => null,
	'#function_print_input' => 'cfdef_input_textbox',
	'#function_string_value' => null,
	'#function_string_value_for_email' => null,
);

$g_custom_field_type_definition[CUSTOM_FIELD_TYPE_FLOAT] = array (
	'#display_possible_values' => true,
	'#display_valid_regexp' => true,
	'#display_length_min' => true,
	'#display_length_max' => true,
	'#display_default_value' => true,
	'#function_return_distinct_values' => null,
	'#function_value_to_database' => null,
	'#function_database_to_value' => null,
	'#function_print_input' => 'cfdef_input_textbox',
	'#function_string_value' => null,
	'#function_string_value_for_email' => null,
);

$g_custom_field_type_definition[CUSTOM_FIELD_TYPE_ENUM] = array (
	'#display_possible_values' => true,
	'#display_valid_regexp' => true,
	'#display_length_min' => true,
	'#display_length_max' => true,
	'#display_default_value' => true,
	'#function_return_distinct_values' => 'cfdef_prepare_list_distinct_values',
	'#function_value_to_database' => null,
	'#function_database_to_value' => null,
	'#function_print_input' => 'cfdef_input_list',
	'#function_string_value' => 'cfdef_prepare_list_value',
	'#function_string_value_for_email' => 'cfdef_prepare_list_value_for_email',
);

$g_custom_field_type_definition[CUSTOM_FIELD_TYPE_EMAIL] = array (
	'#display_possible_values' => true,
	'#display_valid_regexp' => true,
	'#display_length_min' => true,
	'#display_length_max' => true,
	'#display_default_value' => true,
	'#function_return_distinct_values' => null,
	'#function_value_to_database' => null,
	'#function_database_to_value' => null,
	'#function_print_input' => 'cfdef_input_textbox',
	'#function_string_value' => 'cfdef_prepare_email_value',
	'#function_string_value_for_email' => 'cfdef_prepare_email_value_for_email',
);

$g_custom_field_type_definition[CUSTOM_FIELD_TYPE_CHECKBOX] = array (
	'#display_possible_values' => true,
	'#display_valid_regexp' => true,
	'#display_length_min' => true,
	'#display_length_max' => true,
	'#display_default_value' => true,
	'#function_return_distinct_values' => 'cfdef_prepare_list_distinct_values',
	'#function_value_to_database' => 'cfdef_prepare_list_value_to_database',
	'#function_database_to_value' => 'cfdef_prepare_list_database_to_value',
	'#function_print_input' => 'cfdef_input_checkbox',
	'#function_string_value' => 'cfdef_prepare_list_value',
	'#function_string_value_for_email' => 'cfdef_prepare_list_value_for_email',
);

$g_custom_field_type_definition[CUSTOM_FIELD_TYPE_RADIO] = array (
	'#display_possible_values' => true,
	'#display_valid_regexp' => false,
	'#display_length_min' => false,
	'#display_length_max' => false,
	'#display_default_value' => true,
	'#function_return_distinct_values' => 'cfdef_prepare_list_distinct_values',
	'#function_value_to_database' => null,
	'#function_database_to_value' => null,
	'#function_print_input' => 'cfdef_input_radio',
	'#function_string_value' => 'cfdef_prepare_list_value',
	'#function_string_value_for_email' => 'cfdef_prepare_list_value_for_email',
);

$g_custom_field_type_definition[CUSTOM_FIELD_TYPE_LIST] = array (
	'#display_possible_values' => true,
	'#display_valid_regexp' => true,
	'#display_length_min' => true,
	'#display_length_max' => true,
	'#display_default_value' => true,
	'#function_return_distinct_values' => 'cfdef_prepare_list_distinct_values',
	'#function_value_to_database' => null,
	'#function_database_to_value' => null,
	'#function_print_input' => 'cfdef_input_list',
	'#function_string_value' => 'cfdef_prepare_list_value',
	'#function_string_value_for_email' => 'cfdef_prepare_list_value_for_email',
);

$g_custom_field_type_definition[CUSTOM_FIELD_TYPE_MULTILIST] = array (
	'#display_possible_values' => true,
	'#display_valid_regexp' => true,
	'#display_length_min' => true,
	'#display_length_max' => true,
	'#display_default_value' => true,
	'#function_return_distinct_values' => 'cfdef_prepare_list_distinct_values',
	'#function_value_to_database' => 'cfdef_prepare_list_value_to_database',
	'#function_database_to_value' => 'cfdef_prepare_list_database_to_value',
	'#function_print_input' => 'cfdef_input_list',
	'#function_string_value' => 'cfdef_prepare_list_value',
	'#function_string_value_for_email' => 'cfdef_prepare_list_value_for_email',
);

$g_custom_field_type_definition[CUSTOM_FIELD_TYPE_DATE] = array (
	'#display_possible_values' => true,
	'#display_valid_regexp' => true,
	'#display_length_min' => true,
	'#display_length_max' => true,
	'#display_default_value' => true,
	'#function_return_distinct_values' => null,
	'#function_value_to_database' => null,
	'#function_database_to_value' => null,
	'#function_default_to_value' => 'cfdef_prepare_date_default',
	'#function_print_input' => 'cfdef_input_date',
	'#function_string_value' => 'cfdef_prepare_date_value',
	'#function_string_value_for_email' => 'cfdef_prepare_date_value_for_email',
);

/**
 * Prepare List Value for database storage
 * @param string $p_value Value.
 * @return string
 */
function cfdef_prepare_list_database_to_value( $p_value ) {
	return rtrim( ltrim( $p_value, '|' ), '|' );
}

/**
 * Prepare List Value for email
 * @param string $p_value Value.
 * @return string
 */
function cfdef_prepare_list_value_for_email( $p_value ) {
	# strip start and end markers before converting markers to commas
	return str_replace( '|', ', ', utf8_substr( str_replace( '||', '|', '|' . $p_value . '|' ), 1, -1 ) );
}

/**
 * Format email address for email
 * @param string $p_value Value.
 * @return string
 */
function cfdef_prepare_email_value_for_email( $p_value ) {
	return 'mailto:' . $p_value;
}

/**
 * format date value for email
 * @param integer $p_value Value.
 * @return string
 */
function cfdef_prepare_date_value_for_email( $p_value ) {
	if( $p_value != null ) {
		return date( config_get( 'short_date_format' ), $p_value ) ;
	}
}

/**
 * Translates the default date value entered by the creator of the custom
 * field into a date value.  For example, translate '=tomorrow' to tomorrow's
 * date.
 * @param string $p_value The default date string.
 * @return string The calculated default date value if $p_value starts with '=', otherwise, returns $p_value.
 */
function cfdef_prepare_date_default( $p_value ) {
	if( is_blank( $p_value ) ) {
		return '';
	}

	$t_value = trim( $p_value );
	$t_value_length = utf8_strlen( $t_value );

	# We are expanding {tomorrow}, {yesterday}, {+3 days}, {-7 days}, {next week}
	# See strtotime() for more details about supported formats.
	if( $t_value_length >= 3 && $t_value[0] == '{' && $t_value[$t_value_length - 1] == '}' ) {
		$t_value = utf8_substr( $t_value, 1, $t_value_length - 2 );
		$t_value = @strtotime( $t_value );

		# Different versions of PHP return different values in case of error.
		if( $t_value == -1 || $t_value === false ) {
			return '';
		}
	}

	return $t_value;
}

/**
 * string_custom_field_value
 * @param string $p_value Value.
 * @return string
 */
function cfdef_prepare_list_value( $p_value ) {
	# strip start and end markers before converting markers to commas
	return string_display_line( str_replace( '|', ', ', utf8_substr( str_replace( '||', '|', '|' . $p_value . '|' ), 1, -1 ) ) );
}

/**
 * Prepare email value
 * @param string $p_value Email address.
 * @return string
 */
function cfdef_prepare_email_value( $p_value ) {
	return '<a href="mailto:' . string_attribute( $p_value ) . '">' . string_display_line( $p_value ) . '</a>';
}

/**
 * Prepare date value
 * @param integer $p_value Date timestamp.
 * @return string
 */
function cfdef_prepare_date_value( $p_value ) {
	if( $p_value != null ) {
		return date( config_get( 'short_date_format' ), $p_value );
	}
}

/**
 * print_custom_field_input
 * @param array $p_field_def          Custom field definition.
 * @param mixed $p_custom_field_value Custom field value.
 * @return void
 */
function cfdef_input_list( array $p_field_def, $p_custom_field_value ) {
	$t_values = explode( '|', custom_field_prepare_possible_values( $p_field_def['possible_values'] ) );
	$t_list_size = $t_possible_values_count = count( $t_values );

	if( $t_possible_values_count > 5 ) {
		$t_list_size = 5;
	}

	if( $p_field_def['type'] == CUSTOM_FIELD_TYPE_ENUM ) {
		$t_list_size = 0;	# for enums the size is 0
	}

	if( $p_field_def['type'] == CUSTOM_FIELD_TYPE_MULTILIST ) {
		echo '<select ' . helper_get_tab_index() . ' id="custom_field_' . $p_field_def['id'] . '" name="custom_field_' . $p_field_def['id'] . '[]" size="' . $t_list_size . '" multiple="multiple">';
	} else {
		echo '<select ' . helper_get_tab_index() . ' id="custom_field_' . $p_field_def['id'] . '" name="custom_field_' . $p_field_def['id'] . '" size="' . $t_list_size . '">';
	}

	$t_selected_values = explode( '|', $p_custom_field_value );
	foreach( $t_values as $t_option ) {
		if( in_array( $t_option, $t_selected_values, true ) ) {
			echo '<option value="' . string_attribute( $t_option ) . '" selected="selected"> ' . string_display_line( $t_option ) . '</option>';
		} else {
			echo '<option value="' . string_attribute( $t_option ) . '">' . string_display_line( $t_option ) . '</option>';
		}
	}
	echo '</select>';
}

/**
 * print_custom_field_input
 * @param array $p_field_def          Custom field definition.
 * @param mixed $p_custom_field_value Custom field value.
 * @return void
 */
function cfdef_input_checkbox( array $p_field_def, $p_custom_field_value ) {
	$t_values = explode( '|', custom_field_prepare_possible_values( $p_field_def['possible_values'] ) );
	$t_checked_values = explode( '|', $p_custom_field_value );
	for( $i = 0; $i < count( $t_values ); $i++ ) {
		$t_input_id = 'custom_field_' . $p_field_def['id'] . '_value_' . $i;
		$t_input_name = 'custom_field_' . $p_field_def['id'] . '[]';
		echo '<label class="nowrap">';
		echo '<input id="' . $t_input_id . '" ' . helper_get_tab_index() . ' type="checkbox" name="' . $t_input_name . '" value="' . string_attribute( $t_values[$i] ) . '"';
		check_checked( $t_checked_values, $t_values[$i] );
		echo " />\n";
		echo string_display_line( $t_values[$i] ) . '</label>' . "\n";
	}
}

/**
 * print_custom_field_input
 * @param array $p_field_def          Custom field definition.
 * @param mixed $p_custom_field_value Custom field value.
 * @return void
 */
function cfdef_input_radio( array $p_field_def, $p_custom_field_value ) {
	$t_values = explode( '|', custom_field_prepare_possible_values( $p_field_def['possible_values'] ) );

	$t_len = strlen( $p_custom_field_value );
	if( $t_len >= 2 && ( $p_custom_field_value[0] == '|' ) && ( $p_custom_field_value[$t_len-1] == '|' ) ) {
		$t_checked_value = substr( $p_custom_field_value, 1, $t_len - 2 );
	} else {
		$t_checked_value = $p_custom_field_value;
	}

	for( $i = 0; $i < count( $t_values ); $i++ ) {
		$t_input_id = 'custom_field_' . $p_field_def['id'] . '_value_' . $i;
		$t_input_name = 'custom_field_' . $p_field_def['id'];
		echo '<label class="nowrap">';
		echo '<input id="' . $t_input_id . '" ' . helper_get_tab_index() . ' type="radio" name="' . $t_input_name . '" value="' . string_attribute( $t_values[$i] ) . '"';
		check_checked( $t_checked_value, $t_values[$i] );
		echo " />\n";
		echo string_display_line( $t_values[$i] ) . '</label>' . "\n";
	}
}

/**
 * print_custom_field_input
 * @param array $p_field_def          Custom field definition.
 * @param mixed $p_custom_field_value Custom field value.
 * @return void
 */
function cfdef_input_textbox( array $p_field_def, $p_custom_field_value ) {
	echo '<input ' . helper_get_tab_index() . ' type="text" id="custom_field_' . $p_field_def['id'] . '" name="custom_field_' . $p_field_def['id'] . '" size="80"';
	if( 0 < $p_field_def['length_max'] ) {
		echo ' maxlength="' . $p_field_def['length_max'] . '"';
	} else {
		echo ' maxlength="255"';
	}
	echo ' value="' . string_attribute( $p_custom_field_value ) .'"></input>';
}

/**
 * print_custom_field_input
 * @param array $p_field_def          Custom field definition.
 * @param mixed $p_custom_field_value Custom field value.
 * @return void
 */
function cfdef_input_textarea( array $p_field_def, $p_custom_field_value ) {
	echo '<textarea ' . helper_get_tab_index() . ' id="custom_field_' . $p_field_def['id'] . '" name="custom_field_' . $p_field_def['id'] . '"';
	echo ' cols="70" rows="8">' . $p_custom_field_value .'</textarea>';
}

/**
 * Prints the controls for the date selector.
 *
 * @param string $p_field_def          The custom field definition.
 * @param string $p_custom_field_value The custom field value to print.
 * @return void
 */
function cfdef_input_date( $p_field_def, $p_custom_field_value ) {
	print_date_selection_set( 'custom_field_' . $p_field_def['id'], config_get( 'short_date_format' ), $p_custom_field_value, false, true );
}

/**
 * value to database
 * @param string $p_value Value.
 * @return string
 */
function cfdef_prepare_list_value_to_database( $p_value ) {
	if( '' == $p_value ) {
		return '';
	} else {
		return '|' . $p_value . '|';
	}
}

/**
 * Prepare possible values for option list
 * @param array $p_field_def Custom field definition.
 * @return array|boolean
 */
function cfdef_prepare_list_distinct_values( array $p_field_def ) {
	db_param_push();
	$t_query = 'SELECT possible_values FROM {custom_field} WHERE id=' . db_param();
	$t_result = db_query( $t_query, array( $p_field_def['id'] ) );

	$t_row = db_fetch_array( $t_result );
	if( !$t_row ) {
		return false;
	}

	$t_possible_values = custom_field_prepare_possible_values( $t_row['possible_values'] );
	$t_values_arr = explode( '|', $t_possible_values );
	$t_return_arr = array();

	foreach( $t_values_arr as $t_option ) {
		array_push( $t_return_arr, $t_option );
	}
	return $t_return_arr;
}
