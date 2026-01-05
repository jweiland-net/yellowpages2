..  include:: /Includes.rst.txt


..  _changelog:

=========
ChangeLog
=========

Version 8.0.3
=============

*   [BUGFIX] Remove deprecated usage of SoftRef parser: images

Version 8.0.2
=============

*   [BUGFIX] Fixed Property Mapper with CheckFal validation issues

Version 8.0.1
=============

*   [BUGFIX] TCA migration fixes

Version 8.0.0
=============

*   Compatibility fixes for TYPO3 CMS 13.
*   TASK: Migrate all scheduler tasks to TYPO3 CLI Symfony Commands.
*   TASK: Migrate all list type general plugins to TYPO3 CType.
*   TASK: Created migration script for list_type to new CType.
*   Deprecated methods replaced.
*   Removed support for TYPO3 12 LTS

Version 7.0.2
=============

*   Removed Dialogue.js (jquery ui dependent) and it's inclusion from TypoScript
*   TASK: Remove DeleteUploadedFilesEventListener as invalid images already
    will not be assigned to company in TypeConverter.

Version 7.0.1
=============

*   BUGFIX: Category parent property lazy loading with LazyLoadingProxy type

Version 7.0.0
=============

*   Compatibility fixes for TYPO3 12 LTS
*   New Middleware implementations related to company create form
*   New Test cases for testing Middleware
*   Removed hard dependency with extension maps2
*   Testing Framework migrated to TYPO3 Testing Framework
*   Deprecated methods replaced
*   Removed support for TYPO3 11 LTS
*   BUGFIX: Fix Issue with Search Filter not repects district filter

Version 6.1.4
=============

*   BUGFIX: Fix for taking care of district FlexForm configuration

Version 6.1.3
=============

*   TASK: Add .crowdin.yml to .gitattributes

Version 6.1.2
=============

*   BUGFIX: Add tablemapping for FeUser object

Version 6.1.1
=============

*   Order list of categories by their title in search partial
*   Repair fetchRelated method to also fetch company records by trades
*   Prevent cartesian product in fetchRelated method of CategoryRepository

Version 6.1.0
=============

*   Migrate repo back to Extbase Query
*   Simplify implementation of own Pagination with TypoScript

Version 6.0.0
=============

*   Add TYPO3 11 compatibility
*   Replace controller constructors with inject methods
*   Add and update tests for TYPO3 11
*   Update structure of documentation
*   Migrate makeCategorizeable to TCA directly
*   Add defaults in TCA and ext_tables.sql

Version 5.1.1
=============

*   Add TS settings for GlossaryService

Version 5.1.0
=============

*   Use inject methods in controllers
*   Update dependency for glossary2 to 5.0.0

Version 5.0.2
=============

*   User should not get admin mail to activate entry on its own.

Version 5.0.1
=============

*   Move redirects to MapController back to CompanyController

Version 5.0.0
=============

*   Remove TYPO3 9 compatibility
*   Add TYPO3 10 compatibility
*   Replace f:widget.paginate with our own paginator
*   Restructure controllers. Move recurring code into EventListeners
*   Move SlugHelper from constructor argument into getSlugHelper()

Version 4.0.26
==============

*   Remove empty trades from request before ProperyMapping

Version 4.0.23
==============

*   Add activate action to non-cachable actions

Version 4.0.22
==============

*   Use a counter in URL for duplicate companies in Slug UpdateWizard

Version 4.0.21
==============

*   Update translation

Version 4.0.20
==============

*   Remove title from SVG icons

Version 4.0.19
==============

*   Use errorAction to redirect to previous action on Geocoding error

Version 4.0.18
==============

*   Repair new action, if POI could not be created
*   Add Error FlashMessage, why POI could not be created

Version 4.0.17
==============

*   Use correct logo/image TypeConverter of yellowpages2

Version 4.0.16
==============

*   Remove GROUP BY from query, as it is not compatible with pageBrowser

Version 4.0.15
==============

*   Replace Google+ column with Instagram

Version 4.0.14
==============

*   Remove injection of ExtConf of maps2 in MapController

Version 4.0.13
==============

*   Add getter to get first main trade

Version 4.0.12
==============

*   Better structure of getGlossar()
*   Use SchemaManager to build column names in Repo
*   Add String validator to listAction()

Version 4.0.11
==============

*   Wrap Fluid Templates with html namespace
*   Return one logo when using getLogo
*   Differ between image sizes for list and show view

Version 4.0.10
==============

*   Add Services.yaml for TYPO3 10 compatibility

Version 4.0.9
=============

*   Remove useless @param from getMaps2Uid

Version 4.0.8
=============

*   Allow max one Image for Logo

Version 4.0.7
=============

*   Set slug field separator to -

Version 4.0.6
=============

*   Add uid to generated slugs in UpgradeWizard

Version 4.0.5
=============

*   Add path_segment for human readable URLs to company records
*   Add Slug UpgradeWizard

Version 4.0.4
=============

*   Add settings to CompanyRepository SignalSlots

Version 4.0.3
=============

*   Set default sorting to company for QueryBuilder based statements

Version 4.0.2
=============

*   Change return value of mainTrade to nullable Category
*   Change return value of trades to array

Version 4.0.1
=============

*   Allow null for Distinct in Company model

Version 4.0.0
=============

*   Add TYPO3 10 compatibility
*   Remove TYPO3 8 compatibility
*   Update all PHP DocHeaders
*   Add strict types where possible
*   Add documentation
*   Remove sys_category icon implementation.
*   Move ExtIcon to Resources/Public/Icons
*   Update UnitTests
*   Remove maps2 as requirement.

Version 3.0.0
=============

*   Add TYPO3 9 compatibility
*   Remove @return void lines
*   Set description as required
*   Update to new GeoCode Service of maps2

Version 2.2.3
=============

*   Move all TCA modifying methods to TCA/Overrides

Version 2.2.2
=============

*   Remove idea directory
*   Remove default pageTSconfig for maps2
