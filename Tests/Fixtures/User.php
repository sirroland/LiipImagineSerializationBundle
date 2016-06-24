<?php

/*
 * This file is part of the Bukashk0zzzLiipImagineSerializationBundle
 *
 * (c) Denis Golubovskiy <bukashk0zzz@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bukashk0zzz\LiipImagineSerializationBundle\Tests\Fixtures;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Bukashk0zzz\LiipImagineSerializationBundle\Annotation as Bukashk0zzz;

/**
 * User Entity
 *
 * @ORM\Table(name="users")
 * @ORM\Entity()
 *
 * @JMS\ExclusionPolicy("all")
 *
 * @Vich\Uploadable
 * @Bukashk0zzz\LiipImagineSerializableClass
 */
class User
{
    /**
     * @var string $coverUrl Cover url
     *
     * @ORM\Column(type="string", length=255)
     *
     * @JMS\Expose
     * @JMS\SerializedName("cover")
     *
     * @Bukashk0zzz\LiipImagineSerializableField("thumb_filter")
     */
    public $coverUrl;

    /**
     * @var string $imageUrl Image url
     *
     * @ORM\Column(type="string", length=255)
     *
     * @JMS\Expose
     * @JMS\SerializedName("image")
     *
     * @Bukashk0zzz\LiipImagineSerializableField(filter="thumb_filter", virtualField="imageThumb")
     */
    public $imageUrl;

    /**
     * @var string $photoName Photo name
     *
     * @ORM\Column(type="string", length=255)
     *
     * @JMS\Expose
     * @JMS\SerializedName("photo")
     *
     * @Bukashk0zzz\LiipImagineSerializableField(filter="thumb_filter", vichUploaderField="photoFile")
     */
    public $photoName;

    /**
     * @var File $photoFile Photo file
     *
     * @JMS\Exclude
     *
     * @Vich\UploadableField(mapping="user_photo_mapping", fileNameProperty="photoName")
     */
    public $photoFile;

    /**
     * @ORM\OneToMany(targetEntity="Bukashk0zzz\LiipImagineSerializationBundle\Tests\Fixtures\UserPictures", mappedBy="user")
     */
    public $userPictures;

    /**
     * @ORM\OneToMany(targetEntity="Bukashk0zzz\LiipImagineSerializationBundle\Tests\Fixtures\UserPhotos", mappedBy="user")
     */
    public $userPhotos;

    /**
     * User constructor.
     */
    public function __construct()
    {
        $this->setCoverUrl(__DIR__.'/test.png');
        $this->setPhotoName(__DIR__.'/test.png');
        $this->setImageUrl(__DIR__.'/test.png');
    }

    /**
     * To string
     *
     * @return string
     */
    public function __toString()
    {
        return 'New User';
    }

    /**
     * Get photo name
     *
     * @return string Photo name
     */
    public function getPhotoName()
    {
        return $this->photoName;
    }

    /**
     * Set photo name
     *
     * @param string $photoName Photo name
     *
     * @return $this
     */
    public function setPhotoName($photoName)
    {
        $this->photoName = $photoName;

        return $this;
    }

    /**
     * Get photo file
     *
     * @return File Photo file
     */
    public function getPhotoFile()
    {
        return $this->photoFile;
    }

    /**
     * Set photo file
     *
     * @param File $photoFile Photo file
     *
     * @return $this
     */
    public function setPhotoFile(File $photoFile)
    {
        $this->photoFile = $photoFile;

        return $this;
    }

    /**
     * Add userPictures
     *
     * @param UserPictures $userPictures
     *
     * @return $this
     */
    public function addUserPictures(UserPictures $userPictures)
    {
        $this->userPictures[] = $userPictures;

        return $this;
    }

    /**
     * Get userPictures
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUserPictures()
    {
        return $this->userPictures;
    }

    /**
     * Add userPhotos
     *
     * @param UserPhotos $userPhotos
     *
     * @return $this
     */
    public function addUserPhotos(UserPhotos $userPhotos)
    {
        $this->userPhotos[] = $userPhotos;

        return $this;
    }

    /**
     * Get userPhotos
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUserPhotos()
    {
        return $this->userPhotos;
    }

    /**
     * @return string
     */
    public function getCoverUrl()
    {
        return $this->coverUrl;
    }

    /**
     * @param string $coverUrl
     * @return $this
     */
    public function setCoverUrl($coverUrl)
    {
        $this->coverUrl = $coverUrl;

        return $this;
    }

    /**
     * @return string
     */
    public function getImageUrl()
    {
        return $this->imageUrl;
    }

    /**
     * @param string $imageUrl
     * @return $this
     */
    public function setImageUrl($imageUrl)
    {
        $this->imageUrl = $imageUrl;

        return $this;
    }
}
