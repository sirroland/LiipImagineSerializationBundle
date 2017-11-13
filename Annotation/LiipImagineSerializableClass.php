<?php declare(strict_types = 1);
/*
 * This file is part of the Bukashk0zzzLiipImagineSerializationBundle
 *
 * (c) Denis Golubovskiy <bukashk0zzz@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bukashk0zzz\LiipImagineSerializationBundle\Annotation;

use Doctrine\ORM\Mapping\Annotation;

/**
 * LiipImagineSerializableClass
 *
 * @Annotation()
 * @Target({"CLASS", "ANNOTATION"})
 */
final class LiipImagineSerializableClass implements Annotation
{
}
