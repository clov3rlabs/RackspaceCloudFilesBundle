<?php
/**
 * This file is part of the Clov3rLabs/RackspaceCloudFilesBundle package
 *
 * (c) 2013 Clov3r Labs
 *
 * 2013-03-13 16:39
 */

namespace Clov3rLabs\RackspaceCloudFilesBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Clov3rLabs\RackspaceCloudFilesBundle\StreamWrapper\RackspaceCloudFilesStreamWrapper;

/**
 * Clov3rLabs\RackspaceCloudFilesBundle\Clov3rLabsRackspaceCloudFilesBundle.php
 *
 * RackspaceCloudFilesBundle Main Bundle Class
 *
 * @package Clov3rLabs/RackspaceCloudFilesBundle
 *
 * @author Christian Torres <ctorres@clov3rlabs.com>
 *
 * @version 0.0.2
 */
class Clov3rLabsRackspaceCloudFilesBundle extends Bundle
{

    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        parent::boot();

        RackspaceCloudFilesStreamWrapper::registerStreamWrapperClass();
    }

    /**
     * {@inheritdoc}
     */
    public function shutdown()
    {
        RackspaceCloudFilesStreamWrapper::unRegisterStreamWrapperClass();

        parent::shutdown();
    }

}
