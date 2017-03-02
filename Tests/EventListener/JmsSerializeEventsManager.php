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

use Bukashk0zzz\LiipImagineSerializationBundle\EventListener\JmsPostSerializeListener;
use Bukashk0zzz\LiipImagineSerializationBundle\EventListener\JmsPreSerializeListener;
use Bukashk0zzz\LiipImagineSerializationBundle\Tests\EventSubscriber\Bukashk0zzzSerializationEventSubscriber;
use Bukashk0zzz\LiipImagineSerializationBundle\Tests\Fixtures\UserPictures;
use Bukashk0zzz\LiipImagineSerializationBundle\Tests\Fixtures\User;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\EventDispatcher\ObjectEvent;
use JMS\Serializer\EventDispatcher\Events as JmsEvents;
use JMS\Serializer\EventDispatcher\EventDispatcher;
use Doctrine\Common\Annotations\CachedReader;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\Routing\RequestContext;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Vich\UploaderBundle\Storage\StorageInterface;

/**
 * JmsSerializeEventsManager
 *
 * @author Denis Golubovskiy <bukashk0zzz@gmail.com>
 */
class JmsSerializeEventsManager
{
    /**
     * @var EventDispatcher $dispatcher Dispatcher
     */
    private $dispatcher;

    /**
     * @var CachedReader $annotationReader Cached annotation reader
     */
    private $annotationReader;

    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcher
     */
    private $symfonyEventDispatcher;

    /**
     * JmsSerializeEventsManager constructor.
     */
    public function __construct()
    {
        $this->annotationReader = new CachedReader(new AnnotationReader(), new ArrayCache());
        $this->symfonyEventDispatcher = new \Symfony\Component\EventDispatcher\EventDispatcher();
    }

    /**
     * Add post & pre serialize event listeners
     *
     * @param RequestContext   $requestContext
     * @param CacheManager     $cacheManager
     * @param StorageInterface $vichStorage
     */
    public function addEventListeners(RequestContext $requestContext, CacheManager $cacheManager, StorageInterface $vichStorage)
    {
        $this->dispatcher = new EventDispatcher();
        $this->addEvents($this->dispatcher, $requestContext, $cacheManager, $vichStorage, []);
    }

    /**
     * @param DeserializationContext $context
     * @param User|UserPictures      $user
     * @return ObjectEvent
     */
    public function dispatchEvents($context, $user)
    {
        /** @noinspection PhpParamsInspection */
        $event = new ObjectEvent($context, $user, []);
        $this->dispatcher->dispatch(JmsEvents::PRE_SERIALIZE, User::class, $context->getFormat(), $event);
        $this->dispatcher->dispatch(JmsEvents::POST_SERIALIZE, User::class, $context->getFormat(), $event);

        return $event;
    }

    /** @noinspection MoreThanThreeArgumentsInspection
     *
     * Add post & pre serialize event to dispatcher
     *
     * @param EventDispatcher  $dispatcher
     * @param RequestContext   $requestContext
     * @param CacheManager     $cacheManager
     * @param StorageInterface $vichStorage
     * @param array            $config         JMS serializer listner config
     */
    public function addEvents(EventDispatcher $dispatcher, RequestContext $requestContext, CacheManager $cacheManager, StorageInterface $vichStorage, array $config = [])
    {
        if (count($config) === 0) {
            $config = [
                'includeHost' => true,
                'vichUploaderSerialize' => true,
                'includeOriginal' => false,
            ];
        }

        $preListener = new JmsPreSerializeListener($requestContext, $this->annotationReader, $cacheManager, $vichStorage, $this->symfonyEventDispatcher, $config);
        $postListener = new JmsPostSerializeListener($requestContext, $this->annotationReader, $cacheManager, $vichStorage, $this->symfonyEventDispatcher, $config);

        $dispatcher->addListener(JmsEvents::PRE_SERIALIZE, [$preListener, 'onPreSerialize']);
        $dispatcher->addListener(JmsEvents::POST_SERIALIZE, [$postListener, 'onPostSerialize']);
    }

    /**
     * Add normalizer subscriber
     */
    public function addNormalizerSubscriber()
    {
        $this->symfonyEventDispatcher->addSubscriber(new Bukashk0zzzSerializationEventSubscriber());
    }
}
