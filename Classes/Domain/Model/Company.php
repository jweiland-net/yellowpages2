<?php
namespace JWeiland\Yellowpages2\Domain\Model;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Stefan Froemken <projects@jweiland.net>, jweiland.net
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * @package yellowpages2
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class Company extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{

    /**
     * Hidden
     *
     * @var boolean
     */
    protected $hidden = false;

    /**
     * WSP Member
     *
     * @var boolean
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
     * @var \JWeiland\Yellowpages2\Domain\Model\FileReference
     */
    protected $logo = null;

    /**
     * Images
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\JWeiland\Yellowpages2\Domain\Model\FileReference>
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
     * @var boolean
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
    protected $district = null;

    /**
     * MainTrade
     *
     * @var \TYPO3\CMS\Extbase\Domain\Model\Category
     * @validate NotEmpty
     * @lazy
     */
    protected $mainTrade = null;

    /**
     * trades
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\TYPO3\CMS\Extbase\Domain\Model\Category>
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
    protected $txMaps2Uid = null;

    /**
     * FeUser
     *
     * @var \JWeiland\Yellowpages2\Domain\Model\FeUser
     * @lazy
     */
    protected $feUser = null;





    /**
     * Constructor of this object
     */
    public function __construct()
    {
        $this->trades = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
        $this->images = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
    }

    /**
     * Returns the hidden
     *
     * @return boolean $hidden
     */
    public function getHidden()
    {
        return $this->hidden;
    }

    /**
     * Sets the hidden
     *
     * @param boolean $hidden
     * @return void
     */
    public function setHidden($hidden)
    {
        $this->hidden = (bool) $hidden;
    }

    /**
     * Returns the wspMember
     *
     * @return boolean $wspMember
     */
    public function getWspMember()
    {
        return $this->wspMember;
    }

    /**
     * Sets the wspMember
     *
     * @param boolean $wspMember
     * @return void
     */
    public function setWspMember($wspMember)
    {
        $this->wspMember = (bool) $wspMember;
    }

    /**
     * Returns the company
     *
     * @return string $company
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * Sets the company
     *
     * @param string $company
     * @return void
     */
    public function setCompany($company)
    {
        $this->company = $company;
    }

    /**
     * Returns the logo
     * This is only needed by the edit form
     *
     * @return \JWeiland\Yellowpages2\Domain\Model\FileReference $logo
     */
    public function getLogo()
    {
        return $this->logo;
    }

    /**
     * Sets the logo
     *
     * @param \JWeiland\Yellowpages2\Domain\Model\FileReference $logo
     * @return void
     */
    public function setLogo(\JWeiland\Yellowpages2\Domain\Model\FileReference $logo = null)
    {
        $this->logo = $logo;
    }

    /**
     * Returns the images
     * This is only allowed in edit form
     *
     * @return array $images
     */
    public function getImages()
    {
        $references = array();
        foreach ($this->images as $image) {
            $references[] = $image;
        }
        return $references;
    }

    /**
     * Sets the images
     *
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage $images A minimized Array from $_FILES
     * @return void
     */
    public function setImages(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $images)
    {
        $this->images = $images;
    }

    /**
     * Returns the street
     *
     * @return string $street
     */
    public function getStreet()
    {
        return $this->street;
    }

    /**
     * Sets the street
     *
     * @param string $street
     * @return void
     */
    public function setStreet($street)
    {
        $this->street = $street;
    }

    /**
     * Returns the houseNumber
     *
     * @return string $houseNumber
     */
    public function getHouseNumber()
    {
        return $this->houseNumber;
    }

    /**
     * Sets the houseNumber
     *
     * @param string $houseNumber
     * @return void
     */
    public function setHouseNumber($houseNumber)
    {
        $this->houseNumber = $houseNumber;
    }

    /**
     * Returns the zip
     *
     * @return string $zip
     */
    public function getZip()
    {
        return $this->zip;
    }

    /**
     * Sets the zip
     *
     * @param string $zip
     * @return void
     */
    public function setZip($zip)
    {
        $this->zip = $zip;
    }

    /**
     * Returns the city
     *
     * @return string $city
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Sets the city
     *
     * @param string $city
     * @return void
     */
    public function setCity($city)
    {
        $this->city = $city;
    }

    /**
     * Returns the telephone
     *
     * @return string $telephone
     */
    public function getTelephone()
    {
        return $this->telephone;
    }

    /**
     * Sets the telephone
     *
     * @param string $telephone
     * @return void
     */
    public function setTelephone($telephone)
    {
        $this->telephone = $telephone;
    }

    /**
     * Returns the fax
     *
     * @return string $fax
     */
    public function getFax()
    {
        return $this->fax;
    }

    /**
     * Sets the fax
     *
     * @param string $fax
     * @return void
     */
    public function setFax($fax)
    {
        $this->fax = $fax;
    }

    /**
     * Returns the contactPerson
     *
     * @return string $contactPerson
     */
    public function getContactPerson()
    {
        return $this->contactPerson;
    }

    /**
     * Sets the contactPerson
     *
     * @param string $contactPerson
     * @return void
     */
    public function setContactPerson($contactPerson)
    {
        $this->contactPerson = $contactPerson;
    }

    /**
     * Returns the email
     *
     * @return string $email
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Sets the email
     *
     * @param string $email
     * @return void
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * Returns the website
     *
     * @return string $website
     */
    public function getWebsite()
    {
        return $this->website;
    }

    /**
     * Sets the website
     *
     * @param string $website
     * @return void
     */
    public function setWebsite($website)
    {
        $this->website = $website;
    }

    /**
     * Returns the openingTimes
     *
     * @return string $openingTimes
     */
    public function getOpeningTimes()
    {
        return $this->openingTimes;
    }

    /**
     * Sets the openingTimes
     *
     * @param string $openingTimes
     * @return void
     */
    public function setOpeningTimes($openingTimes)
    {
        $this->openingTimes = $openingTimes;
    }

    /**
     * Returns the barrierFree
     *
     * @return boolean $barrierFree
     */
    public function getBarrierFree()
    {
        return $this->barrierFree;
    }

    /**
     * Sets the barrierFree
     *
     * @param boolean $barrierFree
     * @return void
     */
    public function setBarrierFree($barrierFree)
    {
        $this->barrierFree = $barrierFree;
    }

    /**
     * Returns the boolean state of barrierFree
     *
     * @return boolean
     */
    public function isBarrierFree()
    {
        return $this->getBarrierFree();
    }

    /**
     * Returns the description
     *
     * @return string $description
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Sets the description
     *
     * @param string $description
     * @return void
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * Returns the district
     *
     * @return \JWeiland\Yellowpages2\Domain\Model\District $district
     */
    public function getDistrict()
    {
        return $this->district;
    }

    /**
     * Sets the district
     *
     * @param \JWeiland\Yellowpages2\Domain\Model\District $district
     * @return void
     */
    public function setDistrict(\JWeiland\Yellowpages2\Domain\Model\District $district = null)
    {
        $this->district = $district;
    }

    /**
     * Returns the mainTrade
     *
     * @return \TYPO3\CMS\Extbase\Domain\Model\Category $mainTrade
     */
    public function getMainTrade()
    {
        return $this->mainTrade;
    }

    /**
     * Sets the mainTrade
     *
     * @param \TYPO3\CMS\Extbase\Domain\Model\Category $mainTrade
     * @return void
     */
    public function setMainTrade(\TYPO3\CMS\Extbase\Domain\Model\Category $mainTrade = null)
    {
        $this->mainTrade = $mainTrade;
    }

    /**
     * Returns the trades
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage $trades
     */
    public function getTrades()
    {
        return $this->trades;
    }

    /**
     * Sets the trades
     *
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage $trades
     * @return void
     */
    public function setTrades(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $trades)
    {
        $this->trades = $trades;
    }

    /**
     * Returns the facebook
     *
     * @return string $facebook
     */
    public function getFacebook()
    {
        return $this->facebook;
    }

    /**
     * Sets the facebook
     *
     * @param string $facebook
     * @return void
     */
    public function setFacebook($facebook)
    {
        $this->facebook = $facebook;
    }

    /**
     * Returns the twitter
     *
     * @return string $twitter
     */
    public function getTwitter()
    {
        return $this->twitter;
    }

    /**
     * Sets the twitter
     *
     * @param string $twitter
     * @return void
     */
    public function setTwitter($twitter)
    {
        $this->twitter = $twitter;
    }

    /**
     * Returns the google
     *
     * @return string $google
     */
    public function getGoogle()
    {
        return $this->google;
    }

    /**
     * Sets the google
     *
     * @param string $google
     * @return void
     */
    public function setGoogle($google)
    {
        $this->google = $google;
    }

    /**
     * Returns the txMaps2Uid
     *
     * @return \JWeiland\Maps2\Domain\Model\PoiCollection $txMaps2Uid
     */
    public function getTxMaps2Uid()
    {
        return $this->txMaps2Uid;
    }

    /**
     * Sets the txMaps2Uid
     *
     * @param \JWeiland\Maps2\Domain\Model\PoiCollection $txMaps2Uid
     * @return void
     */
    public function setTxMaps2Uid(\JWeiland\Maps2\Domain\Model\PoiCollection $txMaps2Uid)
    {
        $this->txMaps2Uid = $txMaps2Uid;
    }

    /**
     * Returns the feUser
     *
     * @return \JWeiland\Yellowpages2\Domain\Model\FeUser $feUser
     */
    public function getFeUser()
    {
        return $this->feUser;
    }

    /**
     * Sets the feUser
     *
     * @param \JWeiland\Yellowpages2\Domain\Model\FeUser $feUser
     * @return void
     */
    public function setFeUser(\JWeiland\Yellowpages2\Domain\Model\FeUser $feUser)
    {
        $this->feUser = $feUser;
    }

    /**
     * Returns TRUE if user of current record is the same user as currently logged in.
     *
     * Hint: In $GLOBALS all entries were saved as string. So uid has f.e. 3 chars
     * Security: An UID must be given. Else it can be that 0 === 0 returns true
     *
     * @return boolean $hasValidUser
     */
    public function getHasValidUser()
    {
        if (is_array($GLOBALS['TSFE']->fe_user->user) && $GLOBALS['TSFE']->fe_user->user['uid'] > 0 && $this->feUser !== null && $this->feUser->getUid() > 0) {
            return (int) $GLOBALS['TSFE']->fe_user->user['uid'] === $this->feUser->getUid();
        } else {
            return false;
        }
    }

    /**
     * helper method to get the address of the record
     * this is needed by google maps api geocode
     *
     * @return string
     */
    public function getAddress()
    {
        return $this->getStreet() . ' ' . $this->getHouseNumber() . ', ' . $this->getZip() . ' ' . $this->getCity();
    }
}
