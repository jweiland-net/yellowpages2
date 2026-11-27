..  include:: /Includes.rst.txt


..  _upgrade:

========
Updating
========

If you update EXT:yellowpages2 to a newer version, please read this section carefully!

Upgrade to version 11.0.0
=========================

This version is compatible with TYPO3 14 LTS only.

*   EXT:maps2 13 (:composer:`jweiland/maps2`) and EXT:glossary2 8
    (:composer:`jweiland/glossary2`) are required.
*   The site set `jweiland/yellowpages2` depends on the site set
    `jweiland/glossary2` now. If you include the TypoScript of yellowpages2
    without the site set, include the TypoScript and site settings of
    EXT:glossary2, too. Otherwise the A-Z list can not be rendered.
*   The form setup of the company form is the form set
    :file:`EXT:yellowpages2/Configuration/Form/Yellowpages2/config.yaml` now,
    which EXT:form discovers automatically. The file
    :file:`Configuration/Form/OverrideFormSettings.yaml` has been removed.
    Remove references to it from your own TypoScript. As all form sets, it is
    loaded for all sites and the form manager of the backend. To disable it,
    add `jweiland/yellowpages2` to
    :php:`$GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['form']['disabledSets']`.
*   The column `tx_maps2_uid` of the company table is declared with the TCA
    renderType `maps2Relation` of EXT:maps2 13. Its database type stays
    an unsigned integer (:file:`ext_tables.sql`).
*   The TCA option `ctrl.searchFields` has been removed in TYPO3 14. The fields
    `path_segment`, `facebook`, `twitter` and `instagram` are marked as not
    searchable, so the backend search covers the same fields as before.

Upgrade to version 10.0.0
=========================

Companies are created and edited in the frontend with a form based on
EXT:form now, see :ref:`company-form`. The Extbase form of the `Management`
plugin has been removed.

Requirements
------------

*   The system extension EXT:form (:composer:`typo3/cms-form`) is required.
    The site set `jweiland/yellowpages2` depends on the site set
    `typo3/form` now, which provides :typoscript:`plugin.tx_form` in the
    frontend. If you include the TypoScript of yellowpages2 without the
    site set, include the TypoScript of EXT:form, too.
*   EXT:maps2 12.3.0 or higher (:composer:`jweiland/maps2`) is required. It
    was optional before. Its form element `Maps2PoiPosition` renders the map
    step of the company form. The site set `jweiland/yellowpages2` depends on
    the site set `jweiland/maps2-default` now. Configure the map provider of
    EXT:maps2 as described in its documentation.

Plugin configuration
--------------------

*   The `Management` plugin has the actions `listMyCompanies`, `perform`,
    `confirmActivation` and `activate`. The actions `new`, `create`, `edit` and `update` of the
    `ManagementController` and the complete `MapController` (actions `new`,
    `create`, `edit` and `update`) have been removed. Links to them do not
    work anymore.
*   Link to the action `perform` to create a company and to the action
    `perform` with the argument `company` to edit a company:

    ..  code-block:: html

        <f:link.action action="perform"
                       controller="Management"
                       pluginName="Management"
                       pageUid="{settings.pidOfManagementPage}"
                       arguments="{company: company}">
            Edit company
        </f:link.action>

*   Logged-in frontend users need to be member of the user group of
    `yellowpages2.userGroup` to create companies. Before, everyone who could
    open the page could create a company.

Activation of companies
-----------------------

The activation link of the moderation mail opens the new action
`confirmActivation`, which shows a summary of the company and a button.
The button activates the company via POST. The action `activate` does not
activate the company via GET anymore, but redirects to the confirmation
page, so mail security scanners and link previews can not activate
companies. The result is shown as flash message of the `Directory` plugin.
If you have overridden `Templates/Company/List.html`, keep
:html:`<f:flashMessages/>` in it.

The `RestrictAccessEventListener` has been removed. The actions
`confirmActivation` and `activate` check the `moderationToken` on their
own and show an error message, if it is missing or invalid.

Two steps instead of a separate map step
----------------------------------------

The map step of the `MapController` is the second page of the company form
now. The address is geocoded when the first page is submitted, the user can
correct the position on the second page. The company is stored only once,
after the second page was submitted:

*   A new company is no longer stored before the map step. So no hidden
    company without confirmed position is left behind, if the user leaves
    the form on the map step.
*   Each company created **or edited** in the frontend is hidden and the
    moderator gets a mail with links to edit and to activate it. Before, an
    edited company was only hidden and announced, if the map step was
    completed. Edits of moderators with the link of the mail are handled the
    same way, so the moderator activates the company afterwards.
*   The mail of the moderator is rendered with the templates of the
    EXT:form finisher `EmailToReceiver` (see :ref:`company-form-moderation`)
    and contains a text part now.
*   The POI collection of a new company is created with the corrected
    position. The POI collection of an edited company is updated, instead
    of creating a new one.

..  _upgrade-10-0-0-bootstrap:

Frontend markup uses Bootstrap 5
--------------------------------

The templates of yellowpages2 and the company form use the classes of
Bootstrap 5. On a page with Bootstrap 5 the output works without own CSS
or template overrides. The company form uses the template variant
`version2` of EXT:form, see :ref:`company-form-markup`.

The following classes have been replaced in the templates and partials
of the plugins. Update your overrides and your CSS:

..  list-table::
    :header-rows: 1

    *   -   Before
        -   Now
    *   -   `f3-widget-paginator` of the pagination
        -   `nav` with `aria-label`, `pagination`, `page-item` and
            `page-link`
    *   -   `thumbnail grid_1 floatleft` of the logo
        -   `float-start me-3 mb-3`, the image gets
            `img-fluid img-thumbnail`
    *   -   `floatright` of the map image
        -   `float-end img-fluid`
    *   -   images of the company without class
        -   `img-fluid img-thumbnail` in an `ul.list-unstyled`
    *   -   search form without classes
        -   `mb-3`, `form-label`, `form-select`, `form-control` and
            `btn btn-primary`. The select and the text field got labels.
    *   -   `noResults`
        -   `alert alert-info noResults`
    *   -   submit button of the activation without class
        -   `btn btn-primary`

The icon spans with the classes `icon-arrowright`, `icon-phone_pf`,
`icon-fax_pf`, `icon-email` and `icon-earth` have been removed, as
Bootstrap 5 contains no icon font. Telephone and fax number are prefixed
with the labels `Telephone` and `Fax` instead of the icons. Add your own
icons in overrides of the templates, if needed. The new label `pageBrowser`
is the `aria-label` of the pagination.

The links to create a company and to list the own companies are rendered
above the search form instead of below the list, left aligned like the
edit link of a company. If you override
:file:`Templates/Company/List.html`, move the partial
`Company/InsertEditLinks` accordingly.

Removed templates
-----------------

If you have overridden one of these templates or partials in your site
package, remove them. Adapt the form with a copy of
:file:`EXT:yellowpages2/Configuration/Form/Company.form.yaml` instead (see
:ref:`company-form-configuration`).

*   `Templates/Management/New.html`, `Templates/Management/Edit.html`
*   `Templates/Map/New.html`, `Templates/Map/Create.html`,
    `Templates/Map/Edit.html`, `Templates/Map/Update.html` (the moderation
    mail is :file:`Resources/Private/FormFramework/Templates/Finishers/Email/CompanyModeration.html`
    and :file:`CompanyModeration.txt` now)
*   `Partials/Company/FormFields.html`, `Partials/Company/Field.html`,
    `Partials/Company/Fields/*.html`, `Partials/FormErrors.html`

If you have overridden `Partials/Company/Properties.html` or
`Partials/Company/InsertEditLinks.html`, change the action of the links
from `edit` and `new` to `perform`. The partial root path
`EXT:maps2/Resources/Private/Partials/` has been removed from
:typoscript:`plugin.tx_yellowpages2.view.partialRootPaths`.

TypoScript and site settings
----------------------------

*   New site settings `yellowpages2.form.storagePid`,
    `yellowpages2.form.richText.enable` and
    `yellowpages2.form.richText.elements`, see :ref:`site-set-settings` and
    :ref:`frontend-rich-text-editor`.
*   New TypoScript :typoscript:`plugin.tx_yellowpages2.settings.overrideForm`
    to override values of the form definition, see
    :ref:`company-form-configuration`.
*   :typoscript:`plugin.tx_yellowpages2.settings.new.uploadFolder` is the
    folder of the upload elements of the form now. Its default value is
    `1:/user_upload/tx_yellowpages2/`.
*   :typoscript:`plugin.tx_yellowpages2._CSS_DEFAULT_STYLE` has been removed.
    The form is rendered with the Bootstrap 5 template variant `version2` of
    EXT:form.

Removed PHP code
----------------

*   :php:`MapController`, :php:`LocationService`,
    :php:`ApplyUploadFolderEventListener`,
    :php:`CheckFalUploadsEventListener` and
    :php:`RemoveEmptyTradesModifier` have been removed.
*   :php:`PostProcessControllerActionEvent::getMapController()` has been
    removed.
*   :php:`PathSegmentHelper::updatePathSegmentForCompany()` has been removed.
    Use :php:`PathSegmentHelper::generatePathSegment()`.
*   The `#[FileUpload]` and `#[Validate]` attributes of the
    :php:`Company` model have been removed. The values of the form are
    validated by the validators of the form definition.
*   The rights checkbox of EXT:checkfaluploads has to be added to a copy of
    the form definition, see :ref:`company-form-checkfaluploads`.

Labels
------

The labels `create`, `update`, `companyCreated`, `companyUpdated`,
`pleaseChoose`, `hint`, `error.upload`, `error.fileExtension`,
`errorFlashMessage`, `errorAddressInvalid.title`, `email.subject.create`,
`email.subject.update` and all `csh*` labels have been removed from
:file:`locallang.xlf`. The labels of the form are part of
:file:`locallang_form.xlf`.

Upgrade to version 9.0.1
========================

Since version 9.0.0 the frontend functionality is split into three plugins: `Directory`
(`CompanyController`: list, show), `Management` (`ManagementController` and `MapController`:
create, edit and moderate companies) and `Search` (`SearchController`). Links between these
plugins need the target plugin and page. Configure the new site settings
`yellowpages2.pidOfManagementPage` and `yellowpages2.pidOfSearchResults` (see
:ref:`site-set-settings`) to the pages where the `Management` and `Search` plugins are inserted.

If you have overridden one of the following templates in your own site package, add `pluginName`
and `pageUid` to the links and forms targeting another plugin:

*   `Partials/Company/InsertEditLinks.html`, `Partials/Company/Properties.html`:
    `pluginName="Management" pageUid="{settings.pidOfManagementPage}"`
*   `Partials/Company/Search.html`:
    `pluginName="Search" pageUid="{settings.pidOfSearchResults}"`
*   `Partials/Company/List.html`, `Templates/Company/Show.html`,
    `Templates/Management/ListMyCompanies.html`: `pluginName="Directory"`
*   `Templates/Map/Create.html`, `Templates/Map/Update.html` (moderation mail): change
    `controller="Company"` to `controller="Management"` and add
    `pluginName="Management" pageUid="{settings.pidOfManagementPage}"`

..  code-block:: html

    <f:link.action action="edit"
                   controller="Management"
                   pluginName="Management"
                   pageUid="{settings.pidOfManagementPage}"
                   absolute="1"
                   arguments="{company: company, moderationToken: moderationToken}">
        Edit record
    </f:link.action>

Upgrade to version 9.0.0
========================

This version closes a security issue and changes how company logo and image uploads are handled.

The `activate` action, used by the link in the administrator notification email to publish a
company record straight away, previously had no authorization check at all. Anyone who knew or
guessed a company's UID could activate it. `activate`, `edit`, and `update` now require a signed
`moderationToken` request argument in addition to (or, for `activate`, instead of) the existing
frontend user check. If you have overridden `Templates/Map/Create.html` or
`Templates/Map/Update.html` in your own site package, add `moderationToken` to the `arguments` of
both `f:link.action` calls, otherwise the links in the email no longer work:

..  code-block:: html

    <f:link.action action="edit" controller="Management" absolute="1"
                    arguments="{company: company, moderationToken: moderationToken}">
        Edit record
    </f:link.action>

Company logo and image uploads have been migrated to Extbase's native `#[FileUpload]` API
(TYPO3 >= 13.3), replacing the extension's own `UploadMultipleFilesConverter` property mapper and
`AssignMediaTypeConverterEventListener`, both of which have been removed. If you have overridden
`Partials/Company/Fields/Logo.html` or `Images.html`, replace the old per-index upload fields
(`logo.0`, `images.0` through `images.4`) with the native multi-file pattern:

..  code-block:: html

    <f:form.upload property="logo" multiple="1"/>
    <f:form.upload property="images" multiple="1"/>

`Domain\Model\Company::getOriginalLogo()`, `getOriginalImages()`, `getOriginalMainTrade()`, and
`getOriginalTrades()` have been removed. Their former callers should use `getLogo()`,
`getImages()`, `getMainTrade()`, and `getTrades()` instead, which now return the underlying
`ObjectStorage` directly rather than a plain array. Code iterating these values in Fluid keeps
working unchanged; PHP code expecting a plain `array` must call `->toArray()` itself.

The classic static template registration under `Configuration/TypoScript/` has been removed in
favor of the `jweiland/yellowpages2` Site Set. If your site still relies on the classic "Include
static (from extensions)" template mechanism instead of Site Sets, add the
`jweiland/yellowpages2` Site Set to your site's `dependencies` in `config.yaml`, see
:ref:`site-set-settings`.

Upgrade to version 7.0.0
========================

This version is focused on upgrading the compatibility version to TYPO3 version
12 LTS. We moved some extension functionalities from PSR-14 Event Listeners to
Middlewares as the request setArgument not possible anymore with TYPO3 version
12.


Upgrade to version 6.1.0
========================

If you make use of the `ModifyQueryToFindCompanyByLetterEvent` or
`ModifyQueryToSearchForCompaniesEvent` event:
We have changed the strict type of TYPO3 QueryBuilder to Extbase
QueryResult. Yes, this is a breaking change, but it seems that no one
makes use of it. Please adapt your code to use QueryResult. You can retrieve
the Extbase query with method `getQuery()`.

Upgrade to version 6.0.0
========================

As TYPO3 has deprecated the ObjectManager, we have changed many properties in our
scheduler task `Update yellowpages`. Please remove that scheduler task completely
and create a new one of type `Inform users to update their company record`.

We have changed a lot of classes. Please click the `Flush Cache` button in the Install Tool
to rebuild the Dependency Injection cache.

If you have maps2 version 9 installed you have to override or change following
part in `Properties.html` from:

..  code-block:: html

    <f:link.action title="Map detail"
                             action="show"
                             controller="PoiCollection"
                             extensionName="maps2"
                             pluginName="maps2"
                             pageUid="{settings.pidOfMaps2Plugin}"
                             arguments="{poiCollectionUid: company.txMaps2Uid}">

to:

..  code-block:: html

    <f:link.action title="Map detail"
                             action="show"
                             controller="PoiCollection"
                             extensionName="maps2"
                             pluginName="maps2"
                             pageUid="{settings.pidOfMaps2Plugin}"
                             arguments="{poiCollection: company.txMaps2Uid}">


Upgrade to version 5.0.0
========================

We have moved a lot of code of yellowpages2 controllers to EventListeners. Please flush the cache
in the maintenance module.

As a developer, you should check whether you have overwritten some methods of MapController or
CompanyController and adapt them to the new structure.

Upgrade to version 4.0.0
========================

We have removed column wsp_member as this column was specific to one of our customers. If you have
used it, you have to add it back with the help of EXT:extender.

We have removed column icon from table sys_category. This also removed fallbackIconPath. If you
have used it, you have to add it back with the help of EXT:extender.

We are using the API of glossary2 now. Please check whether your own queries are still working.
