<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\Tests\Unit\Domain\Model;

use JWeiland\Yellowpages2\Domain\Model\Company;
use JWeiland\Yellowpages2\Domain\Model\District;
use JWeiland\Yellowpages2\Domain\Model\FeUser;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Extbase\Domain\Model\Category;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Test case.
 */
class CompanyTest extends UnitTestCase
{
    protected Company $subject;

    protected function setUp(): void
    {
        parent::setUp();
        $this->subject = new Company();
    }

    protected function tearDown(): void
    {
        unset(
            $this->subject,
        );

        parent::tearDown();
    }

    #[Test]
    public function getHiddenInitiallyReturnsFalse(): void
    {
        self::assertFalse(
            $this->subject->getHidden(),
        );
    }

    #[Test]
    public function setHiddenSetsHidden(): void
    {
        $this->subject->setHidden(true);
        self::assertTrue(
            $this->subject->getHidden(),
        );
    }

    #[Test]
    public function getCompanyInitiallyReturnsEmptyString(): void
    {
        self::assertSame(
            '',
            $this->subject->getCompany(),
        );
    }

    #[Test]
    public function setCompanySetsCompany(): void
    {
        $this->subject->setCompany('foo bar');

        self::assertSame(
            'foo bar',
            $this->subject->getCompany(),
        );
    }

    #[Test]
    public function getLogoInitiallyReturnsEmptyArray(): void
    {
        self::assertSame(
            [],
            $this->subject->getLogo(),
        );
    }

    #[Test]
    public function getFirstLogoInitiallyReturnsNull(): void
    {
        self::assertNull(
            $this->subject->getFirstLogo(),
        );
    }

    #[Test]
    public function getOriginalLogoInitiallyReturnsObjectStorage(): void
    {
        self::assertEquals(
            new ObjectStorage(),
            $this->subject->getOriginalLogo(),
        );
    }

    #[Test]
    public function setLogoSetsLogo(): void
    {
        $object = new FileReference();
        $objectStorage = new ObjectStorage();
        $objectStorage->attach($object);
        $this->subject->setLogo($objectStorage);

        self::assertSame(
            $objectStorage,
            $this->subject->getOriginalLogo(),
        );
    }

    #[Test]
    public function addLogoAddsOneLogo(): void
    {
        $objectStorage = new ObjectStorage();
        $this->subject->setLogo($objectStorage);

        $object = new FileReference();
        $this->subject->addLogo($object);

        $objectStorage->attach($object);

        self::assertSame(
            $objectStorage,
            $this->subject->getOriginalLogo(),
        );
    }

    #[Test]
    public function removeLogoRemovesOneLogo(): void
    {
        $object = new FileReference();
        $objectStorage = new ObjectStorage();
        $objectStorage->attach($object);
        $this->subject->setLogo($objectStorage);

        $this->subject->removeLogo($object);
        $objectStorage->detach($object);

        self::assertSame(
            $objectStorage,
            $this->subject->getOriginalLogo(),
        );
    }

    #[Test]
    public function getImagesInitiallyReturnsObjectStorage(): void
    {
        self::assertEquals(
            new ObjectStorage(),
            $this->subject->getOriginalImages(),
        );
    }

    #[Test]
    public function setImagesSetsImages(): void
    {
        $object = new FileReference();
        $objectStorage = new ObjectStorage();
        $objectStorage->attach($object);
        $this->subject->setImages($objectStorage);

        self::assertSame(
            $objectStorage,
            $this->subject->getOriginalImages(),
        );
    }

    #[Test]
    public function addImageAddsOneImage(): void
    {
        $objectStorage = new ObjectStorage();
        $this->subject->setImages($objectStorage);

        $object = new FileReference();
        $this->subject->addImage($object);

        $objectStorage->attach($object);

        self::assertSame(
            $objectStorage,
            $this->subject->getOriginalImages(),
        );
    }

    #[Test]
    public function removeImageRemovesOneImage(): void
    {
        $object = new FileReference();
        $objectStorage = new ObjectStorage();
        $objectStorage->attach($object);
        $this->subject->setImages($objectStorage);

        $this->subject->removeImage($object);
        $objectStorage->detach($object);

        self::assertSame(
            $objectStorage,
            $this->subject->getOriginalImages(),
        );
    }

    #[Test]
    public function getStreetInitiallyReturnsEmptyString(): void
    {
        self::assertSame(
            '',
            $this->subject->getStreet(),
        );
    }

    #[Test]
    public function setStreetSetsStreet(): void
    {
        $this->subject->setStreet('foo bar');

        self::assertSame(
            'foo bar',
            $this->subject->getStreet(),
        );
    }

    #[Test]
    public function getHouseNumberInitiallyReturnsEmptyString(): void
    {
        self::assertSame(
            '',
            $this->subject->getHouseNumber(),
        );
    }

    #[Test]
    public function setHouseNumberSetsHouseNumber(): void
    {
        $this->subject->setHouseNumber('foo bar');

        self::assertSame(
            'foo bar',
            $this->subject->getHouseNumber(),
        );
    }

    #[Test]
    public function getZipInitiallyReturnsEmptyString(): void
    {
        self::assertSame(
            '',
            $this->subject->getZip(),
        );
    }

    #[Test]
    public function setZipSetsZip(): void
    {
        $this->subject->setZip('foo bar');

        self::assertSame(
            'foo bar',
            $this->subject->getZip(),
        );
    }

    #[Test]
    public function getCityInitiallyReturnsEmptyString(): void
    {
        self::assertSame(
            '',
            $this->subject->getCity(),
        );
    }

    #[Test]
    public function setCitySetsCity(): void
    {
        $this->subject->setCity('foo bar');

        self::assertSame(
            'foo bar',
            $this->subject->getCity(),
        );
    }

    #[Test]
    public function getTelephoneInitiallyReturnsEmptyString(): void
    {
        self::assertSame(
            '',
            $this->subject->getTelephone(),
        );
    }

    #[Test]
    public function setTelephoneSetsTelephone(): void
    {
        $this->subject->setTelephone('foo bar');

        self::assertSame(
            'foo bar',
            $this->subject->getTelephone(),
        );
    }

    #[Test]
    public function getFaxInitiallyReturnsEmptyString(): void
    {
        self::assertSame(
            '',
            $this->subject->getFax(),
        );
    }

    #[Test]
    public function setFaxSetsFax(): void
    {
        $this->subject->setFax('foo bar');

        self::assertSame(
            'foo bar',
            $this->subject->getFax(),
        );
    }

    #[Test]
    public function getContactPersonInitiallyReturnsEmptyString(): void
    {
        self::assertSame(
            '',
            $this->subject->getContactPerson(),
        );
    }

    #[Test]
    public function setContactPersonSetsContactPerson(): void
    {
        $this->subject->setContactPerson('foo bar');

        self::assertSame(
            'foo bar',
            $this->subject->getContactPerson(),
        );
    }

    #[Test]
    public function getEmailInitiallyReturnsEmptyString(): void
    {
        self::assertSame(
            '',
            $this->subject->getEmail(),
        );
    }

    #[Test]
    public function setEmailSetsEmail(): void
    {
        $this->subject->setEmail('foo bar');

        self::assertSame(
            'foo bar',
            $this->subject->getEmail(),
        );
    }

    #[Test]
    public function getWebsiteInitiallyReturnsEmptyString(): void
    {
        self::assertSame(
            '',
            $this->subject->getWebsite(),
        );
    }

    #[Test]
    public function setWebsiteSetsWebsite(): void
    {
        $this->subject->setWebsite('foo bar');

        self::assertSame(
            'foo bar',
            $this->subject->getWebsite(),
        );
    }

    #[Test]
    public function getOpeningTimesInitiallyReturnsEmptyString(): void
    {
        self::assertSame(
            '',
            $this->subject->getOpeningTimes(),
        );
    }

    #[Test]
    public function setOpeningTimesSetsOpeningTimes(): void
    {
        $this->subject->setOpeningTimes('foo bar');

        self::assertSame(
            'foo bar',
            $this->subject->getOpeningTimes(),
        );
    }

    #[Test]
    public function getBarrierFreeInitiallyReturnsFalse(): void
    {
        self::assertFalse(
            $this->subject->getBarrierFree(),
        );
    }

    #[Test]
    public function setBarrierFreeSetsBarrierFree(): void
    {
        $this->subject->setBarrierFree(true);
        self::assertTrue(
            $this->subject->getBarrierFree(),
        );
    }

    #[Test]
    public function getDescriptionInitiallyReturnsEmptyString(): void
    {
        self::assertSame(
            '',
            $this->subject->getDescription(),
        );
    }

    #[Test]
    public function setDescriptionSetsDescription(): void
    {
        $this->subject->setDescription('foo bar');

        self::assertSame(
            'foo bar',
            $this->subject->getDescription(),
        );
    }

    #[Test]
    public function getDistrictInitiallyReturnsNull(): void
    {
        self::assertNull($this->subject->getDistrict());
    }

    #[Test]
    public function setDistrictSetsDistrict(): void
    {
        $instance = new District();
        $this->subject->setDistrict($instance);

        self::assertSame(
            $instance,
            $this->subject->getDistrict(),
        );
    }

    #[Test]
    public function getMainTradeInitiallyReturnsEmptyArray(): void
    {
        self::assertSame(
            [],
            $this->subject->getMainTrade(),
        );
    }

    #[Test]
    public function getFirstMainTradeInitiallyReturnsNull(): void
    {
        self::assertNull(
            $this->subject->getFirstMainTrade(),
        );
    }

    #[Test]
    public function getOriginalMainTradeInitiallyReturnsObjectStorage(): void
    {
        self::assertEquals(
            new ObjectStorage(),
            $this->subject->getOriginalMainTrade(),
        );
    }

    #[Test]
    public function setMainTradeSetsMainTrade(): void
    {
        $object = new Category();
        $objectStorage = new ObjectStorage();
        $objectStorage->attach($object);
        $this->subject->setMainTrade($objectStorage);

        self::assertSame(
            $objectStorage,
            $this->subject->getOriginalMainTrade(),
        );
    }

    #[Test]
    public function addMainTradeAddsOneMainTrade(): void
    {
        $objectStorage = new ObjectStorage();
        $this->subject->setMainTrade($objectStorage);

        $object = new Category();
        $this->subject->addMainTrade($object);

        $objectStorage->attach($object);

        self::assertSame(
            $objectStorage,
            $this->subject->getOriginalMainTrade(),
        );
    }

    #[Test]
    public function removeMainTradeRemovesOneMainTrade(): void
    {
        $object = new Category();
        $objectStorage = new ObjectStorage();
        $objectStorage->attach($object);
        $this->subject->setMainTrade($objectStorage);

        $this->subject->removeMainTrade($object);
        $objectStorage->detach($object);

        self::assertSame(
            $objectStorage,
            $this->subject->getOriginalMainTrade(),
        );
    }

    #[Test]
    public function getTradesInitiallyReturnsObjectStorage(): void
    {
        self::assertEquals(
            new ObjectStorage(),
            $this->subject->getOriginalTrades(),
        );

        self::assertSame(
            [],
            $this->subject->getTrades(),
        );
    }

    #[Test]
    public function setTradesSetsTrades(): void
    {
        $object = new Category();
        $objectStorage = new ObjectStorage();
        $objectStorage->attach($object);
        $this->subject->setTrades($objectStorage);

        self::assertSame(
            $objectStorage,
            $this->subject->getOriginalTrades(),
        );
    }

    #[Test]
    public function addTradeAddsOneTrade(): void
    {
        $objectStorage = new ObjectStorage();
        $this->subject->setTrades($objectStorage);

        $object = new Category();
        $this->subject->addTrade($object);

        $objectStorage->attach($object);

        self::assertSame(
            $objectStorage,
            $this->subject->getOriginalTrades(),
        );
    }

    #[Test]
    public function removeTradeRemovesOneTrade(): void
    {
        $object = new Category();
        $objectStorage = new ObjectStorage();
        $objectStorage->attach($object);
        $this->subject->setTrades($objectStorage);

        $this->subject->removeTrade($object);
        $objectStorage->detach($object);

        self::assertSame(
            $objectStorage,
            $this->subject->getOriginalTrades(),
        );
    }

    #[Test]
    public function getFacebookInitiallyReturnsEmptyString(): void
    {
        self::assertSame(
            '',
            $this->subject->getFacebook(),
        );
    }

    #[Test]
    public function setFacebookSetsFacebook(): void
    {
        $this->subject->setFacebook('foo bar');

        self::assertSame(
            'foo bar',
            $this->subject->getFacebook(),
        );
    }

    #[Test]
    public function getTwitterInitiallyReturnsEmptyString(): void
    {
        self::assertSame(
            '',
            $this->subject->getTwitter(),
        );
    }

    #[Test]
    public function setTwitterSetsTwitter(): void
    {
        $this->subject->setTwitter('foo bar');

        self::assertSame(
            'foo bar',
            $this->subject->getTwitter(),
        );
    }

    #[Test]
    public function getInstagramInitiallyReturnsEmptyString(): void
    {
        self::assertSame(
            '',
            $this->subject->getInstagram(),
        );
    }

    #[Test]
    public function setInstagramSetsInstagram(): void
    {
        $this->subject->setInstagram('foo bar');

        self::assertSame(
            'foo bar',
            $this->subject->getInstagram(),
        );
    }

    #[Test]
    public function getFeUserInitiallyReturnsNull(): void
    {
        self::assertNull($this->subject->getFeUser());
    }

    #[Test]
    public function setFeUserSetsFeUser(): void
    {
        $instance = new FeUser();
        $this->subject->setFeUser($instance);

        self::assertSame(
            $instance,
            $this->subject->getFeUser(),
        );
    }
}
