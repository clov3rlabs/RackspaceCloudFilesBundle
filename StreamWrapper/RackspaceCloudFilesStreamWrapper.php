<?php
/**
 * This file is part of the Clov3rLabs/RackspaceCloudFilesBundle package
 *
 * (c) 2013 Clov3r Labs
 *
 * 2013-03-13 15:47
 */

namespace Clov3rLabs\RackspaceCloudFilesBundle\StreamWrapper;

use Clov3rLabs\RackspaceCloudFilesBundle\Service\RackspaceCloudFilesService;
use Clov3rLabs\RackspaceCloudFilesBundle\Service\RackspaceCloudFilesResource;
use Clov3rLabs\RackspaceCloudFilesBundle\StreamWrapper\StreamWrapperInterface;
use Clov3rLabs\RackspaceCloudFilesBundle\Exception\NotImplementedException;

/**
 * Clov3rLabs\RackspaceCloudFilesBundle\StreamWrapper\RackspaceCloudFilesStreamWrapper.php
 *
 * This class is a streamWrapper class for Rackspace Cloud Files Service
 *
 * @package Clov3rLabs\RackspaceCloudFilesBundle\StreamWrapper
 *
 * @author Christian Torres <ctorres@clov3rlabs.com>
 *
 * @version 0.0.4
 */
class RackspaceCloudFilesStreamWrapper implements StreamWrapperInterface {

    private static $protocol_name = 'rscf';
    private static $service;

    private $resource = null;
    private $data_buffer = null;
    private $data_position = 0;
    private $on_write_mode = false;

    /**
     * Registers the stream wrapper to handle the specified protocol rscf
     *
     * @param $service RackspaceCloudFilesService
     */
    public static function registerStreamWrapperClass(RackspaceCloudFilesService $service)
    {
        if ( !isset(self::$protocol_name) ) {
            throw new \RuntimeException(
                sprintf('Scheme name not found for %s', __CLASS__));
        }

        self::unregisterStreamWrapperClass();

        if ( !stream_wrapper_register(self::$protocol_name, __CLASS__) ) {
            throw new \RuntimeException(sprintf(
                'Could not register stream wrapper class %s for protocol name %s.', __CLASS__, self::$protocol_name
            ));
        }

        self::$service = $service;
    }

    /**
     * Unregisters the stream wrapper to handle the specified protocolName
     */
    public static function unregisterStreamWrapperClass()
    {
        if ( !in_array(self::$protocol_name, stream_get_wrappers(), true) ) {
            return;
        }

        if ( !isset(self::$protocol_name) ) {
            throw new \RuntimeException(
                sprintf('Scheme name not found for %s', __CLASS__));
        }

        @stream_wrapper_unregister(self::$protocol_name);
    }

    /**
     * @param RackspaceCloudFilesService $service
     */
    public static function setService(RackspaceCloudFilesService $service)
    {
        self::$service = $service;
    }

    /**
     * @return mixed
     */
    public static function getService()
    {
        return self::$service;
    }

    protected function setResource($resource)
    {
        $this->resource = $resource;
    }

    public function getResource()
    {
        return $this->resource;
    }

    protected function setBuffer($buffer)
    {
        $this->data_buffer = $buffer;
    }

    public function getBuffer()
    {
        return $this->data_buffer;
    }

    protected function setPosition($position)
    {
        $this->data_position = $position;
    }

    public function getPosition()
    {
        return $this->data_position;
    }

    protected function setOnWriteMode($on_write_mode)
    {
        $this->on_write_mode = $on_write_mode;
    }

    protected function isOnWriteMode()
    {
        return $this->on_write_mode;
    }

    /**
     * creates the resource, the container and the object by the path given
     *
     * @param string $path
     * @return boolean|RackspaceCloudFilesResource
     */
    public function initFromPath($path)
    {
        $resource = $this->getService()->createResourceFromPath($path);
        if ( !$resource ) {
            return false;
        }

        $this->setResource($resource);

        return $this;
    }

    /**
     * reset the variable
     */
    public function reset()
    {
        $this->setResource(null);
        $this->setPosition(0);
        $this->setBuffer(null);
    }

    /**
     * Append some data to the current property data
     *
     * @param $data
     *
     * @return int
     */
    public function appendDataBuffer($data)
    {
        $appended_data_length = strlen($data);
        $this->data_buffer = is_null($this->data_buffer) ? $data : $this->data_buffer . $data;
        return $appended_data_length;
    }

    // Stream Wrapper Interface Methods

    public function dir_closedir()
    {
        throw new NotImplementedException(__FUNCTION__);

        return true;
    }

    function dir_opendir($path, $options)
    {
        throw new NotImplementedException(__FUNCTION__);

        return true;
    }

    function dir_readdir()
    {
        throw new NotImplementedException(__FUNCTION__);

        return true;
    }

    function dir_rewinddir()
    {
        throw new NotImplementedException(__FUNCTION__);

        return true;
    }

    function mkdir($path, $mode, $options)
    {
        if ( !$this->initFromPath($path) ) {
            return false;
        }

        $status = $this->getResource()->createAsDirectory();
        $this->reset();

        return $status;
    }

    function rename($path_from, $path_to)
    {
        if ( !$this->initFromPath($path_from) ) {
            return false;
        }

        return $this->getResource()->move($path_to);
    }

    function rmdir($path, $options)
    {
        if ( !$this->initFromPath($path) ) {
            return false;
        }

        $recursively = false;
        if ( $options === STREAM_MKDIR_RECURSIVE ) {
            $recursively = true;
        }

        return $this->getResource()->remove(true, $recursively);
    }

    function stream_cast($cast_as)
    {
        throw new NotImplementedException(__FUNCTION__);
    }

    function stream_close()
    {
        if ( $this->isOnWriteMode() ) {
            $this->stream_flush();
        }

        $this->reset();
    }

    function stream_eof()
    {
        throw new NotImplementedException(__FUNCTION__);

        return true;
    }

    function stream_flush()
    {
        if ( !$this->getResource() ) {
            return false;
        }

        $status = $this->getResource()->createAsFileFromBuffer($this->getBuffer());
        $this->setOnWriteMode(false);

        return $status;
    }

    function stream_lock($operation)
    {
        throw new NotImplementedException(__FUNCTION__);

        return true;
    }

    function stream_metadata($path, $option, $var)
    {
        throw new NotImplementedException(__FUNCTION__);

        return true;
    }

    function stream_open($path, $mode, $options, &$opened_path)
    {
        if ( !$this->initFromPath($path) ) {
            return false;
        }

        $this->setOnWriteMode(true);
        return true;
    }

    function stream_read($count)
    {
        throw new NotImplementedException(__FUNCTION__);
    }

    function stream_seek($offset, $whence)
    {
        throw new NotImplementedException(__FUNCTION__);

        return true;
    }

    function stream_set_option($option, $arg1, $arg2)
    {
        throw new NotImplementedException(__FUNCTION__);

        return true;
    }

    function stream_stat()
    {
        throw new NotImplementedException(__FUNCTION__);
    }

    function stream_tell()
    {
        throw new NotImplementedException(__FUNCTION__);
    }

    function stream_truncate ($new_size)
    {
        throw new NotImplementedException(__FUNCTION__);

        return true;
    }

    function stream_write($data)
    {
        if ( $this->isOnWriteMode() ) {
            return $this->appendDataBuffer($data);;
        } else {
            throw new \Exception('dirty mode.');
        }
    }

    function unlink($path)
    {
        if ( !$this->initFromPath($path) ) {
            return false;
        }

        return $this->getResource()->remove();
    }

    function url_stat($path, $flags)
    {
        if ( !$this->initFromPath($path) ) {
            return false;
        }

        $stat = array(
            'dev'     => 0,
            'ino'     => 0,
            'mode'    => 0777,
            'nlink'   => 0,
            'uid'     => 0,
            'gid'     => 0,
            'rdev'    => 0,
            'size'    => $this->getResource()->getContentLength(), // $respHeaders['CONTENT-LENGTH'] or $object->content_length,
            'atime'   => time(),
            'mtime'   => $this->getResource()->getLastModified(), // strtotime($respHeaders['LAST-MODIFIED']) or $object->last_modified,
            'ctime'   => $this->getResource()->getLastModified(), // strtotime($respHeaders['LAST-MODIFIED']) or $object->last_modified,
            'blksize' => -1,
            'blocks'  => -1,
        );

        $this->reset();

        return $stat;
    }

}