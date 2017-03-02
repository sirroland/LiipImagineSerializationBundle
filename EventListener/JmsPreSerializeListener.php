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
 * JmsPreSerializeListener
 *
 * @author Denis Golubovskiy <bukashk0zzz@gmail.com>
 */
class JmsPreSerializeListener extends JmsSerializeListenerAbstract
{
    /**
     * On pre serialize
     *
     * @param ObjectEvent $event Event
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

                    if (!$liipAnnotation->getVirtualField()) {
                        $property->setValue($object, $this->serializeValue($liipAnnotation, $object, $value));
                    } elseif ($vichField && array_key_exists('vichUploaderSerialize', $this->config) && $this->config['vichUploaderSerialize']) {
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
