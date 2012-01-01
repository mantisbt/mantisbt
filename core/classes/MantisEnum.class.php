<?php
# MantisBT - a php based bugtracking system

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
 * @copyright Copyright (C) 2000 - 2012  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

/**
 * A class that handles MantisBT Enumerations.
 *
 * For example: 10:lablel1,20:label2
 *
 * @package MantisBT
 * @subpackage classes
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
	 * @param string $enumString
	 * @param int $value
	 * @return string
	 */
	public static function getLabel( $enumString, $value ) {
		$assocArray = MantisEnum::getAssocArrayIndexedByValues( $enumString );
		$valueAsInteger = (int)$value;

		if ( isset( $assocArray[$valueAsInteger] ) ) {
			return $assocArray[$valueAsInteger];
		}

		return MantisEnum::getLabelForUnknownValue( $valueAsInteger );
	}

	/**
	 * Gets the localized label corresponding to a value.  Note that this method
	 * takes in the standard / localized enums so that if the value is in the localized
	 * enum but not the standard one, then it returns not found.
	 *
	 * @param string $enumString The standard enum string.
	 * @param string $localizedEnumString  The localized enum string.
	 * @param integer $value  The value to lookup.
	 *
	 * @return the label or the decorated value to represent not found.
	 */
	public static function getLocalizedLabel( $enumString, $localizedEnumString, $value ) {
		if ( !MantisEnum::hasValue( $enumString, $value ) ) {
			return MantisEnum::getLabelForUnknownValue( $value );
		}

		return MantisEnum::getLabel( $localizedEnumString, $value );
	}

	/**
	 * Gets the value associated with the specified label.
	 *
	 * @param string $enumString  The enumerated string.
	 * @param string $label       The label to map.
	 * @return integer value of the enum or false if not found.
	 */
	public static function getValue( $enumString, $label ) {
		$assocArrayByLabels = MantisEnum::getAssocArrayIndexedByLabels( $enumString );

		if ( isset( $assocArrayByLabels[$label] ) ) {
			return $assocArrayByLabels[$label];
		}

		return false;
	}

	/**
	 * Get an associate array for the tuples of the enum where the values
	 * are the array indices and the labels are the array values.
	 *
	 * @param string $enumString
	 * @return associate array indexed by labels.
	 */
	public static function getAssocArrayIndexedByValues( $enumString ) {
		if( isset( self::$_cacheAssocArrayIndexedByValues[$enumString] ) ) {
			return self::$_cacheAssocArrayIndexedByValues[$enumString];
		}

		$tuples = MantisEnum::getArrayOfTuples( $enumString );
		$tuplesCount = count( $tuples );

		$assocArray = array();

		foreach ( $tuples as $tuple ) {
			$tupleTokens = MantisEnum::getArrayForTuple( $tuple );

			# if not a proper tuple, skip.
			if ( count( $tupleTokens ) != 2 ) {
				continue;
			}

			$value = (int) trim( $tupleTokens[0] );

			# if already set, skip.
			if ( isset( $assocArray[ $value ] ) ) {
				continue;
			}

			$label = trim( $tupleTokens[1] );

			$assocArray[$value] = $label;
		}

		self::$_cacheAssocArrayIndexedByValues[$enumString] = $assocArray;

		return $assocArray;
	}

	/**
	 * Get an associate array for the tuples of the enum where the labels
	 * are the array indices and the values are the array values.
	 *
	 * @param string $enumString
	 * @return associate array indexed by labels.
	 */
	public static function getAssocArrayIndexedByLabels( $enumString ) {
		return array_flip( MantisEnum::getAssocArrayIndexedByValues( $enumString ) );
	}

	/**
	 * Gets an array with all values in the enum.
	 *
	 * @param $enumString
	 * @return array of unique values.
	 */
	public static function getValues( $enumString ) {
		return array_unique( array_keys( MantisEnum::getAssocArrayIndexedByValues( $enumString ) ) );
	}

	/**
	 * Checks if the specified enum string contains the specified value.
	 *
	 * @param string $enumString  The enumeration string.
	 * @param integer $value      The value to chec,
	 * @return bool true if found, false otherwise.
	 */
	public static function hasValue( $enumString, $value ) {
		$assocArray = MantisEnum::getAssocArrayIndexedByValues( $enumString );
		$valueAsInteger = (int)$value;
		return isset( $assocArray[$valueAsInteger] );
	}

	/**
	 * Breaks up an enum string into num:value elements
	 *
	 * @param string $enumString enum string
	 * @return array array of num:value elements
	 */
	private static function getArrayOfTuples( $enumString ) {
		if ( strlen( trim( $enumString ) ) == 0 ) {
			return array();
		}

		$rawArray = explode( MantisEnum::TUPLE_SEPARATOR, $enumString );
		$trimmedArray = array();

		foreach ( $rawArray as $tuple ) {
			$trimmedArray[] = trim( $tuple );
		}

		return $trimmedArray;
	}

	/**
	 * Given one num:value pair it will return both in an array
	 * num will be first (element 0) value second (element 1)
	 *
	 * @param string $tuple a num:value pair
	 * @return array array(value, label)
	 */
	private static function getArrayForTuple( $tuple ) {
		return explode( MantisEnum::VALUE_LABEL_SEPARATOR, $tuple );
	}

	/**
	 * Given a value it decorates it and returns it as the label.
	 *
	 * @param integer The value (e.g. 50).
	 * @return The decorated value (e.g. @50@).
	 */
	private static function getLabelForUnknownValue( $value ) {
		$valueAsInteger = (int)$value;
		return '@' . $valueAsInteger . '@';
	}
}
