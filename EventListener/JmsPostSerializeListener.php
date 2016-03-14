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
use Doctrine\Common\Util\ClassUtils;
use JMS\Serializer\EventDispatcher\ObjectEvent;
use JMS\Serializer\GenericSerializationVisitor;

/**
 * JmsPostSerializeListener
 *
 * @author Denis Golubovskiy <bukashk0zzz@gmail.com>
 */
class JmsPostSerializeListener extends JmsSerializeListenerAbstract
{
    /**
     * On post serialize
     *
     * @param ObjectEvent $event Event
     */
    public function onPostSerialize(ObjectEvent $event)
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

                if ($liipAnnotation instanceof LiipImagineSerializableField && ($value = $property->getValue($object)) && ($virtualField = $liipAnnotation->getVirtualField())) {
                    if (array_key_exists('vichUploaderSerialize', $this->config) && $this->config['vichUploaderSerialize'] && $liipAnnotation->getVichUploaderField()) {
                        $valueArray = explode('/', $value);
                        $value = end($valueArray);
                        $property->setValue($object, $value);
                    }

                    /** @var GenericSerializationVisitor $visitor */
                    $visitor = $event->getVisitor();
                    $visitor->addData($virtualField, $this->serializeValue($liipAnnotation, $object, $value));
                }
            }
        }
    }
}
