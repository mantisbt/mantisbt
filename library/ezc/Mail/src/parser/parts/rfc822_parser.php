<?php
/**
 * File containing the ezcMailRfc822Parser class
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
 * Note that this class does not parse RFC822 digest messages containing an extra header block.
 * Use the RFC822DigestParser to these.
 *
 * @package Mail
 * @version //autogen//
 * @access private
 */
class ezcMailRfc822Parser extends ezcMailPartParser
{
    /**
     * Holds the headers parsed.
     *
     * @var ezcMailHeadersHolder
     */
    private $headers = null;

    /**
     * This state is used when the parser is parsing headers.
     */
    const PARSE_STATE_HEADERS = 1;

    /**
     * This state is used when the parser is parsing the body.
     */
    const PARSE_STATE_BODY = 2;

    /**
     * Stores the state of the parser.
     *
     * @var int
     */
    private $parserState = self::PARSE_STATE_HEADERS;

    /**
     * The parser of the body.
     *
     * This will be set after the headers have been parsed.
     *
     * @var ezcMailPartParser
     */
    private $bodyParser = null;

    /**
     * Constructs a new ezcMailRfc822Parser.
     */
    public function __construct()
    {
        $this->headers = new ezcMailHeadersHolder();
    }

    /**
     * Parses the body of an rfc 2822 message.
     *
     * @throws ezcBaseFileNotFoundException
     *         if a neccessary temporary file could not be openened.
     * @param string $origLine
     */
    public function parseBody( $origLine )
    {
        $line = rtrim( $origLine, "\r\n" );
        if ( $this->parserState == self::PARSE_STATE_HEADERS && $line == '' )
        {
            $this->parserState = self::PARSE_STATE_BODY;

            // clean up headers for the part
            // the rest of the headers should be set on the mail object.

            $headers = new ezcMailHeadersHolder();
            $headers['Content-Type'] = $this->headers['Content-Type'];
            if ( isset( $this->headers['Content-Transfer-Encoding'] ) )
            {
                $headers['Content-Transfer-Encoding'] = $this->headers['Content-Transfer-Encoding'];
            }

            if ( isset( $this->headers['Content-Disposition'] ) )
            {
                $headers['Content-Disposition'] = $this->headers['Content-Disposition'];
            }

            // get the correct body type
            $this->bodyParser = self::createPartParserForHeaders( $headers );
        }
        else if ( $this->parserState == self::PARSE_STATE_HEADERS )
        {
            $this->parseHeader( $line, $this->headers );
        }
        else // we are parsing headers
        {
            $this->bodyParser->parseBody( $origLine );
        }
    }

    /**
     * Returns an ezcMail corresponding to the parsed message.
     * You can specify an alternate class using the $class parameter, if you
     * extended ezcMail.
     *
     * @param string $class Class to instanciate instead of ezcMail.
     * @return ezcMail
     */
    public function finish( $class = "ezcMail" )
    {
        $mail = new $class();
        $mail->setHeaders( $this->headers->getCaseSensitiveArray() );
        ezcMailPartParser::parsePartHeaders( $this->headers, $mail );

        // from
        if ( isset( $this->headers['From'] ) )
        {
            $mail->from = ezcMailTools::parseEmailAddress( $this->headers['From'] );
        }
        // to
        if ( isset( $this->headers['To'] ) )
        {
            $mail->to = ezcMailTools::parseEmailAddresses( $this->headers['To'] );
        }
        // cc
        if ( isset( $this->headers['Cc'] ) )
        {
            $mail->cc = ezcMailTools::parseEmailAddresses( $this->headers['Cc'] );
        }
        // bcc
        if ( isset( $this->headers['Bcc'] ) )
        {
            $mail->bcc = ezcMailTools::parseEmailAddresses( $this->headers['Bcc'] );
        }
        // subject
        if ( isset( $this->headers['Subject'] ) )
        {
            $mail->subject = ezcMailTools::mimeDecode( $this->headers['Subject'] );
            $mail->subjectCharset = 'utf-8';
        }
        // message ID
        if ( isset( $this->headers['Message-Id'] ) )
        {
            $mail->messageID = $this->headers['Message-Id'];
        }

        // Return-Path
        if ( isset( $this->headers['Return-Path'] ) )
        {
            $mail->returnPath = ezcMailTools::parseEmailAddress( $this->headers['Return-Path'] );
        }

        if ( $this->bodyParser !== null )
        {
            $mail->body = $this->bodyParser->finish();
        }
        return $mail;
    }
}
?>
