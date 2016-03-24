<?php

namespace iVariable\ExtraBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Definition;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class iVariableExtraExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        if (!empty($config['repo'])) {
            //Main Repo
            $container->setDefinition(
                'iv.repo',
                new Definition(
                    $container->getParameter('ivariable.extra.repo.class'),
                    array(
                        'em' => new Reference('em'),
                        'map' => $config['repo'],
                        'container' => new Reference('service_container'),
                    )
                )
            );

            foreach ($config['repo'] as $key => $options) {
                $definition = new Definition(
                    $container->getParameter('ivariable.extra.repo.class'),
                    array($key)
                );
                $definition->setFactory(array(new Reference('iv.repo'), 'get'));
                $container->setDefinition('iv.repo.'.$key, $definition);
            }
        }
    }
}
