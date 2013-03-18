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
use Clov3rLabs\RackspaceCloudFilesBundle\Exception\FileExistsException;

/**
 * Class RackspaceCloudFilesResource
 *
 * @package Clov3rLabs\RackspaceCloudFilesBundle\Service
 *
 * @author Christian Torres <ctorres@clov3rlabs.com>
 *
 * @version 0.0.6
 */
class RackspaceCloudFilesResource {

    private static $directory_type = "application/directory";

    private $container_name;
    private $uri;
    private $path;

    private $object = null;
    private $container = null;

    /**
     * Notification Email, We use to notify when file was purge
     *
     * @var null
     */
    private $notification_mail = null;

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

    /**
     * Return the container name
     *
     * @return mixed
     */
    public function getContainerName()
    {
        return $this->container_name;
    }

    /**
     * Return the path
     *
     * @return mixed
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Return a clean path
     *
     * @return string
     */
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
     * @param $object \OpenCloud\ObjectStore\DataObject
     *
     * @return void
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

    /**
     * Get the container object
     *
     * @return null|\OpenCloud\ObjectStore\Container
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Set the container object
     *
     * @param $container null|\OpenCloud\ObjectStore\Container
     */
    public function setContainer($container)
    {
        $this->container = $container;
    }

    /**
     * Create object as a Directory
     *
     * @return bool
     */
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

    /**
     * Create object from a string/buffer
     *
     * @param $buffer
     *
     * @return bool
     */
    public function createAsFileFromBuffer($buffer)
    {
        $status = true;

        if ( $this->exists() ) {
            $this->object->PurgeCDN($this->notification_mail);
            $this->object = null;
            $this->object = $this->container->DataObject();
        }

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

    /**
     * Create object from a local file
     *
     * @param $filename string
     *
     * @return void
     */
    public function createAsFileFromFile($filename)
    {

    }

    /**
     * Remove object
     *
     * @param bool $is_dir An optional parameter needed if you want to delete a directory
     * @param bool $recursively An optional parameter needed if you want to delete a directory and its content
     *
     * @return bool
     */
    public function remove($is_dir = false, $recursively = false)
    {
        // Directory can exists or not, they are optionals
        if ( $is_dir ) {
            $content = $this->container->ObjectList(array(
                    'prefix'    => $this->getResourceName() . '/',
                )
            );

            // If it has content we have to check if we can delete the files inside
            if ( $content->Size() > 1 && !$recursively ) {
                return false;
            }

            // First, Deleting the content if it has
            while( $o = $content->Next() ) {
                $o->Delete();
            }
        }

        // Validating
        // 1. File exists OR
        // 2. File exists, we're attempting to delete a directory and
        //    that the element is a directory type's object
        if ( $this->exists() ||
             ( $this->exists() && $is_dir && $this->object->content_type == self::$directory_type ) ) {
            return $this->object->Delete();
        }

        return false;
    }

    /**
     * Move/Rename a object
     *
     * @param $path_to
     * @param bool $overwrite
     *
     * @return bool
     * @throws \Clov3rLabs\RackspaceCloudFilesBundle\Exception\FileExistsException
     */
    public function move($path_to, $overwrite = false)
    {
        $target = null;

        // If we can not over write we have to validate the new path
        // to determinate if exists
        if ( !$overwrite ) {
            try {
                $target = $this->container->DataObject($path_to);
            } catch ( \Exception $e ) {
            }
            if ( $target ) {
                throw new FileExistsException(__FUNCTION__);
                return false;
            }
        }

        $target = $this->container->DataObject();
        $target->name = $path_to;
        $this->object->Copy($target);
        $this->remove();
        $this->setObject($target);
    }

    /**
     * Check if object exists in CDN
     *
     * @return bool
     */
    public function exists()
    {
        return (bool)$this->object->CDNUrl();
    }

    /**
     * Get content length or object size
     *
     * @return int
     */
    public function getContentLength()
    {
        $content_length = 0;
        if ( $this->exists() ) {
            $content_length = (int)$this->object->bytes;
        }

        return $content_length;
    }

    /**
     * Return last modified date
     *
     * @return int
     */
    public function getLastModified()
    {
        $last_modified = 0;
        if ( $this->exists() ) {
            $last_modified = $this->object->last_modified;
        }

        return $last_modified;
    }

    /**
     * Set the notification mail address
     *
     * @param null|string $notification_mail
     */
    public function setNotificationMail($notification_mail)
    {
        $this->notification_mail = $notification_mail;
    }

    /**
     * Get the notification mail address
     *
     * @return null|string
     */
    public function getNotificationMail()
    {
        return $this->notification_mail;
    }

}