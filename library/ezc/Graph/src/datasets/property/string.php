<?php
/**
 * File containing the abstract ezcGraphDataSetStringProperty class
 *
 * @package Graph
 * @version //autogentag//
 * @copyright Copyright (C) 2005-2009 eZ Systems AS. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 */
/**
 * Class for string properties of datasets
 *
 * This class is used to store properties for datasets, which should be
 * validated as string values.
 *
 * For a basic usage example of those dataset properties take a look at the API
 * documentation of the ezcGraphDataSetProperty class.
 *
 * @version //autogentag//
 * @package Graph
 */
class ezcGraphDataSetStringProperty extends ezcGraphDataSetProperty
{
    /**
     * Converts value to an {@link ezcGraphColor} object
     * 
     * @param & $value 
     * @return void
     */
    protected function checkValue( &$value )
    {
        $value = (string) $value;
        return true;
    }
}

?>
