<?php

/*
 * This file is part of the Bukashk0zzzLiipImagineSerializationBundle
 *
 * (c) Denis Golubovskiy <bukashk0zzz@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bukashk0zzz\LiipImagineSerializationBundle\Tests\DependencyInjection;

use Bukashk0zzz\LiipImagineSerializationBundle\DependencyInjection\Bukashk0zzzLiipImagineSerializationExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Bukashk0zzzLiipImagineSerializationTest
 *
 * @author Denis Golubovskiy <bukashk0zzz@gmail.com>
 */
class Bukashk0zzzLiipImagineSerializationExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Bukashk0zzzLiipImagineSerializationExtension $extension Bukashk0zzzLiipImagineSerializationExtension
     */
    private $extension;

    /**
     * @var ContainerBuilder $container Container builder
     */
    private $container;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->extension = new Bukashk0zzzLiipImagineSerializationExtension();
        $this->container = new ContainerBuilder();
        $this->container->registerExtension($this->extension);
    }

    /**
     * Test load extension
     */
    public function testLoadExtension()
    {
        // Add some dummy required services
        $this->container->set('router.request_context', new \StdClass());
        $this->container->set('annotations.cached_reader', new \StdClass());
        $this->container->set('liip_imagine.cache.manager', new \StdClass());
        $this->container->set('vich_uploader.storage', new \StdClass());

        $this->container->prependExtensionConfig($this->extension->getAlias(), ['includeHost' => true]);
        $this->container->loadFromExtension($this->extension->getAlias());
        $this->container->compile();

        // Check that services have been loaded
        static::assertTrue($this->container->has('bukashk0zzz_liip_imagine_pre_serialization.listener'));
        static::assertTrue($this->container->has('bukashk0zzz_liip_imagine_post_serialization.listener'));
    }
}
