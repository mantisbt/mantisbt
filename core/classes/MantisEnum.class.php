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
 * MantisBT Enumeration Handling
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 * @package MantisBT
 * @subpackage classes
 */

/**
 * A class that handles MantisBT Enumerations.
 *
 * For example: 10:lablel1,20:label2
 *
 */
class MantisEnum {
	/**
	 * Separator that is used to separate the enum values from their labels.
	 */
	const VALUE_LABEL_SEPARATOR = ':';

	/**
	 * Separator that is used to separate the enum tuples within an enumeration definition.
	 */
	const TUPLE_SEPARATOR = ',';

	/**
	 *
	 * @var array Used to cache previous results
	 */
	private static $_cacheAssocArrayIndexedByValues = array();

	/**
	 * Get the string associated with the $p_enum value
	 *
	 * @param string  $p_enum_string The enumerated string.
	 * @param integer $p_value       The value to lookup.
	 * @return string
	 */
	public static function getLabel( $p_enum_string, $p_value ) {
		$t_assoc_array = MantisEnum::getAssocArrayIndexedByValues( $p_enum_string );
		$t_value_as_integer = (int)$p_value;

		if( isset( $t_assoc_array[$t_value_as_integer] ) ) {
			return $t_assoc_array[$t_value_as_integer];
		}

		return MantisEnum::getLabelForUnknownValue( $t_value_as_integer );
	}

	/**
	 * Gets the localized label corresponding to a value.  Note that this method
	 * takes in the standard / localized enums so that if the value is in the localized
	 * enum but not the standard one, then it returns not found.
	 *
	 * @param string  $p_enum_string           The standard enum string.
	 * @param string  $p_localized_enum_string The localized enum string.
	 * @param integer $p_value                 The value to lookup.
	 *
	 * @return string the label or the decorated value to represent not found.
	 */
	public static function getLocalizedLabel( $p_enum_string, $p_localized_enum_string, $p_value ) {
		if( !MantisEnum::hasValue( $p_enum_string, $p_value ) ) {
			return MantisEnum::getLabelForUnknownValue( $p_value );
		}

		$t_assoc_array = MantisEnum::getAssocArrayIndexedByValues( $p_localized_enum_string );

		if( isset( $t_assoc_array[(int)$p_value] ) ) {
			return $t_assoc_array[(int)$p_value];
		}

		return MantisEnum::getLabel( $p_enum_string, $p_value );
	}

	/**
	 * Gets the value associated with the specified label.
	 *
	 * @param string $p_enum_string The enumerated string.
	 * @param string $p_label       The label to map.
	 * @return integer value of the enumeration or false if not found.
	 */
	public static function getValue( $p_enum_string, $p_label ) {
		$t_assoc_array_by_labels = MantisEnum::getAssocArrayIndexedByLabels( $p_enum_string );

		if( isset( $t_assoc_array_by_labels[$p_label] ) ) {
			return $t_assoc_array_by_labels[$p_label];
		}

		return false;
	}

	/**
	 * Get an associate array for the tuples of the enum where the values
	 * are the array indices and the labels are the array values.
	 *
	 * @param string $p_enum_string The enumerated string.
	 * @return array associate array indexed by labels.
	 */
	public static function getAssocArrayIndexedByValues( $p_enum_string ) {
		if( isset( self::$_cacheAssocArrayIndexedByValues[$p_enum_string] ) ) {
			return self::$_cacheAssocArrayIndexedByValues[$p_enum_string];
		}

		$t_tuples = MantisEnum::getArrayOfTuples( $p_enum_string );

		$t_assoc_array = array();

		foreach ( $t_tuples as $t_tuple ) {
			$t_tuple_tokens = MantisEnum::getArrayForTuple( $t_tuple );

			# if not a proper tuple, skip.
			if( count( $t_tuple_tokens ) != 2 ) {
				continue;
			}

			$t_value = (int)trim( $t_tuple_tokens[0] );

			# if already set, skip.
			if( isset( $t_assoc_array[$t_value] ) ) {
				continue;
			}

			$t_label = trim( $t_tuple_tokens[1] );

			$t_assoc_array[$t_value] = $t_label;
		}

		self::$_cacheAssocArrayIndexedByValues[$p_enum_string] = $t_assoc_array;

		return $t_assoc_array;
	}

	/**
	 * Get an associate array for the tuples of the enumeration where the labels
	 * are the array indices and the values are the array values.
	 *
	 * @param string $p_enum_string The enumerated string.
	 * @return array associate array indexed by labels.
	 */
	public static function getAssocArrayIndexedByLabels( $p_enum_string ) {
		return array_flip( MantisEnum::getAssocArrayIndexedByValues( $p_enum_string ) );
	}

	/**
	 * Gets an array with all values in the enum.
	 *
	 * @param string $p_enum_string The enumerated string.
	 * @return array array of unique values.
	 */
	public static function getValues( $p_enum_string ) {
		return array_unique( array_keys( MantisEnum::getAssocArrayIndexedByValues( $p_enum_string ) ) );
	}

	/**
	 * Checks if the specified enum string contains the specified value.
	 *
	 * @param string  $p_enum_string The enumeration string.
	 * @param integer $p_value       The value to check.
	 * @return boolean true if found, false otherwise.
	 */
	public static function hasValue( $p_enum_string, $p_value ) {
		$t_assoc_array = MantisEnum::getAssocArrayIndexedByValues( $p_enum_string );
		$t_value_as_integer = (int)$p_value;
		return isset( $t_assoc_array[$t_value_as_integer] );
	}

	/**
	 * Breaks up an enum string into num:value elements
	 *
	 * @param string $p_enum_string The enumerated string.
	 * @return array array of num:value elements
	 */
	private static function getArrayOfTuples( $p_enum_string ) {
		if( strlen( trim( $p_enum_string ) ) == 0 ) {
			return array();
		}

		$t_raw_array = explode( MantisEnum::TUPLE_SEPARATOR, $p_enum_string );
		$t_trimmed_array = array();

		foreach( $t_raw_array as $t_tuple ) {
			$t_trimmed_array[] = trim( $t_tuple );
		}

		return $t_trimmed_array;
	}

	/**
	 * Given one num:value pair it will return both in an array
	 * num will be first (element 0) value second (element 1)
	 *
	 * @param string $p_tuple A num:value pair.
	 * @return array An array with of value, label.
	 */
	private static function getArrayForTuple( $p_tuple ) {
		return explode( MantisEnum::VALUE_LABEL_SEPARATOR, $p_tuple );
	}

	/**
	 * Given a value it decorates it and returns it as the label.
	 *
	 * @param integer $p_value The value (e.g. 50).
	 * @return string The decorated value (e.g. @50@).
	 */
	private static function getLabelForUnknownValue( $p_value ) {
		$t_value_as_integer = (int)$p_value;
		return '@' . $t_value_as_integer . '@';
	}
}
