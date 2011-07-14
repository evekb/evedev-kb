<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */

/**
 * @package EDK
 */
class PageSplitter
{
	/**
	 * Create a PageSplitter
	 *
	 * @param integer $max Total count
	 * @param integer $split Number of lines to show on each page
	 */
	function PageSplitter($max, $split)
	{
		$this->max_ = $max;
		$this->split_ = $split;
	}

	function getSplit()
	{
		return $this->split_;
	}

	function generate()
	{
		global $smarty;
		if (!$this->split_ || $this->max_ / $this->split_ <= 1)
			return;

		$endpage = ceil($this->max_ / $this->split_);
		if ($_GET['page'])
		{
			$url = preg_replace("/&?page=([0-9]+)/", "",
					$_SERVER['QUERY_STRING']);
			$url = preg_replace("/&/", "&amp;", $url);
			$page = $_GET['page'];
		}
		else
		{
			$url = $_SERVER['QUERY_STRING'];
			$url = preg_replace("/&/", "&amp;", $url);
			$page = 1;
		}
		$smarty->assign('splitter_endpage', $endpage);
		$smarty->assign('splitter_page', $page);
		$smarty->assign('splitter_url', $url);
		
		return $smarty->fetch(get_tpl('pagesplitter'));
	}
}
