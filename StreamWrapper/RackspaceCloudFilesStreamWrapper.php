<?php
/**
 * This file is part of the Clov3rLabs/RackspaceCloudFilesBundle package
 *
 * (c) 2013 Clov3r Labs
 *
 * 2013-03-13 15:47
 */

namespace Clov3rLabs\RackspaceCloudFilesBundle\StreamWrapper;

use Clov3rLabs\RackspaceCloudFilesBundle\StreamWrapper\StreamWrapperInterface;
use Clov3rLabs\RackspaceCloudFilesBundle\StreamWrapper\Exception\NotImplementedException;

/**
 * Clov3rLabs\RackspaceCloudFilesBundle\StreamWrapper\RackspaceCloudFilesStreamWrapper.php
 *
 * This class is a streamWrapper class for Rackspace Cloud Files Service
 *
 * @package Clov3rLabs/RackspaceCloudFilesBundle/StreamWrapper
 *
 * @author Christian Torres <ctorres@clov3rlabs.com>
 *
 * @version 0.0.2
 */
class RackspaceCloudFilesStreamWrapper implements StreamWrapperInterface {

    static $protocol_name = 'rscf';

    /**
     * Registers the stream wrapper to handle the specified protocol rscf
     */
    public static function registerStreamWrapperClass()
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
        throw new NotImplementedException(__FUNCTION__);

        return true;
    }

    function rename($path_from, $path_to)
    {
        throw new NotImplementedException(__FUNCTION__);

        return true;
    }

    function rmdir($path, $options)
    {
        throw new NotImplementedException(__FUNCTION__);

        return true;
    }

    function stream_cast($cast_as)
    {
        throw new NotImplementedException(__FUNCTION__);
    }

    function stream_close()
    {
        throw new NotImplementedException(__FUNCTION__);
    }

    function stream_eof()
    {
        throw new NotImplementedException(__FUNCTION__);

        return true;
    }

    function stream_flush()
    {
        throw new NotImplementedException(__FUNCTION__);

        return true;
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
        throw new NotImplementedException(__FUNCTION__);

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
        throw new NotImplementedException(__FUNCTION__);
    }

    function unlink($path)
    {
        throw new NotImplementedException(__FUNCTION__);

        return true;
    }

    function url_stat($path, $flags)
    {
        throw new NotImplementedException(__FUNCTION__);
    }

}