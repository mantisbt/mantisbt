<?php
/**
 * File containing the ezcFeedIdElement class.
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
 * Class defining an identifier.
 *
 * @property string $id
 *                  The value of the identifier.
 * @property bool $isPermaLink
 *                The permanent link state of the identifier.
 *
 * @package Feed
 * @version //autogentag//
 */
class ezcFeedIdElement extends ezcFeedElement
{
    /**
     * The identifier value.
     *
     * @var string
     */
    public $id;

    /**
     * The permanent link state of the identifier value.
     *
     * @var bool
     */
    public $isPermaLink;

    /**
     * Returns the id attribute.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->id . '';
    }
}
?>
