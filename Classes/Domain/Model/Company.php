<?php
namespace JWeiland\Yellowpages2\Domain\Model;

/*
 * This file is part of the yellowpages2 project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

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
     * @validate NotEmpty
     */
    protected $company = '';

    /**
     * @var \TYPO3\CMS\Extbase\Domain\Model\FileReference
     */
    protected $logo;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\TYPO3\CMS\Extbase\Domain\Model\FileReference>
     * @lazy
     */
    protected $images;

    /**
     * @var string
     * @validate NotEmpty
     */
    protected $street = '';

    /**
     * @var string
     * @validate NotEmpty
     */
    protected $houseNumber = '';

    /**
     * @var string
     * @validate NotEmpty
     */
    protected $zip = '';

    /**
     * @var string
     * @validate NotEmpty
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
     * @validate NotEmpty
     */
    protected $description = '';

    /**
     * @var \JWeiland\Yellowpages2\Domain\Model\District
     * @validate NotEmpty
     */
    protected $district;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\JWeiland\Yellowpages2\Domain\Model\Category>
     * @validate NotEmpty
     * @lazy
     */
    protected $mainTrade;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\JWeiland\Yellowpages2\Domain\Model\Category>
     * @lazy
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

    /**
     * @return bool
     */
    public function getHidden(): bool
    {
        return $this->hidden;
    }

    /**
     * @param bool $hidden
     */
    public function setHidden(bool $hidden)
    {
        $this->hidden = $hidden;
    }

    /**
     * @return bool
     */
    public function getWspMember(): bool
    {
        return $this->wspMember;
    }

    /**
     * @param bool $wspMember
     */
    public function setWspMember(bool $wspMember)
    {
        $this->wspMember = $wspMember;
    }

    /**
     * @return string
     */
    public function getCompany(): string
    {
        return $this->company;
    }

    /**
     * @param string $company
     */
    public function setCompany(string $company)
    {
        $this->company = $company;
    }

    /**
     * @return FileReference
     */
    public function getLogo()
    {
        return $this->logo;
    }

    /**
     * @param FileReference $logo
     */
    public function setLogo(FileReference $logo = null)
    {
        $this->logo = $logo;
    }

    /**
     * @return array
     */
    public function getImages(): array
    {
        $references = [];
        foreach ($this->images as $image) {
            $references[] = $image;
        }
        return $references;
    }

    /**
     * @param ObjectStorage $images A minimized Array from $_FILES
     */
    public function setImages(ObjectStorage $images)
    {
        $this->images = $images;
    }

    /**
     * @param FileReference $image
     */
    public function addImage(FileReference $image)
    {
        $this->images->attach($image);
    }

    /**
     * @param FileReference $image
     */
    public function removeImage(FileReference $image)
    {
        $this->images->detach($image);
    }

    /**
     * @return string
     */
    public function getStreet(): string
    {
        return $this->street;
    }

    /**
     * @param string $street
     */
    public function setStreet(string $street)
    {
        $this->street = $street;
    }

    /**
     * @return string
     */
    public function getHouseNumber(): string
    {
        return $this->houseNumber;
    }

    /**
     * @param string $houseNumber
     */
    public function setHouseNumber(string $houseNumber)
    {
        $this->houseNumber = $houseNumber;
    }

    /**
     * @return string
     */
    public function getZip(): string
    {
        return $this->zip;
    }

    /**
     * @param string $zip
     */
    public function setZip(string $zip)
    {
        $this->zip = $zip;
    }

    /**
     * @return string
     */
    public function getCity(): string
    {
        return $this->city;
    }

    /**
     * @param string $city
     */
    public function setCity(string $city)
    {
        $this->city = $city;
    }

    /**
     * @return string
     */
    public function getTelephone(): string
    {
        return $this->telephone;
    }

    /**
     * @param string $telephone
     */
    public function setTelephone(string $telephone)
    {
        $this->telephone = $telephone;
    }

    /**
     * @return string
     */
    public function getFax(): string
    {
        return $this->fax;
    }

    /**
     * @param string $fax
     */
    public function setFax(string $fax)
    {
        $this->fax = $fax;
    }

    /**
     * @return string
     */
    public function getContactPerson(): string
    {
        return $this->contactPerson;
    }

    /**
     * @param string $contactPerson
     */
    public function setContactPerson(string $contactPerson)
    {
        $this->contactPerson = $contactPerson;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail(string $email)
    {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getWebsite(): string
    {
        return $this->website;
    }

    /**
     * @param string $website
     */
    public function setWebsite(string $website)
    {
        $this->website = $website;
    }

    /**
     * @return string
     */
    public function getOpeningTimes(): string
    {
        return $this->openingTimes;
    }

    /**
     * @param string $openingTimes
     */
    public function setOpeningTimes(string $openingTimes)
    {
        $this->openingTimes = $openingTimes;
    }

    /**
     * @return bool
     */
    public function getBarrierFree(): bool
    {
        return $this->barrierFree;
    }

    /**
     * @param bool $barrierFree
     */
    public function setBarrierFree(bool $barrierFree)
    {
        $this->barrierFree = $barrierFree;
    }

    /**
     * @return bool
     */
    public function isBarrierFree(): bool
    {
        return $this->getBarrierFree();
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description)
    {
        $this->description = $description;
    }

    /**
     * @return District
     */
    public function getDistrict()
    {
        return $this->district;
    }

    /**
     * @param District $district
     */
    public function setDistrict(District $district = null)
    {
        $this->district = $district;
    }

    /**
     * @return ObjectStorage
     */
    public function getMainTrade()
    {
        return $this->mainTrade;
    }

    /**
     * @param ObjectStorage $mainTrade
     */
    public function setMainTrade(ObjectStorage $mainTrade)
    {
        $this->mainTrade = $mainTrade;
    }

    /**
     * @param Category $mainTrade
     */
    public function addMainTrade(Category $mainTrade)
    {
        $this->mainTrade->attach($mainTrade);
    }

    /**
     * @param Category $mainTrade
     */
    public function removeMainTrade(Category $mainTrade)
    {
        $this->mainTrade->detach($mainTrade);
    }

    /**
     * @return ObjectStorage
     */
    public function getTrades()
    {
        return $this->trades;
    }

    /**
     * @param ObjectStorage $trades
     */
    public function setTrades(ObjectStorage $trades)
    {
        $this->trades = $trades;
    }

    /**
     * @param Category $trade
     */
    public function addTrade(Category $trade)
    {
        $this->trades->attach($trade);
    }

    /**
     * @param Category $trade
     */
    public function removeTrade(Category $trade)
    {
        $this->trades->detach($trade);
    }

    /**
     * @return string
     */
    public function getFacebook(): string
    {
        return $this->facebook;
    }

    /**
     * @param string $facebook
     */
    public function setFacebook(string $facebook)
    {
        $this->facebook = $facebook;
    }

    /**
     * @return string
     */
    public function getTwitter(): string
    {
        return $this->twitter;
    }

    /**
     * @param string $twitter
     */
    public function setTwitter(string $twitter)
    {
        $this->twitter = $twitter;
    }

    /**
     * @return string
     */
    public function getGoogle(): string
    {
        return $this->google;
    }

    /**
     * @param string $google
     */
    public function setGoogle(string $google)
    {
        $this->google = $google;
    }

    /**
     * @return PoiCollection
     */
    public function getTxMaps2Uid()
    {
        return $this->txMaps2Uid;
    }

    /**
     * @param PoiCollection $txMaps2Uid
     */
    public function setTxMaps2Uid(PoiCollection $txMaps2Uid = null)
    {
        $this->txMaps2Uid = $txMaps2Uid;
    }

    /**
     * @return FeUser
     */
    public function getFeUser()
    {
        return $this->feUser;
    }

    /**
     * @param FeUser $feUser
     */
    public function setFeUser(FeUser $feUser)
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
            return (int) $GLOBALS['TSFE']->fe_user->user['uid'] === $this->feUser->getUid();
        }
        return false;
    }

    /**
     * helper method to get the address of the record
     * this is needed by google maps geocode API
     *
     * @return string
     */
    public function getAddress(): string
    {
        return $this->getStreet() . ' ' . $this->getHouseNumber() . ', ' . $this->getZip() . ' ' . $this->getCity();
    }
}
