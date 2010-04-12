-------------------------------------------
Eve Development Network Killboard v3.0.3
-------------------------------------------

VERSION HISTORY
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



//  VERSION NOMENCLATURE
-------------------------------------------
[Base Ver].[dB Ver].[API Ver].[Smarty Ver].[Change Release TB]
v150.12Apoc.32.2622.0 is
- Base 1.5.0
- Database 1.2 for Apocrypha
- API module is to 3.2
- Smarty 2.6.2.2
- Change release for undetailed fixes (missing class files etc)



// BUILT FROM 1.4 CORE v368
-------------------------------------------


// MODS INCLUDED IN THIS PACKAGE
-------------------------------------------
API v3.3 ....................... Capt Thunk
Corp Logo Generator ............ Capt Thunk
Mail Editor 0.9.1 ........... FriedRoadKill
Extended Fitting Mod .......... Unknown ATM
Apoc Fitting Mod................. btcentral


// SUPPORT
-------------------------------------------
Web:                http://eve-id.net/forum/

Check out the EVE-Development Network for
general support, bugfixes and new versions
at http://www.eve-id.net

Developers wanted!
If you'd like to contribute to further version
of this killboard, sign up for the EVE-Dev forums!

// REQUIREMENTS
-------------------------------------------
- Webserver (apache)
- PHP 5+
- Mysql 5+
- GD 2 or higher


// SETUP
-------------------------------------------
- Upload the whole package to a webhost**
- Point your webbrowser to /install inside the
    EDK-Directory
- Follow the instructions
- Don't forget to delete the install folder after
    installation or restrict the access to it!
- Have fun ;)

o/ EVE Development Network


** Alvar recommends the use of FileZilla (free) to
upload the files, as it has a built in easy to use
error log, that allows you to upload files if they
time out or error on the upload due to server
settings.

