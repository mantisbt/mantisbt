<?php
/**
 * File containing the ezcMailRfc822DigestParser class
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
 * Parses RFC822 messages.
 *
 * Note that this class does not parse RFC822 digest messages containing of an extra header block.
 * Use the RFC822DigestParser to these.
 *
 * @package Mail
 * @version //autogen//
 * @access private
 */
class ezcMailRfc822DigestParser extends ezcMailPartParser
{
    /**
     * Holds the headers for this part.
     *
     * @var ezcMailHeadersHolder
     */
    private $headers = null;

    /**
     * Holds the digested message parser.
     *
     * @var ezcMailPartParser
     */
    private $mailParser = null;

    /**
     * Holds the size of the digest.
     *
     * @var int
     */
    private $size;

    /**
     * Constructs a new digest parser with the headers $headers.
     *
     * @param ezcMailHeadersHolder $headers
     */
    public function __construct( ezcMailHeadersHolder $headers )
    {
        $this->headers = $headers;
        $this->mailParser = new ezcMailRfc822Parser();
        $this->size = 0;
    }

    /**
     * Parses each line of the digest body.
     *
     * Every line is part of the digested mail. It is sent directly to the mail parser.
     *
     * @param string $line
     */
    public function parseBody( $line )
    {
        $this->mailParser->parseBody( $line );
        $this->size += strlen( $line );
    }

    /**
     * Returns a ezcMailRfc822Digest with the digested mail in it.
     *
     * @return ezcMailRfc822Digest
     */
    public function finish()
    {
        $digest = new ezcMailRfc822Digest( $this->mailParser->finish() );
        ezcMailPartParser::parsePartHeaders( $this->headers, $digest );
        $digest->size = $this->size;
        return $digest;
    }
}
?>
