<?php
/**
 * File containing the ezcMailMboxTransport class
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
 * ezcMailMboxTransport implements mail retrieval from an mbox file.
 *
 * The mbox set is constructed from a file pointer and iterates over all the
 * messages in an mbox file.
 *
 * @package Mail
 * @version //autogen//
 * @mainclass
 */
class ezcMailMboxTransport
{
    /**
     * Holds the filepointer to the mbox
     *
     * @var resource(filepointer)
     */
    public $fh;

    /**
     * Constructs the ezcMailMboxTransport object
     *
     * Opens the mbox $fileName.
     *
     * @throws ezcBaseFileNotFoundException
     *         if the mbox file could not be found.
     * @throws ezcBaseFilePermissionException
     *         if the mbox file could be opened for reading.
     * @param string $fileName
     */
    public function __construct( $fileName )
    {
        if ( !file_exists( $fileName ) )
        {
            throw new ezcBaseFileNotFoundException( $fileName, 'mbox' );
        }
        if ( !is_readable( $fileName ) )
        {
            throw new ezcBaseFilePermissionException( $fileName, ezcBaseFileException::READ );
        }
        $this->fh = fopen( $fileName, 'rt' );
    }

    /**
     * Finds the position of the first message while skipping a possible header.
     *
     * Mbox files can contain a header which does not describe an email
     * message. This method skips over this optional header by checking for a
     * specific From MAILER-DAEMON header.
     *
     * @return int
     */
    private function findFirstMessage()
    {
        $data = fgets( $this->fh );
        fseek( $this->fh, 0 );
        if ( substr( $data, 0, 18 ) === 'From MAILER-DAEMON' )
        {
            return $this->findNextMessage();
        }
        else
        {
            return 0;
        }
    }

    /**
     * Reads through the Mbox file and stops at the next message.
     *
     * Messages in Mbox files are separated with lines starting with "From "
     * and this function reads to the next "From " marker. It then returns the
     * current posistion in the file. If EOF is detected during reading the
     * function returns false instead.
     *
     * @return int
     */
    private function findNextMessage()
    {
        do
        {
            $data = fgets( $this->fh );
        } while ( !feof( $this->fh ) && substr( $data, 0, 5 ) !== "From " );

        if ( feof( $this->fh ) )
        {
            return false;
        }
        return ftell( $this->fh );
    }

    /**
     * This function reads through the whole mbox and returns starting positions of the messages.
     *
     * @return array(int=>int)
     */
    public function listMessages()
    {
        $messages = array();
        fseek( $this->fh, 0 );
        // Skip the first mail as this is the mbox header
        $position = $this->findFirstMessage();
        if ( $position === false )
        {
            return $messages;
        }
        // Continue reading through the rest of the mbox
        do
        {
            $position = $this->findNextMessage();
            if ( $position !== false )
            {
                $messages[] = $position;
            }
        } while ( $position !== false );

        return $messages;
    }

    /**
     * Returns an ezcMailMboxSet containing all the messages in the mbox.
     *
     * @return ezcMailMboxSet
     */
    public function fetchAll()
    {
        $messages = $this->listMessages();
        return new ezcMailMboxSet( $this->fh, $messages );
    }

    /**
     * Returns an ezcMailMboxSet containing only the $number -th message in the mbox.
     *
     * @throws ezcMailNoSuchMessageException
     *         if the message $number is out of range.
     * @param int $number
     * @return ezcMailMboxSet
     */
    public function fetchByMessageNr( $number )
    {
        $messages = $this->listMessages();
        if ( !isset( $messages[$number] ) )
        {
            throw new ezcMailNoSuchMessageException( $number );
        }
        return new ezcMailMboxSet( $this->fh, array( 0 => $messages[$number] ) );
    }

    /**
     * Returns an ezcMailMboxSet with $count messages starting from $offset.
     *
     * Fetches $count messages starting from the $offset and returns them as a
     * ezcMailMboxSet. If $count is not specified or if it is 0, it fetches
     * all messages starting from the $offset.
     * 
     * @throws ezcMailInvalidLimitException
     *         if $count is negative.
     * @throws ezcMailOffsetOutOfRangeException
     *         if $offset is outside of the existing range of messages.
     * @param int $offset
     * @param int $count
     * @return ezcMailMboxSet
     */
    public function fetchFromOffset( $offset, $count = 0 )
    {
        if ( $count < 0 )
        {
            throw new ezcMailInvalidLimitException( $offset, $count );
        }
        $messages = $this->listMessages();
        if ( !isset( $messages[$offset] ) )
        {
            throw new ezcMailOffsetOutOfRangeException( $offset, $count );
        }
        if ( $count == 0 )
        {
            $range = array_slice( $messages, $offset );
        }
        else
        {
            $range = array_slice( $messages, $offset, $count );
        }
        return new ezcMailMboxSet( $this->fh, $range );
    }
}
?>
