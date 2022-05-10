.. include:: ../../Includes.txt

.. _extensionSettings:

==================
Extension Settings
==================

Some general settings for `yellowpages2` can be configured in *Admin Tools -> Settings*.

Tab: Basic
==========

poiCollectionPid
""""""""""""""""

Default: 0

Only valid, if you have installed EXT:maps2, too.

While creating location records we catch the address and automatically create a maps2 record
for you. Define a storage PID where we should store these records.

editLink
""""""""

Default: empty

yellowpages2 comes with a scheduler task to invalidate records after 13 months. It informs
the customers to renew their entries after 12 months.
This information mail contains a link to review companies record.
Define a page UID where you have added the plugin to review the companies record.

emailFromAddress
""""""""""""""""

Default: empty (use value from INSTALL_TOOL)

With yellowpages2 you can give your website visitors the possibility to create new
events. These created records will be hidden by default. Add an email address
of the sender, if a new record was created over the frontend.

emailFromName
"""""""""""""

Default: empty (use value from INSTALL_TOOL)

With yellowpages2 you can give your website visitors the possibility to create new
events. These created records will be hidden by default. Add a name
of the sender, if a new record was created over the frontend.

emailToAddress
""""""""""""""

Default: empty

With yellowpages2 you can give your website visitors the possibility to create new
events. These created records will be hidden by default. Add an email address
of the receiver, if a new record was created over the frontend.

emailToName
"""""""""""

Default: empty

With yellowpages2 you can give your website visitors the possibility to create new
events. These created records will be hidden by default. Add a name
of the receiver, if a new record was created over the frontend.
