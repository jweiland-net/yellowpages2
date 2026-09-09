..  include:: /Includes.rst.txt


..  _extension-settings:

===================
Extension settings
==================

Configure `yellowpages2` in the backend module *Admin Tools > Settings > Extension Configuration*.

..  confval:: poiCollectionPid
    :name: confval-poicollectionpid
    :type: integer
    :default: 0

    Only relevant if EXT:maps2 is installed. While creating a company record on the frontend, the
    address is geocoded and a maps2 record is created for it automatically. Define the storage page
    ID for these records here.

..  confval:: editLink
    :name: confval-editlink
    :type: string
    :default: (empty)

    `yellowpages2` ships a console command that hides company records older than 13 months and
    informs their owners 12 months after creation, asking them to renew the entry. The reminder
    email links back to this page ID, which should have the plugin configured for editing.

..  confval:: emailFromAddress
    :name: confval-emailfromaddress
    :type: string
    :default: (empty, falls back to the install-wide default from address)

    Sender address used for all notification emails triggered when a visitor creates or updates a
    company record on the frontend.

..  confval:: emailFromName
    :name: confval-emailfromname
    :type: string
    :default: (empty, falls back to the install-wide default from name)

    Sender name used for the notification emails described above.

..  confval:: emailToAddress
    :name: confval-emailtoaddress
    :required: true
    :type: string
    :default: (empty)

    Recipient address for the administrator notification sent whenever a visitor creates or updates
    a company record on the frontend. This is also the address that receives the moderation link to
    edit or activate the record. Unlike `emailFromAddress`, there is no fallback: leaving this empty
    causes sending to fail once a company is created or updated on the frontend.

..  confval:: emailToName
    :name: confval-emailtoname
    :type: string
    :default: (empty)

    Recipient name for the administrator notification described above.

..  confval-menu::
    :name: confval-extension-settings-menu
    :display: table
    :type:
    :default:
