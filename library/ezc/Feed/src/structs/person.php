<?php
/**
 * File containing the ezcFeedPersonElement class.
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
 * Class defining a person.
 *
 * @property string $name
 *                  The name of the person.
 * @property string $email
 *                  The email address of the person.
 * @property string $uri
 *                  The URI of the person.
 *
 * @package Feed
 * @version //autogentag//
 */
class ezcFeedPersonElement extends ezcFeedElement
{
    /**
     * The name of the person.
     *
     * @var string
     */
    public $name = null;

    /**
     * The URI of the person.
     *
     * @var string
     */
    public $uri;

    /**
     * The email address of the person.
     *
     * @var string
     */
    public $email;

    /**
     * Returns the name attribute.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->name . '';
    }
}
?>
