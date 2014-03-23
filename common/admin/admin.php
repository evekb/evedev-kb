<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */

// admin menu now loads all admin pages with options
require_once('common/admin/admin_menu.php');

$page = new Page();
$page->setAdmin();

if ($_POST) {
		options::handlePost();
}
$page->setContent(options::genOptionsPage());
$page->addContext(options::genAdminMenu());

if (!edkURI::getArg('field', 1)
		|| !edkURI::getArg('sub', 1)
		|| edkURI::getArg('field', 1) == 'Advanced'
				&& edkURI::getArg('sub', 2) == 'Configuration') {
	$page->setTitle('Administration - Board Configuration (Current version: '
			.KB_VERSION.' '.KB_RELEASE.')');
}
$page->generate();

