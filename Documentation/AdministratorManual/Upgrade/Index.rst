..  include:: /Includes.rst.txt


========
Updating
========

If you update EXT:yellowpages2 to a newer version, please read this section carefully!

Upgrade to Version 6.1.0
========================

If you make use of `ModifyQueryToFindCompanyByLetterEvent` or
`ModifyQueryToSearchForCompaniesEvent` event:
We have changed the strict type of TYPO3 QueryBuilder to extbase
QueryResult. Yes, this is a breaking change, but it seems that no one
makes use of it. Please adopt your code to use QueryResult. You can retrieve
the extbase query with method `getQuery()`.

Upgrade to Version 6.0.0
========================

As TYPO3 has deprecated the ObjectManager we have changed many properties in our
scheduler task `Update yellowpages`. Please remove that scheduler task completely
and create a new one of type `Inform users to update their company record`.

We have changed a lot of classes. Please click `Flush Cache` button in Installtool
to re-build the Dependency Injection cache.

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


Upgrade to Version 5.0.0
========================

We have moved a lot of code of yellowpages2 controllers to EventListeners. Please flush cache in
maintenance module.

As a developer you should check, if you have overwritten some methods of MapController or CompanyController and
adopt them to new structure.

Upgrade to Version 4.0.0
========================

We have removed column wsp_member as this column was a specific column for one of our customers. If you have used it
you have to add it back with help of EXT:extender.

We have removed column icon from table sys_category. That way we also have remove fallbackIconPath, too. If you have
used it you have to add it back with help of EXT:extender.

We are using the API of glossary2 now. Please check, if your own queries are still working.
