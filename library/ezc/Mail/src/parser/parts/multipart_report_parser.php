<?php
/**
 * File containing the ezcMailMultipartReportParser class
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
 * Parses multipart/report mail parts.
 *
 * @package Mail
 * @version //autogen//
 * @access private
 */
class ezcMailMultipartReportParser extends ezcMailMultipartParser
{
    /**
     * Holds the ezcMailMultipartReport part corresponding to the data parsed with this parser.
     *
     * @var ezcMailMultipartReport
     */
    private $report;

    /**
     * Holds the mail parts which will be part of the returned multipart report.
     *
     * @var array(ezcMailPart)
     */
    private $parts;

    /**
     * Constructs a new ezcMailMultipartReportParser.
     *
     * @param ezcMailHeadersHolder $headers
     */
    public function __construct( ezcMailHeadersHolder $headers )
    {
        parent::__construct( $headers );
        $this->report = new ezcMailMultipartReport();
        $this->parts = array();
        preg_match( '/\s*report-type="?([^;"]*);?/i',
                    $this->headers['Content-Type'],
                    $parameters );
        if ( count( $parameters ) > 0 )
        {
            $this->report->reportType = trim( $parameters[1], '"' );
        }
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
        $this->parts[] = $part;
    }

    /**
     * Returns the parts parsed for this multipart.
     *
     * @return ezcMailMultipartReport
     */
    public function finishMultipart()
    {
        if ( isset( $this->parts[0] ) )
        {
            $this->report->setReadablePart( $this->parts[0] );
        }
        if ( isset( $this->parts[1] ) )
        {
            $this->report->setMachinePart( $this->parts[1] );
        }
        if ( isset( $this->parts[2] ) )
        {
            $this->report->setOriginalPart( $this->parts[2] );
        }
        $size = 0;
        foreach ( $this->report->getParts() as $part )
        {
            $size += $part->size;
        }
        $this->report->size = $size;
        return $this->report;
    }
}
?>
