<?php

/*
 * This file is part of the Bukashk0zzzLiipImagineSerializationBundle
 *
 * (c) Denis Golubovskiy <bukashk0zzz@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bukashk0zzz\LiipImagineSerializationBundle\Tests\Normalizer;

use Bukashk0zzz\LiipImagineSerializationBundle\Normalizer\UrlNormalizerInterface;

/**
 * Filtered url normalizer
 */
class FilteredUrlNormalizer implements UrlNormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function normalize($url)
    {
        return str_replace('example.com', 'img.example.com', $url);
    }
}
