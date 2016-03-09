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
use JMS\Serializer\GenericSerializationVisitor;
use Symfony\Component\Routing\RequestContext;
use Doctrine\Common\Persistence\Proxy;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Vich\UploaderBundle\Storage\StorageInterface;

/**
 * JmsSerializeListener
 *
 * @author Denis Golubovskiy <bukashk0zzz@gmail.com>
 */
class JmsSerializeListener
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
     * @var array $serializedObjects Pre Serialized objects
     */
    private $preSerializedObjects = [];

    /**
     * @var array $postSerializedObjects Post Serialized objects
     */
    private $postSerializedObjects = [];

    /** @noinspection MoreThanThreeArgumentsInspection
     *
     * JmsSerializeListener constructor.
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
     * On pre serialize
     *
     * @param ObjectEvent $event Event
     */
    public function onPreSerialize(ObjectEvent $event)
    {
        $object = $this->getObject($event);
        $objectUid = spl_object_hash($object);

        if (in_array($objectUid, $this->preSerializedObjects, true)) {
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
                $property->setAccessible(true);

                if ($liipImagineAnnotation instanceof LiipImagineSerializableField && $value = $property->getValue($object)) {
                    $vichField = $liipImagineAnnotation->getVichUploaderField();

                    if (!$liipImagineAnnotation->getVirtualField()) {
                        $property->setValue($object, $this->serializeValue($liipImagineAnnotation, $object, $value));
                    } elseif ($vichField && array_key_exists('vichUploaderSerialize', $this->config) && $this->config['vichUploaderSerialize']) {
                        $originalImageUri = $this->vichStorage->resolveUri($object, $vichField);

                        if (array_key_exists('includeHost', $this->config) && $this->config['includeHost']) {
                            $originalImageUri = $this->getHostUrl().$originalImageUri;
                        }
                        $property->setValue($object, $originalImageUri);
                    }
                }
            }

            $this->preSerializedObjects[$objectUid] = $objectUid;
        }

    }

    /**
     * On post serialize
     *
     * @param ObjectEvent $event Event
     */
    public function onPostSerialize(ObjectEvent $event)
    {
        $object = $this->getObject($event);
        $objectUid = spl_object_hash($object);

        if (in_array($objectUid, $this->postSerializedObjects, true)) {
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
                $property->setAccessible(true);

                if ($liipImagineAnnotation instanceof LiipImagineSerializableField && ($value = $property->getValue($object)) && ($virtualField = $liipImagineAnnotation->getVirtualField())) {
                    if (array_key_exists('vichUploaderSerialize', $this->config) && $this->config['vichUploaderSerialize'] && $liipImagineAnnotation->getVichUploaderField()) {
                        $valueArray = explode('/', $value);
                        $value = end($valueArray);
                        $property->setValue($object, $value);
                    }

                    /** @var GenericSerializationVisitor $visitor */
                    $visitor = $event->getVisitor();
                    $visitor->addData($virtualField, $this->serializeValue($liipImagineAnnotation, $object, $value));
                }
            }

            $this->postSerializedObjects[$objectUid] = $objectUid;
        }

    }

    /**
     * @param ObjectEvent $event Event
     * @return mixed
     */
    protected function getObject(ObjectEvent $event)
    {
        $object = $event->getObject();

        if ($object instanceof Proxy
            && ! $object->__isInitialized()
        ) {
            $object->__load();
        }

        return $object;
    }

    /** @noinspection GenericObjectTypeUsageInspection
     * @param LiipImagineSerializableField $liipImagineAnnotation
     * @param object $object Serialized object
     * @param string $value Value of field
     * @return array|string
     */
    private function serializeValue(LiipImagineSerializableField $liipImagineAnnotation, $object, $value)
    {
        if ($vichField = $liipImagineAnnotation->getVichUploaderField()) {
            $value = $this->vichStorage->resolveUri($object, $vichField);
        }

        $result = [];
        if (array_key_exists('includeOriginal', $this->config) && $this->config['includeOriginal']) {
            $result['original'] = $value;
        }

        $filters = $liipImagineAnnotation->getFilter();
        if (is_array($filters)) {
            foreach ($filters as $filter) {
                $result[$filter] = $this->cacheManager->getBrowserPath($value, $filter);
            }

            return $result;
        } else {
            $filtered = $this->cacheManager->getBrowserPath($value, $filters);

            if (count($result) !== 0) {
                $result[$filters] = $filtered;

                return $result;
            }

            return $filtered;
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
