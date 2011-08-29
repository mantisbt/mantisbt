<?php
/**
 * File containing the ezcFeedParseErrorException class.
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
 * Thrown when a feed can not be parsed at all.
 *
 * @package Feed
 * @version //autogentag//
 */
class ezcFeedParseErrorException extends ezcFeedException
{
    /**
     * Constructs a new ezcFeedParseErrorException.
     *
     * If $uri is not null the generated message will contain it.
     *
     * @param string $uri The URI which identifies the XML document which was tried to be parsed
     * @param string $message An extra message to be included in the thrown exception text
     */
    public function __construct( $uri = null, $message )
    {
        if ( $uri !== null )
        {
            parent::__construct( "Parse error while parsing feed '{$uri}': {$message}." );
        }
        else
        {
            parent::__construct( "Parse error while parsing feed: {$message}." );
        }
    }
}
?>
