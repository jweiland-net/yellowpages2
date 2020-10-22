<?php

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\Domain\Model;

use JWeiland\Maps2\Domain\Model\PoiCollection;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Domain model for companies
 */
class Company extends AbstractEntity
{
    /**
     * @var bool
     */
    protected $hidden = false;

    /**
     * @var bool
     */
    protected $wspMember = false;

    /**
     * @var string
     * @TYPO3\CMS\Extbase\Annotation\Validate("NotEmpty")
     */
    protected $company = '';

    /**
     * @var \TYPO3\CMS\Extbase\Domain\Model\FileReference
     */
    protected $logo;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\TYPO3\CMS\Extbase\Domain\Model\FileReference>
     * @TYPO3\CMS\Extbase\Annotation\ORM\Lazy
     */
    protected $images;

    /**
     * @var string
     * @TYPO3\CMS\Extbase\Annotation\Validate("NotEmpty")
     */
    protected $street = '';

    /**
     * @var string
     * @TYPO3\CMS\Extbase\Annotation\Validate("NotEmpty")
     */
    protected $houseNumber = '';

    /**
     * @var string
     * @TYPO3\CMS\Extbase\Annotation\Validate("NotEmpty")
     */
    protected $zip = '';

    /**
     * @var string
     * @TYPO3\CMS\Extbase\Annotation\Validate("NotEmpty")
     */
    protected $city = '';

    /**
     * @var string
     */
    protected $telephone = '';

    /**
     * @var string
     */
    protected $fax = '';

    /**
     * @var string
     */
    protected $contactPerson = '';

    /**
     * @var string
     */
    protected $email = '';

    /**
     * @var string
     */
    protected $website = '';

    /**
     * @var string
     */
    protected $openingTimes = '';

    /**
     * @var bool
     */
    protected $barrierFree = false;

    /**
     * @var string
     * @TYPO3\CMS\Extbase\Annotation\Validate("NotEmpty")
     */
    protected $description = '';

    /**
     * @var \JWeiland\Yellowpages2\Domain\Model\District
     * @TYPO3\CMS\Extbase\Annotation\Validate("NotEmpty")
     */
    protected $district;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\JWeiland\Yellowpages2\Domain\Model\Category>
     * @TYPO3\CMS\Extbase\Annotation\Validate("NotEmpty")
     * @TYPO3\CMS\Extbase\Annotation\ORM\Lazy
     */
    protected $mainTrade;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\JWeiland\Yellowpages2\Domain\Model\Category>
     * @TYPO3\CMS\Extbase\Annotation\ORM\Lazy
     */
    protected $trades;

    /**
     * @var string
     */
    protected $facebook = '';

    /**
     * @var string
     */
    protected $twitter = '';

    /**
     * @var string
     */
    protected $google = '';

    /**
     * @var \JWeiland\Maps2\Domain\Model\PoiCollection
     */
    protected $txMaps2Uid;

    /**
     * @var \JWeiland\Yellowpages2\Domain\Model\FeUser
     */
    protected $feUser;

    public function __construct()
    {
        $this->images = new ObjectStorage();
        $this->mainTrade = new ObjectStorage();
        $this->trades = new ObjectStorage();
    }

    public function getHidden(): bool
    {
        return $this->hidden;
    }

    public function setHidden(bool $hidden): void
    {
        $this->hidden = $hidden;
    }

    public function getWspMember(): bool
    {
        return $this->wspMember;
    }

    public function setWspMember(bool $wspMember): void
    {
        $this->wspMember = $wspMember;
    }

    public function getCompany(): string
    {
        return $this->company;
    }

    public function setCompany(string $company): void
    {
        $this->company = $company;
    }

    public function getLogo(): ?FileReference
    {
        return $this->logo;
    }

    public function setLogo(FileReference $logo): void
    {
        $this->logo = $logo;
    }

    public function getImages(): array
    {
        $references = [];
        foreach ($this->images as $image) {
            $references[] = $image;
        }
        return $references;
    }

    public function setImages(ObjectStorage $images): void
    {
        $this->images = $images;
    }

    public function addImage(FileReference $image): void
    {
        $this->images->attach($image);
    }

    public function removeImage(FileReference $image): void
    {
        $this->images->detach($image);
    }

    public function getStreet(): string
    {
        return $this->street;
    }

    public function setStreet(string $street): void
    {
        $this->street = $street;
    }

    public function getHouseNumber(): string
    {
        return $this->houseNumber;
    }

    public function setHouseNumber(string $houseNumber): void
    {
        $this->houseNumber = $houseNumber;
    }

    public function getZip(): string
    {
        return $this->zip;
    }

    public function setZip(string $zip): void
    {
        $this->zip = $zip;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function setCity(string $city): void
    {
        $this->city = $city;
    }

    public function getTelephone(): string
    {
        return $this->telephone;
    }

    public function setTelephone(string $telephone): void
    {
        $this->telephone = $telephone;
    }

    public function getFax(): string
    {
        return $this->fax;
    }

    public function setFax(string $fax): void
    {
        $this->fax = $fax;
    }

    public function getContactPerson(): string
    {
        return $this->contactPerson;
    }

    public function setContactPerson(string $contactPerson): void
    {
        $this->contactPerson = $contactPerson;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getWebsite(): string
    {
        return $this->website;
    }

    public function setWebsite(string $website): void
    {
        $this->website = $website;
    }

    public function getOpeningTimes(): string
    {
        return $this->openingTimes;
    }

    public function setOpeningTimes(string $openingTimes): void
    {
        $this->openingTimes = $openingTimes;
    }

    public function getBarrierFree(): bool
    {
        return $this->barrierFree;
    }

    public function setBarrierFree(bool $barrierFree): void
    {
        $this->barrierFree = $barrierFree;
    }

    public function isBarrierFree(): bool
    {
        return $this->getBarrierFree();
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getDistrict(): ?District
    {
        return $this->district;
    }

    public function setDistrict(District $district): void
    {
        $this->district = $district;
    }

    public function getMainTrade(): ObjectStorage
    {
        return $this->mainTrade;
    }

    public function setMainTrade(ObjectStorage $mainTrade): void
    {
        $this->mainTrade = $mainTrade;
    }

    public function addMainTrade(Category $mainTrade): void
    {
        $this->mainTrade->attach($mainTrade);
    }

    public function removeMainTrade(Category $mainTrade): void
    {
        $this->mainTrade->detach($mainTrade);
    }

    public function getTrades(): ObjectStorage
    {
        return $this->trades;
    }

    public function setTrades(ObjectStorage $trades): void
    {
        $this->trades = $trades;
    }

    public function addTrade(Category $trade): void
    {
        $this->trades->attach($trade);
    }

    public function removeTrade(Category $trade): void
    {
        $this->trades->detach($trade);
    }

    public function getFacebook(): string
    {
        return $this->facebook;
    }

    public function setFacebook(string $facebook): void
    {
        $this->facebook = $facebook;
    }

    public function getTwitter(): string
    {
        return $this->twitter;
    }

    public function setTwitter(string $twitter): void
    {
        $this->twitter = $twitter;
    }

    public function getGoogle(): string
    {
        return $this->google;
    }

    public function setGoogle(string $google): void
    {
        $this->google = $google;
    }

    public function getTxMaps2Uid(): ?PoiCollection
    {
        return $this->txMaps2Uid;
    }

    public function setTxMaps2Uid(PoiCollection $txMaps2Uid): void
    {
        $this->txMaps2Uid = $txMaps2Uid;
    }

    public function getFeUser(): ?FeUser
    {
        return $this->feUser;
    }

    public function setFeUser(FeUser $feUser): void
    {
        $this->feUser = $feUser;
    }

    /**
     * Returns TRUE if user of current record is the same user as currently logged in.
     *
     * Hint: In $GLOBALS all entries were saved as string. So uid has f.e. 3 chars
     * Security: An UID must be given. Else it can be that 0 === 0 returns true
     *
     * @return bool
     */
    public function getHasValidUser(): bool
    {
        if (is_array($GLOBALS['TSFE']->fe_user->user)
            && $GLOBALS['TSFE']->fe_user->user['uid'] > 0
            && $this->feUser !== null && $this->feUser->getUid() > 0
        ) {
            return (int)$GLOBALS['TSFE']->fe_user->user['uid'] === $this->feUser->getUid();
        }
        return false;
    }

    /**
     * Helper method to get the address of the record.
     * This is needed by google maps geocode API
     *
     * @return string
     */
    public function getAddress(): string
    {
        return $this->getStreet() . ' ' . $this->getHouseNumber() . ', ' . $this->getZip() . ' ' . $this->getCity();
    }
}
