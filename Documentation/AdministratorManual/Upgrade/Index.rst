..  include:: /Includes.rst.txt


..  _upgrade:

========
Updating
========

If you update EXT:yellowpages2 to a newer version, please read this section carefully!

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

    <f:link.action action="edit" controller="Company" absolute="1"
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
