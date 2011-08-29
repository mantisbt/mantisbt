<?php
/**
 * File containing the ezcMailHeaderHolder class
 *
 * Licensed to the Apache Software Foundation (ASF) under one
 * or more contributor license agreements.  See the NOTICE file
 * distributed with this work for additional information
 * regarding copyright ownership.  The ASF licenses this file
 * to you under the Apache License, Version 2.0 (the
 * "License"); you may not use this file except in compliance
 * with the License.  You may obtain a copy of the License at
 * 
 *   http://www.apache.org/licenses/LICENSE-2.0
 * 
 * Unless required by applicable law or agreed to in writing,
 * software distributed under the License is distributed on an
 * "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
 * KIND, either express or implied.  See the License for the
 * specific language governing permissions and limitations
 * under the License.
 *
 * @package Mail
 * @version //autogen//
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

/**
 * Holds the headers of a mail during parsing and allows case insensitive lookup
 * but case sensitive storage.
 *
 * @package Mail
 * @version //autogen//
 * @access private
 */
class ezcMailHeadersHolder implements ArrayAccess
{
    /**
     * Holds the mapping between the case insensitive key and the real key.
     *
     * Format: array(lowerCaseKey, mixedCaseKey)
     *
     * @var array(string=>string)
     */
    private $lookup = array();

    /**
     * Holds the normal associative array between keys in correct case and values.
     *
     * Format: array(mixedCaseKey, value)
     *
     * @var array(string=>string)
     */
    private $map = array();

    /**
     * Constructs a new case insensitive associtive array formed around the array
     * $map with mixed case keys.
     *
     * @param array(string=>string) $map
     */
    public function __construct( array $map = array() )
    {
        $this->map = $map;
        foreach ( $map as $key => $value )
        {
            $this->lookup[strtolower( $key )] = $key;
        }
    }

    /**
     * Returns true if the $key exists in the array.
     *
     * @param string $key
     * @return bool
     */
    public function offsetExists( $key )
    {
        return array_key_exists( strtolower( $key ), $this->lookup );
    }

    /**
     * Returns the value recognized with $key.
     *
     * @param string $key
     * @return mixed
     */
    public function offsetGet( $key )
    {
        $key = strtolower( $key );
        if ( !array_key_exists( $key, $this->lookup ) )
        {
            return null;
        }
        return $this->map[$this->lookup[$key]];
    }

    /**
     * Sets the offset $key to the value $value.
     *
     * If it is a new entry the case in $key will be stored. If the $key exists already
     * using a case insensitive lookup the new spelling will be discarded.
     *
     * @param string $key
     * @param mixed $value
     */
    public function offsetSet( $key, $value )
    {
        $lowerKey = strtolower( $key );
        if ( !array_key_exists( $lowerKey, $this->lookup ) )
        {
            $this->map[$key] = $value;
            $this->lookup[$lowerKey] = $key;
        }
        else // use old case
        {
            $this->map[$this->lookup[$lowerKey]] = $value;
        }
    }

    /**
     * Unsets the key $key.
     *
     * @param string $key
     */
    public function offsetUnset( $key )
    {
        $key = strtolower( $key );
        if ( array_key_exists( $key, $this->lookup ) )
        {
            unset( $this->map[$this->lookup[$key]] );
            unset( $this->lookup[$key] );
        }
    }

    /**
     * Returns a copy of the associative array with the case of the keys preserved.
     *
     * @return array(string=>string)
     */
    public function getCaseSensitiveArray()
    {
        return $this->map;
    }
}
?>
