<?php declare(strict_types = 1);
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
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Bukashk0zzzLiipImagineSerializationTest
 */
class Bukashk0zzzLiipImagineSerializationExtensionTest extends TestCase
{
    /**
     * @var Bukashk0zzzLiipImagineSerializationExtension Bukashk0zzzLiipImagineSerializationExtension
     */
    private $extension;

    /**
     * @var ContainerBuilder Container builder
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
    public function testLoadExtension(): void
    {
        // Add some dummy required services
        $this->container->set('router.request_context', new \stdClass());
        $this->container->set('annotations.cached_reader', new \stdClass());
        $this->container->set('liip_imagine.cache.manager', new \stdClass());
        $this->container->set('vich_uploader.storage', new \stdClass());
        $this->container->set('event_dispatcher', new \stdClass());

        $this->container->prependExtensionConfig($this->extension->getAlias(), ['includeHost' => true]);
        $this->container->loadFromExtension($this->extension->getAlias());
        $this->container->compile();

        // Check that services have been loaded
        static::assertTrue($this->container->has('bukashk0zzz_liip_imagine_pre_serialization.listener'));
        static::assertTrue($this->container->has('bukashk0zzz_liip_imagine_post_serialization.listener'));
    }
}
