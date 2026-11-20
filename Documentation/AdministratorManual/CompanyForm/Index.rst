:navigation-title: Company form

..  include:: /Includes.rst.txt
..  _company-form:

=====================================
Company form in the Management plugin
=====================================

..  versionadded:: 10.0.0

    Companies are created and edited in the frontend with a form based on
    EXT:form. The form has two steps: the company data and the position of
    the company on the map.

The `Management` plugin renders the form definition
:file:`EXT:yellowpages2/Configuration/Form/Company.form.yaml` in its action
`perform`. Logged-in frontend users of the user group of the site setting
`yellowpages2.userGroup` create new companies with it. The owner of a
company and moderators (see :ref:`company-form-moderation`) edit existing
companies with the same form.

..  contents::
    :caption: Table of contents
    :local:

..  _company-form-requirements:

Requirements of the company form
================================

*   The system extension EXT:form (:composer:`typo3/cms-form`)
*   EXT:maps2 12.3.0 or higher (:composer:`jweiland/maps2`) with its form
    element `Maps2PoiPosition`. The site set `jweiland/yellowpages2`
    depends on the site set `jweiland/maps2-default`, which provides the
    form element.
*   Optional: EXT:rte_ckeditor (:composer:`typo3/cms-rte-ckeditor`) for the
    rich text editor of the description, see
    :ref:`frontend-rich-text-editor`.

..  _company-form-steps:

Steps of the company form
=========================

#.  **Company data** (page `company-data`): name, address, district,
    contact data, opening times, description, trades, social media links,
    logo and images.
#.  **Position on the map** (page `map`): the form element
    `Maps2PoiPosition` of EXT:maps2 shows a marker at the position of the
    address. The user can move the marker, if the position is not correct.

When the first step is submitted, the address (street, house number, zip
and city) is geocoded by the :php:`GeocodeAddressHook` with the
:php:`GeoCodeService` of EXT:maps2. The marker of the second step is placed
at the found position. If the address can not be found, the user stays on
the first step and gets an error at the field "Street".

The address is only geocoded again, if it was changed. So a corrected
position is kept, if the user goes back to the first step. When an
existing company is edited, the second step shows the stored position of
its POI collection, as long as the address is not changed.

The identifiers of the address fields are configured in the property
:yaml:`addressElements` of the element `position`:

..  code-block:: yaml
    :caption: EXT:yellowpages2/Configuration/Form/Company.form.yaml (excerpt)

    - type: Maps2PoiPosition
      identifier: position
      properties:
        dbMapping:
          column: tx_maps2_uid
          dataType: poiPosition
        addressElements:
          - street
          - house-number
          - zip
          - city

..  _company-form-saving:

Saving the company
==================

The company is saved by the finishers of the form, after the second step
was submitted:

#.  `SaveCompanyFinisher`: stores the company, its trades (categories),
    its logo and images and the corrected position. A company without POI
    collection gets a new POI collection of type `Point`, which is stored
    in the folder of the extension setting
    :confval:`poiCollectionPid <confval-poicollectionpid>` or else next to
    the company. The POI collection of an edited company is updated.
#.  `EmailToReceiver`: informs the moderator, see
    :ref:`company-form-moderation`.
#.  `FlashMessage`: tells the user, that the company will be visible after
    it was activated.
#.  `Redirect`: redirects to the page of the site setting
    `yellowpages2.pidOfManagementPage` (the list of the own companies) or
    else to the current page.

New companies are stored in the page of the site setting
`yellowpages2.form.storagePid`. If it is empty, the first storage page of
the plugin (`yellowpages2.storagePid`) or else the current page is used.
They get the current frontend user as owner and a path segment. An edited
company keeps its page, owner and path segment.

The values of selects and checkboxes are restricted to their options. So a
manipulated request can not relate other categories or districts.

..  _company-form-moderation:

Moderation of companies of the company form
===========================================

Each company created or edited with the form is hidden, until a moderator
activates it. The `EmailToReceiver` finisher sends a mail to the recipient
of the extension settings (:confval:`emailToAddress
<confval-emailtoaddress>`) with all values of the form and two signed
links:

*   **Edit record**: opens the company form with this company. The link
    contains a `moderationToken`, so no frontend login is needed.
*   **Activate record**: activates the company (action `activate` of the
    `ManagementController`).

The mail is rendered with the templates
:file:`EXT:yellowpages2/Resources/Private/FormFramework/Templates/Finishers/Email/CompanyModeration.html`
and :file:`CompanyModeration.txt`. The template name and its root path are
options of the finisher in :file:`Company.form.yaml`, so they apply to the
company form only and not to other forms of the installation. The finisher
`SaveCompanyFinisher` provides the UID of the company and the token of the
links as finisher variables `SaveCompany.companyUid` and
`SaveCompany.moderationToken`. Rich text elements like the description are
rendered with the :php:`FormatRichTextViewHelper`, see
:ref:`frontend-rich-text-editor-mail`.

..  _company-form-access:

Access rules of the company form
================================

=========================== ================================================
Action                      Allowed for
=========================== ================================================
Create a company            Logged-in frontend users of the user group of
                            `yellowpages2.userGroup`
Edit a company              The owner (frontend user of the company), a
                            backend administrator or everyone with a valid
                            `moderationToken` of the mail
=========================== ================================================

The company to edit is taken from the query parameter
`tx_yellowpages2_management[company]` of the form URI. The rules are
checked by the `perform` action, while prefilling the form and again by the
`SaveCompanyFinisher`.

..  _company-form-configuration:

Configuration of the company form
=================================

The site set `jweiland/yellowpages2` registers the form setup
:file:`EXT:yellowpages2/Configuration/Form/OverrideFormSettings.yaml` and
overrides some values of the form definition with TypoScript
:typoscript:`plugin.tx_yellowpages2.settings.overrideForm`:

..  code-block:: typoscript
    :caption: EXT:yellowpages2/Configuration/Sets/Yellowpages2/setup.typoscript (excerpt)

    plugin.tx_yellowpages2.settings.overrideForm {
      # Upload folder of logo and images ("settings.new.uploadFolder")
      renderables.0.renderables.19.properties.saveToFileMount < plugin.tx_yellowpages2.settings.new.uploadFolder
      # Storage page of new companies
      finishers.1.options.databaseColumnMappings.pid.value = {$yellowpages2.form.storagePid}
      # Page of the links in the mail of the moderator
      finishers.2.options.variables.pidOfManagementPage = {$yellowpages2.pidOfManagementPage}
      # Page of the redirect after saving
      finishers.4.options.pageUid = {$yellowpages2.pidOfManagementPage}
    }

Labels and descriptions of the form elements are translated with
:file:`EXT:yellowpages2/Resources/Private/Language/locallang_form.xlf`.

The form is rendered with the template variant `version2` of EXT:form
(Bootstrap 5 markup). The partial
:file:`EXT:yellowpages2/Resources/Private/FormFramework/Partials/Field/Field.html`
renders the element description as text below the field.

..  _company-form-checkfaluploads:

Rights checkbox of EXT:checkfaluploads
--------------------------------------

The uploads of logo and images are plain EXT:form elements of type
`ImageUpload`. To add the rights checkbox of EXT:checkfaluploads (6.0.0 or
higher), add a checkbox with :yaml:`checkboxType: uploadRights` and
:yaml:`referenceUploadIdentifier` to your copy of the form definition, as
described in the documentation of EXT:checkfaluploads.
