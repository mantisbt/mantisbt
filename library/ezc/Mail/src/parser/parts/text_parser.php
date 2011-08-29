<?php
/**
 * File containing the ezcMailTextParser class
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
 * Parses mail parts of type "text".
 *
 * @package Mail
 * @version //autogen//
 * @access private
 */
class ezcMailTextParser extends ezcMailPartParser
{
    /**
     * Stores the parsed text of this part.
     *
     * @var string $text
     */
    private $text = null;

    /**
     * Holds the headers of this text part.
     *
     * @var ezcMailHeadersHolder
     */
    private $headers = null;

    /**
     * Holds the subtype of the parsed part.
     *
     * @var string
     */
    private $subType = null;

    /**
     * Constructs a new ezcMailTextParser of the subtype $subType and
     * additional headers $headers.
     *
     * @param string $subType
     * @param ezcMailHeadersHolder $headers
     */
    public function __construct( $subType, ezcMailHeadersHolder $headers )
    {
        $this->subType = $subType;
        $this->headers = $headers;
    }

    /**
     * Adds each line to the body of the text part.
     *
     * @param string $line
     */
    public function parseBody( $line )
    {
        $line = rtrim( $line, "\r\n" );
        if ( $this->text === null )
        {
            $this->text = $line;
        }
        else
        {
            $this->text .= "\n" . $line;
        }
    }

    /**
     * Returns the ezcMailText part corresponding to the parsed message.
     *
     * @return ezcMailText
     */
    public function finish()
    {
        $charset = "us-ascii"; // RFC 2822 default
        if ( isset( $this->headers['Content-Type'] ) )
        {
            preg_match( '/\s*charset\s?=\s?"?([^;"\s]*);?/i',
                            $this->headers['Content-Type'],
                            $parameters );
            if ( count( $parameters ) > 0 )
            {
                $charset = strtolower( trim( $parameters[1], '"' ) );
            }
        }

        $encoding = strtolower( $this->headers['Content-Transfer-Encoding'] );
        if ( $encoding == ezcMail::QUOTED_PRINTABLE )
        {
            $this->text = quoted_printable_decode( $this->text );
        }
        else if ( $encoding == ezcMail::BASE64 )
        {
            $this->text = base64_decode( $this->text );
        }

        $this->text = ezcMailCharsetConverter::convertToUTF8( $this->text, $charset );

        $part = new ezcMailText( $this->text, 'utf-8', ezcMail::EIGHT_BIT, $charset );
        $part->subType = $this->subType;
        $part->setHeaders( $this->headers->getCaseSensitiveArray() );
        ezcMailPartParser::parsePartHeaders( $this->headers, $part );
        $part->size = strlen( $this->text );
        return $part;
    }
}
?>
