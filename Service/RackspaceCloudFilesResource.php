<?php
/**
 * This file is part of the Clov3rLabs/RackspaceCloudFilesBundle/Service package
 *
 * (c) 2013 Clov3r Labs
 *
 * 2013-03-14 11:01
 */

namespace Clov3rLabs\RackspaceCloudFilesBundle\Service;

use Clov3rLabs\RackspaceCloudFilesBundle\Util\FileTypeGuesser;

/**
 * Class RackspaceCloudFilesResource
 *
 * @package Clov3rLabs\RackspaceCloudFilesBundle\Service
 *
 * @author Christian Torres <ctorres@clov3rlabs.com>
 *
 * @version 0.0.1
 */
class RackspaceCloudFilesResource {

    private static $directory_type = "application/directory";

    private $container_name;
    private $uri;
    private $path;

    private $object = null;
    private $container = null;

    /**
     *
     * @param string $path
     */
    public function __construct($path = null)
    {
        if ( !empty($path) ) {
            $this->initResourceByPath($path);
        }
    }

    /**
     * Take the container and the resource name from the
     *
     * @param string $path
     *
     * @return RackspaceCloudFilesResource|false
     */
    public function initResourceByPath( $path )
    {
        $parsed = parse_url($path);

        if ( $parsed === false ) {
            return false;
        }
        $this->uri = $path;

        $this->path = $parsed['path'];

        if ( isset($parsed['host']) ) {
            $this->container_name = $parsed['host'];
        }

        return $this;
    }

    public function getContainerName()
    {
        return $this->container_name;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function getResourceName()
    {
        return $this->cleanName($this->path);
    }

    /**
     * Process the current $path and return a clean path
     *
     * @param string $pathName
     *
     * @return string
     */
    public function cleanName($pathName)
    {
        $pathName = ltrim($pathName, '/');
        return $pathName;
    }

    /**
     * Set the variable given to the $object property
     *
     * @param object $object
     */
    public function setObject($object)
    {
        $this->object = $object;
    }

    /**
     * Get the current $object
     *
     * @return $object
     */
    public function getObject()
    {
        return $this->object;
    }

    public function getContainer()
    {
        return $this->container;
    }

    public function setContainer($container)
    {
        $this->container = $container;
    }

    public function createAsDirectory()
    {
        $status = true;
        if ( !$this->exists() ) {
            try {
                $this->object->Create( array(
                        'name'          => $this->getResourceName(),
                        'content_type'  => self::$directory_type,
                    )
                );
            } catch ( \Exception $e ) {
                printf("%s", $e->getMessage());
                $status = false;
            }
        }

        return $status;
    }

    public function createAsFileFromBuffer($buffer)
    {
        $status = true;

        try {
            $this->object->SetData($buffer);
            $this->object->Create( array(
            'name'          => $this->getResourceName(),
            'content_type'  => FileTypeGuesser::guessByFileName($this->getResourceName()),
        )
            );
        } catch ( \Exception $e ) {
            printf("%s", $e->getMessage());
            $status = false;
        }

        return $status;
    }

    public function exists()
    {
        return (bool)$this->object->CDNUrl();
    }

    public function getContentLength()
    {
        $content_length = 0;
        if ( $this->exists() ) {
            $content_length = (int)$this->object->bytes;
        }

        return $content_length;
    }

    public function getLastModified()
    {
        $last_modified = 0;
        if ( $this->exists() ) {
            $last_modified = $this->object->last_modified;
        }

        return $last_modified;
    }

}