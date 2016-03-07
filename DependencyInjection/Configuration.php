<?php

/*
 * This file is part of the Bukashk0zzzLiipImagineSerializationBundle
 *
 * (c) Denis Golubovskiy <bukashk0zzz@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bukashk0zzz\LiipImagineSerializationBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the configuration class
 *
 * @author Denis Golubovskiy <bukashk0zzz@gmail.com>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * Generates the configuration tree.
     *
     * @return \Symfony\Component\Config\Definition\NodeInterface
     * @throws \Exception
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('bukashk0zzz_liip_imagine_serialization');

        $rootNode
            ->children()
            ->scalarNode('includeHost')->defaultValue(true)->end()
            ->scalarNode('vichUploaderSerialize')->defaultValue(true)->end()
        ;

        return $treeBuilder;
    }
}
