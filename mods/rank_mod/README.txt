INSTALLATION

If you are using rank mod 0.96b remove the images form img/ranks/ribbons/ first
Overwrite all the files as the archive is structured

RANK MOD 0.97c (hotfix provided by Ralle030583)
- fix of alliance and corp detail  Ribbon Showroom, count for weapon badges wasnt shown
- fix in aliiance detail which caused an Fatal error: Call to a member function on a non-object  
- number of ribbons in a row at pilot detail showcase limited to 10 instead of 11

RANK MOD 0.97b

- fixed the rank medals table creation and operation to allow it work with multiple killboards on same database
- added all the 8 main ship classes with 21 more selectable subclasses with settable bonus & requirements for each
- reworked & created all the 29 selectable ribbon classes & subclasses images
- added the possibility to give different bonus & requirements for the weapon master ribbons
- added the possibility to give a different bonus for each medal, including a negative value for top losers
- added the possibility to edit rank titles & abbreviations
- added more exp factors for better rank distribution
- modified corp_detail and pilot_detail to reflect the new modifications
- added all the stuff seen in corp detail to alliance detail too
- extended the known members mod to alliance members with page splitter
- added a lot of new rank insignias and titles:
--> Star Trek: Original Series
--> Star Trek: Next Generation
--> Star Trek: Enterprise
--> Star Wars: Rebel Alliance
--> Star Wars: Imperial Navy
--> Battlestar Galactica

To do:

- Finish the corp medals page (still not found nothing that satisfies me - currently in development)
- Enable some functions for alliance detail (tricky)
- Add campaings ribbons (tricky)
- Rework homepage to show most ranked pilots instead of top scorers and top killers (easy)
- Add the possibility to give monthly medals to every corp in an alliance instead only for the alliance (tricky - currently in development)
- Finish and rework rank signatures (tricky)
- Add the possibility to search pilots by badges and medals (very tricky)

Previous Versions

0.96b

- Added Top Losers to Awards Page
- Removed Control Towers, Batteries, Sentry Guns & Warp Bubbles from medal assignation and Awards Page
- Removed Sentry Guns & Batteries from Rank list & Known members list
- Removed a bug preventing Alliance Boards to work properly
- Reworked the small award showcase to be inline with other tables
- Reworked the test sig to use the abbreviation instead the long title and update to new image format

0.95b

- Changed ribbons from png to gif to increase compatibility with older browsers
- Removed Control Towers & Bubbles from Rank List & Known Member List
- Merged a reviewed version of Known Members Mod
- Reworked the class.toplist querys for the weapon list re-introducing drones as most used weapons
- Added the Drone Operator ribbons
- Added a small showcase to pilot detail if you do not want the portrait layered but you still want to see the awards
- Reworked corp detail & pilot detail isk amounts to show Billions or Millions
- Added a Kill Ratio either for corp & pilot
- Added some info to pilot detail page
- Added progression bar & rank data to the rank & decorations section of pilot detail
- Added new settings to the settings page like known members / rank list settings and purple moon value

0.9

- Added a rank generation core (rank.php)
	contains a couple of functions to use them in all the kb
- Added a settings page (settings.php)
	great part of the code is done here, from the table for the medals to the creation of the rank list
- Added the needed images (img/ranks/*)
	needed for the graphical part of the stuff
- Added a 128x128 signature to the signature mod (mods/signature_generator/signatures/portrait128/*)
	made as a test, included as a test
- Modified the awards images (img/awards/*);
	to reflect the "new" medals
- Modified the Pilot Details page (pilot_detail.php)
	it shows a lot of admin settable things
- Modified the Corp Details page (corp_detail.php)
	it lists all the pilots with their ranks
- Modified the config file (common/includes/class.config.php)
	corrects an error preventing to get arrays from the config table
- Modified the top list file (common/includes/class.toplist.php)
	correct a couple of errors on the toplists and add a couple of new functions

Many thanks to:
Overated: for the original mod & inspiration
Lannier: for the request ;)
nxgshadow, Jomanda, Warbear: for giving good ideas
Anne Sapyx: for excellent bug reporting and for giving good ideas

And Very Very Special Thanks to Cpt Guppy, with his constant testing and bug reporting make my mods everyday better ;)
