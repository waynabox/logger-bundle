<?php

namespace Waynabox\LoggerBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class WaynaboxLoggerExtension extends Extension implements PrependExtensionInterface
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../Resources/config')
        );
        $loader->load('services.yml');
    }

    public function prepend(ContainerBuilder $container)
    {
        $configs = $container->getExtensionConfig('waynabox_logger');
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $config = [
            'handlers' => [
                "waynabox_logger_handler" => [
                    "type" => "stream",
                    "formatter" => "waynabox.logger_bundle.infrastructure.waynabox_logging_json_formatter",
                    "path" => $config['logger_file_path'],
                    "channels" =>  [ "waynabox_logger_channel" ]
                ],
                "waynabox_exception_logger_handler" => [
                    "type" => "stream",
                    "formatter" => "waynabox.logger_bundle.infrastructure.waynabox_logging_json_formatter",
                    "path" => $config['logger_exception_file_path'],
                    "channels" =>  [ "waynabox_exception_logger_channel" ]
                ]
            ]
        ];
        $container->prependExtensionConfig('monolog', $config);
    }
}