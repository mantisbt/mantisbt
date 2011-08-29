<?php
/**
 * File containing the ezcFeedEnclosureElement class.
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
 * Class defining an enclosure element.
 *
 * @property string $url
 *                  The URL of the enclosure.
 * @property string $length
 *                  The length of the enclosure (usually in bytes).
 * @property string $type
 *                  The type of the enclosure (eg. 'audio/x-mp3').
 *
 * @package Feed
 * @version //autogentag//
 */
class ezcFeedEnclosureElement extends ezcFeedElement
{
    /**
     * The URL value.
     *
     * @var string
     */
    public $url;

    /**
     * The length in bytes of the resource pointed by href.
     *
     * @var int
     */
    public $length;

    /**
     * The type of the resource pointed by href.
     *
     * @var string
     */
    public $type;
}
?>
