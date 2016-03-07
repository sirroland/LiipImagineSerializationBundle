<?php

/*
 * This file is part of the Bukashk0zzzLiipImagineSerializationBundle
 *
 * (c) Denis Golubovskiy <bukashk0zzz@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bukashk0zzz\LiipImagineSerializationBundle\Tests\Annotation;

use Bukashk0zzz\LiipImagineSerializationBundle\Annotation\LiipImagineSerializableField;

/**
 * LiipImagineSerializableFieldTest
 *
 * @author Denis Golubovskiy <bukashk0zzz@gmail.com>
 */
class LiipImagineSerializableFieldTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test annotation with `value` option
     */
    public function testValueOption()
    {
        $annotation = new LiipImagineSerializableField(['value' => 'thumb_filter']);

        static::assertEquals('thumb_filter', $annotation->getFilter());
        static::assertEmpty($annotation->getVichUploaderField());
        static::assertEmpty($annotation->getVirtualField());
    }

    /**
     * Test annotation with all options
     */
    public function testAllOptions()
    {
        $annotation = new LiipImagineSerializableField([
            'filter' => 'thumb_filter',
            'vichUploaderField' => 'photoFile',
            'virtualField' => 'photo_thumb',
        ]);

        static::assertEquals('thumb_filter', $annotation->getFilter());
        static::assertEquals('photoFile', $annotation->getVichUploaderField());
        static::assertEquals('photo_thumb', $annotation->getVirtualField());
    }

    /**
     * Test annotation without any option
     *
     * @expectedException \LogicException
     */
    public function testAnnotationWithoutOptions()
    {
        new LiipImagineSerializableField([]);
    }

    /**
     * Test annotation with wrong type for `filter` option
     *
     * @expectedException \InvalidArgumentException
     */
    public function testWrongTypeForFilterOption()
    {
        new LiipImagineSerializableField(['filter' => 123]);
    }

    /**
     * Test annotation with wrong type for `value` option
     *
     * @expectedException \InvalidArgumentException
     */
    public function testWrongTypeForValueOption()
    {
        new LiipImagineSerializableField(['value' => 123]);
    }

    /**
     * Test annotation with wrong type for `vichUploaderField` option
     *
     * @expectedException \InvalidArgumentException
     */
    public function testWrongTypeForVichUploaderFieldOption()
    {
        new LiipImagineSerializableField(['filter' => 'thumb_filter', 'vichUploaderField' => 123]);
    }

    /**
     * Test annotation with wrong type for `virtualField` option
     *
     * @expectedException \InvalidArgumentException
     */
    public function testWrongTypeForVirtualFieldOption()
    {
        new LiipImagineSerializableField(['filter' => 'thumb_filter', 'virtualField' => 123]);
    }
}
