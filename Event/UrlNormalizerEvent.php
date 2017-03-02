<?php

namespace Bukashk0zzz\LiipImagineSerializationBundle\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * UrlNormalizerEvent
 *
 * @author Denis Golubovskiy <bukashk0zzz@gmail.com>
 */
class UrlNormalizerEvent extends Event
{
    const ORIGIN = 'bukashk0zzz_liip_imagine.event_pre_origin_normalize';
    const FILTERED = 'bukashk0zzz_liip_imagine.event_pre_filtered_normalize';

    /**
     * @var string
     */
    protected $url;

    /**
     * @param string $url
     */
    public function __construct($url)
    {
        $this->url = $url;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     * @return $this
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }
}
