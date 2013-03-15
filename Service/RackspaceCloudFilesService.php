<?php
/**
 * This file is part of the Clov3rLabs/RackspaceCloudFilesBundle/Service package
 *
 * (c) 2013 Clov3r Labs
 *
 * 2013-03-13 21:09
 */

namespace Clov3rLabs\RackspaceCloudFilesBundle\Service;

use OpenCloud\Rackspace;

/**
 * Class RackspaceCloudFilesService
 *
 * @package Clov3rLabs\RackspaceCloudFilesBundle\Service
 *
 * @author Christian Torres <ctorres@clov3rlabs.com>
 *
 * @version 0.0.6
 */
class RackspaceCloudFilesService {

    private static $authentication;

    private static $connection;

    private static $params;

    private static $cloud_files_service = 'cloudFiles';

    /**
     * Rackspace Cloud Files Service
     */
    public function __construct($params)
    {
        self::$params = $params;
    }

    /**
     * To make lazy instantiation and return the credentials
     *
     * @return mixed
     */
    public static function getAuthentication()
    {
        if ( !self::$authentication ) {
            if ( !defined('RACKSPACE_' . self::$params['endpoint']) )
                return;

            $auth_url = constant( 'RACKSPACE_' . self::$params['endpoint'] );

            self::$authentication = new Rackspace($auth_url,
                array(
                    'username' => self::$params['username'],
                    'apiKey' => self::$params['apikey'],
                )
            );
        }

        return self::$authentication;
    }

    /**
     * To make lazy instantiation and return the connection
     * to the ObjectStore service
     *
     * @return mixed
     */
    public static function getConnection()
    {
        if ( !self::$connection ) {
            // now,
            self::$connection = self::getAuthentication()->ObjectStore(self::$cloud_files_service, self::$params['region']);
        }

        return self::$connection;
    }

    /**
     * Create a resource
     *
     * @param string $path
     *
     * @return resource|false
     */
    public function createResourceFromPath($path)
    {
        $resource = new RackspaceCloudFilesResource($path);

        if ( !$resource ) {
            return false;
        }

        $container = $this->apiGetContainer($resource->getContainerName());

        if ( !$container ) {
            return false;
        }

        $resource->setContainer($container);

        // create_object but no problem if already exists
        $obj = $this->apiGetObjectByContainer($container, $resource->getResourceName());
        if ( !$obj ) {
            return false;
        }

        $resource->setObject($obj);

        return $resource;
    }

    public function apiGetContainer($container_name)
    {
        if ( !$this->getConnection() ) {
            return false;
        }

        $container = $this->getConnection()->Container($container_name);
        if (!$container) {
            return false;
        }

        return $container;
    }

    /**
     * @param $container
     * @param string$object_name
     * @return \stdClass
     */
    public function apiGetObjectByContainer($container, $object_name)
    {
        if (!$container) {
            return false;
        }

        $object = null;

        try {
            $object = $container->DataObject($object_name);
        } catch ( \Exception $e ) {
            $object = $container->DataObject();
        }

        return $object;
    }

}