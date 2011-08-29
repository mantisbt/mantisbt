<?php
/**
 * File containing the ezcFeedLinkElement class.
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
 * @package Feed
 * @version //autogentag//
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @filesource
 */

/**
 * Class defining a link element.
 *
 * @property string $href
 *                  The URL value of the link element.
 * @property string $rel
 *                  The URL relation (eg. 'alternate', 'enclosure', etc).
 * @property string $type
 *                  The type of the resource pointed by href (eg. 'audio/x-mp3').
 * @property string $hreflang
 *                  The language of the resource pointed by href.
 * @property string $title
 *                  The title for the URL.
 * @property int $length
 *               The length in bytes for the resource pointed by href.
 *
 * @package Feed
 * @version //autogentag//
 */
class ezcFeedLinkElement extends ezcFeedElement
{
    /**
     * The URL value.
     *
     * @var string
     */
    public $href;

    /**
     * The rel for the link.
     *
     * @var string
     */
    public $rel;

    /**
     * The type of the resource pointed by href.
     *
     * @var string
     */
    public $type;

    /**
     * The language for the resource pointed by href.
     *
     * @var string
     */
    public $hreflang;

    /**
     * The title for the link.
     *
     * @var string
     */
    public $title;

    /**
     * The length in bytes of the resource pointed by href.
     *
     * @var int
     */
    public $length;

    /**
     * Returns the href attribute.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->href . '';
    }
}
?>
