<?php
/**
 * File containing the ezcMailMultipartRelatedParser class
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
 * Parses multipart/related mail parts.
 *
 * @package Mail
 * @version //autogen//
 * @access private
 */
class ezcMailMultipartRelatedParser extends ezcMailMultipartParser
{
    /**
     * Holds the ezcMailMultipartRelated part corresponding to the data parsed with this parser.
     *
     * @var ezcMailMultipartRelated
     */
    private $part = null;

    /**
     * Constructs a new ezcMailMultipartRelatedParser.
     *
     * @param ezcMailHeadersHolder $headers
     */
    public function __construct( ezcMailHeadersHolder $headers )
    {
        parent::__construct( $headers );
        $this->part = new ezcMailMultipartRelated();
    }

    /**
     * Adds the part $part to the list of multipart messages.
     *
     * This method is called automatically by ezcMailMultipartParser
     * each time a part is parsed.
     *
     * @param ezcMailPart $part
     */
    public function partDone( ezcMailPart $part )
    {
        // TODO: support Content-Type: start= as specified by RFC 2387
        if ( !$this->part->getMainPart() )
        {
            $this->part->setMainPart( $part );
            return;
        }
        $this->part->addRelatedPart( $part );
    }

    /**
     * Returns the parts parsed for this multipart.
     *
     * @return ezcMailMultipartRelated
     */
    public function finishMultipart()
    {
        $size = 0;
        if ( $this->part->getMainPart() )
        {
            $size = $this->part->getMainPart()->size;
        }
        foreach ( $this->part->getRelatedParts() as $part )
        {
            $size += $part->size;
        }
        $this->part->size = $size;
        return $this->part;
    }
}

?>
