..  include:: /Includes.rst.txt


..  _changelog:

=========
ChangeLog
=========

Version 11.0.0
==============

*   [!!!][TASK] Add compatibility for TYPO3 14 LTS and remove support for TYPO3 13 LTS
*   [!!!][TASK] Require EXT:maps2 13 and EXT:glossary2 8. Both are compatible with TYPO3 14 only.
*   [!!!][TASK] The site set `jweiland/yellowpages2` depends on the site set `jweiland/glossary2` now. Since glossary2 8.0.0 the A-Z list needs its TypoScript and site settings.
*   [!!!][TASK] The form setup of the company form is a form set now (:file:`Configuration/Form/Yellowpages2/config.yaml`), which EXT:form discovers automatically. :file:`Configuration/Form/OverrideFormSettings.yaml` and its registration via `plugin.tx_form.settings.yamlConfigurations` have been removed.
*   [!!!][TASK] Declare the column `tx_maps2_uid` with the TCA renderType `maps2Relation` of maps2 13 instead of `Maps2Registry::add()`
*   [!!!][TASK] `Company::getTxMaps2Uid()` and `Company::setTxMaps2Uid()` are typed with `?PoiCollection` now
*   [TASK] Send notification mails via `MailerInterface`, as `MailMessage::send()` has been removed
*   [TASK] Replace removed `FlexFormService` with `FlexFormTools` and use `GridColumnItem::getRow()` in the backend preview
*   [TASK] Use `getSubSchemaTypeInformation()` of the TCA schema, as `getSubSchemaDivisorField()` has been removed
*   [TASK] Replace deprecated `PathUtility::getPublicResourceWebPath()` with the system resource API
*   [TASK] Register the FlexForms as 7th argument of `ExtensionUtility::registerPlugin()` instead of the deprecated `addPiFlexFormValue()` and drop the default plugin type argument of `configurePlugin()`
*   [TASK] Place the Extbase `#[Validate]` and `#[IgnoreValidation]` attributes on the method parameters
*   [TASK] Migrate upgrade wizards to the `TYPO3\CMS\Core\Upgrades` namespace and the `TYPO3\CMS\Core\Attribute\UpgradeWizard` attribute
*   [TASK] Replace the TCA `ctrl` option `searchFields` of company and district, which has been removed in TYPO3 14, with `searchable => false` on the fields that were not searchable before
*   [TASK] Keep `tx_maps2_uid` as unsigned integer column via ext_tables.sql
*   [TASK] Add `version` and `providesPackages` to composer.json
*   [!!!][BUGFIX] Generate the moderation tokens of the mail links as HMAC with SHA3-256. Tokens of mails sent before this version (SHA1) are accepted during version 11.x, but each use is logged as notice. Support for SHA1 tokens will be removed in version 12.
*   [BUGFIX] Log rejected moderation tokens, unauthorized access to foreign companies and rejected submissions of the company form as warning with the UID of the company and the frontend user, without personal data
*   [BUGFIX] Escape the wildcards `%` and `_` of the search term in the company search, so they can not be used to match all companies
*   [BUGFIX] Do not list companies without owner (`fe_user = 0`) to visitors who are not logged in in the action `listMyCompanies`. The action checks login and user group in the controller now, not in the template only.
*   [BUGFIX] Add `CompanyRepository::findByFeUser()`. The magic finder of Extbase has been removed, so the action `listMyCompanies` caused a fatal error.
*   [BUGFIX] Respond with 404 for the action `show` of an unknown, hidden or deleted company instead of a fatal error
*   [!!!][TASK] Pass the request explicitly to `RequestUtility::getMergedRequestArguments()` and to `CompanyPagination`. `RequestUtility::getRequestFromGlobalVariable()` and the access to `$GLOBALS['TYPO3_REQUEST']` have been removed. Custom pagination classes (`pageBrowser.class`) get the request as 3rd constructor argument.
*   [!!!][TASK] `ManagementController` has the `LoggerInterface` as additional constructor argument and does not need `Context` anymore. `MailHelper::sendMail()` returns a boolean and `NotificationMailer` has the `LoggerInterface` as additional constructor argument. Adapt XCLASSes.
*   [TASK] Show translated flash messages with the severity of the message for all rejections of the Management plugin: not logged in, wrong user group, foreign company and invalid moderation link. The activation of a company shows success, "already activated", invalid link and a warning, if the notification mail to the moderator could not be sent.
*   [TASK] Log failed mails (`NotificationMailer`, `MailHelper`), addresses that could not be geocoded, missing files of a company and an unavailable TypoScript setup via the PSR-3 logger
*   [TASK] Do not abort the update command, if a notification mail can not be sent. The error is logged.
*   [TASK] Use TYPO3 14 translation domains (`yellowpages2.db:`, `yellowpages2.messages:`, `yellowpages2.flex_forms:`) instead of `LLL:EXT:` paths and add `target-language` to the German XLIFF files
*   [!!!][TASK] Rename all Fluid templates, partials and layouts to `*.fluid.html` (`*.fluid.txt` for the text part of the moderation mail). Templates of your sitepackage with the old file extension still override them, as Fluid 5 checks both. Update paths that reference a template file directly, e.g. in your own preview or mail configuration.
*   [TASK] Mark classes as `final` except classes that integrators may XCLASS (controllers, repositories, models, `NotificationMailer`, `MailHelper`, `CompanyPagination`, `RichTextEditorOptions`)
*   [TASK] Remove `phpstan/phpdoc-parser` from the dev requirements and the unused php-cs-fixer configuration :file:`Build/php-cs-fixer/config.php`
*   [TASK] Use real objects instead of mocks of own classes in tests and add tests for tokens, flash messages, logging and request handling
*   [TASK] Adapt tests to TYPO3 14 (honeypot in form state, SHA3-256 HMAC of the form state, handling of `FinisherException`, final `GlossaryService`)

Version 10.0.1
==============

*   [BUGFIX] Render the links and texts of the text part of the moderation mail (`CompanyModeration.txt`) without HTML entities. The links contained `&amp;` instead of `&`, so the copied links did not work in mail clients, which show the text part only.

Version 10.0.0
==============

*   [!!!][TASK] Create and edit companies in the frontend with a form based on EXT:form. The form has two steps: the company data and the position of the company on the map (form element `Maps2PoiPosition` of EXT:maps2 12.3.0). The address is geocoded when the first step is submitted.
*   [!!!][TASK] Remove the Extbase form of the Management plugin incl. MapController, LocationService, their templates and the event listeners for uploads
*   [!!!][TASK] Require EXT:form and EXT:maps2 12.3.0 or higher
*   [FEATURE] Store the description of the company form like the backend RTE does and convert it back to plain text for editing
*   [FEATURE] Add CKEditor 5 to the description of the company form (site setting `yellowpages2.form.richText.enable`)
*   [FEATURE] Load the assets of the rich text editor with the overridable Fluid partials `RichTextEditor/Textarea.html` (`renderingOptions.templateName` of rich text elements) and `RichTextEditor/LoadAssets.html` of the company form. The folder of the translations of CKEditor is the site setting `yellowpages2.form.richText.translationsPath`. `RichTextEditorOptions` builds the options of the editor only.
*   [FEATURE] Make the tags of the rich text sanitizer configurable with the PSR-14 event `ModifyRichTextSanitizerConfigurationEvent` and build the toolbar of the editor from them
*   [FEATURE] Render rich text elements in the mail of the moderator with the `FormatRichTextViewHelper`
*   [!!!][TASK] Use Bootstrap 5 classes in the frontend templates: pagination, images, search form, links and messages. Remove the icon spans of an old theme. See :ref:`upgrade-10-0-0-bootstrap`.
*   [BUGFIX] Do not emit PHP warnings for missing settings `presetTrade` and `district` in the list of companies
*   [BUGFIX] Select the categories of the search form with a sub query, which is valid with the SQL mode `only_full_group_by` of MySQL and on PostgreSQL
*   [BUGFIX] Read sender and recipient of the moderation mail from the extension settings only, if the form definition does not set them
*   [TASK] Render the links to create a company and to list the own companies above the search form, left aligned like the edit link of a company
*   [!!!][TASK] Activate companies via a confirmation page (action `confirmActivation`) and POST only. The result is shown as flash message of the Directory plugin. Remove the `RestrictAccessEventListener`.

Version 9.0.2
=============

*   [BUGFIX] Generate path segment for new companies created in the frontend. The former event listener was called before the company was persisted and therefore never generated a path segment. Run the upgrade wizard "[yellowpages2] Update slug of company records" to fill empty path segments of existing companies.

Version 9.0.1
=============

*   [BUGFIX] Apply checkfaluploads validator, upload folder and path segment for actions of ManagementController
*   [BUGFIX] Process trades and search requests of the Management and Search plugin in ControllerActionsMiddleware
*   [BUGFIX] Paginate search results of SearchController and keep the search arguments in the page browser links
*   [BUGFIX] Fix forward to ManagementController::newAction() and redirect after activation to the Directory plugin
*   [BUGFIX] Link to the Management, Search and Directory plugin with pluginName and pageUid in templates and mails
*   [BUGFIX] Add site settings `yellowpages2.pidOfManagementPage` and `yellowpages2.pidOfSearchResults`
*   [BUGFIX] Ignore empty FlexForm values for pidOfDetailPage, pidOfListPage and pidOfSearchResults
*   [TASK] Remove InitializeActionTrait, Fluid link ViewHelpers fall back to the current page for pageUid 0
*   [TASK] Require glossary2 7.1.0 or higher, which declares a generic QueryInterface for GlossaryService

Version 9.0.0
=============

*   [SECURITY] Require a signed moderation token for the "activate" action to prevent unauthenticated activation of arbitrary companies
*   [BUGFIX] Keep company record hidden until the POI position is confirmed on the map
*   [BUGFIX] Fix broken uniqueness check in Yellowpages2SlugUpdate (Doctrine DBAL Result misuse)
*   [BUGFIX] Fix storagePid Site Set setting type (string instead of page)
*   [BREAKING] Migrate company logo/images upload to the native Extbase FileUpload API, remove UploadMultipleFilesConverter and AssignMediaTypeConverterEventListener
*   [BREAKING] Consolidate Company::getOriginalLogo()/getOriginalImages()/getOriginalMainTrade()/getOriginalTrades() into getLogo()/getImages()/getMainTrade()/getTrades(), now returning ObjectStorage instead of array
*   [BREAKING] Remove legacy Configuration/TypoScript static template registration in favor of the Yellowpages2 Site Set

Version 8.1.3
=============

*   [BUGFIX] Fix broken hidden object registration in HiddenObjectHelper
*   [BUGFIX] Use type page for PID site settings
*   [BUGFIX] Load maps2 before yellowpages2 if present
*   [BUGFIX] Remove now auto-created system TCA columns and ext_tables.sql
*   [BUGFIX] Remove dead code in ext_localconf.php and Services.php
*   [TASK] Use AsEventListener attribute instead of Services.yaml tags
*   [TASK] Make Events, EventListeners and Hook final and readonly
*   [TASK] Exclude Domain Models from DI autoconfiguration

Version 8.1.2
=============

*   [TASK] Add RestrictAccessEventListener to check update actions against unauthorised edits by non-owner fe_users

Version 8.1.1
=============

*   [BUGFIX] Replace hardcoded tab labels with localized labels in tt_content

Version 8.1.0
=============

*   [TASK] Remove POI creation logic from MapController
*   [TASK] Delegate POI creation to LocationService in CompanyController
*   [TASK] Refactor: Extract POI creation to LocationService
*   [TASK] Centralize extension key usage via ExtConf::EXT_KEY
*   [TASK] Add localized labels for invalid address errors
*   [TASK] Refactor ExtConf to modern immutable DI with factory method
*   [TASK] Refactor MailHelper to use MailerInterface and fresh MailMessage
*   [BUGFIX] Add maps2 partials to Yellowpages2 view paths
*   [BUGFIX] Check for Company UID before path segment update
*   [BUGFIX] Ensure company is hidden if POI mapping fails in newAction

Version 8.0.4
=============

*   [TASK] Updated wizard title with [extension] name format

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
