.. include:: ../../Includes.txt

Updating
========

If you update EXT:yellowpages2 to a newer version, please read this section carefully!

Update to Version 5.0.0
-----------------------

We have moved a lot of code of yellowpages2 controllers to EventListeners. Please flush cache in
maintenance module.

As a developer you should check

Update to Version 4.0.0
-----------------------

We have removed column wsp_member as this column was a specific column for one of our customers. If you have used it
you have to add it back with help of EXT:extender.

We have removed column icon from table sys_category. That way we also have remove fallbackIconPath, too. If you have
used it you have to add it back with help of EXT:extender.

We are using the API of glossary2 now. Please check, if your own queries are still working.
