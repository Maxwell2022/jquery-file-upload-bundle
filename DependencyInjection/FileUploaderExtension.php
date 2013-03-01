<?php

namespace Mylen\JQueryFileUploadBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Definition;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class FileUploaderExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        var_dump($config);die;

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('parameters.yml');
        $loader->load('filters.yml');
        //$loader->load('assetic.yml');

        $this->configureFileUpload($config, $container);
    }

    /**
     * Setup the file uploader service
     *
     * @param array $config
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    private function configureFileUpload(array $config, ContainerBuilder $container)
    {
        $definition = new Definition('Mylen\JQueryFileUploadBundle\Services\FileUploaderService');
        $definition->setArguments(array(
            $config['file_uploader']['file_base_path'],
            $config['file_uploader']['web_base_path'],
            $config['file_uploader']['allowed_extensions'],
            $config['file_uploader']['sizes'],
            $config['file_uploader']['originals'],
        ));

        // Register the service in the container (default scope is container)
        $container->setDefinition('mylen.file_uploader', $definition);
    }
}
