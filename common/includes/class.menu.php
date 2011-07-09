<?php
/**
 * $Date: 2011-07-06 23:00:02 +1000 (Wed, 06 Jul 2011) $
 * $Revision: 1365 $
 * $HeadURL: https://evedev-kb.googlecode.com/svn/trunk/common/includes/class.page.php $
 * @package EDK
 */

/**
 * Construct a menu.
 * 
 * A Menu is a wrapper around an array of links and matching text.
 * @package EDK
 */
class Menu
{
	private $menu = array();
	/**
	 * Construct a blank side menu.
	 */
	function Menu()
	{
	}
	/**
	 * Return the array of menu options.
	 *
	 * @return array
	 */
	public function get()
	{
		return $this->menu;
	}
	/**
	 * Add a link and text to the array of menu options.
	 *
	 * @param string $link URL for the menu option to link to.
	 * @param string $text text for the menu option.
	 */
	public function add($link, $text)
	{
		$this->menu[] = array('link' => $link, 'text' => $text);
	}
}
