.. include:: ../Includes.txt


.. _known-problems:

==============
Known Problems
==============

Duplicate entries
=================

If you assign a category to main_branch AND the same category to branches, the record will be displayed
in frontend twice. That's because of the OR statement in query (WHERE main_branch IN() OR branches IN()).
If we solve that with GROUP BY the f:widget.paginate() VH will stop working and shows just one page, regardless
if there are more than 15 entries in table.
This is a known bug in TYPO3: https://forge.typo3.org/issues/87655

Maybe we can solve that issue while removing compatibility for TYPO3 9 as we have to switch over to
new paginator API of TYPO3 10/11.
