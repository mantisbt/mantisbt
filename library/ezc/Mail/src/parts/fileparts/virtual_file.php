<?php
/**
 * File containing the ezcMailVirtualFile class
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
 * Mail part for binary data in memory.
 *
 * @property string $contents
 *           The contents to be added as an attachment. The mimeType and
 *           contentType are set in the constructor or if not specified they
 *           are extracted with the fileinfo extension if it is available,
 *           otherwise they are set to application/octet-stream.
 *
 * @package Mail
 * @version //autogen//
 */
class ezcMailVirtualFile extends ezcMailFilePart
{
    /**
     * Constructs a new attachment with $fileName and $contents.
     *
     * If the $mimeType and $contentType are not specified they are extracted
     * with the fileinfo extension if it is available, otherwise they are set
     * to application/octet-stream.
     *
     * @param string $fileName
     * @param string $contents
     * @param string $contentType
     * @param string $mimeType
     */
    public function __construct( $fileName, $contents, $contentType = null, $mimeType = null )
    {
        parent::__construct( $fileName );
        $this->contents = $contents;

        if ( $contentType != null && $mimeType != null )
        {
            $this->contentType = $contentType;
            $this->mimeType = $mimeType;
        }
        elseif ( ezcBaseFeatures::hasExtensionSupport( 'fileinfo' ) )
        {
            // get mime and content type
            $fileInfo = new finfo( FILEINFO_MIME );
            $mimeParts = $fileInfo->buffer( $contents );
            if ( $mimeParts !== false && strpos( $mimeParts, '/' ) !== false )
            {
                list( $this->contentType, $this->mimeType ) = explode( '/', $mimeParts );
            }
            else
            {
                // default to mimetype application/octet-stream
                $this->contentType = self::CONTENT_TYPE_APPLICATION;
                $this->mimeType = "octet-stream";
            }
        }
        else
        {
            // default to mimetype application/octet-stream
            $this->contentType = self::CONTENT_TYPE_APPLICATION;
            $this->mimeType = "octet-stream";
        }
    }

    /**
     * Sets the property $name to $value.
     *
     * @throws ezcBasePropertyNotFoundException
     *         if the property does not exist.
     * @param string $name
     * @param mixed $value
     * @ignore
     */
    public function __set( $name, $value )
    {
        switch ( $name )
        {
            case 'contents':
                $this->properties[$name] = $value;
                break;
            default:
                return parent::__set( $name, $value );
                break;
        }
    }

    /**
     * Returns the value of property $value.
     *
     * @throws ezcBasePropertyNotFoundException
     *         if the property does not exist.
     * @param string $name
     * @return mixed
     * @ignore
     */
    public function __get( $name )
    {
        switch ( $name )
        {
            case 'contents':
                return $this->properties[$name];
                break;
            default:
                return parent::__get( $name );
                break;
        }
    }

    /**
     * Returns true if the property $name is set, otherwise false.
     *
     * @param string $name
     * @return bool
     * @ignore
     */
    public function __isset( $name )
    {
        switch ( $name )
        {
            case 'contents':
                return isset( $this->properties[$name] );

            default:
                return parent::__isset( $name );
        }
    }

    /**
     * Returns the contents of the file with the correct encoding.
     *
     * @return string
     */
    public function generateBody()
    {
        return chunk_split( base64_encode( $this->contents ), 76, ezcMailTools::lineBreak() );
    }
}
?>
