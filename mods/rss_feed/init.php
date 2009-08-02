<?php
event::register('page_assembleheader', 'rss_feed::handler');

class rss_feed
{
    function handler(&$object)
    {
        $object->addHeader('<link rel="alternate" type="application/rss+xml" title="Watched Kills" href="?a=rss&kills" >');
        $object->addHeader('<link rel="alternate" type="application/rss+xml" title="Watched Losses" href="?a=rss&losses" >');
    }
}
?>