<?php

/*
 * This file is part of the Bukashk0zzzLiipImagineSerializationBundle
 *
 * (c) Denis Golubovskiy <bukashk0zzz@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bukashk0zzz\LiipImagineSerializationBundle\Tests\EventListener;

use Bukashk0zzz\LiipImagineSerializationBundle\Tests\Fixtures\UserPictures;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\CachedReader;
use Doctrine\Common\Cache\ArrayCache;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\EventDispatcher\ObjectEvent;
use JMS\Serializer\EventDispatcher\Events as JmsEvents;
use JMS\Serializer\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Routing\RequestContext;
use JMS\Serializer\EventDispatcher\EventDispatcher;
use Vich\UploaderBundle\Storage\StorageInterface;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Imagine\Cache\Signer;
use Bukashk0zzz\LiipImagineSerializationBundle\EventListener\JmsPostSerializeListener;
use Bukashk0zzz\LiipImagineSerializationBundle\Tests\Fixtures\User;

/**
 * JmsPostSerializeListenerTest
 *
 * @author Artem Genvald <genvaldartem@gmail.com>
 */
class JmsPostSerializeListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EventDispatcherInterface $dispatcher Dispatcher
     */
    private $dispatcher;

    /**
     * @var RequestContext $requestContext Request context
     */
    private $requestContext;

    /**
     * @var CachedReader $annotationReader Cached annotation reader
     */
    private $annotationReader;

    /**
     * @var CacheManager $cacheManager LiipImagineBundle Cache Manager
     */
    private $cacheManager;

    /**
     * @var StorageInterface $storage Vich storage
     */
    private $vichStorage;

    /**
     * @var string $filePath Image file path
     */
    private $filePath;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        AnnotationRegistry::registerLoader('class_exists');
        $this->filePath = (new User())->getCoverUrl();
        $this->annotationReader = new CachedReader(new AnnotationReader(), new ArrayCache());
        $this->generateVichStorage();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        $this->dispatcher       = null;
        $this->requestContext   = null;
        $this->annotationReader = null;
        $this->cacheManager = null;
        $this->vichStorage = null;
        $this->filePath = null;
    }

    /**
     * Test serialization
     */
    public function testSerialization()
    {
        $user = new User();
        $this->generateCacheManager();
        $this->generateRequestContext();
        $context = DeserializationContext::create();

        /** @noinspection PhpParamsInspection */
        $event = new ObjectEvent($context, $user, []);
        $this->dispatcher->dispatch(JmsEvents::POST_SERIALIZE, User::class, $context->getFormat(), $event);

        static::assertEquals('http://a/path/to/an/image1.png', $user->getCoverUrl());
        /** @noinspection PhpUndefinedFieldInspection */
        static::assertEquals('http://a/path/to/an/image2.png', $user->imageThumb);
        static::assertEquals('http://a/path/to/an/image3.png', $user->getPhotoName());

        // Serialize same object second time (check cache)
        $context = DeserializationContext::create();

        /** @noinspection PhpParamsInspection */
        $event = new ObjectEvent($context, $user, []);
        $this->dispatcher->dispatch(JmsEvents::POST_SERIALIZE, User::class, $context->getFormat(), $event);

        static::assertEquals('http://a/path/to/an/image1.png', $user->getCoverUrl());
        /** @noinspection PhpUndefinedFieldInspection */
        static::assertEquals('http://a/path/to/an/image2.png', $user->imageThumb);
        static::assertEquals('http://a/path/to/an/image3.png', $user->getPhotoName());
        static::assertEquals($this->filePath, $user->getImageUrl());
    }

    /**
     * Test serialization of proxy object
     */
    public function testProxySerialization()
    {
        $user = new UserPictures();
        $this->generateCacheManager(2);
        $this->generateRequestContext(false, true);
        $context = DeserializationContext::create();

        /** @noinspection PhpParamsInspection */
        $event = new ObjectEvent($context, $user, []);
        $this->dispatcher->dispatch(JmsEvents::POST_SERIALIZE, User::class, $context->getFormat(), $event);

        static::assertEquals('http://a/path/to/an/image1.png', $user->getCoverUrl());
        /** @noinspection PhpUndefinedFieldInspection */
        static::assertEquals('http://a/path/to/an/image2.png', $user->photoThumb);
        static::assertEquals('http://example.com:8000/uploads/photo.jpg', $user->getPhotoName());
        static::assertEmpty($user->getImageUrl());
    }

    /**
     * Test serialization with included http host and port in the URI
     */
    public function testHttpsSerialization()
    {
        $user = new UserPictures();
        $this->generateCacheManager(2);
        $this->generateRequestContext(true, true);
        $context = DeserializationContext::create();

        /** @noinspection PhpParamsInspection */
        $event = new ObjectEvent($context, $user, []);
        $this->dispatcher->dispatch(JmsEvents::POST_SERIALIZE, User::class, $context->getFormat(), $event);

        static::assertEquals('https://example.com:8800/uploads/photo.jpg', $user->getPhotoName());
    }

    /**
     * @param bool $https
     * @param bool $port
     */
    protected function generateRequestContext($https = false, $port = false)
    {
        // Mock Request contest
        $this->requestContext = $this->getMockBuilder('Symfony\Component\Routing\RequestContext')
            ->disableOriginalConstructor()
            ->getMock();

        $scheme = $https ? 'https':'http';

        $this->requestContext->expects(static::any())
            ->method('getScheme')
            ->willReturn($scheme);

        $this->requestContext->expects(static::any())
            ->method('getHost')
            ->willReturn('example.com');

        if ($port) {
            if ($https) {
                $this->requestContext->expects(static::any())
                    ->method('getHttpsPort')
                    ->willReturn(8800);
            } else {
                $this->requestContext->expects(static::any())
                    ->method('getHttpPort')
                    ->willReturn(8000);
            }
        }

        $this->addEventListener();
    }

    /**
     * Add post serialize event listener
     */
    protected function addEventListener()
    {
        $this->dispatcher = new EventDispatcher();
        $listener = new JmsPostSerializeListener($this->requestContext, $this->annotationReader, $this->cacheManager, $this->vichStorage, [
            'includeHost' => true,
            'vichUploaderSerialize' => true,
        ]);

        $this->dispatcher->addListener(JmsEvents::POST_SERIALIZE, [$listener, 'onPostSerialize']);
    }


    /**
     * Prepare mock of Liip cache manager
     * @param int $propertyCount How many properties will be serialized
     */
    protected function generateCacheManager($propertyCount = 3)
    {
        $resolver = static::getMock('Liip\ImagineBundle\Imagine\Cache\Resolver\ResolverInterface');
        $resolver
            ->expects(static::exactly($propertyCount))
            ->method('isStored')
            ->with($this->filePath, 'thumb_filter')
            ->will(static::returnValue(true))
        ;
        $resolver
            ->expects(static::exactly($propertyCount))
            ->method('resolve')
            ->with($this->filePath, 'thumb_filter')
            ->will(static::onConsecutiveCalls('http://a/path/to/an/image1.png', 'http://a/path/to/an/image2.png', 'http://a/path/to/an/image3.png'))
        ;

        $config = static::getMock('Liip\ImagineBundle\Imagine\Filter\FilterConfiguration');
        $config->expects(static::exactly($propertyCount*2))
            ->method('get')
            ->with('thumb_filter')
            ->will(static::returnValue(array(
                'size' => array(180, 180),
                'mode' => 'outbound',
                'cache' => null,
            )))
        ;

        $router = static::getMock('Symfony\Component\Routing\RouterInterface');
        $router->expects(static::never())
            ->method('generate')
        ;

        $eventDispatcher = static::getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');

        /** @noinspection PhpParamsInspection */
        $this->cacheManager = new CacheManager($config, $router, new Signer('secret'), $eventDispatcher);

        /** @noinspection PhpParamsInspection */
        $this->cacheManager->addResolver('default', $resolver);
    }

    protected function generateVichStorage()
    {
        $this->vichStorage = $this->getMockBuilder('Vich\UploaderBundle\Storage\FileSystemStorage')
            ->disableOriginalConstructor()
            ->getMock();
        $this->vichStorage->expects(static::any())
            ->method('resolvePath')
            ->will(static::returnValue($this->filePath));
        $this->vichStorage->expects(static::any())
            ->method('resolveUri')
            ->will(static::returnValue('/uploads/photo.jpg'));
    }
}
