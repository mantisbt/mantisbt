<?php
/**
 * File containing the ezcMailMtaTransport class
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
 * Implementation of the mail transport interface using the system MTA.
 *
 * The system MTA translates to sendmail on most Linux distributions.
 *
 * Qmail insists it should only have "\n" linebreaks and will send
 * garbled messages with the default "\r\n" setting.
 * Use ezcMailTools::setLineBreak( "\n" ) before sending mail to fix this issue.
 *
 * @package Mail
 * @version //autogen//
 * @mainclass
 */
class ezcMailMtaTransport implements ezcMailTransport
{
    /**
     * Constructs a new ezcMailMtaTransport.
     */
    public function __construct(  )
    {
    }

    /**
     * Sends the mail $mail using the PHP mail method.
     *
     * Note that a message may not arrive at the destination even though
     * it was accepted for delivery.
     *
     * @throws ezcMailTransportException
     *         if the mail was not accepted for delivery by the MTA.
     * @param ezcMail $mail
     */
    public function send( ezcMail $mail )
    {
        $mail->appendExcludeHeaders( array( 'to', 'subject' ) );
        $headers = rtrim( $mail->generateHeaders() ); // rtrim removes the linebreak at the end, mail doesn't want it.

        if ( ( count( $mail->to ) + count( $mail->cc ) + count( $mail->bcc ) ) < 1 )
        {
            throw new ezcMailTransportException( 'No recipient addresses found in message header.' );
        }
        $additionalParameters = "";
        if ( isset( $mail->returnPath ) )
        {
            $additionalParameters = "-f{$mail->returnPath->email}";
        }
        $success = mail( ezcMailTools::composeEmailAddresses( $mail->to ),
                         $mail->getHeader( 'Subject' ), $mail->generateBody(), $headers, $additionalParameters );
        if ( $success === false )
        {
            throw new ezcMailTransportException( 'The email could not be sent by sendmail' );
        }
    }
}
?>
