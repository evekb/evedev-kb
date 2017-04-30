<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */


$page = new Page('Fetcher - Item Values from CREST');
$page->setAdmin();

if($_POST['submit'])
{


    $ValueFetcherEsi = new ValueFetcherEsi();

    $html = "<center>";
    try
    {
        $count = $ValueFetcherEsi->fetchValues();
        $html .= "Fetched and updated <b>". $count."</b> items!<br /><br />";

    }
    catch (Exception $e)
    {
        $html .= "Error in fetch: " . $e->getMessage();
    }
    $html .= "</center>";
}
else
{
    // Get from config

    $timestamp = config::get('lastfetch');
    $time = date('r', $timestamp);

    $html .= 'Last update: '.$time.'<br /><br />';

    $html .= '<form method="post" action="'.edkURI::page("admin_value_fetch").'">';
    if ((time() - $timestamp) < 86400)
    {
            $html .= '<tr><td colspan="3" align="center"><b>YOU HAVE UPDATED LESS THAN 24 HOURS AGO!</b><br/>';
    }
    $html .= '<button value="submit" type="submit" name="submit">Fetch</button>';
    $html .= '</form>';
    $html .= '<br /><a href="'.edkURI::page('admin_value_editor').'">Manually update values</a>';
}

$page->setContent($html);
$page->addContext($menubox->generate());
$page->generate();
