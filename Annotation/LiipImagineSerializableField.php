<?php

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
 * LiipImagineSerializableField
 *
 * @Annotation
 * @Target({"PROPERTY", "METHOD"})
 *
 * @author Denis Golubovskiy <bukashk0zzz@gmail.com>
 */
final class LiipImagineSerializableField implements Annotation
{
    /**
     * @var string $filter LiipImagine Filter
     */
    private $filter;

    /**
     * @var string $vichUploaderField Field
     */
    private $vichUploaderField;

    /**
     * @var string $virtualField Virtual Field
     */
    private $virtualField;


    /**
     * Constructor
     *
     * @param array $options Options
     *
     * @throws \Exception
     */
    public function __construct(array $options)
    {
        if (!array_key_exists('value', $options) && !array_key_exists('filter', $options)) {
            throw new \LogicException(sprintf('Either "value" or "filter" option must be set.'));
        }

        if (array_key_exists('value', $options)) {
            if (!is_string($options['value'])) {
                throw new \InvalidArgumentException(sprintf('Option "value" must be a string.'));
            }
            $this->setFilter($options['value']);
        } elseif (array_key_exists('filter', $options)) {
            if (!is_string($options['filter'])) {
                throw new \InvalidArgumentException(sprintf('Option "filter" must be a string.'));
            }
            $this->setFilter($options['filter']);
        }

        if (array_key_exists('vichUploaderField', $options)) {
            if (!is_string($options['vichUploaderField'])) {
                throw new \InvalidArgumentException(sprintf('Option "vichUploaderField" must be a string.'));
            }
            $this->setVichUploaderField($options['vichUploaderField']);
        }

        if (array_key_exists('virtualField', $options)) {
            if (!is_string($options['virtualField'])) {
                throw new \InvalidArgumentException(sprintf('Option "virtualField" must be a string.'));
            }
            $this->setVirtualField($options['virtualField']);
        }
    }

    /**
     * @return string
     */
    public function getFilter()
    {
        return $this->filter;
    }

    /**
     * @param string $filter
     * @return $this
     */
    public function setFilter($filter)
    {
        $this->filter = $filter;

        return $this;
    }

    /**
     * @return string
     */
    public function getVichUploaderField()
    {
        return $this->vichUploaderField;
    }

    /**
     * @param string $vichUploaderField
     * @return $this
     */
    public function setVichUploaderField($vichUploaderField)
    {
        $this->vichUploaderField = $vichUploaderField;

        return $this;
    }

    /**
     * @return string
     */
    public function getVirtualField()
    {
        return $this->virtualField;
    }

    /**
     * @param string $virtualField
     * @return $this
     */
    public function setVirtualField($virtualField)
    {
        $this->virtualField = $virtualField;

        return $this;
    }
}
