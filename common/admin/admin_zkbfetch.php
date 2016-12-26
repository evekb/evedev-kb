<?php

/*
 * zKB API Fetch
 * @author Salvoxia
 *
 */

require_once('common/admin/admin_menu.php');

$page = new Page("Administration - zKillboard Fetch v" . ZKB_FETCH_VERSION);
$page->setCachable(false);
$page->setAdmin();
$html = "";

// add new fetch config
if($_POST['add'])
{
    $newFetchUrl = $_POST['newFetchUrl'];
    if(strlen(trim($newFetchUrl)) == 0)
    {
        $html .=  "Error: Can't add zKB Fetch with empty URL!<br/>";
    }
    
    else
    {
        // must end with a slash
        if(substr($newFetchUrl, -1) != '/')
        {
            $newFetchUrl .= '/';
        }
        $newFetchTimestamp = trim($_POST['newFetchTimestamp']);
        $newFetchTimestamp = strtotime($newFetchTimestamp);

        $NewZKBFetch = new ZKBFetch();
        $NewZKBFetch->setUrl($newFetchUrl);
        $NewZKBFetch->setLastKillTimestamp($newFetchTimestamp);

        try
        {
            $NewZKBFetch->add();
        } 

        catch (Exception $ex) 
        {
            $html .= $ex->getMessage();
        }
    }
}

$fetchConfigs = ZKBFetch::getAll();


// saving urls and options
if ($_POST['submit'] || $_POST['fetch'])
{
	if(is_null($fetchConfigs)) 
            {
		$fetchConfigs = array();
	}
    foreach($fetchConfigs AS &$fetchConfig) 
    {
        $id = $fetchConfig->getID();
        if ($_POST[$id]) 
        {
            $lastKillTimestampFormatted = strftime('%Y-%m-%d %H:00:00', $fetchConfig->getLastKillTimestamp());
            if($_POST['lastKillTimestamp'.$id] != $lastKillTimestampFormatted) 
            {
                $lastKillTimestampNew = strtotime($_POST['lastKillTimestamp'.$id]);
                if($lastKillTimestampNew !== FALSE)
                {
                    $fetchConfig->setLastKillTimestamp($lastKillTimestampNew);
                }
            }
            
            // reset the feed lastkill details if the URL or api status has changed
            if($_POST[$id] != $fetchConfig->getUrl()) 
            {
                $fetchConfig->setUrl($_POST[$id]);
            }
            
            if ($_POST['delete'] && in_array ($id, $_POST['delete'])) 
            {
                ZKBFetch::delete($id);
            }
        } 
        
        else 
        {
            ZKBFetch::delete($id);
	}
    }

    if($_POST['post_no_npc_only_zkb'])
    {
        config::set('post_no_npc_only_zkb', 1);
    }

    else
    {
        config::set('post_no_npc_only_zkb', 0);
    }
    
    // set the negative timestamp offset
    if(isset($_POST['killTimestampOffset']) && is_numeric($_POST['killTimestampOffset']))
    {
        config::set('killTimestampOffset', (int) $_POST['killTimestampOffset']);
    }
    
}

// update fetch configs again, since we could have deleted some above
$fetchConfigs = ZKBFetch::getAll();

// building the request query and fetching of the feeds
if ($_POST['fetch'])
{
    foreach($fetchConfigs AS &$fetchConfig)
    {
            if(!($_POST['fetchApi'] && in_array ($fetchConfig->getID(), $_POST['fetchApi'])) || is_null($fetchConfig->getUrl())) 
            {
                continue;
            }
            $html .= getZKBApi($fetchConfig);
    }
}

// generating the html
$rows = array();
foreach($fetchConfigs as &$fetchConfig) 
{
    $key = $fetchConfig->getID();
    if (!isset($_POST['fetchApi'][$key]) || $_POST['fetchApi'][$key]) 
        {
            $fetch=false;
	} else {
            $fetch = true;
	}
        $lastKillTimestampFormatted = strftime('%Y-%m-%d %H:00:00', $fetchConfig->getLastKillTimestamp());
	$rows[] = array('id'=>$key, 'uri'=>$fetchConfig->getUrl(), 'lastKillTimestmap'=>$lastKillTimestampFormatted,  'fetch'=>!$fetch);
}

$smarty->assignByRef('rows', $rows);
$smarty->assign('results', $html);
$smarty->assign('post_no_npc_only_zkb', config::get('post_no_npc_only_zkb'));
$killTimestampOffset = config::get('killTimestampOffset');
if(is_null($killTimestampOffset) || !is_numeric($killTimestampOffset))
{
    $killTimestampOffset = ZKBFetch::$KILL_TIMESTAMP_OFFSET_DEFAULT;
}
$smarty->assign('killTimestampOffset', $killTimestampOffset);
$smarty->assign("currentTimeUtc", gmdate("Y-m-d H:00:00", time()));
$page->addContext($menubox->generate());
$page->setContent($smarty->fetch(get_tpl('admin_zkbfetch')));
$page->generate();

/**
 * 
 * @param \zKBFetch $fetchConfig
 * @return string
 */
function getZKBApi(&$fetchConfig)
{
	$html = '';
	// Just in case, check for empty urls.
	if(is_null($fetchConfig->getUrl())) 
        {
            return 'No URL given<br />';
	}
	
        if(!$fetchConfig->getLastKillTimestamp())
        {
            $fetchConfig->setLastKillTimestamp(time() - 60 * 60 * 24 * 7);
        }
        
        try
        {
            $fetchConfig->setKillTimestampOffset(config::get('killTimestampOffset'));
            $fetchConfig->setIgnoreNpcOnlyKills((boolean)(config::get('post_no_npc_only_zkb')));
            $fetchConfig->processApi();
            $html .= "ZKBApi: ".$fetchConfig->getUrl()."<br />\n";
            $html .= count($fetchConfig->getPosted())." kills were posted and ".
						count($fetchConfig->getSkipped())." were skipped "
                                                . "(".$fetchConfig->getNumberOfKillsFetched()." kills fetched).";
            $html .= " Timestamp of last kill: ".strftime('%Y-%m-%d %H:%M:%S', $fetchConfig->getLastKillTimestamp());
            $html .= "<br />\n";
            if ($fetchConfig->getParseMessages()) 
            {
                $html .= implode("<br />", $fetchConfig->getParseMessages())."<br />";
            }
        } 
        
        catch (Exception $ex) 
        {
            $html .= "Error reading feed: ".$fetchConfig->getUrl()."<br/>";
            $lastKillTimestampFormatted = strftime('%Y-%m-%d %H:%M:%S', $fetchConfig->getLastKillTimestamp());
            $html .= $ex->getMessage();
            $html .= ", Start time = ".$lastKillTimestampFormatted;
            $html .= "<br/><br/>";

        }
	
	return $html;
}