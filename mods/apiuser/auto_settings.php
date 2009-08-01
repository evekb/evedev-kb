<?php
// this little trick will only request xajax for admin pages
event::register('mod_xajax_initialised', array('mod_xajax', 'xajax'));

options::oldMenu('APIUser', 'User Management', '?a=user_management');
options::oldMenu('APIUser', 'Roles/Titles', '?a=admin_roles');
options::oldMenu('APIUser', 'Setup', '?a=settings_apiuser');