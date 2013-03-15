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
     *
     *
     * @param bool $is_dir An optional parameter needed if you want to delete a directory
     * @param bool $recursively An optional parameter needed if you want to delete a directory and its content
     * @return bool
     */
    public function remove($is_dir = false, $recursively = false)
    {
        $status = false;
        // Directory can exists or not, they are optionals
        if ( $is_dir ) {
            $content = $this->container->ObjectList(array(
                    'prefix'    => $this->getResourceName() . '/',
                )
            );

            // If it has content we have to check if we can delete the files inside
            if ( $content->Size() > 1 && !$recursively ) {
                return $status;
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

    /**
     * @param null $notification_mail
     */
    public function setNotificationMail($notification_mail)
    {
        $this->notification_mail = $notification_mail;
    }

    /**
     * @return null
     */
    public function getNotificationMail()
    {
        return $this->notification_mail;
    }

}