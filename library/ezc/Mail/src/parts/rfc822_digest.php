<?php
/**
 * File containing the ezcMailRfc822Digest class
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
 * Mail part or mail digest parts.
 *
 * This class is used to insert mail into mail.
 *
 *
 * This example assumes that the mail object to digest is availble in the $digest variable:
 * <code>
 * $mail = new ezcMail();
 * $mail->from = new ezcMailAddress( 'sender@example.com', 'Largo LaGrande' );
 * $mail->addTo( new ezcMailAddress( 'receiver@example.com', 'Wally B. Feed' ) );
 * $mail->subject = "This is the subject of the mail with a mail digest.";
 * $textPart = new ezcMailText( "This is the body of the mail with a mail digest." );
 *
 * $mail->body = new ezcMailMultipartMixed( $textPart, new ezcMailRfc822Digest( $digest ) );
 *
 * $transport = new ezcMailMtaTransport();
 * $transport->send( $mail );
 * </code>
 *
 * @property string $mail
 *           The mail object to digest.
 *
 * @package Mail
 * @version //autogen//
 */
class ezcMailRfc822Digest extends ezcMailPart
{
    /**
     * Constructs a new ezcMailDigest with the mail $mail.
     *
     * @param ezcMail $mail
     */
    public function __construct( ezcMail $mail )
    {
        parent::__construct();

        $this->mail = $mail;
        $this->setHeader( 'Content-Type', 'message/rfc822' );
        $this->setHeader( 'Content-Disposition', 'inline' );
    }

    /**
     * Sets the property $name to $value.
     *
     * @throws ezcBasePropertyNotFoundException
     *         if the property does not exist.
     * @param string $name
     * @param mixed $value
     * @ignore
     */
    public function __set( $name, $value )
    {
        switch ( $name )
        {
            case 'mail':
                $this->properties[$name] = $value;
                break;
            default:
                return parent::__set( $name, $value );
                break;
        }
    }

    /**
     * Sets the property $name to $value.
     *
     * @throws ezcBasePropertyNotFoundException
     *         if the property does not exist.
     * @param string $name
     * @return mixed
     * @ignore
     */
    public function __get( $name )
    {
        switch ( $name )
        {
            case 'mail':
                return $this->properties[$name];
                break;
            default:
                return parent::__get( $name );
                break;
        }
    }

    /**
     * Returns true if the property $name is set, otherwise false.
     *
     * @param string $name
     * @return bool
     * @ignore
     */
    public function __isset( $name )
    {
        switch ( $name )
        {
            case 'mail':
                return isset( $this->properties[$name] );

            default:
                return parent::__isset( $name );
        }
    }

    /**
     * Returns the body part of this mail consisting of the digested mail.
     *
     * @return string
     */
    public function generateBody()
    {
        return $this->mail->generate();
    }
}
?>
