<?php
event::register('page_assembleheader', 'rss_feed::handler');

class rss_feed
{
    function handler(&$object)
    {
        $object->addHeader('<link rel="alternate" type="application/rss+xml" title="RSS feed for watched kills" href="?a=rss" >');
    }
}
?>