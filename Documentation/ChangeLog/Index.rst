.. include:: ../Includes.txt


.. _changelog:

=========
ChangeLog
=========

**Version 4.0.10**

- Add Services.yaml for TYPO3 10 compatibility

**Version 4.0.9**

- Remove useless @param from getMaps2Uid

**Version 4.0.8**

- Allow max one Image for Logo

**Version 4.0.7**

- Set slug field separator to -

**Version 4.0.6**

- Add uid to generated slugs in UpgradeWizard

**Version 4.0.5**

- Add path_segment for human readable URLs to company records
- Add Slug UpgradeWizard

**Version 4.0.4**

- Add settings to CompanyRepository SignalSlots

**Version 4.0.3**

- Set default sorting to company for QueryBuilder based statements

**Version 4.0.2**

- Change return value of mainTrade to nullable Category
- Change return value of trades to array

**Version 4.0.1**

- Allow null for Distinct in Company model

**Version 4.0.0**

- Add TYPO3 10 compatibility
- Remove TYPO3 8 compatibility
- Update all PHP DocHeaders
- Add strict types where possible
- Add documentation
- Remove sys_category icon implementation.
- Move ExtIcon to Resources/Public/Icons
- Update UnitTests
- Remove maps2 as requirement.

**Version 3.0.0**

- Add TYPO3 9 compatibility
- Remove @return void lines
- Set description as required
- Update to new GeoCode Service of maps2

**Version 2.2.3**

- Move all TCA modifying methods to TCA/Overrides

**Version 2.2.2**

- Remove idea directory
- Remove default pageTSconfig for maps2
