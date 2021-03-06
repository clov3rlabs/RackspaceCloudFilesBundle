<?php
/**
 * This file is part of the Clov3rLabs/RackspaceCloudFilesBundle package
 *
 * (c) 2013 Clov3r Labs
 *
 * 2013-03-13 16:39
 */

namespace Clov3rLabs\RackspaceCloudFilesBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 *
 * @package Clov3rLabs\RackspaceCloudFilesBundle
 *
 * @author Christian Torres <ctorres@clov3rlabs.com>
 *
 * @version 0.0.4
 */
class Clov3rLabsRackspaceCloudFilesExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        foreach ($config['auth'] as $key => $value) {
            $container->setParameter($this->getAlias() . '.auth.' . $key, $value);
        }
    }
}
