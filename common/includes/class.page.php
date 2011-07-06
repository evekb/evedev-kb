<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */

/**
 * Construct an output page.
 * @package EDK
 */
class Page
{
	private $title = "";
	private $headlines = array();
	private $bodylines = array();
	private $igb = IS_IGB;
	private $timestart = 0;
	private $cachable = true;
	private $cachetime = 60;
	private $onload = null;
	private $contenthtml = "";
	private $contexthtml = array();

	/**
	 * Construct a Page class with the given title.
	 *
	 * Page generation timer is started on Page creation.
	 */
	function Page($title = '', $cachable = true)
	{
		global $timeStarted;
		$this->timestart = &$timeStarted;
		event::call('page_initialisation', $this);
		if (!config::get('public_stats'))
		{
			config::set('public_stats','do nothing');
		}

		$this->title = htmlspecialchars($title);
		$this->cachable = $cachable;
	}
	/**
	 * Set the content html that is displayed in the main body panel.
	 */
	public function setContent($html)
	{
		$this->contenthtml = $html;
	}
	/**
	 * Set the context html that is displayed in the sidebar.
	 */
	public function addContext($html)
	{
		$this->contexthtml[] = $html;
	}
	/**
	 * Create and display an error message.
	 */
	public function error($message)
	{
		global $smarty;

		$smarty->assign('error', $message);
		$this->setContent($smarty->fetch(get_tpl('error')));
		$this->generate();
	}
	/**
	 * Add a line to the header html.
	 */
	public function addHeader($line)
	{
		$this->headlines[] = $line;
	}
	/**
	 * Add a line to the body html.
	 */
	public function addBody($line)
	{
		$this->bodylines[] = $line;
	}
	/**
	 * Generate the output html.
	 *
	 * Output is constructed from the variables passed in through the
	 * add methods and the index.tpl.
	 */
	public function generate()
	{
		global $smarty;

		$smarty->assign('kb_title', KB_TITLE.' '.$this->title);

		if ($this->onload)
		{
			$smarty->assign('on_load', ' onload="'.$this->onload.'"');
		}

		// header
		event::call('page_assembleheader', $this);
		$smarty->assign('page_headerlines', join("\n", $this->headlines));

		event::call('page_assemblebody', $this);
		$smarty->assign('page_bodylines', join("\n", $this->bodylines));

		if (MAIN_SITE) $smarty->assign('banner_link', MAIN_SITE);

		$smarty->assign('banner', config::get('style_banner'));
		$smarty->assign('banner_x', config::get('style_banner_x'));
		$smarty->assign('banner_y', config::get('style_banner_y'));

		$nav = new Navigation();
		$nav->setPage($_GET['a']);
		$menu = $nav->generateMenu();
		if(!count($menu->get())) $w = 100;
		else $w = floor(100 / count($menu->get()));

		$smarty->assign('menu_w',$w.'%');
		$smarty->assign('menu_count', count($menu->get()));
		$smarty->assign('menu', $menu->get());

		//check if banner is a swf
		$bannerExn = substr(config::get('style_banner'),-3);
		if (strtoupper($bannerExn) == 'SWF')
		{
			$smarty->assign('bannerswf', 'true');
		}
		else
		{
			$smarty->assign('bannerswf', 'false');
		}

		$smarty->assign('page_title', $this->title);

		$processingtime = number_format((microtime(true) - $this->timestart),4);

		$qry = DBFactory::getDBQuery();
		$smarty->assign('profile_sql_cached', $qry->queryCachedCount());
		$smarty->assign('profile_sql', $qry->queryCount());
		$smarty->assign('profile_time', $processingtime);
		$smarty->assign('sql_time', number_format($qry->getTotalTime(),4));
		if($this->isAdmin() || config::get('cfg_profile') || intval(KB_PROFILE))
			$smarty->assign('profile', 1);
		$smarty->assign('content_html', $this->contenthtml);
		if (config::get('user_showmenu'))
		{
			$this->contexthtml = array_merge(array(user::menu()), $this->contexthtml);
		}
		$smarty->assign('context_html', implode($this->contexthtml));
		$smarty->assignByRef('context_divs', $this->contexthtml);
		event::call('smarty_displayindex', $smarty);

		$html = $smarty->fetch(get_tpl('index'));
		event::call('final_content', $html);
		echo $html;
	}
	/**
	 * Return whether this will display as an igb page.
	 */
	public function igb()
	{
		return $this->igb;
	}
	/**
	 * Set the onload variable for Smarty.
	 */
	public function setOnLoad($onload)
	{
		$this->onload = $onload;
	}
	// Set the page title.
	public function setTitle($title)
	{
		$this->title = htmlspecialchars($title);
	}
	// Get the page title.
	public function getTitle()
	{
		return $this->title;
	}
	/**
	 * If this is not an admin session redirect to the login page.
	 */
	public function setAdmin()
	{
		if (!Session::isAdmin())
		{
			header("Location: ?a=login");
			echo '<a href="?a=login">Login</a>';
			exit;
		}
	}
	/**
	 * Return whether this is an admin session.
	 */
	public function isAdmin()
	{
		return Session::isAdmin();
	}
	/**
	 * Return whether this is a superadmin session.
	 */
	public function isSuperAdmin()
	{
		return Session::isSuperAdmin();
	}
	/**
	 * If this is not a superadmin session redirect to the login page.
	 */
	public function setSuperAdmin()
	{
		if (!Session::isSuperAdmin())
			Header("Location: ?a=login");
	}
	/**
	 * Set whether this page is cacheable.
	 */
	public function setCachable($cachable)
	{
		$this->cachable = $cachable;
	}
	/**
	 * Set how long to cache this page.
	 */
	public function setCacheTime($cachetime)
	{
		$this->cachetime = $cachetime;
	}
}
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
	 */
	public function get()
	{
		return $this->menu;
	}
	/**
	 * Add a link and text to the array of menu options.
	 */
	public function add($link, $text)
	{
		$this->menu[] = array('link' => $link, 'text' => $text);
	}
}
