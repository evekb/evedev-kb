-------------------------------------------
Eve Development Network Killboard v4.0.4
-------------------------------------------

// REQUIREMENTS
-------------------------------------------
- Webserver (Apache, IIS)
- PHP 5.2+
- Mysql 5+
- GD 2 or higher


// SETUP
-------------------------------------------
- Upload the whole package to a webhost
- Point your webbrowser to /install inside the
    EDK-Directory
- Follow the instructions
- Don't forget to delete the install folder after
    installation or restrict the access to it!
- Have fun ;)

o/ EVE Development Network


VERSION HISTORY
v4.0.4
Fixes:
Add Crucible 1.1 DB packages.
Add \n to cronjobs.
Ship class links for public summary tables fixed.
fix parser translation for pre-crucible 1.0 ABs.
Page cache timing fixes - respects admin settings.
Setting prices on kill details works with unitialised Kills
Neaten update errors for unlinking missing files.
Remove kill points from killer rather than victim on deletion.
-------------------------------------------
v4.0.3
Fixes:
Updates old owner ids to new format
idfeeds no longer fall back to old feed on error
items inside containers are stored correctly
blueprint copies inside containers work
-------------------------------------------
v4.0.2
Fixes:
Unknown ships are added with correct id.
Installer finds http class when curl is not enabled.
Killlog error messages are clearer.
Parser fails less dramatically on unknown ships and weapons.
Add new, unknown, alliances when adding new corps.
Add server status class.
Fix CCP's latest killmail bugs.
Fix for updated german killmail translations.
Fix kill related links.
Item values can be updated on kills.
Forum post and known members mods fixed.
CSS fixes for kill_details
-------------------------------------------
v4.0.1
Fixes:
API static methods under 5.2
Name display on self details page cleaned.
BPCs in quantities greater than 1 found
API kills show API as source.
search redirectes to correct page.
Classified mails fixed.
previous month selection in corp details corrected.
german translation of mails updated.
pilot names shown on some awards where they were missing
better escaping of old killlog API names.
moons table created if it does not exist.
URL redirects with non-simple urls no longer escaped.
filename fixed in renamed cron files.
-------------------------------------------
v4.0.0
Cleaner URLs: kburl/home/2011/23/kills/
More caching! Objects can now be filecached(meh)/memcached(yay!)
Corp logo transparency returns
IIS bug avoided
improved error messages
improved duplicate checks
improved toplist display for mixed boards
ajcron admin panel display fix
various other internal fixes
Added html5 theme
New API support
Support Implants on pod mails
Support BPC flag
Updated with Crucible database
More stuff
-------------------------------------------
v3.2.3
Related kill count fix
Incarna DB
Custom EDK URL creation support (supports future versions of EDK)
-------------------------------------------
v3.2.2
Use CCP image server for images
If using local cache of images, fetch Types from CCP
Fix for pilot points and corp icons in killlists
Increased contrast of red graphs in red theme
Troubleshooting page checks SSL support.
-------------------------------------------
v3.2.1
Corp logo transparency returns
IIS bug avoided
pilot points shown
improved error messages
thumbnail fixes for lighttpd
more default thumbnail sizes
summary tables on public boards work again
improved duplicate checks
improved toplist display for mixed boards
ajcron admin panel display fix
various other internal fixes
-------------------------------------------
v3.2.0
Mixed pilot/corp/alliance board owner
- no limits on number or type of owners
Code cleaning
DB speed optimisations
Quicker install
Moved everything to the left a bit.
More separation of mods and core for easy modding and theming
Images now use CCP's types directory structure
API updated with CCP's latest changes.
Images are now accessed through thumb.php
- simpler image handling
- improved speed for large sites
- ready for CCP's planned item image server
Russian mail parser fixes
API over HTTPS
kill_details has optional 256x256 ship background
kill_details links to killmail source.
-------------------------------------------
v3.1.8
Refixed hyphenated name display
-------------------------------------------
v3.1.7
Handles CCP's new default image system
Incursion 1.1 DB
Campaign end dates are respected
Cronjob feed fetches update last kill on first run
Fixed hyphenated name display
Structure names handled consistently
Improved alliance clustering in related kills
-------------------------------------------
v3.1.6
Incursion updates
- Name
- Image files
- Installation db
IDFeed renames structures as per standard parser.
kill_related improvements
-------------------------------------------
v3.1.5
Fixed killlist generation speed issues.
Fixes for cache generation issues
Corp/alliances with a null timestamp are updated correctly.
Alliance portraits show same image for small and large sizes
-------------------------------------------
v3.1.4
IDFeed reader stops posting 0 quantity kills.
-------------------------------------------
v3.1.3
IDFeed reader works more convincingly.
Single pilot boards authorise owner to post kills
-------------------------------------------
v3.1.2
IDFeed reader works.
Feed page works for pilot feeds.
Hashing works for updated kills
Update can reset db level.
-------------------------------------------
v3.1.1
IDFeed reader works for Corporate boards.
corp_detail correctly links to CEO
Constellation highlighting on maps highlights
Memcache support in page cache improved.
Region names on kill lists return
-------------------------------------------
VERSION HISTORY
v3.1.0
IDFeed reader implemented similar to CCP API feed.
Classes can be overridden by mods.
Code has been rewritten to fit a more OO design.
Code has been rewritten to be more maintainable.
Error handling has been added to improve error messages.
Error messages can be logged to file.
Memcache is used more widely, if enabled.
Standard cache manager has been added enabling simpler cache handling.
CCP's new image server is used.
Newer, faster version of the Smarty templating system is used.
Front page cam be toggled to display current week/month only.
Feed syndication uses 'trust'. API ids from trusted boards are used to verify kills.
Hashes of killmails are used to speed up the processing of duplicate kills.
Image cache now organised by ID and shared between pilot/corp/alliance.
More theming-friendly changes
Default character encoding added to db setup
New ships added.
API admin more friendly
More display options.
Numerous minor bugfixes and improvements.
-------------------------------------------
v3.0.8
Final Tyrannis content added to installer.
-------------------------------------------
v3.0.7
Auto db-updater works again
-------------------------------------------
v3.0.6
Tyrannis rather bizarrely sets pilots known to have no alliance as 'Unknown'
instead of 'None'. EDK will convert this back to 'None' until sense is restored.
Admin upgrade will report errors if upgrade could not be performed.
Prepared queries return better error messages.
-------------------------------------------
v3.0.5
Cache clearing script fixed.
Corp detail page shows top ships used rather than killed.
New ships can be added to admin ship values again.
-------------------------------------------
v3.0.4
Rare error in renaming pilots fixed.
-------------------------------------------
v3.0.3
Callback function passes references (Mod makers rejoice)
Updates to core mods missing from previous updates added.
-------------------------------------------
v3.0.2
Toplist speed fixes
Memory limits added to db caches
Backglow returned to kill detail modules
Kill related is more related.
Ship values set in the admin panel are set
Cache clearing cronjob checks directories exist
-------------------------------------------
v3.0.1
Toplist speed fixes.
Campaign speed fixes.
Pages no longer allow incorrect caching.
Signatures display correctly.
Kill detail themes work again.
-------------------------------------------
v3.0.0
PHP5 support only. PHP 4 may work in some cases but is not supported.
External ID for kills, corps and alliances added
large database handling improved
extended theme support
event driven page modification
-------------------------------------------
-------------------------------------------
v2.0.10 (svn r488)
Date function works in php4
-------------------------------------------
v2.0.9 (svn r486)
Year end date handling fixed.
missing rank mod images returned.
Dominion parser and db
-------------------------------------------
v2.0.8 (svn r479)
Board updated with Dominion installation DB and IGB pages changed.
PHP4 compatibility improved
Rank mod no longer gives EWAR award for missile use.
-------------------------------------------
v2.0.7 (svn r476)
PHP4 compatibility improved
Install speed increased
EFT fittings show subsystems
session handling improved
corrected ship class filtering
pilot's corp reset correctly
eve-dev references changed to eve-id
-------------------------------------------
v2.0.6 (svn r459)
Security fixes
Updated parser
Improved handling of large cache directories
cache clearing script included
-------------------------------------------
v2.0.3 (svn 445)
- various bufixes
- PHP4 support
-------------------------------------------
v2.0.1 (svn 435)
- first release of EDK v2
-------------------------------------------
v2.0.0 RC1 (svn 370)
- Incorporates changes and additional mods from Alvar's EDK Full Package v150.13Apoc.33.2622.0
- Database structure changed
- SQL queries optimised for speed and error protection.
- feed syndication optimised
- front page includes optional clock and optional kill/loss display
- mysqli support added
- transaction protection of kills added where supported
- summary tables and contracts show total destroyed value instead of ship value
- html errors reduced
- related kill calculation improved
- query caching errors reduced
- minor bugfixes
- Smarty 2.6.25 added
- apoc fitting mod added and modified
- code optimisations
- comments added (doxygen format)
- conflicting mods are now identified

// Alvar Package VERSION HISTORY
-------------------------------------------
v150.10Apoc.29.2622.0 - Initial Release
- This was the basic build as above but only for the QR information.
- Included updated Smarty to 2.6.2.2

v150.11Apoc.30.2622.0 - Updates for Apocrypha
- Updated the API to 3.0
- Added the Corp Logo mod
- Added the Apoc dB dump 1.1 from FriedRoadKill

v150.11Apoc.30.2622.1 - Updates for Missing files
- Added missing files for core mod: rank mod
- Added the missing parser files for the latest version
- class.registry.php included to fix borking api

v150.12Apoc.32.2622.0 - Updates for
- Update to API to 3.2
- Added the Apoc dB dump 1.2 from FriedRoadKill
- Updated the Version nomenclature.

v150.13Apoc.33.2622.0 - Updates for
- Update API to 3.3
- Updated the Apoc dB dump 1.3 from FRK
- Added the Apoc Fitting screen 1.3 by btcentral
- Removed the "Non Installed Mods" folder
- Removed the "Alvar" style



// SUPPORT
-------------------------------------------
Web:                http://eve-id.net/forum/

Check out the EVE-Development Network for
general support, bugfixes and new versions
at http://www.eve-id.net

Developers wanted!
If you'd like to contribute to further version
of this killboard, sign up for the EVE-Dev forums!
