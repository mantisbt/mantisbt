<?php
declare(encoding="latin1");

/**
 * File containing the ezcMailVariableSet class
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
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @version //autogen//
 * @package Mail
 */

/**
 * ezcMailVariableSet is an internal class that can be used to parse mail directly from
 * a string variable in your script.
 *
 * The variable should contain the complete mail message in RFC822 format.
 *
 * Example:
 *
 * <code>
 * $mail = "To: user@example.com\r\nSubject: Test mail    .....";
 * $set = new ezcMailVariableSet( $mail ) );
 * $parser = new ezcMailParser();
 * $mail = $parser->parseMail( $set );
 * </code>
 *
 * @package Mail
 * @version //autogen//
 */
class ezcMailVariableSet implements ezcMailParserSet
{
    /**
     * Holds the mail split by linebreaks.
     *
     * @var array(string)
     */
    private $mail = array();

    /**
     * Constructs a new set that contains one mail from $mail.
     *
     * @param string $mail
     */
    public function __construct( $mail )
    {
        $this->mail = preg_split( "/\r\n|\n/", $mail );
        reset( $this->mail );
    }

    /**
     * Returns one line of data from the current mail in the set.
     *
     * Null is returned if there is no current mail in the set or
     * the end of the mail is reached.
     *
     * @return string
     */
    public function getNextLine()
    {
        $line = current( $this->mail );
        next( $this->mail );

        if ( $line === false )
        {
            return null;
        }

        return $line . "\n";
    }

    /**
     * Moves the set to the next mail and returns true upon success.
     *
     * False is returned if there are no more mail in the set (always).
     *
     * @return bool
     */
    public function nextMail()
    {
        return false;
    }

    /**
     * Returns whether the variable set contains mails.
     *
     * @return bool
     */
    public function hasData()
    {
        return ( count( $this->mail ) > 1 );
    }
}
?>
