<?php
/**
 * File containing the ezcMailTransportSmtpException class
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
 * @access private
 */

/**
 * ezcMailTransportSmtpException is thrown when an exceptional state
 * occures internally in the ezcMailSmtpTransport class. As it never enters
 * "userspace" the class is marked as private.
 *
 * @package Mail
 * @version //autogen//
 * @access private
 */
class ezcMailTransportSmtpException extends ezcMailException
{
    /**
     * Constructs an ezcMailTransportSmtpException with the highlevel error
     * message $message.
     *
     * @param string $message
     */
    public function __construct( $message )
    {
        parent::__construct( $message );
    }
}
?>
