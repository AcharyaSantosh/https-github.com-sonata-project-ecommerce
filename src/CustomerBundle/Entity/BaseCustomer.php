<?php

declare(strict_types=1);

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\CustomerBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Sonata\Component\Customer\AddressInterface;
use Sonata\Component\Customer\CustomerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

abstract class BaseCustomer implements CustomerInterface
{
    public const TITLE_MLLE = 1;
    public const TITLE_MME = 2;
    public const TITLE_MR = 3;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $firstname;

    /**
     * @var string
     */
    protected $lastname;

    /**
     * @var string
     */
    protected $email;

    /**
     * @var \DateTime
     */
    protected $birthDate;

    /**
     * @var string
     */
    protected $birthPlace;

    /**
     * @var string
     */
    protected $phoneNumber;

    /**
     * @var string
     */
    protected $mobileNumber;

    /**
     * @var string
     */
    protected $faxNumber;

    /**
     * @var \DateTime
     */
    protected $updatedAt;

    /**
     * @var \DateTime
     */
    protected $createdAt;

    /**
     * @var UserInterface
     */
    protected $user;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    protected $addresses;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    protected $orders;

    protected $locale;

    /**
     * @var bool
     */
    protected $isFake;

    public function __construct()
    {
        $this->title = self::TITLE_MR;
        $this->addresses = new ArrayCollection();
        $this->orders = new ArrayCollection();
        $this->isFake = false;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return $this->getFullname();
    }

    public function prePersist(): void
    {
        $this->setCreatedAt(new \DateTime());
        $this->setUpdatedAt(new \DateTime());
    }

    public function preUpdate(): void
    {
        $this->setUpdatedAt(new \DateTime());
    }

    public function getAdminTitle()
    {
        return $this->getFullname();
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * {@inheritdoc}
     */
    public function setTitle($title): void
    {
        $this->title = $title;
    }

    /**
     * @static
     *
     * @return array
     */
    public static function getTitlesList()
    {
        return [
            self::TITLE_MLLE => 'customer_title_mlle',
            self::TITLE_MME => 'customer_title_mme',
            self::TITLE_MR => 'customer_title_mr',
        ];
    }

    /**
     * Get title name.
     *
     * @return string
     */
    public function getTitleName()
    {
        $list = self::getTitlesList();

        return isset($list[$this->title]) ? $list[$this->title] : '';
    }

    /**
     * {@inheritdoc}
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * {@inheritdoc}
     */
    public function setFirstname($firstname): void
    {
        $this->firstname = $firstname;
    }

    /**
     * {@inheritdoc}
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * {@inheritdoc}
     */
    public function setLastname($lastname): void
    {
        $this->lastname = $lastname;
    }

    /**
     * {@inheritdoc}
     */
    public function getFullname()
    {
        return $this->getFirstname().' '.$this->getLastname();
    }

    /**
     * {@inheritdoc}
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * {@inheritdoc}
     */
    public function setEmail($email): void
    {
        $this->email = $email;
    }

    /**
     * {@inheritdoc}
     */
    public function getBirthDate()
    {
        return $this->birthDate;
    }

    /**
     * {@inheritdoc}
     */
    public function setBirthDate(\DateTime $birthDate = null): void
    {
        $this->birthDate = $birthDate;
    }

    /**
     * {@inheritdoc}
     */
    public function getBirthPlace()
    {
        return $this->birthPlace;
    }

    /**
     * {@inheritdoc}
     */
    public function setBirthPlace($birthPlace): void
    {
        $this->birthPlace = $birthPlace;
    }

    /**
     * {@inheritdoc}
     */
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    /**
     * {@inheritdoc}
     */
    public function setPhoneNumber($phoneNumber): void
    {
        $this->phoneNumber = $phoneNumber;
    }

    /**
     * {@inheritdoc}
     */
    public function getMobileNumber()
    {
        return $this->mobileNumber;
    }

    /**
     * {@inheritdoc}
     */
    public function setMobileNumber($mobileNumber): void
    {
        $this->mobileNumber = $mobileNumber;
    }

    /**
     * {@inheritdoc}
     */
    public function getFaxNumber()
    {
        return $this->faxNumber;
    }

    /**
     * {@inheritdoc}
     */
    public function setFaxNumber($faxNumber): void
    {
        $this->faxNumber = $faxNumber;
    }

    /**
     * {@inheritdoc}
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * {@inheritdoc}
     */
    public function setCreatedAt(\DateTime $createdAt = null): void
    {
        $this->createdAt = $createdAt;
    }

    /**
     * {@inheritdoc}
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * {@inheritdoc}
     */
    public function setUpdatedAt(\DateTime $updatedAt = null): void
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * {@inheritdoc}
     */
    public function setUser(UserInterface $user): void
    {
        $this->user = $user;
    }

    /**
     * {@inheritdoc}
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * {@inheritdoc}
     */
    public function addAddress(AddressInterface $address): void
    {
        $address->setCustomer($this);

        if (0 === count($this->getAddressesByType($address->getType()))) {
            $address->setCurrent(true);
        }

        $this->getAddresses()->add($address);

        if (null === $this->getFirstname()) {
            $this->setFirstname($address->getFirstname());
        }
        if (null === $this->getLastname()) {
            $this->setLastname($address->getLastname());
        }
        if (null === $this->getPhoneNumber()) {
            $this->setPhoneNumber($address->getPhone());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getAddresses()
    {
        return $this->addresses;
    }

    /**
     * {@inheritdoc}
     */
    public function getAddressesByType($type)
    {
        $addresses = new ArrayCollection();

        foreach ($this->getAddresses() as $address) {
            if ($type == $address->getType()) {
                $addresses->set($address->getId(), $address);
            }
        }

        return $addresses;
    }

    /**
     * {@inheritdoc}
     */
    public function setOrders($orders): void
    {
        $this->orders = $orders;
    }

    /**
     * {@inheritdoc}
     */
    public function getOrders()
    {
        return $this->orders;
    }

    /**
     * {@inheritdoc}
     */
    public function setIsFake($isFake): void
    {
        $this->isFake = $isFake;
    }

    /**
     * {@inheritdoc}
     */
    public function getIsFake()
    {
        return $this->isFake;
    }

    /**
     * {@inheritdoc}
     */
    public function setLocale($locale): void
    {
        $this->locale = $locale;
    }

    /**
     * {@inheritdoc}
     */
    public function getLocale()
    {
        return $this->locale;
    }
}
