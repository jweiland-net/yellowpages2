<?php

/*
 * This file is part of the package jweiland/yellowpages2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Yellowpages2\Domain\Model;

use JWeiland\Maps2\Domain\Model\PoiCollection;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\Exception\AspectNotFoundException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Annotation as Extbase;
use TYPO3\CMS\Extbase\Annotation\ORM\Lazy;
use TYPO3\CMS\Extbase\Domain\Model\Category;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Domain model for companies
 */
class Company extends AbstractEntity
{
    protected bool $hidden = false;

    #[Extbase\Validate(['validator' => 'NotEmpty'])]
    protected string $company = '';

    protected string $pathSegment = '';

    /**
     * @var ObjectStorage<FileReference>
     */
    #[Lazy]
    protected $logo;

    /**
     * @var ObjectStorage<FileReference>
     */
    #[Lazy]
    protected $images;

    #[Extbase\Validate(['validator' => 'NotEmpty'])]
    protected string $street = '';

    #[Extbase\Validate(['validator' => 'NotEmpty'])]
    protected string $houseNumber = '';

    #[Extbase\Validate(['validator' => 'NotEmpty'])]
    protected string $zip = '';

    #[Extbase\Validate(['validator' => 'NotEmpty'])]
    protected string $city = '';

    protected string $telephone = '';

    protected string $fax = '';

    protected string $contactPerson = '';

    protected string $email = '';

    protected string $website = '';

    protected string $openingTimes = '';

    protected bool $barrierFree = false;

    #[Extbase\Validate(['validator' => 'NotEmpty'])]
    protected string $description = '';

    #[Extbase\Validate(['validator' => 'NotEmpty'])]
    protected ?District $district = null;

    /**
     * @var ObjectStorage<Category>
     */
    #[Extbase\Validate(['validator' => 'NotEmpty'])]
    #[Lazy]
    protected ObjectStorage $mainTrade;

    /**
     * @var ObjectStorage<Category>
     */
    #[Lazy]
    protected ObjectStorage $trades;

    protected string $facebook = '';

    protected string $twitter = '';

    protected string $instagram = '';

    protected ?PoiCollection $txMaps2Uid = null;

    protected ?FeUser $feUser = null;

    public function __construct()
    {
        $this->logo = new ObjectStorage();
        $this->images = new ObjectStorage();
        $this->mainTrade = new ObjectStorage();
        $this->trades = new ObjectStorage();
    }

    /**
     * Called again with initialize object, as fetching an entity from the DB does not use the constructor
     */
    public function initializeObject(): void
    {
        $this->logo ??= new ObjectStorage();
        $this->images ??= new ObjectStorage();
        $this->mainTrade ??= new ObjectStorage();
        $this->trades ??= new ObjectStorage();
    }

    public function getHidden(): bool
    {
        return $this->hidden;
    }

    public function setHidden(bool $hidden): void
    {
        $this->hidden = $hidden;
    }

    public function getCompany(): string
    {
        return $this->company;
    }

    public function setCompany(string $company): void
    {
        $this->company = $company;
    }

    public function getPathSegment(): string
    {
        return $this->pathSegment;
    }

    public function setPathSegment(string $pathSegment): void
    {
        $this->pathSegment = $pathSegment;
    }

    /**
     * @return FileReference[]
     */
    public function getLogo(): array
    {
        return $this->logo->toArray();
    }

    public function getFirstLogo(): ?FileReference
    {
        return current($this->getLogo()) ?: null;
    }

    public function getOriginalLogo(): ObjectStorage
    {
        return $this->logo;
    }

    public function setLogo(ObjectStorage $logo): void
    {
        $this->logo = $logo;
    }

    public function addLogo(FileReference $logo): void
    {
        $this->logo->attach($logo);
    }

    public function removeLogo(FileReference $logo): void
    {
        $this->logo->detach($logo);
    }

    /**
     * @return array|FileReference[]
     */
    public function getImages(): array
    {
        return $this->images->toArray();
    }

    /**
     * @return ObjectStorage|\TYPO3\CMS\Core\Resource\FileReference[]
     */
    public function getOriginalImages(): ObjectStorage
    {
        return $this->images;
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

    public function setDistrict(?District $district): void
    {
        $this->district = $district;
    }

    /**
     * @return Category[]
     */
    public function getMainTrade(): array
    {
        return $this->mainTrade->toArray();
    }

    public function getFirstMainTrade(): ?Category
    {
        if (!$this->mainTrade instanceof ObjectStorage || $this->mainTrade->count() === 0) {
            return null;
        }

        return current($this->getMainTrade()) ?: null;
    }

    public function getOriginalMainTrade(): ObjectStorage
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

    /**
     * @return Category[]
     */
    public function getTrades(): array
    {
        return $this->trades->toArray();
    }

    public function getOriginalTrades(): ObjectStorage
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

    public function getInstagram(): string
    {
        return $this->instagram;
    }

    public function setInstagram(string $instagram): void
    {
        $this->instagram = $instagram;
    }

    /**
     * SF: Do not add PoiCollection as strict_type to $txMaps2Uid
     * as this will break DataMap in Extbase when maps2 is not installed.
     */
    public function getTxMaps2Uid()
    {
        return $this->txMaps2Uid;
    }

    public function hasTxMaps2Uid(): bool
    {
        return $this->txMaps2Uid instanceof PoiCollection;
    }

    public function setTxMaps2Uid($txMaps2Uid): void
    {
        if ($txMaps2Uid instanceof PoiCollection) {
            $this->txMaps2Uid = $txMaps2Uid;
        }
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
     */
    public function getHasValidUser(): bool
    {
        try {
            $frontendUser = GeneralUtility::makeInstance(Context::class)
                ->getPropertyFromAspect('frontend.user', 'id');

            if ($frontendUser > 0 && $this->feUser instanceof FeUser && $this->feUser->getUid() > 0) {
                return (int)$frontendUser === $this->feUser->getUid();
            }
        } catch (AspectNotFoundException $e) {
            return false;
        }

        return false;
    }

    /**
     * Helper method to get the address of the record.
     * This is needed by Google Maps geocode API
     */
    public function getAddress(): string
    {
        return $this->getStreet() . ' ' . $this->getHouseNumber() . ', ' . $this->getZip() . ' ' . $this->getCity();
    }

    /**
     * Helper method to build a baseRecord for path_segment
     * Needed in PathSegmentHelper
     */
    public function getBaseRecordForPathSegment(): array
    {
        return [
            'uid' => $this->getUid(),
            'pid' => $this->getPid(),
            'company' => $this->getCompany(),
        ];
    }
}
