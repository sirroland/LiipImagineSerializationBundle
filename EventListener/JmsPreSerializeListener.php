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
use Bukashk0zzz\LiipImagineSerializationBundle\Normalizer\UrlNormalizerInterface;
use Doctrine\Common\Util\ClassUtils;
use JMS\Serializer\EventDispatcher\ObjectEvent;

/**
 * JmsPreSerializeListener.
 *
 * @author Denis Golubovskiy <bukashk0zzz@gmail.com>
 */
class JmsPreSerializeListener extends JmsSerializeListenerAbstract
{
    /**
     * Cache attributes already processed (in case of collection serialization).
     *
     * @var array
     */
    private $cache = [];

    /**
     * On pre serialize.
     *
     * @param ObjectEvent $event Event
     *
     * @throws \InvalidArgumentException
     */
    public function onPreSerialize(ObjectEvent $event)
    {
        $object = $this->getObject($event);

        $classAnnotation = $this->annotationReader->getClassAnnotation(
            new \ReflectionClass(ClassUtils::getClass($object)),
            LiipImagineSerializableClass::class
        );

        if ($classAnnotation instanceof LiipImagineSerializableClass) {
            $reflectionClass = ClassUtils::newReflectionClass(get_class($object));

            foreach ($reflectionClass->getProperties() as $property) {
                $liipAnnotation = $this->annotationReader->getPropertyAnnotation($property, LiipImagineSerializableField::class);
                $property->setAccessible(true);
                if ($liipAnnotation instanceof LiipImagineSerializableField && ($value = $property->getValue($object)) && !is_array($value)) {
                    $vichField = $liipAnnotation->getVichUploaderField();

                    $uriComponents = explode($value, '/');
                    $cacheKey      = $vichField.array_pop($uriComponents);

                    if (in_array($cacheKey, $this->cache)) {
                        continue;
                    }

                    if (!$liipAnnotation->getVirtualField()) {
                        $this->cache[] = $cacheKey;

                        $property->setValue($object, $this->serializeValue($liipAnnotation, $object, $value));
                    } elseif ($vichField && array_key_exists('vichUploaderSerialize', $this->config) && $this->config['vichUploaderSerialize']) {
                        $this->cache[] = $cacheKey;

                        $originalImageUri = $this->vichStorage->resolveUri($object, $vichField);

                        if (array_key_exists('includeHost', $this->config) && $this->config['includeHost']) {
                            $originalImageUri = $this->getHostUrl().$originalImageUri;
                        }
                        $property->setValue($object, $this->normalizeUrl($originalImageUri, UrlNormalizerInterface::TYPE_ORIGIN));
                    }
                }
            }
        }
    }
}
