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
 * @package yellowpages2
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class Company extends AbstractEntity
{
    /**
     * Hidden
     *
     * @var bool
     */
    protected $hidden = false;

    /**
     * WSP Member
     *
     * @var bool
     */
    protected $wspMember = false;

    /**
     * Company
     *
     * @var string
     * @validate NotEmpty
     */
    protected $company = '';

    /**
     * Logo
     *
     * @var \TYPO3\CMS\Extbase\Domain\Model\FileReference
     */
    protected $logo;

    /**
     * Images
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\TYPO3\CMS\Extbase\Domain\Model\FileReference>
     */
    protected $images;

    /**
     * Street
     *
     * @var string
     * @validate NotEmpty
     */
    protected $street = '';

    /**
     * house number
     *
     * @var string
     * @validate NotEmpty
     */
    protected $houseNumber = '';

    /**
     * Zip
     *
     * @var string
     * @validate NotEmpty
     */
    protected $zip = '';

    /**
     * City
     *
     * @var string
     * @validate NotEmpty
     */
    protected $city = '';

    /**
     * Telephone
     *
     * @var string
     */
    protected $telephone = '';

    /**
     * Fax
     *
     * @var string
     */
    protected $fax = '';

    /**
     * Contact person
     *
     * @var string
     */
    protected $contactPerson = '';

    /**
     * Email
     *
     * @var string
     */
    protected $email = '';

    /**
     * Website
     *
     * @var string
     */
    protected $website = '';

    /**
     * Opening times
     *
     * @var string
     */
    protected $openingTimes = '';

    /**
     * Barrier-free
     *
     * @var bool
     */
    protected $barrierFree = false;

    /**
     * Description
     *
     * @var string
     * @validate NotEmpty
     */
    protected $description = '';

    /**
     * District
     *
     * @var \JWeiland\Yellowpages2\Domain\Model\District
     * @validate NotEmpty
     * @lazy
     */
    protected $district;

    /**
     * MainTrade
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\JWeiland\Yellowpages2\Domain\Model\Category>
     * @validate NotEmpty
     * @lazy
     */
    protected $mainTrade;

    /**
     * trades
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\JWeiland\Yellowpages2\Domain\Model\Category>
     * @lazy
     */
    protected $trades;

    /**
     * Facebook
     *
     * @var string
     */
    protected $facebook = '';

    /**
     * Twitter
     *
     * @var string
     */
    protected $twitter = '';

    /**
     * Google
     *
     * @var string
     */
    protected $google = '';

    /**
     * TxMaps2Uid
     *
     * @var \JWeiland\Maps2\Domain\Model\PoiCollection
     */
    protected $txMaps2Uid;

    /**
     * FeUser
     *
     * @var \JWeiland\Yellowpages2\Domain\Model\FeUser
     */
    protected $feUser;

    /**
     * Constructor of this object
     */
    public function __construct()
    {
        $this->images = new ObjectStorage();
        $this->mainTrade = new ObjectStorage();
        $this->trades = new ObjectStorage();
    }

    /**
     * Returns the hidden
     *
     * @return bool $hidden
     */
    public function getHidden(): bool
    {
        return $this->hidden;
    }

    /**
     * Sets the hidden
     *
     * @param bool $hidden
     * @return void
     */
    public function setHidden(bool $hidden)
    {
        $this->hidden = $hidden;
    }

    /**
     * Returns the wspMember
     *
     * @return bool $wspMember
     */
    public function getWspMember(): bool
    {
        return $this->wspMember;
    }

    /**
     * Sets the wspMember
     *
     * @param bool $wspMember
     * @return void
     */
    public function setWspMember(bool $wspMember)
    {
        $this->wspMember = $wspMember;
    }

    /**
     * Returns the company
     *
     * @return string $company
     */
    public function getCompany(): string
    {
        return $this->company;
    }

    /**
     * Sets the company
     *
     * @param string $company
     * @return void
     */
    public function setCompany(string $company)
    {
        $this->company = $company;
    }

    /**
     * Returns the logo
     * This is only needed by the edit form
     *
     * @return FileReference $logo
     */
    public function getLogo()
    {
        return $this->logo;
    }

    /**
     * Sets the logo
     *
     * @param FileReference $logo
     * @return void
     */
    public function setLogo(FileReference $logo = null)
    {
        $this->logo = $logo;
    }

    /**
     * Returns the images
     * This is only allowed in edit form
     *
     * @return array $images
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
     * Sets the images
     *
     * @param ObjectStorage $images A minimized Array from $_FILES
     * @return void
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
     * Returns the street
     *
     * @return string $street
     */
    public function getStreet(): string
    {
        return $this->street;
    }

    /**
     * Sets the street
     *
     * @param string $street
     * @return void
     */
    public function setStreet(string $street)
    {
        $this->street = $street;
    }

    /**
     * Returns the houseNumber
     *
     * @return string $houseNumber
     */
    public function getHouseNumber(): string
    {
        return $this->houseNumber;
    }

    /**
     * Sets the houseNumber
     *
     * @param string $houseNumber
     * @return void
     */
    public function setHouseNumber(string $houseNumber)
    {
        $this->houseNumber = $houseNumber;
    }

    /**
     * Returns the zip
     *
     * @return string $zip
     */
    public function getZip(): string
    {
        return $this->zip;
    }

    /**
     * Sets the zip
     *
     * @param string $zip
     * @return void
     */
    public function setZip(string $zip)
    {
        $this->zip = $zip;
    }

    /**
     * Returns the city
     *
     * @return string $city
     */
    public function getCity(): string
    {
        return $this->city;
    }

    /**
     * Sets the city
     *
     * @param string $city
     * @return void
     */
    public function setCity(string $city)
    {
        $this->city = $city;
    }

    /**
     * Returns the telephone
     *
     * @return string $telephone
     */
    public function getTelephone(): string
    {
        return $this->telephone;
    }

    /**
     * Sets the telephone
     *
     * @param string $telephone
     * @return void
     */
    public function setTelephone(string $telephone)
    {
        $this->telephone = $telephone;
    }

    /**
     * Returns the fax
     *
     * @return string $fax
     */
    public function getFax(): string
    {
        return $this->fax;
    }

    /**
     * Sets the fax
     *
     * @param string $fax
     * @return void
     */
    public function setFax(string $fax)
    {
        $this->fax = $fax;
    }

    /**
     * Returns the contactPerson
     *
     * @return string $contactPerson
     */
    public function getContactPerson(): string
    {
        return $this->contactPerson;
    }

    /**
     * Sets the contactPerson
     *
     * @param string $contactPerson
     * @return void
     */
    public function setContactPerson(string $contactPerson)
    {
        $this->contactPerson = $contactPerson;
    }

    /**
     * Returns the email
     *
     * @return string $email
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * Sets the email
     *
     * @param string $email
     * @return void
     */
    public function setEmail(string $email)
    {
        $this->email = $email;
    }

    /**
     * Returns the website
     *
     * @return string $website
     */
    public function getWebsite(): string
    {
        return $this->website;
    }

    /**
     * Sets the website
     *
     * @param string $website
     * @return void
     */
    public function setWebsite(string $website)
    {
        $this->website = $website;
    }

    /**
     * Returns the openingTimes
     *
     * @return string $openingTimes
     */
    public function getOpeningTimes(): string
    {
        return $this->openingTimes;
    }

    /**
     * Sets the openingTimes
     *
     * @param string $openingTimes
     * @return void
     */
    public function setOpeningTimes(string $openingTimes)
    {
        $this->openingTimes = $openingTimes;
    }

    /**
     * Returns the barrierFree
     *
     * @return bool $barrierFree
     */
    public function getBarrierFree(): bool
    {
        return $this->barrierFree;
    }

    /**
     * Sets the barrierFree
     *
     * @param bool $barrierFree
     * @return void
     */
    public function setBarrierFree(bool $barrierFree)
    {
        $this->barrierFree = $barrierFree;
    }

    /**
     * Returns the bool state of barrierFree
     *
     * @return bool
     */
    public function isBarrierFree(): bool
    {
        return $this->getBarrierFree();
    }

    /**
     * Returns the description
     *
     * @return string $description
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Sets the description
     *
     * @param string $description
     * @return void
     */
    public function setDescription(string $description)
    {
        $this->description = $description;
    }

    /**
     * Returns the district
     *
     * @return District $district
     */
    public function getDistrict()
    {
        return $this->district;
    }

    /**
     * Sets the district
     *
     * @param District $district
     * @return void
     */
    public function setDistrict(District $district = null)
    {
        $this->district = $district;
    }

    /**
     * Returns the mainTrade
     *
     * @return ObjectStorage $mainTrade
     */
    public function getMainTrade()
    {
        return $this->mainTrade;
    }

    /**
     * Sets the mainTrade
     *
     * @param ObjectStorage $mainTrade
     * @return void
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
     * Returns the trades
     *
     * @return ObjectStorage $trades
     */
    public function getTrades()
    {
        return $this->trades;
    }

    /**
     * Sets the trades
     *
     * @param ObjectStorage $trades
     * @return void
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
     * Returns the facebook
     *
     * @return string $facebook
     */
    public function getFacebook(): string
    {
        return $this->facebook;
    }

    /**
     * Sets the facebook
     *
     * @param string $facebook
     * @return void
     */
    public function setFacebook(string $facebook)
    {
        $this->facebook = $facebook;
    }

    /**
     * Returns the twitter
     *
     * @return string $twitter
     */
    public function getTwitter(): string
    {
        return $this->twitter;
    }

    /**
     * Sets the twitter
     *
     * @param string $twitter
     * @return void
     */
    public function setTwitter(string $twitter)
    {
        $this->twitter = $twitter;
    }

    /**
     * Returns the google
     *
     * @return string $google
     */
    public function getGoogle(): string
    {
        return $this->google;
    }

    /**
     * Sets the google
     *
     * @param string $google
     * @return void
     */
    public function setGoogle(string $google)
    {
        $this->google = $google;
    }

    /**
     * Returns the txMaps2Uid
     *
     * @return PoiCollection $txMaps2Uid
     */
    public function getTxMaps2Uid()
    {
        return $this->txMaps2Uid;
    }

    /**
     * Sets the txMaps2Uid
     *
     * @param PoiCollection $txMaps2Uid
     * @return void
     */
    public function setTxMaps2Uid(PoiCollection $txMaps2Uid)
    {
        $this->txMaps2Uid = $txMaps2Uid;
    }

    /**
     * Returns the feUser
     *
     * @return FeUser $feUser
     */
    public function getFeUser()
    {
        return $this->feUser;
    }

    /**
     * Sets the feUser
     *
     * @param FeUser $feUser
     * @return void
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
