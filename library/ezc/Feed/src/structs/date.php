<?php
/**
 * File containing the ezcFeedDateElement class.
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
 * @package Feed
 * @version //autogentag//
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @filesource
 */

/**
 * Class defining a date element.
 *
 * @property DateTime $date
 *                    The date stored as a DateTime object. An integer timestamp
 *                    or a formatted string date can be assigned to the $date
 *                    property, and it will be converted to a DateTime object.
 *                    If the conversion was not successful, the current date
 *                    is assigned to the property.
 *
 * @package Feed
 * @version //autogentag//
 */
class ezcFeedDateElement extends ezcFeedElement
{
    /**
     * Sets the property $name to $value.
     *
     * @param string $name The property name
     * @param mixed $value The property value
     * @ignore
     */
    public function __set( $name, $value )
    {
        switch ( $name )
        {
            case 'date':
                $this->properties[$name] = $this->prepareDate( $value );
                break;

            default:
                parent::__set( $name, $value );
        }
    }

    /**
     * Returns the value of property $name.
     *
     * @param string $name The property name
     * @return mixed
     * @ignore
     */
    public function __get( $name )
    {
        switch ( $name )
        {
            case 'date':
                if ( isset( $this->properties[$name] ) )
                {
                    return $this->properties[$name];
                }
                break;

            default:
                return parent::__get( $name );
        }
    }

    /**
     * Returns if the property $name is set.
     *
     * @param string $name The property name
     * @return bool
     * @ignore
     */
    public function __isset( $name )
    {
        switch ( $name )
        {
            case 'date':
                return isset( $this->properties[$name] );

            default:
                return parent::__isset( $name );
        }
    }

    /**
     * Returns the provided $date (timestamp, string or DateTime object) as a
     * DateTime object.
     *
     * It preserves the timezone if $date contained timezone information.
     *
     * @param mixed $date A date specified as a timestamp, string or DateTime object
     * @return DateTime
     */
    private function prepareDate( $date )
    {
        if ( is_numeric( $date ) )
        {
            return new DateTime( "@{$date}" );
        }
        else if ( $date instanceof DateTime )
        {
            return $date;
        }
        else
        {
            try
            {
                $d = new DateTime( $date );
            }
            catch ( Exception $e )
            {
                return new DateTime();
            }

            return $d;
        }
    }
}
?>
