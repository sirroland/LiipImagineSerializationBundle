<?php
/*
 * This file is part of the Bukashk0zzzLiipImagineSerializationBundle
 *
 * (c) Denis Golubovskiy <bukashk0zzz@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bukashk0zzz\LiipImagineSerializationBundle\EventListener;

use Bukashk0zzz\LiipImagineSerializationBundle\Annotation\LiipImagineSerializableClass;
use Bukashk0zzz\LiipImagineSerializationBundle\Annotation\LiipImagineSerializableField;
use Doctrine\Common\Annotations\CachedReader;
use Doctrine\Common\Util\ClassUtils;
use JMS\Serializer\EventDispatcher\ObjectEvent;
use Symfony\Component\Routing\RequestContext;
use Doctrine\Common\Persistence\Proxy;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Vich\UploaderBundle\Storage\StorageInterface;

/**
 * JmsPostSerializeListener
 *
 * @author Denis Golubovskiy <bukashk0zzz@gmail.com>
 */
class JmsPostSerializeListener
{
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
     * @var array $config Bundle config
     */
    private $config;

    /**
     * @var array $serializedObjects Serialized objects
     */
    private $serializedObjects = [];

    /** @noinspection MoreThanThreeArgumentsInspection
     *
     * JmsPostSerializeListener constructor.
     * @param RequestContext   $requestContext
     * @param CachedReader     $annotationReader
     * @param CacheManager     $cacheManager
     * @param StorageInterface $vichStorage
     * @param array            $config
     */
    public function __construct(
        RequestContext $requestContext,
        CachedReader $annotationReader,
        CacheManager $cacheManager,
        StorageInterface $vichStorage,
        array $config
    ) {
        $this->requestContext = $requestContext;
        $this->annotationReader = $annotationReader;
        $this->cacheManager = $cacheManager;
        $this->vichStorage = $vichStorage;
        $this->config = $config;
    }

    /**
     * On post serialize
     *
     * @param ObjectEvent $event Event
     */
    public function onPostSerialize(ObjectEvent $event)
    {
        $object = $event->getObject();

        if ($object instanceof Proxy
            && ! $object->__isInitialized()
        ) {
            $object->__load();
        }

        $objectUid = spl_object_hash($object);
        if (in_array($objectUid, $this->serializedObjects, true)) {
            return;
        }

        $classAnnotation = $this->annotationReader->getClassAnnotation(
            new \ReflectionClass(ClassUtils::getClass($object)),
            LiipImagineSerializableClass::class
        );

        if ($classAnnotation instanceof LiipImagineSerializableClass) {
            $reflectionClass = ClassUtils::newReflectionClass(get_class($object));

            foreach ($reflectionClass->getProperties() as $property) {
                $liipImagineAnnotation = $this->annotationReader->getPropertyAnnotation($property, LiipImagineSerializableField::class);

                if ($liipImagineAnnotation instanceof LiipImagineSerializableField && $value = $property->getValue($object)) {
                    if ($vichField = $liipImagineAnnotation->getVichUploaderField()) {
                        $value = $this->vichStorage->resolvePath($object, $vichField);
                    }

                    $uri = $this->cacheManager->getBrowserPath($value, $liipImagineAnnotation->getFilter());
                    if ($virtualField = $liipImagineAnnotation->getVirtualField()) {
                        $object->{$virtualField} = $uri;

                        if ($vichField && array_key_exists('vichUploaderSerialize', $this->config) && $this->config['vichUploaderSerialize']) {
                            $originalImageUri = $this->vichStorage->resolveUri($object, $vichField);

                            if (array_key_exists('includeHost', $this->config) && $this->config['includeHost']) {
                                $originalImageUri = $this->getHostUrl().$originalImageUri;
                            }
                            $property->setValue($object, $originalImageUri);
                        }
                    } else {
                        $property->setValue($object, $uri);
                    }
                }
            }

            $this->serializedObjects[$objectUid] = $objectUid;
        }

    }

    /**
     * Get host url (scheme, host, port)
     *
     * @return string Host url
     */
    private function getHostUrl()
    {
        $url = $this->requestContext->getScheme().'://'.$this->requestContext->getHost();

        if ($this->requestContext->getScheme() === 'http' && $this->requestContext->getHttpPort() && $this->requestContext->getHttpPort() !== 80) {
            $url .= ':'.$this->requestContext->getHttpPort();
        } elseif ($this->requestContext->getScheme() === 'https' && $this->requestContext->getHttpsPort() && $this->requestContext->getHttpsPort() !== 443) {
            $url .= ':'.$this->requestContext->getHttpsPort();
        }

        return $url;
    }
}
