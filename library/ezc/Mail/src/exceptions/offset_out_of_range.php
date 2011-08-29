<?php
/**
 * File containing the ezcMailOffsetOutOfRangeException class
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
 * The ezcMailOffsetOutOfRangeException is thrown when request is made to
 * fetch messages with the offset outside of the existing message range.
 *
 * @package Mail
 * @version //autogen//
 */
class ezcMailOffsetOutOfRangeException extends ezcMailException
{
    /**
     * Constructs an ezcMailOffsetOutOfRangeException
     *
     * @param mixed $offset
     * @param mixed $count
     */
    public function __construct( $offset, $count )
    {
        parent::__construct( "The offset '{$offset}' is outside of the message subset '{$offset}', '{$count}'." );
    }
}
?>
