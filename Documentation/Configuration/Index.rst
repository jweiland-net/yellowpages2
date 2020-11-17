.. include:: ../Includes.txt


.. _configuration:

=============
Configuration
=============

Target group: **Developers, Integrators**

How to configure the extension. Try to make it easy to configure the extension.
Give a minimal example or a typical example.


Minimal Example
===============

- It is necessary to include static template `Industry Directory (yellowpages2)`

We prefer to set a Storage PID with help of TypoScript Constants:

.. code-block:: none

   plugin.tx_yellowpages2.persistence {
      # Define Storage PID where company records are located
      storagePid = 4
   }

.. _configuration-typoscript:

TypoScript Setup Reference
==========================


.. _includeJQueryLibrary:

includeJQueryLibrary
--------------------

Default: 1

Our JavaScript for frontend editing needs jquery to be present. Please disable this option
if you have added your own jquery library within your SitePackage.


.. _pidOfMaps2Plugin:

pidOfMaps2Plugin
----------------

Default:

If you have assigned an EXT:maps2 PoiCollection record to company record you set this value to
a PID where you have inserted the EXT:maps2 plugin.


.. _startingUidForCategories:

startingUidForCategories
------------------------

Default:

If you need frontend editing you should set this value to a parent category UID whose sub-categories
should be assigned to the category selector.


.. _uidOfDefaultDistrict:

uidOfDefaultDistrict
--------------------

Default:

If you need frontend editing you can set this value to be a preset for new companies in frontend.


.. _pidOfDetailPage:

pidOfDetailPage
---------------

Example: plugin.tx_yellowpages2.settings.pidOfDetailPage = 4

If you have inserted the Industry Directory plugin for detail view onto another
page, you can set its PID to this property here.


.. _pidOfListPage:

pidOfListPage
-------------

Example: plugin.tx_yellowpages2.settings.pidOfListPage = 2

If you have inserted the Industry Directory plugin for detail view onto another
page, you can set the PID of list page here to have correct links back to list view.


.. _userGroup:

userGroup
---------

Default:

If you will allow for frontend users to create and edit their own business listings you have
assign them a frontend usergroup and add this group UID to this property.


.. _pageBrowser:

pageBrowser
-----------

You can fine tuning the page browser

Example: plugin.tx_yellowpages2.settings.pageBrowser.itemsPerPage = 15
Example: plugin.tx_yellowpages2.settings.pageBrowser.insertAbove = 1
Example: plugin.tx_yellowpages2.settings.pageBrowser.insertBelow = 0
Example: plugin.tx_yellowpages2.settings.pageBrowser.maximumNumberOfLinks = 5

**itemsPerPage**

Reduce result of company records to this value for a page

**insertAbove**

Insert page browser above list of company records

**insertBelow**

Insert page browser below list of company records. I remember a bug in TYPO3 CMS. So I can not guarantee
that this option will work.

**maximumNumberOfLinks**

If you have many company records it makes sense to reduce the amount of pages in page browser to a fixed maximum
value. Instead of 1, 2, 3, 4, 5, 6, 7, 8 you will get 1, 2, 3...8, 9 if you have configured this option to 5.
