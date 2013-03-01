<?php

namespace Mylen\JQueryFileUploadBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('file_uploader')->children();

        $rootNode
            ->scalarNode('uploader_service')->defaultValue('Mylen\JQueryFileUploadBundle\Services\FileUploaderService')->end()
            ->scalarNode('file_base_path')->defaultValue('%kernel.root_dir%/../web/uploads')->end()
            ->scalarNode('web_base_path')->defaultValue('/uploads')->end()
            ->scalarNode('app_folder')->defaultValue('tmp')->end()
            ->arrayNode('allowed_extensions')
                ->defaultValue(array(
                    'gif', #image/gif
                    'png', #image/png
                    'jpg', #image/jpeg
                    'jpeg', #image/jpeg
                    'pdf', #application/pdf
                    'mp3', #audio/mpeg
                    'xls', #application/vnd.ms-excel
                    'ppt', #application/vnd.ms-powerpoint
                    'doc', #application/msword
                    'pptx', #application/vnd.openxmlformats-officedocument.presentationml.presentation
                    'sldx', #application/vnd.openxmlformats-officedocument.presentationml.slide
                    'ppsx', #application/vnd.openxmlformats-officedocument.presentationml.slideshow
                    'potx', #application/vnd.openxmlformats-officedocument.presentationml.template
                    'xlsx', #application/vnd.openxmlformats-officedocument.spreadsheetml.sheet
                    'xltx', #application/vnd.openxmlformats-officedocument.spreadsheetml.template
                    'docx', #application/vnd.openxmlformats-officedocument.wordprocessingml.document
                    'dotx', #application/vnd.openxmlformats-officedocument.wordprocessingml.template
                    'txt', #text/plain
                    'rtf', #text/rtf
                    'xml', #text/xml
                ))
                ->prototype('array')->end()
            ->end()
            ->arrayNode('originals')
                ->defaultValue(array('folder' => 'originals'))
                ->prototype('array')
                    ->children()
                        ->scalarNode('folder')->cannotBeEmpty()->end()
                    ->end()
                ->end()
            ->end()
            ->arrayNode('sizes')
                ->useAttributeAsKey('name')
                ->defaultValue(array(
                    'thumbnail' => array(
                        'folder' => 'thumbnails',
                        'max_width' => 80,
                        'max_height' => 80,
                    ),
                    'small' => array(
                        'folder' => 'small',
                        'max_width' => 320,
                        'max_height' => 480,
                    ),
                    'medium' => array(
                        'folder' => 'medium',
                        'max_width' => 640,
                        'max_height' => 960,
                    ),
                    'large' => array(
                        'folder' => 'large',
                        'max_width' => 1140,
                        'max_height' => 1140,
                    )
                ))
                ->prototype('array')
                ->children()
                    ->scalarNode('folder')->cannotBeEmpty()->end()
                    ->scalarNode('max_width')->cannotBeEmpty()->end()
                    ->scalarNode('max_height')->cannotBeEmpty()->end()
                ->end()
            ->end()
        ->end()
        ;

        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.

        return $treeBuilder;
    }
}
