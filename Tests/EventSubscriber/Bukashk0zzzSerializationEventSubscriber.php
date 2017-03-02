<?php

/*
 * This file is part of the Bukashk0zzzLiipImagineSerializationBundle
 *
 * (c) Denis Golubovskiy <bukashk0zzz@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bukashk0zzz\LiipImagineSerializationBundle\Tests\EventSubscriber;

use Bukashk0zzz\LiipImagineSerializationBundle\Event\UrlNormalizerEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Bukashk0zzzSerializationEventSubscriber
 *
 * @author Denis Golubovskiy <bukashk0zzz@gmail.com>
 */
class Bukashk0zzzSerializationEventSubscriber implements EventSubscriberInterface
{
    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            UrlNormalizerEvent::ORIGIN => [
                ['normalizeOrigin', 10],
            ],
            UrlNormalizerEvent::FILTERED => [
                ['normalizeFiltered', 10],
            ],
        ];
    }

    /**
     * @param UrlNormalizerEvent $event
     */
    public function normalizeOrigin(UrlNormalizerEvent $event)
    {
        $event->setUrl(str_replace('photo', 'newPhoto', $event->getUrl()));
    }

    /**
     * @param UrlNormalizerEvent $event
     */
    public function normalizeFiltered(UrlNormalizerEvent $event)
    {
        $event->setUrl(str_replace('example.com', 'img.example.com', $event->getUrl()));
    }
}
