<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */

$page = new Page('Campaigns');
/*
 * @package EDK
 */
class pCampaignList extends pageAssembly
{
	/** @var Page The Page object used to display this page. */
	public $page;
	
	/** @var string The selected view. */
	protected $view = null;
	/** @var array The list of views and their callbacks. */
	protected $viewList = array();
	/** @var array The list of menu options to display. */
	protected $menuOptions = array();

	/**
	 * Construct the Contract Details object.
	 * Set up the basic variables of the class and add the functions to the
	 *  build queue.
	 */
	function __construct()
	{
		parent::__construct();

		$this->view = preg_replace('/[^a-zA-Z0-9_-]/','', edkURI::getArg('view', 1));

		$this->queue("start");
		$this->queue("listCampaigns");

	}
	/**
	 *  Reset the assembly object to prepare for creating the context.
	 */
	function context()
	{
		parent::__construct();
		$this->queue("menuSetup");
		$this->queue("menu");
	}
	/**
	 * Start constructing the page.
	 * Prepare all the shared variables such as dates and check alliance ID.
	 *
	 */
	function start()
	{
		$this->page = new Page();
	}
	/**
	 *  Show the list of campaigns.
	 */
	function listCampaigns()
	{
		if(isset($this->viewList[$this->view])) {
			return call_user_func_array($this->viewList[$this->view], array(&$this));
		}
		$pageNum = (int)edkURI::getArg('page');

		switch ($this->view)
		{
			case '':
				$activelist = new ContractList();
				$activelist->setActive('yes');
				$this->page->setTitle('Active campaigns');
				$table = new ContractListTable($activelist);
				$table->paginate(10, $pageNum);
				return $table->generate();
				break;
			case 'past':
				$pastlist = new ContractList();
				$pastlist->setActive('no');
				$this->page->setTitle('Past campaigns');
				$table = new ContractListTable($pastlist);
				$table->paginate(10, $pageNum);
				return $table->generate();
				break;
		}
		return $html;
	}
	/**
	 * Set up the menu.
	 *
	 *  Prepare all the base menu options.
	 */
	function menuSetup()
	{
		$this->addMenuItem('link', 'Active campaigns', KB_HOST.'/?a=campaigns');
		$this->addMenuItem('link', 'Past campaigns', KB_HOST.'/?a=campaigns&amp;view=past');
		return "";
	}
	/**
	 * Build the menu.
	 *
	 *  Add all preset options to the menu.
	 */
	function menu()
	{
		$menubox = new box("Menu");
		$menubox->setIcon("menu-item.gif");
		foreach($this->menuOptions as $options)
		{
			if(isset($options[2]))
				$menubox->addOption($options[0],$options[1], $options[2]);
			else
				$menubox->addOption($options[0],$options[1]);
		}
		return $menubox->generate();
	}
	/**
	 * Add an item to the menu in standard box format.
	 *
	 *  Only links need all 3 attributes
	 * @param string $type Types can be caption, img, link, points.
	 * @param string $name The name to display.
	 * @param string $url Only needed for URLs.
	 */
	function addMenuItem($type, $name, $url = '')
	{
		$this->menuOptions[] = array($type, $name, $url);
	}

	/**

	 * Add a type of view to the options.

	 *
	 * @param string $view The name of the view to recognise.
	 * @param mixed $callback The method to call when this view is used.
	 */
	function addView($view, $callback)
	{
		$this->viewList[$view] = $callback;
	}

	/**
	 * Return the set month.
	 * @return integer
	 */
	function getMonth()
	{
		return $this->month;
	}

	/**
	 * Return the set year.
	 * @return integer
	 */
	function getYear()
	{
		return $this->year;
	}

	/**
	 * Return the set view.
	 * @return string
	 */
	function getView()
	{
		return $this->view;
	}
}

$campaignList = new pCampaignList();
event::call("campaignList_assembling", $campaignList);
$html = $campaignList->assemble();
$campaignList->page->setContent($html);

$campaignList->context();
event::call("campaignList_context_assembling", $campaignList);
$context = $campaignList->assemble();
$campaignList->page->addContext($context);

$campaignList->page->generate();
