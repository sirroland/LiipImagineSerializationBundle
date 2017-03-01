<?php

namespace Bukashk0zzz\LiipImagineSerializationBundle\Normalizer;

/**
 * UrlNormalizerInterface
 *
 * @author Denis Golubovskiy <bukashk0zzz@gmail.com>
 */
interface UrlNormalizerInterface
{
    /**
     * @param string $url
     * @return string
     */
    public function normalize($url);
}
