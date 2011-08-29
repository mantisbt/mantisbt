<?php
/**
 * File containing the ezcMailPop3TransportOptions class
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
 * Class containing the options for POP3 transport.
 *
 * The options from {@link ezcMailTransportOptions} are inherited.
 *
 * Example of how to use POP3 transport options:
 * <code>
 * $options = new ezcMailPop3TransportOptions();
 * $options->ssl = true;
 * $options->timeout = 3;
 * $options->authenticationMethod = ezcMailPop3Transport::AUTH_APOP;
 *
 * $pop3 = new ezcMailPop3Transport( 'pop3.example.com', null, $options );
 * </code>
 *
 * @property int $authenticationMethod
 *           Specifies the method to connect to the POP3 transport. The methods
 *           supported are {@link ezcMailPop3Transport::AUTH_PLAIN_TEXT} and
 *           {@link ezcMailPop3Transport::AUTH_APOP}.
 *
 * @package Mail
 * @version //autogen//
 */
class ezcMailPop3TransportOptions extends ezcMailTransportOptions
{
    /**
     * Constructs an object with the specified values.
     *
     * @throws ezcBasePropertyNotFoundException
     *         if $options contains a property not defined
     * @throws ezcBaseValueException
     *         if $options contains a property with a value not allowed
     * @param array(string=>mixed) $options
     */
    public function __construct( array $options = array() )
    {
        // default authentication method is PLAIN
        $this->authenticationMethod = ezcMailPop3Transport::AUTH_PLAIN_TEXT;

        parent::__construct( $options );
    }

    /**
     * Sets the option $name to $value.
     *
     * @throws ezcBasePropertyNotFoundException
     *         if the property $name is not defined
     * @throws ezcBaseValueException
     *         if $value is not correct for the property $name
     * @param string $name
     * @param mixed $value
     * @ignore
     */
    public function __set( $name, $value )
    {
        switch ( $name )
        {
            case 'authenticationMethod':
                if ( !is_numeric( $value ) ) 
                {
                    throw new ezcBaseValueException( $name, $value, 'int' );
                }
                $this->properties[$name] = (int) $value;
                break;

            default:
                parent::__set( $name, $value );
        }
    }
}
?>
