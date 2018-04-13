<?php

namespace JWeiland\Yellowpages2\Tests\Unit\Domain\Model;

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
use JWeiland\Yellowpages2\Domain\Model\Category;
use JWeiland\Yellowpages2\Domain\Model\Company;
use JWeiland\Yellowpages2\Domain\Model\District;
use JWeiland\Yellowpages2\Domain\Model\FeUser;
use Nimut\TestingFramework\TestCase\UnitTestCase;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Test case.
 *
 * @author Stefan Froemken <projects@jweiland.net>
 */
class CompanyTest extends UnitTestCase
{
    /**
     * @var Company
     */
    protected $subject;

    /**
     * set up.
     */
    public function setUp()
    {
        $this->subject = new Company();
    }

    /**
     * tear down.
     */
    public function tearDown()
    {
        unset($this->subject);
        parent::tearDown();
    }

    /**
     * @test
     */
    public function getHiddenInitiallyReturnsFalse() {
        $this->assertSame(
            false,
            $this->subject->getHidden()
        );
    }

    /**
     * @test
     */
    public function setHiddenSetsHidden() {
        $this->subject->setHidden(true);
        $this->assertSame(
            true,
            $this->subject->getHidden()
        );
    }

    /**
     * @test
     */
    public function setHiddenWithStringReturnsTrue() {
        $this->subject->setHidden('foo bar');
        $this->assertTrue($this->subject->getHidden());
    }

    /**
     * @test
     */
    public function setHiddenWithZeroReturnsFalse() {
        $this->subject->setHidden(0);
        $this->assertFalse($this->subject->getHidden());
    }

    /**
     * @test
     */
    public function getwspMemberInitiallyReturnsFalse() {
        $this->assertSame(
            false,
            $this->subject->getwspMember()
        );
    }

    /**
     * @test
     */
    public function setwspMemberSetswspMember() {
        $this->subject->setwspMember(true);
        $this->assertSame(
            true,
            $this->subject->getwspMember()
        );
    }

    /**
     * @test
     */
    public function setwspMemberWithStringReturnsTrue() {
        $this->subject->setwspMember('foo bar');
        $this->assertTrue($this->subject->getwspMember());
    }

    /**
     * @test
     */
    public function setwspMemberWithZeroReturnsFalse() {
        $this->subject->setwspMember(0);
        $this->assertFalse($this->subject->getwspMember());
    }

    /**
     * @test
     */
    public function getCompanyInitiallyReturnsEmptyString() {
        $this->assertSame(
            '',
            $this->subject->getCompany()
        );
    }

    /**
     * @test
     */
    public function setCompanySetsCompany() {
        $this->subject->setCompany('foo bar');

        $this->assertSame(
            'foo bar',
            $this->subject->getCompany()
        );
    }

    /**
     * @test
     */
    public function setCompanyWithIntegerResultsInString() {
        $this->subject->setCompany(123);
        $this->assertSame('123', $this->subject->getCompany());
    }

    /**
     * @test
     */
    public function setCompanyWithBooleanResultsInString() {
        $this->subject->setCompany(true);
        $this->assertSame('1', $this->subject->getCompany());
    }

    /**
     * @test
     */
    public function getLogoInitiallyReturnsNull() {
        $this->assertNull($this->subject->getLogo());
    }

    /**
     * @test
     */
    public function setLogoSetsLogo() {
        $instance = new FileReference();
        $this->subject->setLogo($instance);

        $this->assertSame(
            $instance,
            $this->subject->getLogo()
        );
    }

    /**
     * @test
     */
    public function getImagesInitiallyReturnsArray() {
        $this->assertEquals(
            [],
            $this->subject->getImages()
        );
    }

    /**
     * @test
     */
    public function setImagesSetsImages() {
        $object = new FileReference();
        $objectStorage = new ObjectStorage();
        $objectStorage->attach($object);
        $this->subject->setImages($objectStorage);

        $this->assertSame(
            [
                $object
            ],
            $this->subject->getImages()
        );
    }

    /**
     * @test
     */
    public function addImageAddsOneImage() {
        $objectStorage = new ObjectStorage();
        $this->subject->setImages($objectStorage);

        $object = new FileReference();
        $this->subject->addImage($object);

        $objectStorage->attach($object);

        $this->assertSame(
            [
                $object
            ],
            $this->subject->getImages()
        );
    }

    /**
     * @test
     */
    public function removeImageRemovesOneImage() {
        $object = new FileReference();
        $objectStorage = new ObjectStorage();
        $objectStorage->attach($object);
        $this->subject->setImages($objectStorage);

        $this->subject->removeImage($object);
        $objectStorage->detach($object);

        $this->assertSame(
            [],
            $this->subject->getImages()
        );
    }

    /**
     * @test
     */
    public function getStreetInitiallyReturnsEmptyString() {
        $this->assertSame(
            '',
            $this->subject->getStreet()
        );
    }

    /**
     * @test
     */
    public function setStreetSetsStreet() {
        $this->subject->setStreet('foo bar');

        $this->assertSame(
            'foo bar',
            $this->subject->getStreet()
        );
    }

    /**
     * @test
     */
    public function setStreetWithIntegerResultsInString() {
        $this->subject->setStreet(123);
        $this->assertSame('123', $this->subject->getStreet());
    }

    /**
     * @test
     */
    public function setStreetWithBooleanResultsInString() {
        $this->subject->setStreet(true);
        $this->assertSame('1', $this->subject->getStreet());
    }

    /**
     * @test
     */
    public function getHouseNumberInitiallyReturnsEmptyString() {
        $this->assertSame(
            '',
            $this->subject->getHouseNumber()
        );
    }

    /**
     * @test
     */
    public function setHouseNumberSetsHouseNumber() {
        $this->subject->setHouseNumber('foo bar');

        $this->assertSame(
            'foo bar',
            $this->subject->getHouseNumber()
        );
    }

    /**
     * @test
     */
    public function setHouseNumberWithIntegerResultsInString() {
        $this->subject->setHouseNumber(123);
        $this->assertSame('123', $this->subject->getHouseNumber());
    }

    /**
     * @test
     */
    public function setHouseNumberWithBooleanResultsInString() {
        $this->subject->setHouseNumber(true);
        $this->assertSame('1', $this->subject->getHouseNumber());
    }

    /**
     * @test
     */
    public function getZipInitiallyReturnsEmptyString() {
        $this->assertSame(
            '',
            $this->subject->getZip()
        );
    }

    /**
     * @test
     */
    public function setZipSetsZip() {
        $this->subject->setZip('foo bar');

        $this->assertSame(
            'foo bar',
            $this->subject->getZip()
        );
    }

    /**
     * @test
     */
    public function setZipWithIntegerResultsInString() {
        $this->subject->setZip(123);
        $this->assertSame('123', $this->subject->getZip());
    }

    /**
     * @test
     */
    public function setZipWithBooleanResultsInString() {
        $this->subject->setZip(true);
        $this->assertSame('1', $this->subject->getZip());
    }

    /**
     * @test
     */
    public function getCityInitiallyReturnsEmptyString() {
        $this->assertSame(
            '',
            $this->subject->getCity()
        );
    }

    /**
     * @test
     */
    public function setCitySetsCity() {
        $this->subject->setCity('foo bar');

        $this->assertSame(
            'foo bar',
            $this->subject->getCity()
        );
    }

    /**
     * @test
     */
    public function setCityWithIntegerResultsInString() {
        $this->subject->setCity(123);
        $this->assertSame('123', $this->subject->getCity());
    }

    /**
     * @test
     */
    public function setCityWithBooleanResultsInString() {
        $this->subject->setCity(true);
        $this->assertSame('1', $this->subject->getCity());
    }

    /**
     * @test
     */
    public function getTelephoneInitiallyReturnsEmptyString() {
        $this->assertSame(
            '',
            $this->subject->getTelephone()
        );
    }

    /**
     * @test
     */
    public function setTelephoneSetsTelephone() {
        $this->subject->setTelephone('foo bar');

        $this->assertSame(
            'foo bar',
            $this->subject->getTelephone()
        );
    }

    /**
     * @test
     */
    public function setTelephoneWithIntegerResultsInString() {
        $this->subject->setTelephone(123);
        $this->assertSame('123', $this->subject->getTelephone());
    }

    /**
     * @test
     */
    public function setTelephoneWithBooleanResultsInString() {
        $this->subject->setTelephone(true);
        $this->assertSame('1', $this->subject->getTelephone());
    }

    /**
     * @test
     */
    public function getFaxInitiallyReturnsEmptyString() {
        $this->assertSame(
            '',
            $this->subject->getFax()
        );
    }

    /**
     * @test
     */
    public function setFaxSetsFax() {
        $this->subject->setFax('foo bar');

        $this->assertSame(
            'foo bar',
            $this->subject->getFax()
        );
    }

    /**
     * @test
     */
    public function setFaxWithIntegerResultsInString() {
        $this->subject->setFax(123);
        $this->assertSame('123', $this->subject->getFax());
    }

    /**
     * @test
     */
    public function setFaxWithBooleanResultsInString() {
        $this->subject->setFax(true);
        $this->assertSame('1', $this->subject->getFax());
    }

    /**
     * @test
     */
    public function getContactPersonInitiallyReturnsEmptyString() {
        $this->assertSame(
            '',
            $this->subject->getContactPerson()
        );
    }

    /**
     * @test
     */
    public function setContactPersonSetsContactPerson() {
        $this->subject->setContactPerson('foo bar');

        $this->assertSame(
            'foo bar',
            $this->subject->getContactPerson()
        );
    }

    /**
     * @test
     */
    public function setContactPersonWithIntegerResultsInString() {
        $this->subject->setContactPerson(123);
        $this->assertSame('123', $this->subject->getContactPerson());
    }

    /**
     * @test
     */
    public function setContactPersonWithBooleanResultsInString() {
        $this->subject->setContactPerson(true);
        $this->assertSame('1', $this->subject->getContactPerson());
    }

    /**
     * @test
     */
    public function getEmailInitiallyReturnsEmptyString() {
        $this->assertSame(
            '',
            $this->subject->getEmail()
        );
    }

    /**
     * @test
     */
    public function setEmailSetsEmail() {
        $this->subject->setEmail('foo bar');

        $this->assertSame(
            'foo bar',
            $this->subject->getEmail()
        );
    }

    /**
     * @test
     */
    public function setEmailWithIntegerResultsInString() {
        $this->subject->setEmail(123);
        $this->assertSame('123', $this->subject->getEmail());
    }

    /**
     * @test
     */
    public function setEmailWithBooleanResultsInString() {
        $this->subject->setEmail(true);
        $this->assertSame('1', $this->subject->getEmail());
    }

    /**
     * @test
     */
    public function getWebsiteInitiallyReturnsEmptyString() {
        $this->assertSame(
            '',
            $this->subject->getWebsite()
        );
    }

    /**
     * @test
     */
    public function setWebsiteSetsWebsite() {
        $this->subject->setWebsite('foo bar');

        $this->assertSame(
            'foo bar',
            $this->subject->getWebsite()
        );
    }

    /**
     * @test
     */
    public function setWebsiteWithIntegerResultsInString() {
        $this->subject->setWebsite(123);
        $this->assertSame('123', $this->subject->getWebsite());
    }

    /**
     * @test
     */
    public function setWebsiteWithBooleanResultsInString() {
        $this->subject->setWebsite(true);
        $this->assertSame('1', $this->subject->getWebsite());
    }

    /**
     * @test
     */
    public function getOpeningTimesInitiallyReturnsEmptyString() {
        $this->assertSame(
            '',
            $this->subject->getOpeningTimes()
        );
    }

    /**
     * @test
     */
    public function setOpeningTimesSetsOpeningTimes() {
        $this->subject->setOpeningTimes('foo bar');

        $this->assertSame(
            'foo bar',
            $this->subject->getOpeningTimes()
        );
    }

    /**
     * @test
     */
    public function setOpeningTimesWithIntegerResultsInString() {
        $this->subject->setOpeningTimes(123);
        $this->assertSame('123', $this->subject->getOpeningTimes());
    }

    /**
     * @test
     */
    public function setOpeningTimesWithBooleanResultsInString() {
        $this->subject->setOpeningTimes(true);
        $this->assertSame('1', $this->subject->getOpeningTimes());
    }

    /**
     * @test
     */
    public function getBarrierFreeInitiallyReturnsFalse() {
        $this->assertSame(
            false,
            $this->subject->getBarrierFree()
        );
    }

    /**
     * @test
     */
    public function setBarrierFreeSetsBarrierFree() {
        $this->subject->setBarrierFree(true);
        $this->assertSame(
            true,
            $this->subject->getBarrierFree()
        );
    }

    /**
     * @test
     */
    public function setBarrierFreeWithStringReturnsTrue() {
        $this->subject->setBarrierFree('foo bar');
        $this->assertTrue($this->subject->getBarrierFree());
    }

    /**
     * @test
     */
    public function setBarrierFreeWithZeroReturnsFalse() {
        $this->subject->setBarrierFree(0);
        $this->assertFalse($this->subject->getBarrierFree());
    }

    /**
     * @test
     */
    public function getDescriptionInitiallyReturnsEmptyString() {
        $this->assertSame(
            '',
            $this->subject->getDescription()
        );
    }

    /**
     * @test
     */
    public function setDescriptionSetsDescription() {
        $this->subject->setDescription('foo bar');

        $this->assertSame(
            'foo bar',
            $this->subject->getDescription()
        );
    }

    /**
     * @test
     */
    public function setDescriptionWithIntegerResultsInString() {
        $this->subject->setDescription(123);
        $this->assertSame('123', $this->subject->getDescription());
    }

    /**
     * @test
     */
    public function setDescriptionWithBooleanResultsInString() {
        $this->subject->setDescription(true);
        $this->assertSame('1', $this->subject->getDescription());
    }

    /**
     * @test
     */
    public function getDistrictInitiallyReturnsNull() {
        $this->assertNull($this->subject->getDistrict());
    }

    /**
     * @test
     */
    public function setDistrictSetsDistrict() {
        $instance = new District();
        $this->subject->setDistrict($instance);

        $this->assertSame(
            $instance,
            $this->subject->getDistrict()
        );
    }

    /**
     * @test
     */
    public function getMainTradeInitiallyReturnsObjectStorage() {
        $this->assertEquals(
            new ObjectStorage(),
            $this->subject->getMainTrade()
        );
    }

    /**
     * @test
     */
    public function setMainTradeSetsMainTrade() {
        $object = new Category();
        $objectStorage = new ObjectStorage();
        $objectStorage->attach($object);
        $this->subject->setMainTrade($objectStorage);

        $this->assertSame(
            $objectStorage,
            $this->subject->getMainTrade()
        );
    }

    /**
     * @test
     */
    public function addMainTradeAddsOneMainTrade() {
        $objectStorage = new ObjectStorage();
        $this->subject->setMainTrade($objectStorage);

        $object = new Category();
        $this->subject->addMainTrade($object);

        $objectStorage->attach($object);

        $this->assertSame(
            $objectStorage,
            $this->subject->getMainTrade()
        );
    }

    /**
     * @test
     */
    public function removeMainTradeRemovesOneMainTrade() {
        $object = new Category();
        $objectStorage = new ObjectStorage();
        $objectStorage->attach($object);
        $this->subject->setMainTrade($objectStorage);

        $this->subject->removeMainTrade($object);
        $objectStorage->detach($object);

        $this->assertSame(
            $objectStorage,
            $this->subject->getMainTrade()
        );
    }

    /**
     * @test
     */
    public function getTradesInitiallyReturnsObjectStorage() {
        $this->assertEquals(
            new ObjectStorage(),
            $this->subject->getTrades()
        );
    }

    /**
     * @test
     */
    public function setTradesSetsTrades() {
        $object = new Category();
        $objectStorage = new ObjectStorage();
        $objectStorage->attach($object);
        $this->subject->setTrades($objectStorage);

        $this->assertSame(
            $objectStorage,
            $this->subject->getTrades()
        );
    }

    /**
     * @test
     */
    public function addTradeAddsOneTrade() {
        $objectStorage = new ObjectStorage();
        $this->subject->setTrades($objectStorage);

        $object = new Category();
        $this->subject->addTrade($object);

        $objectStorage->attach($object);

        $this->assertSame(
            $objectStorage,
            $this->subject->getTrades()
        );
    }

    /**
     * @test
     */
    public function removeTradeRemovesOneTrade() {
        $object = new Category();
        $objectStorage = new ObjectStorage();
        $objectStorage->attach($object);
        $this->subject->setTrades($objectStorage);

        $this->subject->removeTrade($object);
        $objectStorage->detach($object);

        $this->assertSame(
            $objectStorage,
            $this->subject->getTrades()
        );
    }

    /**
     * @test
     */
    public function getFacebookInitiallyReturnsEmptyString() {
        $this->assertSame(
            '',
            $this->subject->getFacebook()
        );
    }

    /**
     * @test
     */
    public function setFacebookSetsFacebook() {
        $this->subject->setFacebook('foo bar');

        $this->assertSame(
            'foo bar',
            $this->subject->getFacebook()
        );
    }

    /**
     * @test
     */
    public function setFacebookWithIntegerResultsInString() {
        $this->subject->setFacebook(123);
        $this->assertSame('123', $this->subject->getFacebook());
    }

    /**
     * @test
     */
    public function setFacebookWithBooleanResultsInString() {
        $this->subject->setFacebook(true);
        $this->assertSame('1', $this->subject->getFacebook());
    }

    /**
     * @test
     */
    public function getTwitterInitiallyReturnsEmptyString() {
        $this->assertSame(
            '',
            $this->subject->getTwitter()
        );
    }

    /**
     * @test
     */
    public function setTwitterSetsTwitter() {
        $this->subject->setTwitter('foo bar');

        $this->assertSame(
            'foo bar',
            $this->subject->getTwitter()
        );
    }

    /**
     * @test
     */
    public function setTwitterWithIntegerResultsInString() {
        $this->subject->setTwitter(123);
        $this->assertSame('123', $this->subject->getTwitter());
    }

    /**
     * @test
     */
    public function setTwitterWithBooleanResultsInString() {
        $this->subject->setTwitter(true);
        $this->assertSame('1', $this->subject->getTwitter());
    }

    /**
     * @test
     */
    public function getGoogleInitiallyReturnsEmptyString() {
        $this->assertSame(
            '',
            $this->subject->getGoogle()
        );
    }

    /**
     * @test
     */
    public function setGoogleSetsGoogle() {
        $this->subject->setGoogle('foo bar');

        $this->assertSame(
            'foo bar',
            $this->subject->getGoogle()
        );
    }

    /**
     * @test
     */
    public function setGoogleWithIntegerResultsInString() {
        $this->subject->setGoogle(123);
        $this->assertSame('123', $this->subject->getGoogle());
    }

    /**
     * @test
     */
    public function setGoogleWithBooleanResultsInString() {
        $this->subject->setGoogle(true);
        $this->assertSame('1', $this->subject->getGoogle());
    }

    /**
     * @test
     */
    public function getTxMaps2UidInitiallyReturnsNull() {
        $this->assertNull($this->subject->getTxMaps2Uid());
    }

    /**
     * @test
     */
    public function setTxMaps2UidSetsTxMaps2Uid() {
        $instance = new PoiCollection();
        $this->subject->setTxMaps2Uid($instance);

        $this->assertSame(
            $instance,
            $this->subject->getTxMaps2Uid()
        );
    }

    /**
     * @test
     */
    public function getFeUserInitiallyReturnsNull() {
        $this->assertNull($this->subject->getFeUser());
    }

    /**
     * @test
     */
    public function setFeUserSetsFeUser() {
        $instance = new FeUser();
        $this->subject->setFeUser($instance);

        $this->assertSame(
            $instance,
            $this->subject->getFeUser()
        );
    }
}
