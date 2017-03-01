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
use Doctrine\Common\Persistence\Proxy;
use JMS\Serializer\Annotation as JMS;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\HttpFoundation\File\File;
use Bukashk0zzz\LiipImagineSerializationBundle\Annotation as Bukashk0zzz;

/**
 * UserPictures Entity
 *
 * @ORM\Table(name="userPictures")
 * @ORM\Entity()
 *
 * @JMS\ExclusionPolicy("all")
 *
 * @Vich\Uploadable
 * @Bukashk0zzz\LiipImagineSerializableClass
 */
class UserPictures implements Proxy
{
    /**
     * @ORM\ManyToOne(targetEntity="Bukashk0zzz\LiipImagineSerializationBundle\Tests\Fixtures\User", inversedBy="pictures")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;

    /**
     * @ORM\Column(type="integer")
     */
    protected $userId;

    /**
     * @var string $coverUrl Cover url
     *
     * @ORM\Column(type="string", length=255)
     *
     * @JMS\Expose
     * @JMS\SerializedName("cover")
     *
     * @Bukashk0zzz\LiipImagineSerializableField(filter={"big", "small"})
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
     * @Bukashk0zzz\LiipImagineSerializableField(filter="thumb_filter", virtualField="image_thumb")
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
     * @Bukashk0zzz\LiipImagineSerializableField(filter="thumb_filter", vichUploaderField="photoFile", virtualField="photoThumb")
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
     * @var bool
     */
    private $status = false;

    // @codingStandardsIgnoreStart
    /**
     * @inheritdoc
     */
    public function __load()
    {
        $this->setCoverUrl(__DIR__.'/test.png');
        $this->setPhotoName('/uploads/photo.jpg');
        $this->status = true;
    }

    /**
     * @inheritdoc
     * @return bool
     */
    public function __isInitialized()
    {
        return $this->status;
    }
    // @codingStandardsIgnoreEnd

    /**
     * To string
     *
     * @return string
     */
    public function __toString()
    {
        return 'New Photo';
    }

    /**
     * Set userId
     *
     * @param integer $userId
     *
     * @return $this
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Get userId
     *
     * @return integer
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set user
     *
     * @param User $user
     *
     * @return $this
     */
    public function setUser(User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
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

    /**
     * @return mixed
     */
    public function getPhotoName()
    {
        return $this->photoName;
    }

    /**
     * @param mixed $photoName
     * @return $this
     */
    public function setPhotoName($photoName)
    {
        $this->photoName = $photoName;

        return $this;
    }

    /**
     * @return File
     */
    public function getPhotoFile()
    {
        return $this->photoFile;
    }

    /**
     * @param File $photoFile
     * @return $this
     */
    public function setPhotoFile($photoFile)
    {
        $this->photoFile = $photoFile;

        return $this;
    }
}
