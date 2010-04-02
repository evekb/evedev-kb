<?php
event::register('home_assembling', 'rss_feed::handler');

class rss_feed
{
	public static function handler(&$home)
	{
		$home->addBehind("start", "rss_feed::addRSS");
	}
	public static function addRSS($home)
	{
		$home->page->addHeader('<link rel="alternate" type="application/rss+xml" title="Watched Kills" href="?a=rss&amp;kills" />');
		$home->page->addHeader('<link rel="alternate" type="application/rss+xml" title="Watched Losses" href="?a=rss&amp;losses" />');
	}

}
