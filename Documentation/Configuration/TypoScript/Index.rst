..  include:: /Includes.rst.txt


..  _site-set-settings:

=================
Site Set settings
=================

..  tip::

    Configure EXT:maps2 first if you want to show company addresses on a map.

    `Maps2 documentation <https://docs.typo3.org/p/jweiland/maps2/master/en-us/>`__

`yellowpages2` ships its configuration as the Site Set `jweiland/yellowpages2`. Add it to the
`dependencies` list of your site configuration (`config/sites/<your-site-identifier>/config.yaml`),
then override any of the following settings for your site in
`config/sites/<your-site-identifier>/settings.yaml`:

..  code-block:: yaml
    :caption: config/sites/my_site/settings.yaml

    yellowpages2.storagePid: '21,45,3234'
    yellowpages2.pidOfMaps2Plugin: 12

..  typo3:site-set-settings:: PROJECT:/Configuration/Sets/Yellowpages2/settings.definitions.yaml
    :name: yellowpages2-site-set-settings
