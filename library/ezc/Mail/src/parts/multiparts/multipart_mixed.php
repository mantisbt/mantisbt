<?php
/**
 * File containing the ezcMailMultipartMixed class
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
 * The mixed multipart type is used to bundle an ordered list of mail
 * parts.
 *
 * Each part will be shown in the mail in the order provided.
 *
 * The following example shows how to build a mail with a text part
 * and an attachment using ezcMailMultipartMixed.
 * <code>
 *        $mixed = new ezcMailMultipartMixed( new ezcMailTextPart( "Picture of me flying!" ),
 *                                            new ezcMailFile( "fly.jpg" ) );
 *        $mail = new ezcMail();
 *        $mail->body = $mixed;
 * </code>
 *
 * @package Mail
 * @version //autogen//
 */
class ezcMailMultipartMixed extends ezcMailMultipart
{
    /**
     * Constructs a new ezcMailMultipartMixed
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
     * Returns "mixed".
     *
     * @return string
     */
    public function multipartType()
    {
        return "mixed";
    }
}
?>
