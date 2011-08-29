<?php
/**
 * File containing the ezcMailMultipartAlternative class
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
 * ezcMailMultipartAlternative is used to bundle a group of mail parts
 * where only one should be shown.
 *
 * This is useful e.g if you have a text in some fancy format but you also want
 * to provide a backup plain text format to make sure everyone can read the
 * mail. The alternatives should be added in an order of increasing
 * faithfulness to the original content.  In general, the best choice is the
 * LAST part of a type supported by the recipients mail client.
 *
 * The following example shows a HTML mail with a plain text backup in case
 * the recipients client can't display HTML mail.
 * <code>
 * $mail = new ezcMail();
 * $mail->from = new ezcMailAddress( 'sender@example.com', 'Adrian Ripburger' );
 * $mail->addTo( new ezcMailAddress( 'receiver@example.com', 'Maureen Corley' ) );
 * $mail->subject = "Example of an HTML email with attachments";
 * $plainText = new ezcMailText( "This is the plain text part" );
 * $htmlText = new ezcMailText( "<html>This is the HTML part</html>" );
 * $htmlText->subType = 'html';
 * $mail->body = new ezcMailMultipartAlternative( $plainText, $htmlText );
 * </code>
 *
 * @package Mail
 * @version //autogen//
 */
class ezcMailMultipartAlternative extends ezcMailMultipart
{
    /**
     * Constructs a new ezcMailMultipartAlternative
     *
     * The constructor accepts an arbitrary number of ezcMailParts or arrays with ezcMailparts.
     * Parts are added in the order provided. Parameters of the wrong
     * type are ignored.
     *
     * @param ezcMailPart|array(ezcMailPart) $...
     */
    public function __construct()
    {
        $args = func_get_args();
        parent::__construct( $args );
    }

    /**
     * Appends a part to the list of parts.
     *
     * @param ezcMailPart $part
     */
    public function appendPart( ezcMailPart $part )
    {
        $this->parts[] = $part;
    }

    /**
     * Returns the mail parts associated with this multipart.
     *
     * @return array(ezcMailPart)
     */
    public function getParts()
    {
        return $this->parts;
    }

    /**
     * Returns "alternative".
     *
     * @return string
     */
    public function multipartType()
    {
        return "alternative";
    }
}
?>
