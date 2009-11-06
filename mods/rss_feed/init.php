<?php
event::register('home_assembling', 'rss_feed::handler');

class rss_feed
{
    function handler(&$home)
    {
		$home->addBehind("start", "rss_feed::addRSS");
    }
	function addRSS($home)
	{
        $home->page->addHeader('<link rel="alternate" type="application/rss+xml" title="Watched Kills" href="?a=rss&amp;kills" />');
        $home->page->addHeader('<link rel="alternate" type="application/rss+xml" title="Watched Losses" href="?a=rss&amp;losses" />');
	}

}
?>